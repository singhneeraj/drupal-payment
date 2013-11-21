<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentMethodUI.
 */

namespace Drupal\payment;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for payment method routes.
 */
class PaymentMethodUI implements ContainerInjectionInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The payment method plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager
   */
  protected $paymentMethodManager;

  /**
   * Constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, Manager $payment_method_manager) {
    $this->entityManager = $entity_manager;
    $this->paymentMethodManager = $payment_method_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.method'));
  }

  /**
   * Enables a payment method.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function enable(PaymentMethodInterface $payment_method) {
    $payment_method->enable();
    $payment_method->save();

    return new RedirectResponse(url('admin/config/services/payment/method', array('absolute' => TRUE)));
  }

  /**
   * Disables a payment method.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function disable(PaymentMethodInterface $payment_method) {
    $payment_method->disable();
    $payment_method->save();

    return new RedirectResponse(url('admin/config/services/payment/method', array('absolute' => TRUE)));
  }

  /**
   * Displays a list of available payment method plugins.
   *
   * @return string
   */
  public function select() {
    $definitions = $this->paymentMethodManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    $items = array();
    foreach ($definitions as $plugin_id => $definition) {
      $plugin = $this->paymentMethodManager->createInstance($plugin_id);
      $payment_method = $this->entityManager->getStorageController('payment_method')->create(array())->setPlugin($plugin);
      if ($payment_method->access('create')) {
        $items[] = array(
          'title' => $definition['label'],
          'href' => 'admin/config/services/payment/method-add/' . $plugin_id,
          'description' => $definition['description'],
          'localized_options' => array(),
        );
      }
    }
    $rendered_content = theme('admin_block_content', array(
      'content' => $items,
    ));

    return array(
      '#markup' => $rendered_content,
    );
  }

  /**
   * Displays a payment method add form.
   *
   * @param string $payment_method_plugin_id
   *
   * @return array
   */
  public function add($payment_method_plugin_id) {
    $plugin = $this->paymentMethodManager->createInstance($payment_method_plugin_id);
    $payment_method = $this->entityManager->getStorageController('payment_method')->create(array())->setPlugin($plugin);

    return drupal_get_form($this->entityManager->getFormController('payment_method', 'default')->setEntity($payment_method));
  }

  /**
   * Displays a payment method clone form.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface $payment_method
   *
   * @return array
   */
  public function duplicate(PaymentMethodInterface $payment_method) {
    $clone = $payment_method
      ->createDuplicate()
      ->setLabel(t('!label (duplicate)', array(
        '!label' => $payment_method->label(),
      )));

    return drupal_get_form($this->entityManager->getFormController('payment_method', 'default')->setEntity($clone));
  }
}
