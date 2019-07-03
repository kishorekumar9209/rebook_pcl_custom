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
class UpdateCompanyContactNodeFields extends FormBase
{

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function getFormId() 
    {
        return 'update_contact_node_fields';
    }

    /**Description
    * {@inheritdoc}
         * @param array   $form       Check the form
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
        . 'We are going update the phone fields which are not existing in contact as empty'
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
    * @param array     $form       Check the form
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
        array(
          '\Drupal\redbook_pcl_custom\Form\UpdateCompanyContactNodeFields::UpdateContactNodeFields',
          array()
        ),
        ),
        'finished' => '\Drupal\redbook_pcl_custom\Form\UpdateContactNodeFields::finishedCallback',
        'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
        );

        batch_set($batch);
    }
    /**Declare the form
    * @param array $context Check the form
    *
    * @return void
    */
    static function UpdateContactNodeFields(&$context) 
    {
        if (empty($context['sandbox'])) {
            $context['sandbox']['progress'] = 0;
            $context['sandbox']['current_id'] = 0;
            $entityQuery = \Drupal::entityQuery('node')
            ->condition('type', 'contact')
            ->notExists('field_cont_phones');
            $all_node = $entityQuery->execute();
            $count = count($all_node);
            $context['sandbox']['max'] = $count;
            $context['results']['totalCount'] = $count;
        }   

        $limit = 50;

        $entityQuery = \Drupal::entityQuery('node')
        ->condition('type', 'contact')
        ->condition('nid', $context['sandbox']['current_id'], '>')
        ->sort('nid')
        ->range(0, $limit)
        ->notExists('field_cont_phones');
        $result = $entityQuery->execute();
        foreach ($result as $nid) {
            $context['sandbox']['progress']++;
            $context['sandbox']['current_id'] = $nid;
            $service = \Drupal::service('redbook_pcl_custom.company');
            $node = $service->load($nid);
            $phone_number_value = '';
            $phone_number_extension = '';
            $phone_type = '';
            $paragraph = Paragraph::create(
                [
                'type' => 'contact_phone',
                'field_cont_phone_num' => $phone_number_value,
                'field_cont_phone_num_extension' => $phone_number_extension,
                'field_cont_phone_num_type' => $phone_type,
                ]
            );
            $paragraph->save();
            $phone_contact = array(
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            );
            $node->field_cont_phones = $phone_contact;
            $node->save();
        }
        $processedCountTillNow = $context['sandbox']['progress'];
        $totalCount = $context['sandbox']['max'];
        $current_id = $context['sandbox']['current_id'];
        $context['message'] = '<br>Updating the company contact node field values'
        . '<br><br>Processed nodes: ' . $processedCountTillNow . ' / ' . $totalCount . ' Current 50th Processing node: ' . $current_id;
        if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
            $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
        }
    }
    /**Declare the form
    * @param array  $success     Check
    *
    * @param array $results check
    *
    * @param array $operations check
    *
    * @return void
    */
    static function finishedCallback($success, $results, $operations) 
    {
        /* @var  \Drupal\Core\Database\Connection $connection */
        if ($success) {
            $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed.';
            \Drupal::logger('init content moderation nodes')->info($message);
        } else {
            $message = 'Finished with an error.';
            \Drupal::logger('init content moderation nodes')->error($message);
        }
        drupal_set_message($message);
    }

}
