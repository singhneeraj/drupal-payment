<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentOperationAccessCheck.
 */

namespace Drupal\payment;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\payment\Plugin\payment\method\Manager;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks if the user has access to perform a payment operation.
 *
 * To use the access check, add a _payment_operation_access key to the route of
 * which the value is the name of the route path slug that contains the payment
 * entity, a period, and the name of the route path slug that contains the
 * operation.
 */
class PaymentOperationAccessCheck implements AccessCheckInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_payment_operation_access', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    list($payment_slug, $operation_slug) = explode('.', $route->getRequirement('_payment_operation_access'));
    $payment = $request->attributes->get($payment_slug);
    $operation = $request->attributes->get($operation_slug);

    return $payment->getPaymentMethod()->paymentOperationAccess($payment, $operation, $payment->getPaymentMethodBrand()) ? self::ALLOW : self::DENY;
  }
}
