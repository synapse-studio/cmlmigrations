<?php

namespace Drupal\cmlmigrations\Plugin\migrate\source;

use Drupal\cmlmigrations\MigrationsSourceBase;
use Drupal\cmlservice\Controller\GetLastCml;
use Drupal\cmlservice\Xml\CatalogParcer;

/**
 * Source for CSV.
 *
 * @MigrateSource(
 *   id = "cml_catalog"
 * )
 */
class CmlCatalogMigrationPlugin extends MigrationsSourceBase {

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    $k = 0;
    $rows = [];
    $filepath = GetLastCml::filePath('import');
    $source = CatalogParcer::getRows($filepath);
    if ($source) {
      foreach ($source as $key => $row) {
        if ($k++ < 700 || !$this->uipage) {
          $rows[$key] = [
            'uuid' => $row['id'],
            'name' => $row['name'],
            'weight' => $row['term_weight'],
          ];
          if (isset($row['parent']) && $row['parent']) {
            $rows[$key]['parent'] = $row['parent'];
          }
          else {
            // Fix parent to '0' if termin exists.
            self::fixHierarchyParent($row['id']);
          }
        }
      }
    }
    $this->debug = TRUE;
    return $rows;
  }

  /**
   * Fix Hierarchy Parent.
   */
  public static function fixHierarchyParent($id) {
    $query = \Drupal::database()->select('migrate_map_cmlmigrations_taxonomy_catalog', 'taxonomy_migration');
    $query->fields('taxonomy_migration', ['destid1', 'sourceid1']);
    $query->condition('sourceid1', $id);
    $result = $query->execute();
    $tid = FALSE;
    if ($result) {
      foreach ($result as $key => $value) {
        $tid = $value->destid1;
        $query = \Drupal::database()->update('taxonomy_term_hierarchy');
        $query->fields(['parent' => 0]);
        $query->condition('tid', $tid);
        $query->execute();
      }
    }
    return $tid;

  }

}
