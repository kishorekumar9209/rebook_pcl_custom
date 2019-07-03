<?php
/**
 * @file
 * Contains \Drupal\redbook_pcl_custom\Routing\MenuAlter.
 */

namespace Drupal\redbook_pcl_custom\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Core\Database\Database;

/**
 * Listens to the dynamic route events.
 */
class MenuAlter extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    // As nodes are the primary type of content, the node listing should be
    // easily available. In order to do that, override admin/content to show
    // a node listing instead of the path's child links.

      $nids = \Drupal::entityQuery('node')->condition('type','produce_category')->execute();
      $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);

      // For Getting plugin id of parent
      $conn = Database::getConnection();
      $db_query = $conn->select('menu_link_content_data', 'mlcd');
      $db_query->leftJoin('menu_link_content', 'mlc', 'mlcd.Id = mlc.Id');
      $db_query->fields('mlc', ['uuid']);
      $db_query->condition('mlcd.title', 'Find Produce', '=');
      $parent_pluign_id = $db_query->execute()->fetchField();
      $parent = 'menu_link_content:' . $parent_pluign_id;

      // Getting all the child menu of "Find Produce"
      $db_query = $conn->select('menu_link_content_data', 'mlcd');
      $db_query->fields('mlcd', ['title']);
      $db_query->condition('mlcd.parent', $parent, '=');
      $child = $db_query->execute()->fetchCol();

       //Inserting Produce Index menu
      if(!in_array('Produce Index', $child) && $parent_pluign_id) {
        $menu_link = MenuLinkContent::create([
          'title' => 'Produce Index',
          'link' => ['uri' => 'internal:/produce' ],
          'menu_name' => 'left-sliding-menu',
          'parent' => $parent,
          'enabled' => TRUE,
          'weight' => -50,
        ]);
        $menu_link->save();
        $conn->update('menu_link_content_data')
          ->fields(['expanded' => TRUE])
          ->condition('title', 'Find Produce')
          ->execute();
      }

      foreach($nodes as $items) {
        if (!in_array($items->getTitle(), $child) && $parent_pluign_id) {
          $link = \Drupal::service('path.alias_manager')->getAliasByPath('/node/'.$items->Id());
          $menu_link = MenuLinkContent::create([
            'title' => $items->getTitle(),
            'link' => ['uri' => 'internal:'. $link],
            'menu_name' => 'left-sliding-menu',
            'parent' => $parent,
            'enabled' => TRUE,
          ]);
          $menu_link->save();
        }
     }
  }
}
