<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HKA_Everymin_Job extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hka_job:everymin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Respectively execute jobs in hka_scheduled_jobs table every minute.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        
        $this->info('Successfully run.');
    }
}
