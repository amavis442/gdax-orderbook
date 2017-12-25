<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\User;
use App\Notifications\Telegram;


class SendTelegramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $balances;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data = [], $balances = [])
    {
        $this->data = $data;
        $this->balances = $balances;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find(1);

        $user->notify(new Telegram($this->data, $this->balances));
    }
}
