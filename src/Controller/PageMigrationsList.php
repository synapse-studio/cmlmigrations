<?php

namespace Drupal\cmlmigrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Controller routines for page example routes.
 */
class PageMigrationsList extends ControllerBase {

  /**
   * Page.
   */
  public function page() {
    $rows = [];
    $manager = \Drupal::service('plugin.manager.migration');
    $plugins = $manager->createInstances([]);
    if (!empty($plugins)) {
      foreach ($plugins as $id => $migration) {
        $rows[] = $this->buildRow($migration);
      }
    }

    $form = \Drupal::formBuilder()->getForm('Drupal\cmlmigrations\Form\ExecMigrations', $plugins);
    $table = [
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#rows' => $rows,
    ];

    return [
      'form' => $form,
      'migr-table' => $table,
    ];
  }

  /**
   * Header.
   */
  public function buildHeader() {
    $header['label'] = $this->t('Migration');
    $header['status'] = $this->t('Status');
    $header['total'] = $this->t('Total');
    $header['imported'] = $this->t('Imported');
    $header['unprocessed'] = $this->t('Unprocessed');
    $header['messages'] = $this->t('Messages');
    $header['last_imported'] = $this->t('Last');
    return $header;
  }

  /**
   * Builds a row for a migration plugin.
   */
  public function buildRow($migration) {
    $label = $migration->label();
    $id = $migration->id();
    $row['label'] = [
      'data' => ['#markup' => "$label<br><small>$id</small>"],
    ];

    $row['status'] = $migration->getStatusLabel();

    // Derive the stats.
    $source_plugin = $migration->getSourcePlugin();
    $row['total'] = $source_plugin->count();
    $map = $migration->getIdMap();
    $row['imported'] = $map->importedCount();
    // -1 indicates uncountable sources.
    if ($row['total'] == -1) {
      $row['total'] = $this->t('N/A');
      $row['unprocessed'] = $this->t('N/A');
    }
    else {
      $row['unprocessed'] = $row['total'] - $map->processedCount();
    }
    $migration_group = $migration->get('migration_group');
    if (!$migration_group) {
      $migration_group = 'default';
    }
    $route_parameters = array(
      'migration_group' => $migration_group,
      'migration' => $migration->id(),
    );
    $row['messages'] = array(
      'data' => array(
        '#type' => 'link',
        '#title' => $map->messageCount(),
        '#url' => Url::fromRoute("migrate_tools.messages", $route_parameters),
      ),
    );
    $migrate_last_imported_store = \Drupal::keyValue('migrate_last_imported');
    $last_imported = $migrate_last_imported_store->get($migration->id(), FALSE);
    if ($last_imported) {
      $date_formatter = \Drupal::service('date.formatter');
      $row['last_imported'] = $date_formatter->format($last_imported / 1000,
        'custom', 'Y-m-d H:i:s');
    }
    else {
      $row['last_imported'] = '';
    }
    return $row;
  }

}
