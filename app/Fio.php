<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fio extends Model
{
  /**
   * @var string
   */
  protected $table = 'fio';

  /**
   * @var array
   */
  protected $fillable = ['name', 'nameRU'];

  /**
   * Проверяем на "валидность" ФИО  работников
   * @param array $workers
   * @param $result
   */
  public static function checkFIO(array $workers, &$result)
  {
    // проверяем существует ли такой человек в бд
    $exists = self::whereIn('name', $workers)->pluck('name')->all();

    // отсеиваем всех кого мы нашли по ФИО
    $notExists   = array_diff($workers, $exists);
    $firstNames  = [];
    $lastNames   = [];
    $middleNames = [];

    foreach($notExists as $fio) {
      // разбиваем фио на имя, фамилия, отчество
      // если получилось не 3 элемента, отмечаем ФИО как неправильный
      $fioExp = explode(' ', $fio);
      if(count($fioExp) != 3) {
        $result[] = $fio;
        continue;
      }

      // собираем отдельно имена, фамилии и отчества
      $firstNames[$fioExp[1]]  = $fio;
      $lastNames[$fioExp[0]]   = $fio;
      $middleNames[$fioExp[2]] = $fio;
    }

    static::_checkNames($firstNames, FirstName::class, $result);
    static::_checkNames($lastNames, LastName::class, $result);
    static::_checkNames($middleNames, MiddleName::class, $result);
  }

  /**
   * Вынес поиск по таблицам "firstName", "lastName"... отдельно
   * @param array $names
   * @param string $Model
   * @param array $result
   */
  private static function _checkNames(array $names, $Model, &$result)
  {
    $existsNames = call_user_func($Model.'::whereIn', 'nameUA', array_keys($names))->pluck('nameUA')->all();
    if(count($existsNames) != count($names)) {
      foreach($names as $name => $fio) {
        if(!in_array($name, $existsNames))
          $result[] = $fio;
      }
    }
  }
}
