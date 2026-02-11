<?php

namespace App\Mail;

use App\Models\CustomQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuoteRequestAdmin extends Mailable
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
                    ->to('noreply@you-priced.com')
                    ->subject('Nuova richiesta di quotazione personalizzata #' . $this->quoteRequest->id)
                    ->view('emails.quote-request-admin');
    }
}
