<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\CatalogParcer;
use Drupal\cmlservice\Xml\XmlObject;

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    // Debug.
    $debug = FALSE;
    if (\Drupal::routeMatch()->getRouteName() == "entity.migration.list") {
      $debug = TRUE;
    }

    $filepath = GetLastCml::filePath('import');
    $rows = CatalogParcer::getRows($filepath);
    $this->rows = $rows;

    $fields = [];
    foreach ($rows as $key => $row) {
      $fields[$key] = [
        'uuid' => $row['id'],
        'name' => $row['name'],
        'weight' => $row['term_weight'],
      ];
      if (isset($row['parent']) && $row['parent']) {
        $fields[$key]['parent'] = $row['parent'];
      }
    }
    $this->fields = $fields;
    if ($debug) {
      // dsm($fields);
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
      'uuid' => $this->t('UUID Key'),
      'name' => $this->t('Catalog Group Name'),
      'weight' => $this->t('Weight'),
      'parent' => $this->t('Parent UUID'),
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
