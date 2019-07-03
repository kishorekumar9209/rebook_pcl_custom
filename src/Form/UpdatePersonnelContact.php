<?php
/**
 * {@inheritdoc}
 * PHP Version 7
 *
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 * PHP Version 7
 */
namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;
use Drupal\Core\Database\Database;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Class CompanyBatchForm.
 *
 * @category Class
 * @package  Drupalredbook_Pcl_CustomForm
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class UpdatePersonnelContact extends FormBase
{

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  public function getFormId()
  {
    return 'update_personnel_contact_value';
  }

  /**Description
   * {@inheritdoc}
   * @param array  $form       Check the form
   *
   * @param FormStateInterface $form_state Check  form
   *
   * @return array
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {

    $form['note'] = [
      '#markup' => ''
        . '<div class="jumbotron">'
        . 'We are going to move the personnel contact(field_comp_personnel_contact)'
        . 'datas to the new field (field_comp_personnel_contacts) Since the'
        . 'field type changes to 	Entity reference revisions'
        . '<br><br>'
        . '<ol>'
        . '<li>field_comp_personnel_contact(Entity reference) to field_comp_personnel_contacts(Entity reference revisions)</li>'
        . '</ol>'
        . '<br>'
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
          ':input[name="confirmation_check1"]' => array('checked' => true),
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
          ':input[name="confirmation_check1"]' => array('checked' => true),
          ':input[name="confirmation_check2"]' => array('checked' => true),
        ),
      ),
    ];

    return $form;
  }
  /**Description
   * @param array $form       Check
   *
   * @param FormStateInterface $form_state Check  form
   *
   * @return array
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $batch = array(
      'title' => t('Initializing moderation nodes...'),
      'operations' => array(
        array('\Drupal\redbook_pcl_custom\Form\UpdatePersonnelContact::UpdateContactField', array()),
      ),
      'finished' => '\Drupal\redbook_pcl_custom\Form\UpdatePersonnelContact::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    );

    batch_set($batch);
  }
  /**Description
   * @param array $context check
   *
   */
  static function UpdateContactField(&$context)
  {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $entityQuery = \Drupal::entityQuery('node')
        ->condition('type', 'company')
        ->sort('nid')
        ->exists('field_comp_personnel_contact')
        ->notExists('field_comp_personnel_contacts');
      $all_node = $entityQuery->execute();
      $count = count($all_node);
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
      $context['results']['missing_contact_ids'] = [];
    }

    $limit = 50;
    $entityQuery = \Drupal::entityQuery('node')
      ->condition('type', 'company')
      ->condition('nid', $context['sandbox']['current_id'], '>')
      ->sort('nid')
      ->range(0, $limit)
      ->exists('field_comp_personnel_contact')
      ->notExists('field_comp_personnel_contacts');
    $result = $entityQuery->execute();

    foreach ($result as $nid) {
      $context['sandbox']['progress'] ++;
      $context['sandbox']['current_id'] = $nid;

      $contact_id = [];
      $service = \Drupal::service('redbook_pcl_custom.company');
      $node = $service->load($nid);

      $contact_node_ids = $node->get('field_comp_personnel_contact')->getValue();
      foreach ($contact_node_ids as $contact_nid) {
        $service = \Drupal::service('redbook_pcl_custom.company');
        $contact_node = $service->load($contact_nid['target_id']);
        if (!empty($contact_node)) {
          $contact_id[] = array(
            'target_id' => $contact_nid['target_id'],
            'target_revision_id' => $contact_node->getRevisionId(),);
        } else {
          $missing_contacts_nid[] = $contact_nid['target_id'];
        }
      }
      if (!empty($contact_id)) {
        $node->field_comp_personnel_contacts = $contact_id;
        $node->save();
      }
      if (!empty($missing_contacts_nid)) {
        if (!empty($context['results']['missing_contact_ids'])) {
          $context['results']['missing_contact_ids'] = $context['results']['missing_contact_ids'] + $missing_contacts_nid;
        } else {
          $context['results']['missing_contact_ids'] = $missing_contacts_nid;
        }
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
  /**Description
   * @param array  $success     Check
   *
   * @param array $results Check
   *
   * @param array $operations check
   *
   * @return array
   */
  static function finishedCallback($success, $results, $operations)
  {
    /* @var  \Drupal\Core\Database\Connection $connection */
    if ($success) {
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed, Skipped Missing contacts: ' . print_r($results['missing_contact_ids'], true);
      \Drupal::logger('init contact update')->info($message);
    } else {
      $message = 'Finished with an error.';
      \Drupal::logger('init contact update')->error($message);
    }
    drupal_set_message($message);
  }

}
