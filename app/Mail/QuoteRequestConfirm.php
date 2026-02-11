<?php

namespace App\Mail;

use App\Models\CustomQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestConfirm extends Mailable
{
    use Queueable, SerializesModels;

    public CustomQuoteRequest $quoteRequest;

    public function __construct(CustomQuoteRequest $quoteRequest)
    {
        $this->quoteRequest = $quoteRequest;
    }

    public function build()
    {
        return $this->from('noreply@you-priced.com', 'You-Price')
                    ->to($this->quoteRequest->user->email, $this->quoteRequest->user->name)
                    ->subject('Abbiamo ricevuto la tua richiesta di quotazione')
                    ->view('emails.quote-request-confirm');
    }
}
