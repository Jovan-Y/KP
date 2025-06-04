<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class SupplierOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $otpCode;
    public $otpExpiresAt;

    public function __construct(string $otpCode, Carbon $otpExpiresAt)
    {
        $this->otpCode = $otpCode;
        $this->otpExpiresAt = $otpExpiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi OTP Anda untuk Unggah Gambar',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.suppliers.otp',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}