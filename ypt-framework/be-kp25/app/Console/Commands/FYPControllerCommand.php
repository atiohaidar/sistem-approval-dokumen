<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\FYP\FYPController;

class FYPControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fyp:register-controller {namespace} {appid}';

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
        $create = FYPController::create([
            'namespaces' => $this->argument('namespace'),
            'application_id' => $this->argument('appid'),
            'created_by' => $this->argument('appid'),
        ]);

        if ($create) {
            $this->info('Success register controller');
        } else {
            $this->info('Failed register controller');
        }
    }
}
