<?php

namespace App;

class Search
{
  /**
   * Поиск организаций
   * @param  string  $query
   * @param  integer $limit
   */
  public function searchOrganizations(string $query, int $limit = 10)
  {
    $organizations = Organization::orWhere('fullName', 'like', "%{$query}%")
      ->orWhere('shortName', 'like', "%{$query}%")
      ->limit($limit)
      ->with('opf')
      ->get();

    return $organizations;
  }

  /**
   * Поиск сотрудников
   * @param  string  $query
   * @param  integer $limit
   */
  public function searchWorkers(string $query, int $limit = 10)
  {
    $workers = Worker::limit($limit)->get();
    return $workers;
  }
}