<?php

/**
 * @file
 * Contains \Drupal\redbook_pcl_custom\Plugin\Block\Category.
 */

namespace Drupal\redbook_pcl_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\views;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides a 'Produce Category List' block.
 *
 * @Block(
 *  id = "redbook_pcl_category_list",
 *  admin_label = @Translation("Category List"),
 * )
 */
class Category extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $current_path = \Drupal::service('path.current')->getPath();
    $exp = explode('/', $current_path);
    $class_all_produce = $class_fruit = $class_vegetable = $class_organic_produce = '';
    if(isset($exp[1])) {
      if(($exp[1] == 'produce' && (isset($exp[2]) && $exp[2] != 'fruit')) && ($exp[1] == 'produce' && (isset($exp[2]) && $exp[2] != 'vegetables')) && ($exp[1] == 'produce' && (isset($exp[2]) && $exp[2] != 'organic-produce')) || ($exp[1] == 'produce' && !isset($exp[2]))) {
        $class_all_produce = 'is-active';
      } elseif($exp[1] == 'produce' && (isset($exp[2]) && $exp[2] == 'fruit')) {
        $class_fruit = 'is-active';
      } elseif($exp[1] == 'produce' && (isset($exp[2]) && $exp[2] == 'vegetables')) {
        $class_vegetable = 'is-active';
      } elseif($exp[1] =='produce' && (isset($exp[2]) && $exp[2] == 'organic-produce')) {
        $class_organic_produce = 'is-active';
      }
    }

//    $output = "<ul>
// <li><a href = '/produce' class=" . $class_all_produce . ">All Produce</a></li>
// <li><a href = '/produce/fruit' class=" . $class_fruit . ">Fruit</a></li>
// <li><a href = '/produce/vegetables' class=" . $class_vegetable . ">Vegetables</a></li>
// <li><a href = '/produce/organic-produce' class=" . $class_organic_produce . ">Organic Produce</a></li>
// </ul>";

    $view = Views::getView('all_produce');
    $view->setDisplay('page_1');
    $view->execute();
    $all_produce_count = count( $view->result );

    $view = Views::getView('fruit_category');
    $view->setDisplay('page_1');
    $view->execute();
    $fruit_count = count( $view->result );

    $view = Views::getView('vegetable_category');
    $view->setDisplay('page_1');
    $view->execute();
    $vegetable_count = count( $view->result );

    $view = Views::getView('organic_produce_category');
    $view->setDisplay('page_1');
    $view->execute();
    $organic_produce_count = count( $view->result );

    if ($all_produce_count == 0 && $current_path == '/produce') {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    if ($fruit_count == 0 && $current_path == '/produce/fruits') {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    if ($vegetable_count == 0 && $current_path == '/produce/vegetables') {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    if ($organic_produce_count == 0 && $current_path == '/produce/organic-produce') {
      throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
    }
    $output = "<ul>";
    if ($all_produce_count > 0) {
      $output.="<li><a href = '/produce' class=" . $class_all_produce . ">All Produce</a></li>";
    }
    if ($fruit_count > 0) {
      $output.= "<li><a href = '/produce/fruit' class=" . $class_fruit . ">Fruit</a></li>";
    }
    if ($vegetable_count > 0) {
      $output.="<li><a href = '/produce/vegetables' class=" . $class_vegetable . ">Vegetables</a></li>";
    }
//    if ($organic_produce_count > 0) {
//      $output.="<li><a href = '/produce/organic-produce' class=" . $class_organic_produce . ">Organic Produce</a></li>";
//    }
    $output.= "</ul>";

    return array(
      '#markup' => $output,
      '#cache' => array(
        'max-age' => 0,
      ),
    );
  }
}