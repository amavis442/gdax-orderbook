<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

use NotificationChannels\Telegram\TelegramChannel;
use NotificationChannels\Telegram\TelegramMessage;


class Telegram extends Notification
{
    use Queueable;

    protected $data;
    
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TelegramChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toTelegram($notifiable)
    {
        $data = $this->data;
        $content = "*".$data['trade']. ' '.$data['wallet']."*\n".
                'Koers: '. number_format($data['coinprice'],2)."\n".
                'Aantal: '. $data['amount']. "\n".
                
                'Handelsprijs: *'.number_format($data['tradeprice'],2). "*\n".
                '_Kosten: '. number_format($data['fee'],2) ."_\n".
                'Aangemaakt op: '. $data['created_at']
                ;
        
        return TelegramMessage::create()
            //->to($this->user->telegram_user_id) // Optional.
                ->to('67592636')
            ->content($content); // Markdown supported.
            //->button('View Invoice', $url); // Inline Button
    }

    
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}