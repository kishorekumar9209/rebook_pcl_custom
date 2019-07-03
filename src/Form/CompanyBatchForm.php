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

/**
 * Class CompanyBatchForm.
 *
 * @category Class
 * @package  Drupalredbook_Pcl_CustomForm
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class CompanyBatchForm extends FormBase
{

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function getFormId() 
    {
        return 'company_batch_form';
    }

    /**Description
    * {@inheritdoc}
     *
         * @param array              $form       Check the form
         *
         * @param FormStateInterface $form_state Check  form
         *
         * @return void
    */
    public function buildForm(array $form, FormStateInterface $form_state) 
    {
        $form['company_update'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Update Company fields'),
        );
        return $form;
    }

    /**Description
    * {@inheritdoc}
     *
         * @param form               $form       Check the form
         *
         * @param FormStateInterface $form_state Check  form
         *
         * @return void
    */
    public function submitForm(array &$form, FormStateInterface $form_state) 
    {
        $batch = $this->update_company_content_fields();
        batch_set($batch);
    }
    /**Description
     * {@inheritdoc}

     * @return array
     */
    function update_company_content_fields() 
    {
        // Give helpful information about how many nodes are being operated on.
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'company');
        $all_node = $query->execute();
        $chunk_nid = array_chunk($all_node, 500);
        foreach ($chunk_nid as $data) {
            $operations[] = array(array(get_class($this), 'update_process_operation'), array($data));
        }
        $batch = array(
        'operations' => $operations,
        'finished' => 'batch_example_finished',
        'finished' => array(get_class($this), 'batch_example_finished'),
        // We can define custom messages instead of the default ones.
        'title' => t('Processing update company content'),
        'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
        'error_message' => t('error occured at @current'),
        );
        return $batch;
    }
    /**Description
     * {@inheritdoc}
    *
     * @param array              $dataSet Check the form
     *
     * @param FormStateInterface $context Check  form check
     *
     * @return void
     */
    function update_process_operation($dataSet, &$context) 
    {
        foreach ($dataSet as $data => $node_id) {
            // We will not load node with Node method
            // $node = Node::load($node_id);
            // We will load node with our company service
            // This will ensure that we will get node object of latest revision
            // and will have no data loss on previous draft.
            $service = \Drupal::service('redbook_pcl_custom.company');
            $node = $service->load($node_id);
      
            if (empty($node->get('field_comp_latitude')              ->getValue()) || empty($node->get('field_comp_longitude')->getValue())
            ) {

                $string = $latitude = $longitude = '';
                foreach ($node->get('field_comp_address')
                    ->getValue() as $node_field_value) {
                    if (!empty($node_field_value['postal_code'])) {
                            $postal_code = str_replace(' ', '+', $node_field_value['postal_code']);
                            $string = $postal_code;
                    }
                    if (!empty($node_field_value['address_line1'])) {
                              $address_line1 = str_replace(' ', '+', $node_field_value['address_line1']);
                              $string .= ',' . $address_line1;
                    }
                    if (!empty($node_field_value['address_line2'])) {
                              $address_line2 = str_replace(' ', '+', $node_field_value['address_line2']);
                              $string .= ',' . $address_line2;
                    }
                    if (!empty($node_field_value['administrative_area'])) {
                              $administrative_area = str_replace(' ', '+', $node_field_value['administrative_area']);
                              $string .= ',' . $administrative_area;
                    }
                    if (!empty($node_field_value['locality'])) {
                              $locality = str_replace(' ', '+', $node_field_value['locality']);
                              $string .= ',' . $locality;
                    }
                    if (!empty($node_field_value['country_code'])) {
                              $country_code = $node_field_value['country_code'];
                              $string .= ',' . $country_code;
                    }
                }
                if (!empty($string)) {
                      $result = json_decode(file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $string . '&key=AIzaSyD_vksSgomrwdDt6Z7p7QOQyjPueO21tlc'));
                    if ($result->status == 'OVER_QUERY_LIMIT') {
                        \Drupal::logger('Request failed at:')->debug('<pre>' . print_r($node_id, true) . '</pre>');
                        break;
                    }
                    if (isset($result->results[0]->geometry)) {
                        $location = $result->results[0]->geometry;
                        if (isset($location->location)) {
                            $latitude = $location->location->lat;
                            $longitude = $location->location->lng;
                            $node->field_comp_latitude = $latitude;
                            $node->field_comp_longitude = $longitude;

                            // Calling a Service to save company.
                            $service = \Drupal::service('redbook_pcl_custom.company_node_save');
                            $service->saveCompany($node);
                        }
                    }
                }
                // Increment "current" by 1.
                $context['sandbox']['progress'] ++;
                $context['message'] = $node_id . ' processed.';
                $context['results'][] = $node_id;
            } else {
                $context['message'] = $node_id . ' skipped.';
            }
        }
    }
    /**Description
    * @param array $success check
    *
    * @param array $results check
    *
    * @param array $operations check
    *
    * @return void
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
