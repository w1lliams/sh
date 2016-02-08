<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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
    $this->attributes['phone'] = json_encode($value);
  }

  /**
   * @return []
   */
  public function getEmailAttribute()
  {
    if(empty($this->attributes['email']))
      return [];
    return json_decode($this->attributes['email']);
  }

  /**
   * @param array $value
   */
  public function setEmailAttribute(array $value)
  {
    $this->attributes['email'] = json_encode($value);
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
}
