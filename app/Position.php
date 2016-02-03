<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Должность сотрудника
 * Class Position
 * @package App
 */
class Position extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['name'];
}
