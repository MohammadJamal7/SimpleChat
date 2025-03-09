<?php

namespace App\Console\Commands;

use App\Events\NewChatMessage;
use Illuminate\Console\Command;

class testpusher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testpusher {--data=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $data = $this->option('data');
        broadcast(new NewChatMessage($data));
        dd('done');
    }
}
