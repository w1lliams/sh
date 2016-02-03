<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['fio'];


  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function position()
  {
    return $this->belongsTo('App\Position');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function organization()
  {
    return $this->belongsTo('App\Organization');
  }
}
