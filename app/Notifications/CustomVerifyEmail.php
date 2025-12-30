<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class CustomVerifyEmail extends BaseVerifyEmail
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifica il tuo indirizzo email - ' . config('app.name'))
            ->greeting('Ciao ' . $notifiable->name . '!')
            ->line('Grazie per esserti registrato su ' . config('app.name') . '.')
            ->line('Per completare la registrazione, verifica il tuo indirizzo email cliccando sul pulsante qui sotto:')
            ->action('Verifica Email', $verificationUrl)
            ->line('Questo link di verifica scadrà tra 15 minuti.')
            ->line('Se non hai creato un account, ignora questa email.')
            ->salutation('Cordiali saluti, Il team di ' . config('app.name'));
    }

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(15), // Link valido per 15 minuti
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }
}