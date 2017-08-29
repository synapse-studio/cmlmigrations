<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_catalog"
 * )
 */
class CmlCatalogMigrationPlugin extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    return [
      'uuid' => [
        'type' => 'string',
        'alias' => 'uuid',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    $xml = $fetcher_result->getRaw();
    $raws  = CatalogParcer::parce($xml);
    foreach ($raws as $key => $raw) {
      $fields[$key] = [
        'uuid' => $raw['id'],
        'name' => $raw['name'],
        'weight' => $raw['term_weight'],
        'parent_uuid' => [trim($raw['parent'])],
      ];
    }
    return $fields;
  }

}
