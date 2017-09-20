<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\OffersParcer;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_offer"
 * )
 */
class CmOfferMigrationPlugin extends MigrationsSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    $k = 0;
    $rows = [];
    $filepath = GetLastCml::filePath('import');
    $source = OffersParcer::getRows($filepath);
    if ($source) {
      foreach ($source as $key => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $id = $row['Id'];
          $rows[$id] = [
            'uuid' => $row['Id'],
            'title' => $row['Naimenovanie'],
            'unit' => $row['BazovaaEdinica'],
            'Kolicestvo' => $row['Kolicestvo'],
            'Sklad' => $row['Sklad'],
            'price' => $row['Ceny'][0]['ЦенаЗаЕдиницу'],
            'ccode' => 'RUB',
          ];
        }
      }
    }
    $this->debug = TRUE;
    return $rows;
  }

}
