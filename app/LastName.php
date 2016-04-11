<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LastName extends Model
{
  protected $table = 'last_names';

  /**
   * Ищем перевод с русс. на укр.
   * @param $name
   * @return null|string
   */
  public static function translate($name)
  {
    $doc = self::where('nameRU', $name)->orderBy('c', 'desc')->first();
    if(is_null($doc)) return null;
    return $doc->nameUA;
  }
}