<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Opf extends Model
{
  use RevisionableTrait;

  /**
   * @var string
   */
  protected $table = 'opf';

  /**
   * @var array
   */
  protected $fillable = ['name'];
}
