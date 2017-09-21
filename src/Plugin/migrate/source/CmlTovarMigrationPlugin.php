<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\TovarParcer;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_tovar"
 * )
 */
class CmlTovarMigrationPlugin extends MigrationsSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    $k = 0;
    $rows = [];
    $filepath = GetLastCml::filePath('import');
    $source = TovarParcer::getRows($filepath);
    if ($source) {
      foreach ($source as $key => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $product = $row['offers'];
          $offers = $row['product'];
          $id = $product['Id'];
          $rows[$id] = [
            'uuid' => $product['Id'],
            'title' => $product['Naimenovanie'],
            'catalog' => $product['Gruppy'][0],
            'changed' => time(),
            'variations' => $product['Id'],
            'body_value' => FALSE,
            'body_format' => "wysiwyg",
          ];
        }
      }
    }
    $this->debug = TRUE;
    return $rows;
  }

}
