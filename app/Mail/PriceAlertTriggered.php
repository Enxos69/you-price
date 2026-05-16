<?php

namespace App\Mail;

use App\Models\PriceAlert;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PriceAlertTriggered extends Mailable
{
    use Queueable, SerializesModels;

    public PriceAlert $alert;

    public function __construct(PriceAlert $alert)
    {
        $this->alert = $alert;
    }

    public function build()
    {
        $departure = $this->alert->departure;
        $product   = $departure?->product;

        $subject = $this->alert->alert_type === 'percentage_discount'
            ? "Sconto del {$this->alert->percentage_threshold}% raggiunto su {$product?->cruise_name}"
            : "Prezzo obiettivo raggiunto su {$product?->cruise_name}";

        return $this->from('noreply@you-priced.com', 'You-Price')
                    ->subject($subject)
                    ->view('emails.price-alert-triggered');
    }
}
