<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentInterface.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface as PluginPaymentStatusInterface;

/**
 * Defines a payment entity type .
 */
interface PaymentInterface extends ContentEntityInterface, EntityChangedInterface, ExecutableInterface {

  /**
   * Gets the payment's type plugin.
   *
   * @return \Drupal\payment\Plugin\Payment\Type\PaymentTypeInterface
   */
  public function getPaymentType();

  /**
   * Gets the currency of the payment amount.
   *
   * @return \Drupal\currency\Entity\CurrencyInterface
   */
  public function getCurrency();

  /**
   * Sets the ISO 4217 currency code of the payment amount.
   *
   * @param string $currency_code
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setCurrencyCode($currency_code);

  /**
   * Gets the ISO 4217 currency code of the payment amount.
   *
   * @return string
   */
  public function getCurrencyCode();

  /**
   * Sets line items.
   *
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $line_items
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setLineItems(array $line_items);

  /**
   * Sets a line item.
   *
   * @param \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setLineItem(PaymentLineItemInterface $line_item);

  /**
   * Unsets a line item.
   *
   * @param string $name
   *   The line item's name.
   *
   * @return self
   */
  public function unsetLineItem($name);

  /**
   * Gets all line items.
   *
   * @return array
   */
  public function getLineItems();

  /**
   * Gets a line item.
   *
   * @param string $name
   *   The line item's machine name.
   *
   * @return \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface
   */
  public function getLineItem($name);

  /**
   * Gets line items by plugin type.
   *
   * @param string $plugin_id
   *   The line item plugin's ID.
   *
   * @return \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[]
   */
  public function getLineItemsByType($plugin_id);

  /**
   * Sets/replaces all statuses without notifications.
   *
   * @param array $statuses
   *   \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface objects.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setStatuses(array $statuses);

  /**
   * Sets a status.
   *
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $status
   * @param bool $notify
   *   Whether or not to trigger a notification event.
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setStatus(PluginPaymentStatusInterface $status, $notify = TRUE);

  /**
   * Gets all statuses.
   *
   * @return array
   */
  public function getStatuses();

  /**
   * Gets the status.
   *
   * @return \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface
   */
  public function getStatus();

  /**
   * Gets the payment method plugin.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function getPaymentMethod();

  /**
   * Gets the payment method plugin.
   *
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   *
   * @return self
   */
  public function setPaymentMethod(PluginPaymentMethodInterface $payment_method);

  /**
   * Sets the ID of the user who owns this payment.
   *
   * @param int $id
   *
   * @return \Drupal\payment\Entity\PaymentInterface
   */
  public function setOwnerId($id);

  /**
   * Gets the ID of the user who owns this payment.
   *
   * @return int
   */
  public function getOwnerId();

  /**
   * Gets the owner.
   *
   * @return \Drupal\user\UserInterface
   */
  public function getOwner();

  /**
   * Gets the payment amount.
   *
   * @return float
   */
  public function getAmount();
}
