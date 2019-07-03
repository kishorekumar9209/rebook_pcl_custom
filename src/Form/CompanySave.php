<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CompanyUpdateUrlAlias.
 */
class CompanySave extends FormBase {

  /**
   * Get formid.
   *
   * @method getFormId
   *
   * @return Formid
   *   Form id.
   */
  public function getFormId() {
    return 'company_save';
  }

  /**
   * Build form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['company_save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update to save companies'),
    ];
    return $form;
  }

  /**
   * Submit Form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => t('Company node save...'),
      'operations' => [
        ['\Drupal\redbook_pcl_custom\Form\CompanySave::companysave', []],
      ],
      'finished' => '\Drupal\redbook_pcl_custom\Form\CompanySave::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    ];

    batch_set($batch);
  }

  /**
   * Batch Submit callback.
   */
  public function companysave(&$context) {
    // Give helpful information about how many nodes are being operated on.
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $query = \Drupal::entityQuery('node');
      $query->condition('type', 'company');
      $count = $query->count()->execute();
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
    }

    $limit = 50;
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'company');
    $query->sort('nid');
    $query->condition('nid', $context['sandbox']['current_id'], '>');
    $query->range(0, $limit);
    $company_ids = $query->execute();

    $tempstore = \Drupal::service('user.private_tempstore')->get('redbook_pcl_custom');
    $tempstore->set('company_update_batch', TRUE);

    $service = \Drupal::service('redbook_pcl_custom.company');
    foreach ($company_ids as $entity_rev_id => $entity_id) {
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $entity_id;
      $node = $service->load($entity_id);
      if (!empty($node)) {
        $pin = $node->get('field_comp_pmg_pin')->value;
        if(!empty($pin)){
          $node->save();
        }
      }
    }
    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Updating Url Alias for Company.'
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
    $message .= ' Tempstore variable company_update_batch is unset.';
    drupal_set_message($message);
  }

}
