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

    protected $ledger;
        
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ledger = [])
    {
        $this->ledger = $ledger;
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
        $ledger = $this->ledger;
	$mydate = \Carbon\Carbon::now('Europe/Amsterdam')->format('d-m-Y H:i:s');
	$content = "_Report van ".$mydate."_\n----------------\n";
        foreach ($ledger['wallets'] as $wallet) {
            $content .= '*'.$wallet['name'].' : '.$wallet['koers']."*\n". 
                    'Balance : '.$wallet['balance']."\n". 
                    'Waarde : '.$wallet['waarde']."\n-------------------------\n";
        }
        $content .= '*Portfolio: '.$ledger['portfolio'].'*';
        
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
