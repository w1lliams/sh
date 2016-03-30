<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Organization extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['shortName', 'edrpou', 'fullName', 'postCode', 'address', 'email', 'phone'];

  /**
   * @return []
   */
  public function getPhoneAttribute()
  {
    if(empty($this->attributes['phone']))
      return [];
    return json_decode($this->attributes['phone']);
  }

  /**
   * @param array $value
   */
  public function setPhoneAttribute(array $value)
  {
    $this->attributes['phone'] = json_encode(array_filter($value));
  }

  /**
   * @return array|mixed
   */
  public function getEmailAttribute()
  {
    if(empty($this->attributes['email']))
      return [];
    return json_decode($this->attributes['email'], true);
  }

  /**
   * @param array $value
   */
  public function setEmailAttribute(array $value)
  {
    $this->attributes['email'] = json_encode(array_filter($value));
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function status()
  {
    return $this->belongsTo('App\Status');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function city()
  {
    return $this->belongsTo('App\City');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function opf()
  {
    return $this->belongsTo('App\Opf');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function type()
  {
    return $this->belongsTo('App\Type');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function chief()
  {
    return $this->belongsTo('App\Worker');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function organizations()
  {
    return $this->hasMany('App\Organization', 'parent_id');
  }

  /**
   * Поиск организаций по параметрам
   * @param $query
   * @param array $params
   * @return mixed
   * @internal param Request $request
   */
  public function scopeFilter($query, array $params)
  {
    $inCriteria = function($paramName, $fieldName) use ($query, $params) {
      if(isset($params[$paramName])) {
        $params[$paramName] = array_filter($params[$paramName]);
        if (!empty($params[$paramName]))
          $query->whereIn($fieldName, $params[$paramName]);
      }
    };

    $inCriteria('status', 'status_id');
    $inCriteria('opf', 'opf_id');
    $inCriteria('type', 'type_id');

    if(!empty($params['chief'])) {
      $query->whereHas('chief', function($query) use ($params) {
        $query->where('fio', 'like', "%{$params['chief']}%");
      });
    }

    if(!empty($params['city'])) {
      $query->whereHas('city', function($query) use ($params) {
        $query->where('name', 'like', "%{$params['city']}%");
      });
    }

    if(!empty($params['edrpou']))
      $query->where('edrpou', 'like', $params['edrpou'].'%');

    if(!empty($params['name'])) {
      $query->where(function ($query) use ($params) {
        $query->orWhere('fullName', 'like', "%{$params['name']}%");
        $query->orWhere('shortName', 'like', "%{$params['name']}%");
      });
    }

    return $query;
  }
}
