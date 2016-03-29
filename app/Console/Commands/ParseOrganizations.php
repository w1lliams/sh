<?php

namespace App\Console\Commands;

use App\Opf;
use App\Organization;
use App\Status;
use App\City;
use App\Worker;
use App\Type;
use Illuminate\Console\Command;

class ParseOrganizations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:organizations {file}';

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

    /**
     * Списки которые нужно проверить на нновые значения до начала парсинга
     * @var array
     */
    protected $lists = [
        'Stan' => Status::class,
        'Opf' => Opf::class,
        'Address|||~,\s*(?:м.|місто)\s*(\w+)\s*,~ui' => City::class  // передаем регулярку, которой вырезать нужное значение

    ];

     protected $misc = [];

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
                    $key   = trim($matches[1]);
                    $value = trim($matches[2]);

                    if(isset($fields[$key])) {
                        if(!is_array($fields[$key])) {
                            $old = $fields[$key];
                            $fields[$key] = [$old];
                        }
                        $fields[$key][] = $value;
                    }
                    else $fields[$key] = $value;
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

            // определяем функцию поиска новых значений (с регуляркой или без)
            $tmp = explode('|||', $field);
            if(count($tmp) > 1) { // передали регулярное выражение
                $searchFunc = function($row) use ($tmp, &$values, $field, &$notFound) {
                    $field = $tmp[0];
                    $regExp = $tmp[1];
                    if(!isset($row[$field])) return;
                    if(!preg_match($regExp, $row[$field], $matches)) return;

                    $val = mb_strtolower($matches[1]);
                    if (!isset($notFound[$val]) && $values->search($val) === false) {
                        $notFound[$val] = true;
                        $this->line("\t{$val}");
                    }
                };
            } else {
                $searchFunc = function($row) use (&$values, $field, &$notFound) {
                    if(!isset($row[$field])) return;
                    $val = mb_strtolower($row[$field]);
                    if (!isset($notFound[$val]) && $values->search($val) === false) {
                        $notFound[$val] = true;
                        $this->line("\t{$val}");
                    }
                };
            }

            array_walk($this->data, $searchFunc);

            if(count($notFound) > 0 && $this->confirm('Добавить эти значения в БД?')) {
                foreach($notFound as $name => $v) {
                    $name = mb_convert_case($name, MB_CASE_TITLE);
                    $class::create(['name' => $name]);
                }
            }
        }
    }

    /**
     *
     */
    protected function createOrganizations()
    {
        $this->misc['statuses']  = Status::all()->keyBy('name');
        $this->misc['opfs']      = Opf::all()->keyBy('name');
        $this->misc['cities']    = City::all()->keyBy('name');
        $this->misc['types']     = Type::all()->keyBy('name');

        foreach($this->data as $row)
            $this->createOrganization($row);
    }

    /**
     * Создание организации
     * @param array $data
     * @param Organization $parent
     * @return Organization|null
     */
    protected function createOrganization(array $data, Organization $parent = null)
    {
        $organization = new Organization;
        // если есть родительская организация, берем статус из нее
        if($parent === null)
            $organization->status()->associate($this->misc['statuses']->get(
              mb_convert_case(array_get($data, 'Stan'), MB_CASE_TITLE)
            ));
        else $organization->status()->associate($parent->status);

        $organization->opf()->associate($this->misc['opfs']->get(mb_convert_case(array_get($data, 'Opf'), MB_CASE_TITLE)));
        // если передан тип значит это подорганизация, иначе это главная организация и она - юр лицо
        $organization->type()->associate(array_get($data, 'type', $this->misc['types']['Юридична особа']));
        $organization->fullName  = array_get($data, 'Name', '');
        $organization->shortName = array_get($data, 'ShortName', '');
        $organization->edrpou    = array_get($data, 'Kod', '');
        $organization->address   = array_get($data, 'Address', '');

        if(!empty($organization->address)) {
            // почтовый индекс
            if(preg_match('~^(\d+),~u', $organization->address, $matches))
                $organization->postCode = $matches[1];
            // вытягиваем город из адреса
            if(preg_match('~,\s*(?:м.|місто)\s*(\w+)\s*,~ui', $organization->address, $matches))
                $organization->city()->associate($this->misc['cities'][mb_convert_case($matches[1], MB_CASE_TITLE)]);
        }

        if(!$organization->save())
            return null;

        // создаем под организации
        if(isset($data['Pidrozdil'])) {
            if(is_array($data['Pidrozdil']))
                foreach($data['Pidrozdil'] as $subOrg) {
                    $subOrgData = $this->parseSubOrganization($subOrg);
                    $organization->organizations()->save($this->createOrganization($subOrgData, $organization));
                }
            else {
                $subOrgData = $this->parseSubOrganization($data['Pidrozdil']);
                $organization->organizations()->save($this->createOrganization($subOrgData, $organization));
            }
        }

        // если указан руководитель, добавляем его в БД
        if(isset($data['Kerivnik'])) {
            $worker = new Worker;
            $worker->position = 'Керівник';

            // поднимаем первые буквы в вверхний регист, остальные в нижний
            $worker->fio = implode(' ', array_map(function ($item) {
                $parts = explode('-', $item);
                if(count($parts) == 1)
                    return mb_convert_case($item, MB_CASE_TITLE);
                else {
                    return implode('-', array_map(function($it){
                        return mb_convert_case($it, MB_CASE_TITLE);
                    }, $parts));
                }
            }, explode(' ', mb_strtolower($data['Kerivnik']))));
            $worker->organization()->associate($organization);
            $worker->save();
            $organization->chief()->associate($worker);
            $organization->save();
        }

        return $organization;
    }

    /**
     * Распаршиваем строку с подрозделением
     * @param string $sourceData
     * @return array
     */
    protected function parseSubOrganization($sourceData)
    {
        $sourceData = explode('|', $sourceData);
        return [
            'type'      => $this->misc['types']['Підрозділ'],
            'Name'      => $sourceData[0],
            'Kod'       => $sourceData[1],
            'Address'   => $sourceData[2]
        ];
    }
}
