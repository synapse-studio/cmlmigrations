<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\TovarParcer;
use Drupal\cmlservice\Xml\XmlObject;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_tovar"
 * )
 */
class CmlTovarMigrationPlugin extends SourcePluginBase {

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
    $filepath = GetLastCml::filePath('import');
    $rows = TovarParcer::getRows($filepath);
    $this->rows = $rows;
    if ($rows) {
      $k = 0;
      $keys = [0, 100];
      foreach ($rows as $key => $row) {
        if (($k >= $keys[0] && $k < $keys[1]) || !$debug) {
          $k++;
          $fields[$key] = [
            'uuid' => $row['Id'],
            'title' => $row['Naimenovanie'],
            'catalog' => $row['Gruppy'][0],
            'created' => time(),
            'changed' => time(),
            'variations' => $row['Id'],
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
    $fields = [
      'Id' => $this->t('UUID Key'),
      'Artikul' => $this->t('Artikul'),
      'Naimenovanie' => $this->t('Naimenovanie'),
      'BazovaaEdinica' => $this->t('BazovaaEdinica'),
      'Gruppy' => $this->t('Gruppy'),
      'Kategoria' => $this->t('Kategoria'),
      'Opisanie' => $this->t('Opisanie'),
      'Kartinka' => $this->t('Kartinka'),
      'ZnaceniaSvoistv' => $this->t('ZnaceniaSvoistv'),
      'StavkiNalogov' => $this->t('StavkiNalogov'),
      'HarakteristikiTovara' => $this->t('HarakteristikiTovara'),
      'ZnaceniaRekvizitov' => $this->t('ZnaceniaRekvizitov'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    return count($this->rows);
  }

}
