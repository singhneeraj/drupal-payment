<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Payment\OperationsProviderInterface.
 */

namespace Drupal\payment\Plugin\Payment;

interface OperationsProviderInterface {

  /**
   * Gets payment status operations
   *
   * @param string $plugin_id
   *   The ID of the payment status plugin the operations are for.
   *
   * @return array[]
   *   An array of the same structure as
   *   \Drupal\Core\Entity\EntityListBuilderInterface::getOperations()' return
   *   value.
   */
  public function getOperations($plugin_id);

}
