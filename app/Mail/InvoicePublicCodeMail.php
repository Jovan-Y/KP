<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Invoice; // Penting: Impor model Invoice

class InvoicePublicCodeMail extends Mailable implements ShouldQueue // Menggunakan ShouldQueue untuk antrean
{
    use Queueable, SerializesModels;

    public $invoice; // Properti publik untuk mengakses objek Invoice di view email

    /**
     * Create a new message instance.
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Upload Gambar Faktur Anda: ' . $this->invoice->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoices.public_code', // Nama view Markdown untuk email
            // Anda juga bisa pakai view: 'emails.invoices.public_code_plain' jika HTML biasa
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}