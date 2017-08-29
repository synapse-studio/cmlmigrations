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
    $cml = GetLastCml::load();
    $cml_xml = $cml->field_cml_xml->getValue();
    $files = [];
    $data = FALSE;
    $filekeys['offers'] = TRUE;
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
      $data = OffersParcer::parce($xmlObj->xmlString);
    }
    $this->rows = $data;

    $fields = [];
    foreach ($this->rows as $key => $row) {
      $fields[$key] = [
        'uuid' => $row['Id'],
        'Naimenovanie' => $row['Naimenovanie'],
        'BazovaaEdinica' => $row['BazovaaEdinica'],
        'Kolicestvo' => $row['Kolicestvo'],
        'Sklad' => $row['Sklad'],
        'price' => $row['Ceny'][0]['ЦенаЗаЕдиницу'],
        'ccode' => 'RUB',
      ];
    }
    dsm($fields);
    $this->fields = $fields;
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
