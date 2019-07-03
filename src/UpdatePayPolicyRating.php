<?php

namespace Drupal\redbook_pcl_custom;

use Drupal\node\Entity\Node;

/**
 * Class UpdatePayPolicyRating.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class UpdatePayPolicyRating {

  /**
   * {@inheritdoc}
   */
  public static function updatePayPolicyRatingField($entity_ids, &$context) {

    if (!empty($entity_ids)) {
      $message = 'Updating Pay Policy Rating Field...';
      $results = [];
      foreach ($entity_ids as $key => $value) {

        // Load Company services to get node object.
        $service = \Drupal::service('redbook_pcl_custom.company');
        $node = $service->load($value->entity_id);
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $value->field_pay_policy_value]);
        $term = reset($terms);
        $node->field_pay_policy_rating->target_id = $term->id();
        $results[] = $node->save();
      }
      $context['message'] = $message;
      $context['results'] = $results;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function finishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()->formatPlural(
        count($results),
        'One post processed.', '@count posts processed.'
      );
    }
    else {
      $message = t('Finished with an error.');
    }
    drupal_set_message($message);
  }

}
