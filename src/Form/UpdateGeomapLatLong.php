<?php

namespace Drupal\redbook_pcl_custom\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UpdateGeomapLatLong.
 *
 * @package Drupal\redbook_pcl_custom\Form
 */
class UpdateGeomapLatLong extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'update_geomap_latlong_field_value';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['note'] = [
      '#markup' => ''
        . '<div class="jumbotron">'
        . 'Batch To Update Latitude & Longitude values for a company node.'
        . '</div>'
    ];

    $form['update_node'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update Companies'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = [
      'title' => t('Updating Latitude and Longitude values...'),
      'operations' => [
        ['\Drupal\redbook_pcl_custom\Form\UpdateGeomapLatLong::updateGeomapLatLongField', []],
      ],
      'finished' => '\Drupal\redbook_pcl_custom\Form\UpdateGeomapLatLong::finishedCallback',
      'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    ];

    batch_set($batch);
  }

  /**
   * {@inheritdoc}
   */
  public static function updateGeomapLatLongField(&$context) {

    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = 0;
      $query = \Drupal::database()->select('node__field_comp_address', 'fca');
      $query->fields('fca', ['entity_id']);
      $query->leftJoin('node__field_map_source', 'nfms', 'fca.entity_id = nfms.entity_id');
      $query->isNull('nfms.entity_id');
      $query->leftJoin('node_revision__field_map_source', 'nrfms', 'fca.entity_id = nrfms.entity_id');
      $query->isNull('nrfms.entity_id');
      $count = $query->countQuery()->execute()->fetchField();
      $context['sandbox']['max'] = $count;
      $context['results']['totalCount'] = $count;
      $context['results']['updated_nids'] = [];
      $context['results']['missed_nids'] = [];
      $context['results']['limit_reached'] = FALSE;
      $context['results']['node_id_to_be_processed'] = 0;
      if (defined('calls_to_api')) {
        $context['results']['calls_to_api'] = calls_to_api;
      }
      else {
        $context['results']['calls_to_api'] = 0;
      }
    }

    $limit = 20;
    $query = \Drupal::database()->select('node__field_comp_address', 'fca');
    $query->fields('fca', ['entity_id']);
    $query->leftJoin('node__field_map_source', 'nfms', 'fca.entity_id = nfms.entity_id');
    $query->isNull('nfms.entity_id');
    $query->leftJoin('node_revision__field_map_source', 'nrfms', 'fca.entity_id = nrfms.entity_id');
    $query->isNull('nrfms.entity_id');
    $query->range(0, $limit);
    $query->orderBy('fca.entity_id');
    $company_ids = $query->execute()->fetchAll();
    // Adding a Delay of 2 seconds for every 50 requests.
    usleep(1000000);

    foreach ($company_ids as $key => $entity) {
      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $entity->entity_id;
      $service = \Drupal::service('redbook_pcl_custom.company');
      $node = $service->load($entity->entity_id);
      if (!empty($node)) {
        $address = NULL;
        $address = isset($node->field_comp_address->address_line1) ? $address . $node->field_comp_address->address_line1 : $address;
        $address = isset($node->field_comp_address->address_line2) ? $address . ', ' . $node->field_comp_address->address_line2 : $address;
        $address = isset($node->field_comp_address->locality) ? $address . ', ' . $node->field_comp_address->locality : $address;
        $address = isset($node->field_comp_address->administrative_area) ? $address . ', ' . $node->field_comp_address->administrative_area : $address;
        $address = isset($node->field_comp_address->postal_code) ? $address . ' ' . $node->field_comp_address->postal_code : $address;
        $address = isset($node->field_comp_address->country_code) ? $address . ', ' . $node->field_comp_address->country_code : $address;

        // Check the boolean value 'field_comp_gmap_name' on each company to see
        // if we should include name in lookup.
        if (!isset($node->field_comp_gmap_name)) {
          $address = urlencode($node->label()) . ', ' . $address;
        }

        // See if place_id exist for "address with company name".
        $address = str_replace(' ', '+', $address);
        $response = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=AIzaSyD_vksSgomrwdDt6Z7p7QOQyjPueO21tlc');
        $result = json_decode($response);

        if ($result->status == 'OK') {
          $node->set('field_map_source', 'place_id');
          $node->set('field_comp_map_value', $result->results[0]->place_id);
          $node->set('field_comp_latitude', $result->results[0]->geometry->location->lat);
          $node->set('field_comp_longitude', $result->results[0]->geometry->location->lng);
          $context['results']['calls_to_api']++;
        }
        elseif ($result->status == 'OVER_QUERY_LIMIT') {
          $context['results']['missed_nids'][] = $entity->entity_id;
          continue;
        }
        else {
          // We log in case api fails by some reason.
          // more information can be found in log then.
          // fallback: we will request map on 'address'.
          $node->set('field_map_source', 'address');
          $node->set('field_comp_map_value', $node->label());
          $node->set('field_comp_latitude', 0);
          $node->set('field_comp_longitude', 0);
          $context['results']['calls_to_api']++;
        }
        $node->save();
        $updated_nids[] = $entity->entity_id;
      }
    }
    if (!empty($context['results']['updated_nids'])) {
      $context['results']['updated_nids'] = $context['results']['updated_nids'] + $updated_nids;
    }
    else {
      $context['results']['updated_nids'] = $updated_nids;
    }

    $processedCountTillNow = $context['sandbox']['progress'];
    $totalCount = $context['sandbox']['max'];
    $current_id = $context['sandbox']['current_id'];
    $context['message'] = '<br>Updating Latitude & Longitude field values'
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
      $message = 'Finished with Success. Total ' . $results['totalCount'] . ' nodes have been processed, Skipped Missing nodes: ' . print_r($results['missing_nids'], TRUE);
      \Drupal::logger('Latitude & Longitude Batch Process')->info($message);
    }
    else {
      $message = 'Finished with an error.';
      \Drupal::logger('Latitude & Longitude Batch Process')->error($message);
    }
    $message .= '<br>No. of calls made to api: ' . $results['calls_to_api'];
    define('calls_to_api', $results['calls_to_api']);
    drupal_set_message($message);
  }

}
