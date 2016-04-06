<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * Статусы предприятия
 * Class Status
 * @package App
 */
class Status extends Model
{
  use RevisionableTrait;

  /**
   * @var array
   */
  protected $fillable = ['name'];
}
