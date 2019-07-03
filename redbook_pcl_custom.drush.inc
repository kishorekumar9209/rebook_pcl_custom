<?php

/**
 * @file
 * Custom Drush commands.
 */

function redbook_pcl_custom_drush_command() {
  $items = array();
  $items['update-contacts'] = array(
    'description' => 'Update Contacts from old field to new field',
    'examples' => array(
      'drush update-contacts' => 'Update Contacts',
    ),
  );
  $items['update-license'] = array(
    'description' => 'Update License from old field to new field',
    'examples' => array(
      'drush update-license' => 'Update License',
    ),
  );
  return $items;
}

function drush_redbook_pcl_custom_update_contacts() {
  drush_print('Update Contacts.');

  $batch = array(
    'title' => t('Initializing Updating nodes...'),
    'operations' => array(
      array('\Drupal\redbook_pcl_custom\Form\UpdatePersonnelContact::UpdateContactField', array()),
    ),
    'finished' => '\Drupal\redbook_pcl_custom\Form\UpdatePersonnelContact::finishedCallback',
    'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
    'init_message' => t('Starting Contacts Update.....'),
    'error_message' => t('Contact Update has encountered an error.'),
  );
  // Initialize the batch.
  batch_set($batch);
  // Start the batch process.
  drush_backend_batch_process();
}

function drush_redbook_pcl_custom_update_license() {
  drush_print('Update License.');

  $batch = array(
    'title' => t('Initializing Updating nodes...'),
    'operations' => array(
      array('\Drupal\redbook_pcl_custom\Form\UpdateCompanyLicense::UpdateLicenseField', array()),
    ),
    'finished' => '\Drupal\redbook_pcl_custom\Form\UpdateCompanyLicense::finishedCallback',
    'progress_message' => t('Completed @current of @total Records.... Estimation time: @estimate; @elapsed taken till now'),
  );
  // Initialize the batch.
  batch_set($batch);
  // Start the batch process.
  drush_backend_batch_process();
}