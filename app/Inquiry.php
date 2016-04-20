<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Статус запроса
 * Class Inquiry
 * @package App
 */
class Inquiry extends Model
{
  use RevisionableTrait;

  /**
   * @var array
   */
  protected $fillable = ['name'];
}
