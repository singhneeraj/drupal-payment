<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentMethodAccessCheck.
 */

namespace Drupal\payment;

use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Access\AccessCheckInterface;

/**
 * Checks if there is any payment method plugin for which the user has access to
 * perform an operation on payment methods using that plugin.
 *
 * To use the access check, add a _payment_method_access key to the route, with
 * its value being the operation to check for. If the route pattern contains a
 * {payment_method_plugin_id} slug, only the plugin with this ID will be checked.
 */
class PaymentMethodAccessCheck implements AccessCheckInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(Route $route) {
    return array_key_exists('_payment_method_access', $route->getRequirements());
  }

  /**
   * {@inheritdoc}
   */
  public function access(Route $route, Request $request) {
    $requirements = $route->getRequirements();
    $manager = \Drupal::service('plugin.manager.payment.payment_method');
    if ($request->attributes->has('payment_method_plugin_id')) {
      $plugin_id = $request->attributes->get('payment_method_plugin_id');
      $definitions = array(
        $plugin_id => $manager->getDefinition($plugin_id),
      );
    }
    else {
      $definitions = $manager->getDefinitions();
    }
    unset($definitions['payment_unavailable']);
    foreach (array_keys($definitions) as $plugin_id) {
      $payment_method = entity_create('payment_method', array())->setPlugin($manager->createInstance($plugin_id));
      if ($payment_method->access($requirements['_payment_method_access'])) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
