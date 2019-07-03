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
class UpdatePhoneFieldValue extends FormBase
{

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function getFormId() 
    {
        return 'update_phone_field_value';
    }

    /**Description
    * @param array              $form       Check the form
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
        . 'We are going update the field collection values to new paragraph field'
        . '<br><br>'
        . '<ol>'
        . '<li>field_cont_phone(field collection field) to field_cont_phones(paragraph)</li>'
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
    * @param array              $form       Check the form
    *
    * @param FormStateInterface $form_state Check  form
    *
    * @return array
    */

    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $batch = $this->update_phone_field_value();
        batch_set($batch);
    }
    /**Description
    * @return array
    */

    function update_phone_field_value() 
    {
        // Give helpful information about how many nodes are being operated on.
        $entityQuery = \Drupal::entityQuery('node')
        ->condition('type', 'contact')
        ->exists('field_cont_phone');
        $nids = $entityQuery->execute();
        $operations = array();
        foreach ($nids as $data) {
            $operations[] = array(
            array(get_class($this), 'update_process_operation'),
            array($data)
            );
        }

        $batch = array(
        'operations' => $operations,
        //   'finished' => 'batch_example_finished',
        'finished' => array(get_class($this), 'batch_example_finished'),
        // We can define custom messages instead of the default ones.
        'title' => t('Processing update company content'),
        'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
        'error_message' => t('error occured at @current'),
        );
        return $batch;
    }
    /**Description
    * @param array  $dataSet      Check
    *
    * @param array $context check
    *
    * @return array
    */

    function update_process_operation($dataSet, &$context) 
    {
        $phone_contact = [];
        $node = Node::load($dataSet);
        $node_phone_ids = $node->get('field_cont_phone')->getValue();

        foreach ($node_phone_ids as $fc_id) {
            $fc = \Drupal\field_collection\Entity\FieldCollectionItem::load($fc_id['value']);
            $phone_number_value = $fc->get('field_cont_phone_no')->value;
            $phone_number_extension = $fc->get('field_cont_phone_no_extension')->value;
            $phone_type = $fc->get('field_cont_phone_no_type')->value;
            $paragraph = Paragraph::create(
                [
                'type' => 'contact_phone',
                'field_cont_phone_num' => $phone_number_value,
                'field_cont_phone_num_extension' => $phone_number_extension,
                'field_cont_phone_num_type' => $phone_type,
                ]
            );
            $paragraph->save();
            $phone_contact[] = array(
              'target_id' => $paragraph->id(),
              'target_revision_id' => $paragraph->getRevisionId(),
            );
        }
        $node->field_cont_phones = $phone_contact;
        $node->save();
        // Increment "current" by 1.
        $context['sandbox']['progress'] ++;
        $context['message'] = $node->id() . ' processed.';
        $context['results'][] = $node->id();
    }
    /**Description
    * @param array   $success   Check
    *
    * @param array $results Check
    *
    * @param array $operations check
    *
    * @return array
    */

    function batch_example_finished($success, $results, $operations) 
    {
        if ($success) {
            $message = \Drupal::translation()
              ->formatPlural(count($results), 'One post processed.', '@count posts processed.');
            drupal_set_message(t('Processed @nodes.', array('@nodes' => $results)));
        } else {
            $message = t('Finished with an error.');
        }
        drupal_set_message($message);
    }

}
