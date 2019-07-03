<?php

/**Description
 * @file
 * Custom service for saving a Company node.
 * @category Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */

namespace Drupal\redbook_pcl_custom;

use Drupal\node\Entity\Node;

/**
 * Class CompanySave.
 *
 * @category Class
 * @package  Drupalredbook_Pcl_Custom
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class CompanySave
{
    /**Description
    * @param array  $node     Check
    *
    * @return array
    */
    public function saveCompany($node) 
    {
        $node->save();
        //    return 'value123';
    }
}