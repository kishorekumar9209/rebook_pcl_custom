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
 * Contains \Drupal\redbook_pcl_custom\Plugin\Block\Subscribe.
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */

namespace Drupal\redbook_pcl_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Email Subscribe' block.
 *
 * @Block(
 *  id = "redbook_pcl_email_subscribe",
 *  admin_label = @Translation("Email Subscribe"),
 * )
 * @category Class
 * @package  Class
 * @author   Display Name <username@example.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     Produce Market
 */
class Subscribe extends BlockBase
{


    /**
   * {@inheritdoc}
         *
         * @return array
   */
    public function build() 
    {
        return array(
        '#type' => 'markup',
        '#markup' => '
      <div id="om-ukeiijve8xcg3jb1-holder" ></div>
      ',
        );
    }

}