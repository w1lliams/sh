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
}
