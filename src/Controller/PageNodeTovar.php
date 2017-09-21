<?php

namespace Drupal\cmlmigrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Controller routines for page example routes.
 */
class PageNodeTovar extends ControllerBase {

  /**
   * Page tree.
   */
  public function update() {
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
        if ($nid == 9552 || TRUE) { // $k < 302 ||
          $options[$nid] = $node->title->value;
          FixVariation::setVariations($node);
        }
      }
    }
    return [
      '#markup' => $result,
    ];
  }

}
