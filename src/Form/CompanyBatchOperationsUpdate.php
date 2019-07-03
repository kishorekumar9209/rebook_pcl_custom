<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;

/**
 * Class CompanyBatchOperationsUpdate.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class CompanyBatchOperationsUpdate extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_batch_operations_update';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['note'] = [
      '#markup' => ''
        . '<div class="jumbotron">'
        . 'Batch To Update Operations field based on primary and secondary operations'
        . '</div>'
    ];

    $form['confirmation_check1'] = [
      '#type' => 'checkbox',
      '#title' => 'I have done with database backup and read all above instructions.',
    ];

    $form['confirmation_check2'] = [
      '#type' => 'checkbox',
      '#title' => 'Really I have done with database backup and read all above instructions.',
      '#states' => array(
        'visible' => array(
          ':input[name="confirmation_check1"]' => array('checked' => TRUE),
        ),
      ),
    ];

    $form['actions']['cancel'] = [
      '#markup' => '<a class="btn btn-default" href="/">< Go back</a>',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => 'Proceed',
      '#attributes' => [
        'style' => 'display:none'
      ],
      '#states' => array(
        'visible' => array(
          ':input[name="confirmation_check1"]' => array('checked' => TRUE),
          ':input[name="confirmation_check2"]' => array('checked' => TRUE),
        ),
      ),
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = array(
      'title' => t('Initializing moderation nodes...'),
      'operations' => array(
        array('\Drupal\redbook_pcl_custom\Form\CompanyBatchOperationsUpdate::UpdateOperationsField', array()),
      ),
      'finished' => '\Drupal\redbook_pcl_custom\Form\CompanyBatchOperationsUpdate::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    );

    batch_set($batch);
  }

  static function UpdateOperationsField(&$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $entityQuery = \Drupal::entityQuery('node')
        ->condition('type', 'company')
        ->condition('status', 1)
        ->sort('nid')
        ->notExists('field_comp_op_types');
      $all_node = $entityQuery->execute();
      $count = count($all_node);
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
    }

    $limit = 50;
    $entityQuery = \Drupal::entityQuery('node')
      ->condition('type', 'company')
      ->condition('nid', $context['sandbox']['current_id'], '>')
      ->condition('status', 1)
      ->sort('nid')
      ->range(0, $limit)
      ->notExists('field_comp_op_types');
    $result = $entityQuery->execute();

    foreach ($result as $nid) {
      $context['sandbox']['progress'] ++;
      $context['sandbox']['current_id'] = $nid;

      $service = \Drupal::service('redbook_pcl_custom.company');
      $node = $service->load($nid);

      if (!empty($node->get('field_comp_operation_primary'))) {
        $ops[] = $node->get('field_comp_operation_primary')->getValue()[0]['target_id'];
      }
      $secondary_ops = $node->get('field_comp_legacy_operation')->getValue();
      if (!empty($secondary_ops)) {
        foreach ($secondary_ops as $secondary_op) {
          $ops[] = $secondary_op['target_id'];
        }
      }
      if (!empty($ops)) {
        $node->set('field_comp_op_types', array_unique($ops));
        $node->save();
      }
    }
    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Updating the contact field values'
      . '<br><br>Processed nodes: ' . $processedCountTillNow . ' / ' . $totalCount . ' Current Processing node: ' . $current_id;
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  static function finishedCallback($success, $results, $operations) {
    /* @var  \Drupal\Core\Database\Connection $connection */
    if ($success) {
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed';
      \Drupal::logger('init operations update')->info($message);
    }
    else {
      $message = 'Finished with an error.';
      \Drupal::logger('init operations update')->error($message);
    }
    drupal_set_message($message);
  }

}
