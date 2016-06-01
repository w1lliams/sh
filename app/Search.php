<?php

namespace App;

class Search
{
  /**
   * Поиск организаций
   * @param  string $query
   * @param  integer $limit
   * @return \Elasticquent\ElasticquentResultCollection
   */
  public function searchOrganizations(string $query, int $limit = 10)
  {
    return Organization::searchByQuery([
      'bool' => [
        'should' => [
          ['match' => ['fullName' => $query]],
          ['match' => ['shortName' => $query]],
        ]
      ]
    ]);
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