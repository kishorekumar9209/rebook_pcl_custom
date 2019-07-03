<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PayPolicyRatingUpdate.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class PayPolicyRatingUpdate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_pay_policy_rating_value';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['update_node'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Node'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database()->select('node__field_pay_policy', 't');
    $query->fields('t', ['entity_id', 'field_pay_policy_value']);
    $entity_ids = $query->execute()->fetchAll();
    $batch = [
      'title' => t('Updating Pay Policy Rating Field...'),
      'operations' => [
        [
          '\Drupal\redbook_pcl_custom\UpdatePayPolicyRating::updatePayPolicyRatingField',
          [$entity_ids],
        ],
      ],
      'finished' => '\Drupal\redbook_pcl_custom\PayPolicyRating::finishedCallback',
    ];
    batch_set($batch);
  }

}
