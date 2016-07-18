<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;
use Elasticquent\ElasticquentTrait;

class Organization extends Model
{
  use RevisionableTrait;
  use ElasticquentTrait;

  /**
   * @var array
   */
  protected $fillable = ['shortName', 'edrpou', 'fullName', 'postCode', 'address', 'email', 'phone', 'chief'];

  /**
   * elastic search mapping
   * @var array
   */
  protected $mappingProperties = [
    'fullName' => [
      'type' => 'string',
      'index' => 'not_analyzed'
    ],

    'shortName' => [
      'type' => 'string',
      'index' => 'not_analyzed'
    ],

    'address' => [
      'type' => 'string',
      'index' => 'not_analyzed'
    ],

    'search' => [
      'type' => 'string',
      'analyzer' => 'index_ngram',
      'search_analyzer' => 'search_ngram'
    ]
  ];

  /**
   * @return array
   */
  public function getIndexDocumentData()
  {
    return [
      'id' => $this->id,
      'shortName' => $this->shortName,
      'fullName' => $this->fullName,
      'search' => $this->shortName .' '. $this->fullName,
      'address' => $this->address
    ];
  }

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
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function organizations()
  {
    return $this->hasMany('App\Organization', 'parent_id');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function parent()
  {
    return $this->belongsTo('App\Organization', 'parent_id');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function snapshots()
  {
    return $this->hasMany('App\Snapshot');
  }

  /**
   * Последний снимок сотрудников
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function snapshot()
  {
    return $this->belongsTo('App\Snapshot');
  }

  /**
   * Поиск организаций по параметрам
   * @param $query
   * @param array $params
   * @param bool $withDepartments
   * @return mixed
   * @internal param Request $request
   */
  public function scopeFilter($query, array $params, $withDepartments = false)
  {
    $inCriteria = function($paramName, $fieldName) use ($query, &$params) {
      if(isset($params[$paramName])) {
        if (!empty($params[$paramName])) {
          $query->whereIn($fieldName, $params[$paramName]);
        }
      }
    };

    if(!$withDepartments && !isset($params['type']))
      $query->where('type_id', 1);

    $inCriteria('status', 'status_id');
    $inCriteria('opf', 'opf_id');
    $inCriteria('type', 'type_id');

    if(isset($params['city']) && ($key = array_search('nocity', $params['city'])) !== false) {
      $params['city'][] = '';
      unset($params['city'][$key]);
      $inCriteria('city', 'city_id');
      $query->orWhereNull('city_id');
    }
    else {
      $inCriteria('city', 'city_id');
    }

    if(!empty($params['chief'])) {
      $query->where('chief', 'like', "%{$params['chief']}%");
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
