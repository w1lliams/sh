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
      'match' => [
        'search' => [
          'query' => $query,
          'operator' => 'and'
        ]
      ]
    ], null, null, $limit);
  }

  /**
   * Поиск сотрудников
   * @param  string $query
   * @param  integer $limit
   * @return \Elasticquent\ElasticquentResultCollection
   */
  public function searchWorkers(string $query, int $limit = 10)
  {
    return Worker::searchByQuery([
      'match' => [
        'search' => [
          'query' => $query,
          'operator' => 'and'
        ]
      ]
    ], null, null, $limit)->load('organization');

//    $result = Worker::searchByQuery(
//      ['match' => [
//        'search' => [
//          'query' => $query,
//          'operator' => 'and',
//        ]
//      ]],
//
//      ['orgs' => [
//        'terms' => [
//          'field' => 'organization_id',
//          'size' => 10
//        ],
//        'aggregations' => [
//          'fio' => [
//            'terms' => [
//              'field' => 'fio',
//            ],
//            'aggregations' => [
//              'top' => [
//                'top_hits' => [
//                  'size' => 1
//                ]
//              ]
//            ]
//          ]
//        ]
//      ]], null, 0
//    );
//
//    $ids = [];
//    $aggregations = $result->getAggregations();
//    foreach($aggregations['orgs']['buckets'] as $data) {
//      foreach($data['fio']['buckets'] as $data2) {
//        $ids[] = $data2['top']['hits']['hits'][0]['_id'];
//      }
//    }
//    $ids = array_slice($ids, 0, 20);
//
//    return Worker::whereIn('id', $ids)->with('organization')->get();
  }
}