<?php

namespace Drupal\cmlmigrations\Controller;

use Drupal\Core\Controller\ControllerBase;
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
          $variations[] = [
            'src' => $value->sourceid1,
            'target_id' => $value->destid1,
          ];
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
      $vids_current = self::varIds($variations);

      $id1c = self::getTovarUuid($nid);
      $variations = self::findVariations($id1c);
      $vids_new = self::varIds($variations);
      if ($vids_current != $vids_new) {
        $node->field_tovar_variation->setValue($vids_new);
        $node->save();
        self::fix($vids_new, $node);
      }

    }
  }

  /**
   * VAriation Ids.
   */
  public static function varIds($variations) {
    $vids = FALSE;
    if (!empty($variations)) {
      $vids = [];
      foreach ($variations as $variation) {
        $vids[] = (int) $variation['target_id'];
      }
    }
    return $vids;
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
      }
    }
  }

  /**
   * Fix.
   */
  public static function fixVariations(NodeInterface $node) {
    $type = $node->getType();
    if ($type == 'tovar') {
      $nid = $node->id();
      $variations = $node->field_tovar_variation->getValue();
      $vids = self::varIds($variations);
      if ($vids) {
        self::fix($vids, $node);
      }
    }
  }

}
