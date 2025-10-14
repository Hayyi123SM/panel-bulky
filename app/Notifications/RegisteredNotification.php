<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $password;

    public function __construct($user, $password = null)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function via($notifiable): array
    {
        $channels = ['mail'];
        if ($this->password) {
            $channels[] = 'whatsapp';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Selamat Datang di Bulky.id')
            ->greeting("Halo {$notifiable->name},")
            ->line('Terima kasih telah mendaftar di bulky.id! Kami senang menyambut Anda ke dalam komunitas kami.')
            ->line('Dengan bulky.id, Anda akan mendapatkan akses ke berbagai fitur menarik yang akan membantu Anda menemukan produk berkualitas, mendapatkan penawaran terbaik, atau melacak pesanan Anda.');

        if ($this->password) {
            $mail->line('Akun Anda telah dibuat oleh admin/kasir. Berikut password Anda: ' . $this->password);
        }

        $mail->line('Jika Anda memiliki pertanyaan atau membutuhkan bantuan, jangan ragu untuk menghubungi tim dukungan pelanggan kami di admin@bulky.id.')
            ->line('Kami berharap Anda menikmati pengalaman berbelanja di bulky.id!')
            ->salutation('Salam hangat, Tim bulky.id');

        return $mail;
    }

    public function toWhatsApp($notifiable)
    {
        return "Selamat datang di Bulky.id! Akun Anda telah dibuat.\nEmail: {$notifiable->email}\nPassword: {$this->password}\nSilakan login dan ubah password Anda setelah berhasil masuk.";
    }
}
