<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CompanyRemoveBuyerOpType.
 */
class CompanyRemoveBuyerOpType extends FormBase {

  /**
   * Get formid.
   *
   * @method getFormId
   *
   * @return Formid
   *   Form id.
   */
  public function getFormId() {
    return 'company_remove_buyer_batch';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['company_update'] = [
      '#type' => 'submit',
      '#value' => $this->t('Remove Buyer Operation'),
    ];
    return $form;
  }

  /**
   * Submit Form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => t('Removing Buyer Operation...'),
      'operations' => [
        ['\Drupal\redbook_pcl_custom\Form\CompanyRemoveBuyerOpType::removeBuyerOperationFromCompany', []],
      ],
      'finished' => '\Drupal\redbook_pcl_custom\Form\CompanyRemoveBuyerOpType::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    ];

    batch_set($batch);
  }

  /**
   * Batch Submit callback.
   */
  public function removeBuyerOperationFromCompany(&$context) {
    // Give helpful information about how many nodes are being operated on.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $query = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
      $query->condition('ttfd.name', 'Buyer');
      $query->addField('ttfd', 'tid');
      $query->leftJoin('node__field_comp_operation_primary', 'nfcop', 'nfcop.field_comp_operation_primary_target_id = ttfd.tid');
      $query->addField('nfcop', 'entity_id');

      $legacy_operation = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
      $legacy_operation->condition('ttfd.name', 'Buyer');
      $legacy_operation->addField('ttfd', 'tid');
      $legacy_operation->leftJoin('node__field_comp_legacy_operation', 'nfclo', 'nfclo.field_comp_legacy_operation_target_id = ttfd.tid');
      $legacy_operation->addField('nfclo', 'entity_id');
      $query->union($legacy_operation);
      $results = $query->execute()->fetchAll();
      $count = count($results);

      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
    }

    $limit = 50;
    $query = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
    $query->condition('ttfd.name', 'Buyer');
    $query->addField('ttfd', 'tid');
    $query->leftJoin('node__field_comp_operation_primary', 'nfcop', 'nfcop.field_comp_operation_primary_target_id = ttfd.tid');
    $query->addField('nfcop', 'entity_id');

    $legacy_operation = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
    $legacy_operation->condition('ttfd.name', 'Buyer');
    $legacy_operation->addField('ttfd', 'tid');
    $legacy_operation->leftJoin('node__field_comp_legacy_operation', 'nfclo', 'nfclo.field_comp_legacy_operation_target_id = ttfd.tid');
    $legacy_operation->addField('nfclo', 'entity_id');
    $query->union($legacy_operation);
    $query->range(0, $limit);
    $company_ids = $query->execute()->fetchAll();

    $tempstore = \Drupal::service('user.private_tempstore')->get('redbook_pcl_custom');
    $tempstore->set('company_update_batch', TRUE);

    $service = \Drupal::service('redbook_pcl_custom.company');
    foreach ($company_ids as $key => $entity) {
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $entity->entity_id;
      $node = $service->load($entity->entity_id);
      if (!empty($node)) {
        $primary_operation = $node->get('field_comp_operation_primary')->target_id;
        if (!empty($primary_operation) && $primary_operation == $entity->tid) {
          $node->set('field_comp_operation_primary', NULL);
          $buyer_exists = TRUE;
        }
        $legacy_operations = $node->get('field_comp_legacy_operation')->getValue();
        if (!empty($legacy_operations)) {
          foreach ($legacy_operations as $key => $legacy_operation) {
            if ($legacy_operation['target_id'] == $entity->tid) {
              unset($legacy_operations[$key]);
              $buyer_exists = TRUE;
              break;
            }
          }
          $node->set('field_comp_legacy_operation', array_values($legacy_operations));
        }
        if ($buyer_exists) {
          $op_types = $node->get('field_comp_op_types')->getValue();
          if (!empty($op_types)) {
            foreach ($op_types as $op_key => $op_type) {
              if ($op_type['target_id'] == $entity->tid) {
                unset($op_types[$op_key]);
                $op_types = array_values($op_types);
                $buyer_exists = TRUE;
                break;
              }
            }
            if (count($op_types) == 0) {
              $wholesaler_query = \Drupal::database()->select('taxonomy_term_field_data', 'ttfd');
              $wholesaler_query->condition('ttfd.name', 'Wholesaler');
              $wholesaler_query->fields('ttfd', ['tid']);
              $wholesaler_id = $wholesaler_query->execute()->fetchField();
              $op_types[0]['target_id'] = $wholesaler_id;
              $node->set('field_comp_operation_primary', $wholesaler_id);
            }
            $node->set('field_comp_op_types', $op_types);
          }
        }
        $node->save();
      }
    }
    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Removing Buyer option for Company.'
      . '<br><br>Processed nodes: ' . $processedCountTillNow . ' / ' . $totalCount . ' Current Processing node: ' . $current_id;
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function finishedCallback($success, $results, $operations) {
    if ($success) {
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed.';
      \Drupal::logger('Removing Buyer opertion Batch Process')->info($message);
    }
    else {
      $message = 'Finished with an error.';
      \Drupal::logger('Removing Buyer opertion Batch Process')->error($message);
    }
    $tempstore = \Drupal::service('user.private_tempstore')->get('redbook_pcl_custom');
    $tempstore->set('company_update_batch', FALSE);
    $message .= ' tempstore variable company_update_batch is unset.';
    drupal_set_message($message);
  }

}
