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
   * @param [string] $workers
   */
  public static function checkFIO(array $workers, &$result)
  {
    // проверяем существует ли такой человек в бд
    $exists = self::whereIn('name', $workers)->pluck('name')->all();

    // отсеиваем всех кого мы нашли по ФИО
    $notExists = array_diff($workers, $exists);

    foreach($notExists as $fio) {
      // разбиваем фио на имя, фамилия, отчество
      // если получилось не 3 элемента, отмечаем ФИО как неправильный
      $fioExp = explode(' ', $fio);
      if(count($fioExp) != 3) {
        $result[] = $fio;
        continue;
      }

      // проверяем отдельно имя фамилию отчество
      $r = (
        FirstName::where('nameUA', $fioExp[1])->first() &&
        LastName::where('nameUA', $fioExp[0])->first() &&
        MiddleName::where('nameUA', $fioExp[2])->first()
      );
      if(!$r) $result[] = $fio;
    }
  }
}
