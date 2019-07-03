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

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use \Drupal\node\Entity\Node;

/**
 * Class VerifyCompanyBatchForm.
 *
 * @category Class
 * @package  Drupalredbook_Pcl_CustomForm
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class VerifyCompanyBatchForm extends FormBase
{

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function getFormId() 
    {
        return 'verify_company_batch_form';
    }

    /**Description
    * @param array              $form       Check
    *
    * @param FormStateInterface $form_state Check
    *
    * @return array
    */
    public function buildForm(array $form, FormStateInterface $form_state) 
    {
        $form['company_verify'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Enable to verify this company'),
        );
        return $form;
    }

    /**Description
    * @param array              $form       Check
    *
    * @param FormStateInterface $form_state Check
    *
    * @return array
    */
    public function submitForm(array &$form, FormStateInterface $form_state) 
    {
        $batch = $this->update_verification_content_fields();
        batch_set($batch);
    }

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    function update_verification_content_fields() 
    {
        // Give helpful information about how many nodes are being operated on.
        //$result = db_query("select entity_id from {node__field_cv_vfyd_checkmark_status} WHERE field_cv_vfyd_checkmark_status_value = '2222'")->fetchAll();
        $conn = Database::getConnection();
        $query = $conn->select('node__field_cv_vfyd_checkmark_status', 'cs');
        $query->fields('cs', array('entity_id'));
        $query->condition('cs.field_cv_vfyd_checkmark_status_value', '2222');
        $result = $query->execute()->fetchAll();

        $chunk_nid = array_chunk($result, 500);
        foreach ($chunk_nid as $data) {
            $operations[] = array(array(get_class($this), 'verify_process_operation'), array($data));
        }
        $batch = array(
        'operations' => $operations,
        'finished' => 'batch_example_finished',
        'finished' => array(get_class($this), 'batch_example_finished'),
        // We can define custom messages instead of the default ones.
        'title' => t('Processing update claim and company verification content'),
        'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
        'error_message' => t('error occured at @current'),
        );
        return $batch;
    }

    /**Description
    * {@inheritdoc}
         *
         * @param array $dataSet check
         *
         * @param array $context check
             *
             * @return array
    */
    function verify_process_operation($dataSet, &$context) 
    {
        foreach ($dataSet as $data => $node_id) {
            //$service = \Drupal::service('redbook_pcl_custom.company');
            $node = Node::load($node_id->entity_id);
            $node->set('field_verify_this_company', 1);
            $node->save();
        }
    }
    /**Description
    * @param array  $success      Check
    *
    * @param array $results Check
    *
    * @param $operations check
    *
    * @return array
    */
    function batch_example_finished($success, $results, $operations) 
    {
        if ($success) {
            $message = \Drupal::translation()->formatPlural(count($results), 'One post processed.', '@count posts processed.');
            drupal_set_message(t('Processed @nodes.', array('@nodes' => $results)));
        } else {
            $message = t('Finished with an error.');
        }
        drupal_set_message($message);
    }

}
