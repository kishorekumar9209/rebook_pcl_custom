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

// Not using the Queueworker any more. Putting it for reference for pantheon ticket created.

namespace Drupal\redbook_pcl_custom\Plugin\QueueWorker;

use Drupal\Core\Queue\QueueWorkerBase;
use GuzzleHttp\Exception\RequestException;

/**
 * Processes License field update.
 *
 * @QueueWorker(
 *   id = "update_license_fields_queue_worker",
 *   title = @Translation("Update License Fields"),
 *   cron = {"time" = 0}
 * )
 * @category     Class
 * @package      Class
 * @author       Display Name <username@example.com>
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link         Produce Market
 */
class LicenseUpdate extends QueueWorkerBase
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
        $license_id = [];
        $service = \Drupal::service('redbook_pcl_custom.company');
        $node = $service->load($node_id);

        $license_node_ids = $node->get('field_comp_licenses')->getValue();

        foreach ($license_node_ids as $license_nid) {
            $service = \Drupal::service('redbook_pcl_custom.company');
            $license_node = $service->load($license_nid['target_id']);
            if (!empty($license_node)) {
                $license_id[] = array(
                'target_id' => $license_nid['target_id'],
                'target_revision_id' => $license_node->getRevisionId(),);
            } else {
                $missing_license_nids[] = $license_nid['target_id'];
            }
        }
        if (!empty($license_id)) {
            $node->field_comp_license = $license_id;
            $node->save();
        }
        if (!empty($missing_license_nids)) {
            \Drupal::logger('Missing License target ids')->notice('<pre>'.print_r($missing_license_nids).'</pre>');
        }
    }
}