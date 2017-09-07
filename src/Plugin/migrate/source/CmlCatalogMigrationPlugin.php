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
    $rout_name = \Drupal::routeMatch()->getRouteName();

    $filepath = $this->getFilePath();
    $rows = $this->filePathToData($filepath);
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
    if ($rout_name == "entity.migration.list") {
      dsm($fields);
    }
    $this->fields = $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilePath() {
    $cml = GetLastCml::load();
    $cml_xml = $cml->field_cml_xml->getValue();
    $files = [];
    $data = FALSE;
    $filekeys['import'] = TRUE;
    if (!empty($cml_xml)) {
      foreach ($cml_xml as $xml) {
        $file = file_load($xml['target_id']);
        $filename = $file->getFilename();
        $filekey = strstr($filename, '.', TRUE);
        if (isset($filekeys[$filekey]) && $filekeys[$filekey]) {
          $files[] = $file->getFileUri();
        }
      }
    }
    $filepath = array_shift($files);
    $this->filepath = $filepath;
    return $filepath;
  }

  /**
   * {@inheritdoc}
   */
  public function filePathToData($filepath) {
    $rows = FALSE;
    if ($filepath) {
      $xmlObj = new XmlObject();
      $xmlObj->parseXmlFile($filepath);
      $data = CatalogParcer::parce($xmlObj->xmlString);
      if (!empty($data)) {
        $rows = $data;
      }
    }
    $this->rows = $data;
    return $rows;
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
