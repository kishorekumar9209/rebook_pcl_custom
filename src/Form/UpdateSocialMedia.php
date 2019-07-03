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

/**Class CompanyBatchForm.
 * @category Class
 * @package Drupalredbook_Pcl_CustomForm
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class UpdateSocialMedia extends FormBase
{

    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function getFormId() 
    {
        return 'update_social_media_url_link';
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
        $form['update_social_media_url_link'] = array(
        '#type' => 'submit',
        '#value' => $this->t('Update social media url link'),
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
        $batch = $this->update_social_media_url_link();
        batch_set($batch);
    }
    /**Description
    * @return array
    */
    function update_social_media_url_link() 
    {
        // Give helpful information about how many nodes are being operated on.

        $entityQuery = \Drupal::entityQuery('node')
        ->condition('type', 'company')
        ->exists('field_comp_social_media');

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
    * @param array  $dataSet     Check
    *
    * @param array $context Check
    *
    * @return array
    */
    function update_process_operation($dataSet, &$context) 
    {

        $node = Node::load($dataSet);
        $node_social_ids = $node->get('field_comp_social_media')->getValue();

        foreach ($node_social_ids as $fc_id) {
            $fc = \Drupal\field_collection\Entity\FieldCollectionItem::load($fc_id['value']);
            if (!empty($fc->get('field_comp_social_media_url')              ->getValue()[0]['uri'])
            ) {
                $value = $fc->get('field_comp_social_media_url')
                    ->getValue()[0]['uri'];
                $fc->field_comp_social_media_url_link->setValue($value);
                $fc->save();
            }
        }
        $node->save();
        // Increment "current" by 1.
        $context['sandbox']['progress']++;
        $context['message'] = $node->id() . ' processed.';
        $context['results'][] = $node->id();
    }
    /**Description
    * @param array   $success     Check
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
