<?php
/**Description
 * @file
 * Contains \Drupal\redbook_pcl_custom\Plugin\QueueWorker\EmailQueue.
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */

// Not using the Queueworker any more.
// Putting it for reference for pantheon ticket created.

namespace Drupal\redbook_pcl_custom\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use GuzzleHttp\Exception\RequestException;

/**
 * Processes Contact field update.
 *
 * @QueueWorker(
 *   id = "update_contact_fields_queue_worker",
 *   title = @Translation("Update Contact Fields"),
 *   cron = {"time" = 0}
 * )
 * @category     Class
 * @package      Class
 * @author       Display Name <username@example.com>
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link         Produce Market
 */
class ContactsUpdate extends QueueWorkerBase
{
    /**
   * {@inheritdoc}
         *
         * @param array $node_id check
         *
         * @return array
   */
    public function processItem($node_id) 
    {
        $contact_id = [];
        $service = \Drupal::service('redbook_pcl_custom.company');
        $node = $service->load($node_id);

        $contact_node_ids = $node->get('field_comp_personnel_contact')->getValue();
        foreach ($contact_node_ids as $contact_nid) {
            $service = \Drupal::service('redbook_pcl_custom.company');
            $contact_node = $service->load($contact_nid['target_id']);
            if (!empty($contact_node)) {
                $contact_id[] = array(
                'target_id' => $contact_nid['target_id'],
                'target_revision_id' => $contact_node->getRevisionId(),);

            } else {
                $missing_contacts_nid[] = $contact_nid['target_id'];
            }
        }
        if (!empty($contact_id)) {
            $node->field_comp_personnel_contacts = $contact_id;
            $node->save();
        }
        if (!empty($missing_contacts_nid)) {
            \Drupal::logger('Missing Target contact nids')
            ->notice('<pre>'.print_r($missing_contacts_nid).'</pre>');
        }
    }
}