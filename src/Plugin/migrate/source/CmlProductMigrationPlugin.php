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
 *   id = "cml_product"
 * )
 */
class CmlProductMigrationPlugin extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $rout_name = \Drupal::routeMatch()->getRouteName();

    $filepath = $this->getFilePath();
    if (FALSE) {

      $rows = $this->filePathToData($filepath);
      $fields = [];
      if ($rows) {
        $k = 0;
        $keys = [0, 100];
        foreach ($this->rows as $key => $row) {
          if (($k >= $keys[0] && $k < $keys[1]) || $rout_name != "entity.migration.list") {
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
      if ($rout_name == "entity.migration.list") {
        dsm($fields);
      }
    }
    else {
      $this->rows = 0;
      $fields = [];
    }
    // Итератор возьмёт данные отсюда.
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
      $data = TovarParcer::parce($xmlObj->xmlString);
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
