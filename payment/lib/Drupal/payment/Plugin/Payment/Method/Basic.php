<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\Basic.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A basic payment method that does not transfer money.
 *
 * Plugins that extend this class must have the following keys in their plugin
 * definitions:
 * - entity_id: The ID of the payment method entity the plugin is for.
 * - status: The ID of the payment status plugin to set at payment execution.
 *
 * @PaymentMethod(
 *   derivative = "Drupal\payment\Plugin\Payment\Method\BasicDerivative",
 *   id = "payment_basic"
 * )
 */
class Basic extends PaymentMethodBase implements ContainerFactoryPluginInterface {

  /**
   * The payment status manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Utility\Token $token
   *   The token API.
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   *   The payment status manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, Token $token, PaymentStatusManagerInterface $payment_status_manager) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler, $token);
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('token'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'brand_label' => '',
      'status' => '',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function currencies() {
    return TRUE;
  }

  /**
   * Gets the ID of the payment method this plugin is for.
   *
   * @return string
   */
  public function getEntityId() {
    return $this->pluginDefinition['entity_id'];
  }

  /**
   * Gets the final payment status.
   *
   * @return string
   *   The plugin ID of the payment status to set.
   */
  public function getStatus() {
    return $this->pluginDefinition['status'];
  }

  /**
   * {@inheritdoc}
   */
  public function executePayment(PaymentInterface $payment) {
    $payment->setStatus($this->paymentStatusManager->createInstance($this->getStatus()));
    $payment->save();
    $payment->getPaymentType()->resumeContext();
  }

  /**
   * {@inheritdoc}
   */
  public static function getOperations($plugin_id) {
    // @todo Use the payment method operations provider when
    //   https://drupal.org/node/1839516 is committed.

    // Strip the base plugin ID and the colon.
    $entity_id = substr($plugin_id, 14);
    $payment_method = \Drupal::entityManager()->getStorage('payment_method')->load($entity_id);
    $operations = array();
    if ($payment_method->access('update')) {
      $operations['update'] = array(
        'title' => t('Edit configuration'),
        'route_name' => 'payment.payment_method.edit',
        'route_parameters' => array(
          'payment_method' => $entity_id,
        ),
      );
    }
    if ($payment_method->access('delete')) {
      $operations['delete'] = array(
        'title' => t('Delete configuration'),
        'route_name' => 'payment.payment_method.delete',
        'route_parameters' => array(
          'payment_method' => $entity_id,
        ),
      );
    }
    if ($payment_method->access('enable')) {
      $operations['enable'] = array(
        'title' => t('Enable configuration'),
        'route_name' => 'payment.payment_method.enable',
        'route_parameters' => array(
          'payment_method' => $entity_id,
        ),
      );
    }
    if ($payment_method->access('disable')) {
      $operations['disable'] = array(
        'title' => t('Disable configuration'),
        'route_name' => 'payment.payment_method.disable',
        'route_parameters' => array(
          'payment_method' => $entity_id,
        ),
      );
    }

    // Set the destinations, as we re-use existing operations routes elsewhere,
    // but we want users to end up at the same page as where these links are
    // displayed.
    foreach (array('enable', 'disable') as $operation) {
      if (isset($operations[$operation])) {
        $operations[$operation]['query']['destination'] = \Drupal::request()->attributes->get('_system_path');
      }
    }

    return $operations;
  }
}
