<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['fio', 'position', 'department', 'subDepartment'];


  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function snapshot()
  {
    return $this->belongsTo('App\Snapshot');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function organization()
  {
    return $this->belongsTo('App\Organization');
  }
}
