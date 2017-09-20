<?php

namespace Drupal\cmlmigrations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Controller routines for page example routes.
 */
class FixVariation extends ControllerBase {

  /**
   * Find variations.
   */
  public static function findVariations($id1c) {
    $variations = [];

    if ($id1c) {
      $query = \Drupal::database()->select('migrate_map_cmlmigrations_commerce_product_variation', 'variation_migration');
      $query->fields('variation_migration', ['destid1', 'sourceid1']);
      $query->condition('sourceid1', "%$id1c%", 'LIKE');
      $res = $query->execute();

      if ($res) {
        foreach ($res as $key => $value) {
          $variations[$value->destid1] = $value->sourceid1;
        }
      }
    }

    return $variations;
  }

  /**
   * Find variations.
   */
  public static function getTovarUuid($nid) {
    $query = \Drupal::database()->select('migrate_map_cmlmigrations_node_tovar', 'tovar_migration');
    $query->fields('tovar_migration', ['destid1', 'sourceid1']);
    $query->condition('destid1', $nid);
    $result = $query->execute();
    $id1c = FALSE;
    if ($result) {
      foreach ($result as $key => $value) {
        $id1c = $value->sourceid1;
      }
    }
    return $id1c;
  }

  /**
   * Find variations.
   */
  public static function setVariations(NodeInterface $node) {
    $type = $node->getType();
    if ($type == 'tovar') {
      $nid = $node->id();
      $variations = $node->field_tovar_variation->getValue();
      $vids_current = [];
      if (!empty($variations)) {
        foreach ($variations as $variation) {
          $vids_current[] = (int) $variation['target_id'];
        }
      }

      $id1c = self::getTovarUuid($nid);
      $variations = self::findVariations($id1c);
      $vids_new = array_keys($variations);
      if ($vids_current != $vids_new) {
        $node->field_tovar_variation->setValue($vids_new);
        $node->save();
        self::fix($vids_new, $node);
      }

    }
  }

  /**
   * Fix.
   */
  public static function fix($vids, $node) {
    foreach ($vids as $vid) {
      $storage = \Drupal::entityManager()->getStorage('commerce_product_variation');
      $variation = $storage->load($vid);
      $title = $variation->title->value;
      if (!$title) {
        $pid = $variation->get('product_id');
        $pid->setValue($node);
        $variation->save();
        // dsm("UPD: " . $node->id() . " -- " . $node->title->value);
      }
    }
  }

  /**
   * Fix.
   */
  public static function fixTovar(NodeInterface $node) {
    $type = $node->getType();
    if ($type == 'tovar') {
      $variations = $node->field_tovar_variation->getValue();
      if (!empty($variations)) {
        foreach ($variations as $var) {
          $vid = $var['target_id'];
          $storage = \Drupal::entityManager()->getStorage('commerce_product_variation');
          $variation = $storage->load($vid);
          $title = $variation->title->value;
          if (is_object($variation) && !$title) {
            $pid = $variation->get('product_id');
            $pid->setValue($node);
            $variation->save();
          }
        }
      }
    }
  }

}
