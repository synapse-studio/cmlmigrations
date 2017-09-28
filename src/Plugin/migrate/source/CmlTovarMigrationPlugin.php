<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\TovarParcer;
use Drupal\cmlmigrations\Controller\FixVariation;

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
      foreach ($source as $id => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $product = $row['product'];
          $offers = $row['offers'];
          $variations = FixVariation::findVariations($id);
          $rows[$id] = [
            'uuid' => $id,
            'title' => trim($product['Naimenovanie']),
            'catalog' => $product['Gruppy'][0],
            'changed' => time(),
            'variations' => $variations,
            'body_value' => FALSE,
            'body_format' => "wysiwyg",
          ];
          self::hasImage($rows, $product, $id);
        }
      }
    }
    $this->debug = TRUE;
    return $rows;
  }

  /**
   * HasImage.
   */
  public function hasImage(&$rows, $product, $id, $field = 'Kartinka') {
    $result = FALSE;
    if (isset($product[$field])) {
      $image = $product[$field];
      $query = \Drupal::entityQuery('file');
      $query->condition('uri', "%$image%", 'LIKE');
      $query->condition('status', 1);
      $query->sort('created', 'DESC');
      $fileIds = $query->execute();
      $fid = array_shift($fileIds);
      $rows[$id]['field_image'] = [
        'image' => $image,
        'fid' => $fid,
      ];
    }
    return $result;
  }

}
