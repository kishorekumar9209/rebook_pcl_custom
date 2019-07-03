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
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class Company
{
    /**Description
    * @param array   $node   Check
    *
    * @param array $on_save_create_revision Check
    *
    * @return array
    */
    public function load($node, $on_save_create_revision = false) 
    {
        if (!is_object($node)) {
            $node = Node::load($node);
        }
    
        if (!$node) {
            return false;
        }
    
        
        $nodeStorage = \Drupal::entityManager()->getStorage('node');
    
        $revisionIds = $nodeStorage->revisionIds($node);
        $latest_revision_id = end($revisionIds);
    
        $latestRevisionNode = $nodeStorage->loadRevision($latest_revision_id);
    
        if ($on_save_create_revision == false) {
            $latestRevisionNode->rb_update_current_revision = true;
            $latestRevisionNode->rb_current_revision_id = $latest_revision_id;
        }
        return $latestRevisionNode;
    }
    /**Description
    * @param array   $latestRevisionNode      Check
    *
    * @param array $log_message Check
    *
    * @return array
    */
    public function setRevisionLogMessage(&$latestRevisionNode, $log_message) 
    {
        $latestRevisionNode->setRevisionLogMessage($log_message);
    }
}
