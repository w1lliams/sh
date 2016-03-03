<?php

namespace App\Console\Commands;

use App\Fio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ParseNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим имена, фамилии и отчества в одельные таблицы';

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
     * @return mixed
     */
    public function handle()
    {
        $bar = $this->output->createProgressBar(Fio::count());

        Fio::chunk(2000, function($rows) use ($bar) {
            $bar->advance(2000);
            $firstNames     = [];
            $lastNames      = [];
            $middleNames    = [];

            foreach($rows as $row) {
                // рабиваем фио на составляющие
                $row['name']   = explode(' ', $row['name']);
                $row['nameRU'] = explode(' ', $row['nameRU']);
                if(count($row['name']) != 3 || count($row['nameRU']) != 3)
                    continue;

                $firstNames[] = [
                    'nameUA' => $row['name'][1],
                    'nameRU' => $row['nameRU'][1],
                ];
                $lastNames[] = [
                  'nameUA' => $row['name'][0],
                  'nameRU' => $row['nameRU'][0],
                ];
                $middleNames[] = [
                  'nameUA' => $row['name'][2],
                  'nameRU' => $row['nameRU'][2],
                ];
            }

            DB::insert("INSERT IGNORE INTO `first_names` (`nameUA`, `nameRU`) VALUES {$this->_implodeValues($firstNames)} ON DUPLICATE KEY UPDATE c=c+1");
            DB::insert("INSERT IGNORE INTO `last_names` (`nameUA`, `nameRU`) VALUES {$this->_implodeValues($lastNames)} ON DUPLICATE KEY UPDATE c=c+1");
            DB::insert("INSERT IGNORE INTO `middle_names` (`nameUA`, `nameRU`) VALUES {$this->_implodeValues($middleNames)} ON DUPLICATE KEY UPDATE c=c+1");
        });
    }

    /**
     * @param array $values
     * @return string
     */
    private function _implodeValues(array $values)
    {
        return implode(', ', array_map(function($v){
            foreach($v as &$vv)
                $vv = DB::connection()->getPdo()->quote($vv);
            return '('. implode(',', $v) .')';
        }, $values));
    }
}
