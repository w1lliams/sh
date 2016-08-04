<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['text', 'worker_id', 'file'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function worker()
  {
    return $this->belongsTo('App\Worker');
  }
}
