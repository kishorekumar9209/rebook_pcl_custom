<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;

/**
 * Class CompanyBatchForm.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class CompanyBatchOrganicForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'company_batch_organic_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['note'] = [
      '#markup' => ''
        . '<div class="jumbotron">'
        . 'We are going to update the value in related business tags from "Organic" to "USDA Certified Organic".'
        . 'Companies with "Organic"(USDA Certified Organic) related business tag should have the checkbox checked by default'
        . '<br><br>'
        . '<h1>'
        . 'It\'s strongly recommended...'
        . '<h3>=> Put site in maintenance mode. </h3>'
        . '<h3>=> Backup your database. </h3>'
        . '<h3>=> Do NOT let your computer sleep until batch process completes. </h3>'
        . '</h1>'
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
        array('\Drupal\redbook_pcl_custom\Form\CompanyBatchOrganicForm::UpdateRelatedBusinessField', array()),
      ),
      'finished' => '\Drupal\redbook_pcl_custom\Form\CompanyBatchOrganicForm::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    );

    batch_set($batch);
  }

  static function UpdateRelatedBusinessField(&$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $conn = Database::getConnection();
      $query = $conn->select('node__field_comp_related_business_tags', 'n');
      $query->fields('n', array('entity_id'));
      $query->condition('n.bundle', 'company');
      $query->condition('n.field_comp_related_business_tags_value', 'Organic');
      $all_node = $query->execute()->fetchAll();
      $count = count($all_node);
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
      $context['results']['entity_id'] = [];
    }

    $limit = 50;
    $conn = Database::getConnection();
    $query = $conn->select('node__field_comp_related_business_tags', 'n');
    $query->fields('n', array('entity_id'));
    $query->condition('n.bundle', 'company');
    $query->condition('n.field_comp_related_business_tags_value', 'Organic');
    $query->condition('n.entity_id', $context['sandbox']['current_id'], '>');
    $query->range(0, $limit);
    $results = $query->execute()->fetchAll();

    foreach ($results as $result) {
      $nid = $result->entity_id;
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $nid;

      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $nid;
      $service = \Drupal::service('redbook_pcl_custom.company');
      $node = $service->load($nid);
      $field_comp_related_business_tags = $node->get('field_comp_related_business_tags')->getValue();
      $search = array_search('Organic', array_column($field_comp_related_business_tags, 'value'));
      if(!empty($field_comp_related_business_tags)) {
        if(is_numeric($search)) {
           $node->field_comp_related_business_tags[$search] = 'USDA Certified Organic';
           $node->set('field_organic', 'usda certified organic');
        }
      }
        // Calling node save.
        $node->save();
    }
    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Updating the related business field values'
      . '<br><br>Processed nodes: ' . $processedCountTillNow . ' / ' . $totalCount . ' Current Processing node: ' . $current_id;
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  static function finishedCallback($success, $results, $operations) {
    /* @var  \Drupal\Core\Database\Connection $connection */
    if ($success) {
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed, Skipped Missing Related Business Tags: ' . print_r($results['entity_id'], TRUE);
      \Drupal::logger('init update related business tags')->info($message);
    }
    else {
      $message = 'Finished with an error.';
      \Drupal::logger('init update related business tags')->error($message);
    }
    drupal_set_message($message);
  }

}
