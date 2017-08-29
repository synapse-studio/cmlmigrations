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
    if ($filepath) {
      $xmlObj = new XmlObject();
      $xmlObj->parseXmlFile($filepath);
      $data = CatalogParcer::parce($xmlObj->xmlString);
    }
    $this->rows = $data;
    dsm($this->rows);
  }

  /**
   * {@inheritdoc}
   */
  public function initializeIterator() {
    $fields = [];
    foreach ($this->rows as $key => $row) {
      $fields[$key] = [
        'uuid' => $row['id'],
        'name' => $row['name'],
        'weight' => $raw['term_weight'],
      ];
      if ($row['parent']) {
        $fields[$key]['parent'] = $row['parent'];
      }
    }
    return new \ArrayIterator($fields);
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
