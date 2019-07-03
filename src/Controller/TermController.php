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
/**Description
 * @file
 * Contains \Drupal\redbook_pcl_custom\Controller\TermController.
 *
 * Used for Commodity Content type, Variety Content type.
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */

namespace Drupal\redbook_pcl_custom\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Url;
use Zend\Diactoros\Response\RedirectResponse;
/**Functions to fetch categories
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class TermController extends ControllerBase
{

    /**Function to fetch categories for selected commodity.
    *
    *
    * @param array              $form       check
         *
    * @param FormStateInterface $form_state check
         *
    * @return mixed
    */
    function fetchCategory(array &$form, FormStateInterface $form_state) 
    {
        $cm_commodity_id = $form_state->getValue('cm_commodity');
        $category_options = array();
        if (!empty($cm_commodity_id) && is_numeric($cm_commodity_id)) {
            // Fetch Parents of selected commodity.
            $storage = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term');
            $commodity_parents = $storage->loadParents($cm_commodity_id);
            foreach ($commodity_parents as $term_id => $term) {
                $category_options[$term_id] = $term->getName();
            }
            foreach ($form['field_cm_fc_produce_category']['widget'] as $index => $pc_item) {
                if (is_numeric($index)) {
                    $form['field_cm_fc_produce_category']['widget'][$index]['field_fc_produce_category']['widget']['#default_value'] = array();
                    $form['field_cm_fc_produce_category']['widget'][$index]['field_fc_produce_category']['widget']['#options'] = $category_options;
                }
            }
        } else {
            $category_options = redbook_pcl_custom_get_terms('produce_category');
            foreach ($form['field_cm_fc_produce_category']['widget'] as $index => $pc_item) {
                if (is_numeric($index)) {
                    $form['field_cm_fc_produce_category']['widget'][$index]['field_fc_produce_category']['widget']['#options'] = $category_options;
                }
            }
        }
        return $form['field_cm_fc_produce_category'];
    }

    /**Function to fetch commodities for selected variety.
    *
    *
    * @param array              $form       check
         *
    * @param FormStateInterface $form_state check
         *
    * @return mixed
    */
    function fetchCommodity(array &$form, FormStateInterface $form_state) 
    {
        $vr_variety_id = $form_state->getValue('vr_variety');
        $commodity_options = array();
        if (!empty($vr_variety_id) && is_numeric($vr_variety_id)) {
            // Fetch Parents of selected variety.
            $storage = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term');
            $variety_parents = $storage->loadParents($vr_variety_id);
            foreach ($variety_parents as $term_id => $term) {
                $commodity_options[$term_id] = $term->getName();
            }
            foreach ($form['field_vr_fc_commodity']['widget'] as $index => $pc_item) {
                if (is_numeric($index)) {
                    $form['field_vr_fc_commodity']['widget'][$index]['field_fc_commodity']['widget']['#default_value'] = array();
                    $form['field_vr_fc_commodity']['widget'][$index]['field_fc_commodity']['widget']['#options'] = $commodity_options;
                }
            }
        } else {
            $commodity_options = redbook_pcl_custom_get_terms('commodity');
            foreach ($form['field_vr_fc_commodity']['widget'] as $index => $pc_item) {
                if (is_numeric($index)) {
                    $form['field_vr_fc_commodity']['widget'][$index]['field_fc_commodity']['widget']['#options'] = $commodity_options;
                }
            }
        }
        return $form['field_vr_fc_commodity'];
    }
    /**Declare the form
    * @param array $emailsalt check
    *
    * @return void
    */

    function unsubscribeEmail($emailsalt) 
    {
        $decodeEmailId = base64_decode($emailsalt);
        if (!empty($decodeEmailId)) {
            $query = \Drupal::database()->delete('contact_message');
            $query->condition('contact_form', 'i_wanna_play');
            $query->condition('mail', "%" . $decodeEmailId . "%", 'LIKE');
            $query->execute();
        }
        return array(
        '#markup' => 'You have successfully Unsubscribed',
        );
    }

}
