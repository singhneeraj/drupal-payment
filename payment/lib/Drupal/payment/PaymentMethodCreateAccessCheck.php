<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentMethodAccessCheck.
 */

namespace Drupal\payment;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\payment\Plugin\payment\method\Manager;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Checks if the user has create access for payment methods.
 *
 * To use the access check, add a _payment_method_create_access key to the
 * route, of which the value is a boolean. If the route pattern contains a
 * {payment_method_plugin_id} slug, only the plugin with this ID will be
 * checked.
 */
class PaymentMethodCreateAccessCheck implements AccessCheckInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\payment\type\Manager
   */
  protected $paymentMethodManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityManager $entity_manager
   * @param \Drupal\payment\Plugin\payment\method\Manager $payment_method_manager
   */
  public function __construct(EntityManager $entity_manager, Manager $payment_method_manager) {
    $this->entityManager = $entity_manager;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_payment_method_create_access', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    if ($request->attributes->has('payment_method_plugin_id')) {
      $plugin_ids = array($request->attributes->get('payment_method_plugin_id'));
    }
    else {
      $definitions = $this->paymentMethodManager->getDefinitions();
      unset($definitions['payment_unavailable']);
      $plugin_ids = array_keys($definitions);
    }
    foreach ($plugin_ids as $plugin_id) {
      if ($this->entityManager->getAccessController('payment_method')->createAccess($plugin_id)) {
        return TRUE;
      }
    }
    return FALSE;
  }
}
