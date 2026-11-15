<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $newPassword;

    public function __construct($name, $newPassword)
    {
        $this->name = $name;
        $this->newPassword = $newPassword;
    }

    public function build()
    {
        return $this->subject('Cấp lại mật khẩu hệ thống MathMaster')
                    ->view('emails.new_password'); // Trỏ tới file giao diện
    }
}
