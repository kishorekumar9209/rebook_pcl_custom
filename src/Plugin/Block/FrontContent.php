<?php

/**
 * @file
 * Contains \Drupal\redbook_pcl_custom\Plugin\Block\FrontContent.
 */

namespace Drupal\redbook_pcl_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Database;

/**
 * Provides a 'FrontContent' block.
 *
 * @Block(
 *  id = "redbook_pcl_front_content",
 *  admin_label = @Translation("FrontContent"),
 * )
 */
class FrontContent extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $conn = Database::getConnection();
    $output = '';
    $query = $conn->select('taxonomy_index', 'ti');
    $query->join('node__body', 'nb', 'nb.entity_id = ti.nid');
    $query->join('node_field_data', 'nf', 'nf.nid = ti.nid');
    $query->fields('nb', ['body_value']);
    $query->fields('nf', ['title']);
    $query->fields('ti', ['nid']);
    $query->condition('ti.status', 1);
    $query->range(0, 1);
    $query->condition('ti.tid', 2);
    $query->orderBy("ti.created", "DESC");
    $nodes = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
    if ($nodes) {
      $output .= '<h2>' . $nodes[0]['title'] . '</h2>';
      $output .= $nodes[0]['body_value'];
      if (\Drupal::currentUser()->hasPermission('edit any article content')) {
        $nid = $nodes[0]['nid'];
        $output .= "<a href='/node/$nid/edit?destination=/node' hreflang='en'>edit</a>";
      }
      return array(
        '#markup' => $output,
        '#cache' => array(
          'max-age' => 600,
        ),
      );
    }
    else {
      return array(
        '#markup' => t('No data'),
        '#cache' => array(
          'max-age' => 600,
        ),
      );
    }
  }

}
