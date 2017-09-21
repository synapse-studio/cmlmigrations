<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\TovarParcer;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_product"
 * )
 */
class CmlProductMigrationPlugin extends MigrationsSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    $k = 0;
    $rows = [];
    $source = FALSE;
    // $filepath = GetLastCml::filePath('import');
    // $source = TovarParcer::getRows($filepath);
    if ($source) {
      foreach ($source as $key => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $id = $row['Id'];
          $product = $row['offers'];
          $offers = $row['product'];
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
    $this->debug = FALSE;
    return $rows;
  }

}
