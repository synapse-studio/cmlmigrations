<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\CatalogParcer;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_catalog"
 * )
 */
class CmlCatalogMigrationPlugin extends MigrationsSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    $k = 0;
    $rows = [];
    $filepath = GetLastCml::filePath('import');
    $source = CatalogParcer::getRows($filepath);
    if ($source) {
      foreach ($source as $key => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $rows[$key] = [
            'uuid' => $row['id'],
            'name' => $row['name'],
            'weight' => $row['term_weight'],
          ];
          if (isset($row['parent']) && $row['parent']) {
            $rows[$key]['parent'] = $row['parent'];
          }
        }
      }
    }
    $this->debug = TRUE;
    return $rows;
  }

}
