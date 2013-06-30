<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Basic.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Core\Annotation\Translation;
use Drupal\payment\Annotations\LineItem;
use Drupal\payment\Plugin\payment\line_item\Base;

/**
 * A basic line item.
 *
 * @LineItem(
 *   id = "payment_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends Base {
}
