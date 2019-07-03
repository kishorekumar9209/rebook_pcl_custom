<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\redbook_pcl_custom;

use Drupal\node\Entity\Node;

/**
 * Description of LoadCompany
 *
 * @author Admin
 */
class GetNotificationCount {

  public function noticationcount() {
    $current_user_id = \Drupal::currentUser()->id();
    $user = \Drupal\user\Entity\User::load($current_user_id);
    $notification_count = 2;
    if (in_array('regular', $user->getRoles()) || in_array('business', $user->getRoles()) || in_array('editor', $user->getRoles()) || in_array('site_owner', $user->getRoles()) || in_array('administrator', $user->getRoles())) {
      $notification_count = $notification_count - 1;
    }
    if (count($user->get('field_where_i_work')->getValue()) != 0) {
      $notification_count = $notification_count - 1;
    }
    $pending_notification_count = 0;
    $endorsement_service = \Drupal::service('endorsement.service');
    $current_user_id = \Drupal::currentUser()->id();
    $conditions = array('read_receipt' => 0);
    $results = $endorsement_service->getEndorsementByOwner($current_user_id, TRUE, $conditions);
    if (!empty($results)) {
      foreach ($results['company_endorsement'] as $endorsed_entity_id => $values) {
        foreach ($values['endorsement_entity'] as $endorsement_entity_id => $endorsement) {
          if (!empty($endorsement->getEndorsedBy())) {
            $endorsed_company_id = $values['target_entity']->id();

            $claimed_companies = _get_claimed_companies($current_user_id);
            if (in_array($endorsed_company_id, $claimed_companies)) {
              $pending_notification_count++;
            }
          }
        }
      }
    }
    $count = $notification_count + $pending_notification_count;
    return $count;
  }

}
