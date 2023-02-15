<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ParserJob;

class Parse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
	dispatch(new ParserJob);
        return Command::SUCCESS;
    }
}
