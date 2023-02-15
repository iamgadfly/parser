<?php

namespace App\Console\Commands;

use App\Http\Controllers\ParserController;
use Illuminate\Console\Command;

class ParseByLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse_one {--product_id=}';

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
        ParserController::parseByOneId($this->option('product_id'));
        return Command::SUCCESS;
    }
}
