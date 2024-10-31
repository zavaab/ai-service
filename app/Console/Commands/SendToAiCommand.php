<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\AiController;

class SendToAiCommand extends Command
{
    protected $signature = 'send:to-ai';
    protected $description = 'Send data to AI Service every 10 seconds';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $aiController = new AiController();
        $aiController->sendToAi();
    }
}
