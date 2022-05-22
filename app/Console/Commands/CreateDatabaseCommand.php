<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for creating both application database & test database configured in environment file';

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
        $applicationDatabaseName = env('DB_DATABASE', 'reo');
        DB::statement('CREATE DATABASE IF NOT EXISTS '. $applicationDatabaseName);

        $testDatabaseName = env('TEST_DB_DATABASE', 'reo_testing');
        DB::statement('CREATE DATABASE IF NOT EXISTS '. $testDatabaseName);
    }
}
