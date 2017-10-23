<?php

namespace Drupal\cmlmigrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;

/**
 * Controller routines for page example routes.
 */
class PageNodeTovar extends ControllerBase {

  /**
   * Page tree.
   */
  public function update() {
    // Load Mapping.
    $fields = ["sourceid1", "destid1", "source_row_status"];
    $query = \Drupal::database()->select('migrate_map_cmlmigrations_taxonomy_catalog', 'tx_map');
    $query->fields('tx_map', $fields);
    $result = $query->execute();
    $tids = [];
    $k = 0;
    while ($row = $result->fetchAssoc()) {
      // If isn't changed:.
      if ($row["source_row_status"] == 1) {
        $tids[] = $row["destid1"];
      }
    }
    // Load upnchanged terms.
    $terms = Term::loadMultiple($tids);
    foreach ($terms as $tid => $termin) {
      $k++;
      if ($termin->getVocabularyId() == 'catalog') {
        // Move tot old catalog.
        $termin->set('vid', 'old');
        $termin->save();
      }
    }
    dsm($tids);
    return [
      '#markup' => "++",
    ];
  }

  /**
   * Old debug func.
   */
  public static function oldTovarFixer() {
    $result = '++';
    // Nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition('type', 'tovar');
    $ids = $query->execute();
    $k = 300;
    if (!empty($ids)) {
      foreach (Node::loadMultiple($ids) as $nid => $node) {
        $k++;
        if ($nid == 9552 || $k < 500) {
          // FixVariation::setVariations($node);.
          $options[$nid] = $node->title->value;
        }
      }
    }
    dsm($options);
    return $result;
  }

}
