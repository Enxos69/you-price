<?php

namespace App\Console\Commands;

use App\Mail\PriceAlertTriggered;
use App\Models\PriceAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckPriceAlertsCommand extends Command
{
    protected $signature = 'alerts:check
                            {--dry-run : Controlla senza salvare né inviare email}';

    protected $description = 'Controlla gli alert attivi e invia notifiche email se il target è raggiunto';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $alerts = PriceAlert::query()
            ->where('is_active', true)
            ->whereHas('departure', fn($q) => $q->where('dep_date', '>', now()))
            ->with(['departure.product', 'user'])
            ->get();

        if ($alerts->isEmpty()) {
            $this->info('Nessun alert attivo da controllare.');
            return self::SUCCESS;
        }

        $this->info("Alert da controllare: {$alerts->count()}");

        $checked   = 0;
        $triggered = 0;
        $failed    = 0;

        foreach ($alerts as $alert) {
            $shipId = $alert->departure->product->ship_id ?? null;

            if (! $shipId) {
                $this->line("  [SKIP] Alert #{$alert->id} — nave non trovata per departure {$alert->departure_id}");
                continue;
            }

            // Traduce cruisehost_cat → cl_cat (stesso mapping usato in store())
            $clCats = DB::table('ship_categories')
                ->where('ship_id', $shipId)
                ->where('cruisehost_cat', $alert->category_code)
                ->pluck('cl_cat');

            if ($clCats->isEmpty()) {
                $this->line("  [SKIP] Alert #{$alert->id} — categoria '{$alert->category_code}' non mappata per nave {$shipId}");
                continue;
            }

            // Prezzo minimo tra i cl_cat corrispondenti (stesso approccio di store())
            $currentPrice = DB::table('price_history')
                ->whereIn('id', function ($sub) use ($alert) {
                    $sub->select(DB::raw('MAX(id)'))
                        ->from('price_history')
                        ->where('departure_id', $alert->departure_id)
                        ->groupBy('category_code');
                })
                ->whereIn('category_code', $clCats)
                ->min('price');

            if ($currentPrice === null) {
                $this->line("  [SKIP] Alert #{$alert->id} — nessun prezzo per cl_cat [" . $clCats->implode(', ') . "]");
                continue;
            }

            $alert->current_price   = $currentPrice;
            $alert->last_checked_at = now();

            if (! $alert->isPriceReached() || $alert->notification_sent) {
                if (! $dryRun) {
                    $alert->save();
                }
                $checked++;
                continue;
            }

            // Target raggiunto e notifica non ancora inviata
            $this->line("  [TRIGGERED] Alert #{$alert->id} — prezzo {$alert->current_price} ≤ target {$alert->target_price} (utente #{$alert->user_id})");

            if ($dryRun) {
                $triggered++;
                continue;
            }

            try {
                $alert->load(['departure.product.ship', 'departure.product.cruiseLine']);
                Mail::to($alert->user->email)->send(new PriceAlertTriggered($alert));

                $alert->notification_sent         = true;
                $alert->last_notification_sent_at = now();
                $alert->save();

                $triggered++;

                Log::info('[AlertsCheck] Notifica inviata', [
                    'alert_id'      => $alert->id,
                    'user_id'       => $alert->user_id,
                    'departure_id'  => $alert->departure_id,
                    'category_code' => $alert->category_code,
                    'current_price' => $alert->current_price,
                    'target_price'  => $alert->target_price,
                ]);

            } catch (\Throwable $e) {
                $failed++;
                $alert->save();

                Log::error('[AlertsCheck] Invio email fallito', [
                    'alert_id' => $alert->id,
                    'error'    => $e->getMessage(),
                ]);

                $this->error("  Invio email fallito per alert #{$alert->id}: {$e->getMessage()}");
            }

            $checked++;
        }

        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $this->info("{$prefix}Completato — controllati: {$checked}, notifiche inviate: {$triggered}, errori: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
