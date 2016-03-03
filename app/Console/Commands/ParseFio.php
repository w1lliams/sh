<?php

namespace App\Console\Commands;

use App\Fio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class ParseFio extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'parse:fio {file}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Command description';

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
    $i = 0;
    $bar = $this->output->createProgressBar(52884416);
    $bar->setFormat('%percent:3s%% [%bar%] %estimated%');
    $handle = fopen($this->argument('file'), 'r');
    if (!$handle) throw new FileNotFoundException($this->argument('file'));

    $j = 0;
    $rows = [];
    while (($line = fgets($handle)) !== false)
    {
      $bar->advance();
      if(++$i <= 1) continue;
      mb_internal_encoding('UTF-8');
      $line = mb_convert_encoding($line, 'utf-8', 'cp1251');
      $cols = explode('|', $line);
      if(count($cols) != 8) {
        dd($line);
        continue;
      }

      $rows[] = [
        'name'   => mb_convert_case("{$cols[1]} {$cols[2]} {$cols[3]}", MB_CASE_TITLE, "UTF-8"),
        'nameRU' => mb_convert_case("{$cols[4]} {$cols[5]} {$cols[6]}", MB_CASE_TITLE, "UTF-8")
      ];

      if(++$j >= 2000) {
        $values = implode(', ', array_map(function($v){
          foreach($v as &$vv)
            $vv = DB::connection()->getPdo()->quote($vv);
          return '('. implode(',', $v) .')';
        }, $rows));

        DB::insert('insert ignore into `fio` (`name`, `nameRU`) values '. $values .' ON DUPLICATE KEY UPDATE c=c+1');
        $j = 0;
        $rows = [];
      }
    }

    fclose($handle);
    $bar->finish();
  }
}
