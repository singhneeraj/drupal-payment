<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Basic.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\PaymentLineItem;
use Drupal\payment\Plugin\payment\line_item\Base;

/**
 * A basic line item.
 *
 * @PaymentLineItem(
 *   id = "payment_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends Base {

  /**
   * {@inheritdoc}
   */
  function getDescription() {
  }
}
