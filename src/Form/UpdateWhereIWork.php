<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\field_collection\Entity\FieldCollectionItem;

/**
 * Class UpdateWhereIWork.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class UpdateWhereIWork extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_where_i_work_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['note'] = [
      '#markup' => ''
        . '<div class="jumbotron">'
        . 'We are going to automatically adding the companies '
        . 'to their where i work, If a user is added to a company’s user seat '
        . 'management for newly adding user"'
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
        array('\Drupal\redbook_pcl_custom\Form\UpdateWhereIWork::UpdateWhereIWorkField', array()),
      ),
      'finished' => '\Drupal\redbook_pcl_custom\Form\UpdateWhereIWork::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    );

    batch_set($batch);
  }
  
  static function UpdateWhereIWorkField(&$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $conn = Database::getConnection();
      $query = $conn->select('node__field_cv_user_accounts', 'u');
      $query->join('node__field_cv_company', 'c', 'c.entity_id=u.entity_id');
      $query->fields('u',  array('field_cv_user_accounts_target_id'));
      $query->fields('c',  array('field_cv_company_target_id'));
      $query->condition('u.bundle', 'company_claims_and_verification');
      $all_node = $query->execute()->fetchAll();
      $count = count($all_node);
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
      $context['results']['entity_id'] = [];
    }

    $limit = 10;
    $conn = Database::getConnection();
    $query = $conn->select('node__field_cv_user_accounts', 'u');
    $query->join('node__field_cv_company', 'c', 'c.entity_id=u.entity_id');
    $query->fields('u',  array('field_cv_user_accounts_target_id'));
    $query->fields('c',  array('field_cv_company_target_id'));
    $query->condition('u.bundle', 'company_claims_and_verification');
    $query->condition('u.entity_id', $context['sandbox']['current_id'], '>');
    $query->range(0, $limit);
    $results = $query->execute()->fetchAll();
    $all_nid = [];
    foreach ($results as $result) {
      $nid = $result->field_cv_company_target_id;
      $uid = $result->field_cv_user_accounts_target_id;
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $nid;

      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $nid;
      $user = User::load($uid);
      $field_collection_item_id_array = $user->get("field_where_i_work")->getvalue();
      if($field_collection_item_id_array) {
        foreach ($field_collection_item_id_array as $field_collection_item_id) {
          $fc = FieldCollectionItem::load($field_collection_item_id['value']);
          if (!empty($fc)) {
            $fields_array = $fc->toArray();
            $all_nid[] = $fields_array['field_where_i_work_company'][0]['target_id'];
          }
        }
      }
      else {
        $fc = FieldCollectionItem::create(['field_name' => 'field_where_i_work']);
        $fc->field_where_i_work_company->setValue($nid);
        $fc->setHostEntity($user);
        $fc->save();
        $user->save();
      }
      if (!in_array($nid, $all_nid)) {
        $fc = FieldCollectionItem::create(['field_name' => 'field_where_i_work']);
        $fc->field_where_i_work_company->setValue($nid);
        $fc->setHostEntity($user);
        $fc->save();
        $user->save();
      }
      unset($all_nid);
    }

    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Adding the companies to their to where i work '
      . '<br><br>Processed nodes: ' . $processedCountTillNow . ' / ' . $totalCount . ' Current Processing node: ' . $current_id;
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  static function finishedCallback($success, $results, $operations) {
    /* @var  \Drupal\Core\Database\Connection $connection */
    if ($success) {
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed, Skipped adding companies to their where i work: ' . print_r($results['entity_id'], TRUE);
      \Drupal::logger('init update companies to their where i work')->info($message);
    }
    else {
      $message = 'Finished with an error.';
      \Drupal::logger('init update companies to their where i work')->error($message);
    }
    drupal_set_message($message);
  }

}
