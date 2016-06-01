<?php

namespace App\Console\Commands;

use App\Organization;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class Test extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'test_command:run';

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
   */
  public function handle()
  {
    $file = base_path('resources') . '/new_name.txt';
    $handle = fopen($file, 'r');
    if (!$handle) throw new FileNotFoundException($file);

    while (($line = fgets($handle)) !== false)
    {
      $data = explode("\t", $line);
      if(count($data) != 2) continue;
      $newName = trim($data[1]);
      if(empty($newName)) continue;

      DB::update('update organizations set fullName='. DB::connection()->getPdo()->quote($newName) . ' where edrpou='. DB::connection()->getPdo()->quote($data[0]) .' limit 1');
      var_dump(1);


//      if(++$j >= 2000) {
//        $values = implode(', ', array_map(function($v){
//          foreach($v as &$vv)
//            $vv = DB::connection()->getPdo()->quote($vv);
//          return '('. implode(',', $v) .')';
//        }, $rows));
//
//        DB::insert('insert ignore into `fio` (`name`, `nameRU`) values '. $values .' ON DUPLICATE KEY UPDATE c=c+1');
//        $j = 0;
//        $rows = [];
//      }
    }

    fclose($handle);
  }
}
