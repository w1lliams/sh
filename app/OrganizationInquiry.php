<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Venturecraft\Revisionable\RevisionableTrait;

/**
 * id - organization id
 */
class OrganizationInquiry extends Model
{
  use RevisionableTrait;

  /**
   * @var array
   */
  protected $fillable = ['note'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\belongsTo
   */
  public function inquiry()
  {
    return $this->belongsTo('App\Inquiry');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\belongsTo
   */
  public function organization()
  {
    return $this->belongsTo('App\Organization');
  }
}
