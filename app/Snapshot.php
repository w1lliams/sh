<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
  protected $fillable = ['name', 'edrpou'];

  protected $dates = ['date'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function organization()
  {
    return $this->belongsTo('App\Organization');
  }
}