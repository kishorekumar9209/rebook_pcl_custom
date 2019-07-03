<?php

/**
 * @file
 * Contains \Drupal\redbook_pcl_custom\Plugin\Block\ClaimYourCompany.
 */

namespace Drupal\redbook_pcl_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'Produce Category List' block.
 *
 * @Block(
 *  id = "redbook_pcl_claim_your_company",
 *  admin_label = @Translation("Claim Your Company"),
 * )
 */
class ClaimYourCompany extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    return array(
      '#type' => 'markup',
      '#markup' => '
      <div class="hd-bg clam-bg">
    <div class="pg-tit">
        <div class="m-wdth ma wdth w-dmpl w-dmpr pl pr">    
          <div class="hd-wrp">
            <h1>Claim Your produce Market Guide Company Profile Today!</h1>
            <div class="desc"> <p> Get discovered on the most comprehensive produce industry tool to find people, products and information</p></div>
            </div>
        </div>
    </div>
  </div>
      ',
    );
  }
}