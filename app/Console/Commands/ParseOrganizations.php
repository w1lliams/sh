<?php

namespace App\Console\Commands;

use App\Opf;
use App\Status;
use Illuminate\Console\Command;

class ParseOrganizations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'organizations:parse {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсим организации из файла';

    /**
     * Содержание файла
     * @var array
     */
    protected $data = [];

    protected $lists = [
        'Stan' => Status::class,
        'Opf' => Opf::class,

    ];

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
        $this->parseFile($this->argument('file'));
        $this->checkLists();
        $this->createOrganizations();
    }

    /**
     * Парсим содержимое файла и ложим все в двумерный массив
     * @param $file
     * @return array
     */
    protected function parseFile($file)
    {
        $content = file_get_contents($file);
        $rows = explode('==============', $content);
        $result = [];
        foreach($rows as $row) {
            if(empty($row)) continue;
            $fields = [];
            foreach(explode("\n", $row) as $line) {
                if(preg_match('~^([\s\w]+):(.*)$~u', $line, $matches)) {
                    $fields[trim($matches[1])] = trim($matches[2]);
                }
            }
            if(!empty($fields))
                $result[] = $fields;
        }
        $this->data = $result;
    }

    /**
     * Проверяем новые значения в списках, и если есть, добавляем в бд
     */
    protected function checkLists()
    {
        foreach ($this->lists as $field => $class) {
            $this->info("Новые значения <{$field}>:");
            $notFound = [];
            $values = $class::all()->transform(function($item){
                return mb_strtolower($item->name);
            });

            foreach($this->data as $row) {
                if(!isset($row[$field])) continue;
                $val = mb_strtolower($row[$field]);
                if (!isset($notFound[$val]) && $values->search($val) === false) {
                    $notFound[$val] = true;
                    $this->line("\t{$val}");
                }
            }

            if(count($notFound) > 0 && $this->confirm('Добавить эти значения в БД?')) {
                foreach($notFound as $name => $v) {
                    $name = mb_convert_case($name, MB_CASE_TITLE);
                    $class::create(['name' => $name]);
                }
            }
        }
    }

    protected function createOrganizations()
    {

    }
}
