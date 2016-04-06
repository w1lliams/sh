<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class City extends Model
{
  use RevisionableTrait;

  /**
   * @var array
   */
  protected $fillable = ['name'];
}
