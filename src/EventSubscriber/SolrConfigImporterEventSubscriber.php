<?php

/**
 * @file
 * Contains \Drupal\redbook_pcl_custom\EventSubscriber
 */

namespace Drupal\redbook_pcl_custom\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\ConfigImporterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Modifying solr config after config Import.
 */
class SolrConfigImporterEventSubscriber implements EventSubscriberInterface {
  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The source storage used to discover configuration changes.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $sourceStorage;

  /**
   * Constructs the SolrConfigImporterEventSubscriber object.
   *
   * @param StorageInterface $source_storage
   *   The source storage used to discover configuration changes.
   * @param StorageInterface $snapshot_storage
   *   The snapshot storage used to write configuration changes.
   */
  public function __construct(ConfigManagerInterface $config_manager, StorageInterface $source_storage) {
    $this->configManager = $config_manager;
    $this->sourceStorage = $source_storage;
  }

  /**
   * Change the Solr config (to index itrems immediately) on Live after
   * Configuration import.
   *
   * @param \Drupal\Core\Config\ConfigImporterEvent $event
   *   The Event to process.
   */
  public function onConfigImporterImport(ConfigImporterEvent $event) {
    // replace with $source_storage.
    if ($_SERVER['HTTP_HOST'] == 'live-redbook-pcl.pantheonsite.io' || $_SERVER['HTTP_HOST'] == 'www.producemarketguide.com') {
      $search_api_index_settings = $this->sourceStorage->read('search_api.index.content_search_index');
      $search_api_index_settings['options']['index_directly'] = TRUE;
      $this->sourceStorage->write('search_api.index.content_search_index', $search_api_index_settings);
    }
  }

  /**
   * Registers the methods in this class that should be listeners.
   *
   * @return array
   *   An array of event listener definitions.
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::IMPORT][] = ['onConfigImporterImport'];
    return $events;
  }
}