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

  /**
   * Helper to understend problem.
   */
  public function fixVariation($id) {
    // #print "$id\n"; //Debug.
    $storage = \Drupal::entityManager()->getStorage('commerce_product_variation');
    $variation = $storage->load($id);
    if ($variation) {
      $title = $variation->title->value;
      $product_id = $variation->product_id->getValue();
      if (!$title || empty($product_id)) {
        $product = \Drupal::entityManager()->getStorage('commerce_product')->load($id);
        if ($product) {
          $pid = $variation->get('product_id');
          $pid->setValue($product);
          $variation->save();
        }
      }
    }
  }

}
