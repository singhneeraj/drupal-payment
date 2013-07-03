<?php

/**
 * @file
 * Contains \Drupal\payment\Annotations\PaymentMethod.
 */

namespace Drupal\payment\Annotations;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a payment method plugin annotation.
 *
 * @Annotation
 */
class PaymentMethod extends Plugin {

  /**
   * The translated human-readable plugin name (optional).
   *
   * @var string
   */
  public $description = '';

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The translated human-readable plugin name.
   *
   * @var string
   */
  public $label;

  /**
   * Available payment operations.
   *
   * For interoperability, there are a few operations with standard names that
   * plugins may or must implement:
   * - execute (required): This is a special operation that must always be
   *   implemented, and will usually result in the authorization and optionally
   *   the capture of the payment by the payer. The exact implementation depends
   *   on the plugin and its configuration.
   * - authorize: the authorization of the payment by the payer, but not the
   *   transfer of funds.
   * - cancel: the cancellation of a pending payment.
   * - capture: the transfer of funds after authorization was given previously.
   * - refund: the refund of an already completed payment.
   *
   * @var array
   *   Keys are operation names. Values are associative arrays:
   *   - interrupts_execution (bool): whether executing the operation will
   *     interrupt the execution of the script. Defaults to TRUE.
   *   - label (string): The human-readable label. Required.
   */
  public $operations = array();
}
