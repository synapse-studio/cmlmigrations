<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\OffersParcer;
use Drupal\cmlservice\Xml\XmlObject;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_offer"
 * )
 */
class CmOfferMigrationPlugin extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    // Debug.
    $debug = FALSE;
    if (\Drupal::routeMatch()->getRouteName() == "entity.migration.list") {
      $debug = TRUE;
    }
    $filepath = GetLastCml::filePath('offers');
    $rows = OffersParcer::getRows($filepath);
    $this->rows = $rows;
    if ($rows) {
      $k = 0;
      $keys = [0, 100];
      foreach ($rows as $key => $row) {
        if (($k >= $keys[0] && $k < $keys[1]) || !$debug) {
          $k++;
          $fields[$key] = [
            'uuid' => $row['Id'],
            'sku' => $row['Id'],
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
    // Итератор возьмёт данные отсюда.
    $this->fields = $fields;
    if ($debug) {
      dsm($fields);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    return new \ArrayIterator($this->fields);
  }

  /**
   * Allows class to decide how it will react when it is treated like a string.
   */
  public function __toString() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getIDs() {
    return [
      'uuid' => [
        'type' => 'string',
        'alias' => 'id',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = OffersParcer::map();
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->rows);
  }

}
