<?php

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

class Worker extends Model
{
  use RevisionableTrait;
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

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function notes()
  {
    return $this->hasMany('App\Note');
  }

  /**
   * @return Collection
   */
  public function publications()
  {
    return $this->notes()->where('type', 'publications');
  }

  /**
   * @return Collection
   */
  public function finance()
  {
    return $this->notes()->where('type', 'finance');
  }

  /**
   * @return Collection
   */
  public function realty()
  {
    return $this->notes()->where('type', 'realty');
  }

  /**
   * @return Collection
   */
  public function cars()
  {
    return $this->notes()->where('type', 'cars');
  }

  /**
   * @return Collection
   */
  public function law()
  {
    return $this->notes()->where('type', 'law');
  }

  /**
   * @return Collection
   */
  public function business()
  {
    return $this->notes()->where('type', 'business');
  }

  /**
   * Собираем работников в древовидную структуру
   * @param  Collection $workers
   * @return array
   */
  public static function structure($workers) {
    $result = [
      'main' => [
        'workers' => []
      ]
    ];

    foreach($workers as $worker) {
      if(empty($worker['department'])) {
        $result['main']['workers'][] = $worker;
      } else {
        if(empty($worker['subDepartment']))
          $result[$worker['department']]['workers'][] = $worker;
        else
          $result[$worker['department']]['sub'][$worker['subDepartment']][] = $worker;
      }
    }

    return $result;
  }
}
