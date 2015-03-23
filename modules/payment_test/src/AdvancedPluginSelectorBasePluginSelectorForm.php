<?php

/**
 * @file
 * Contains \Drupal\payment_test\AdvancedPluginSelectorBasePluginSelectorForm.
 */

namespace Drupal\payment_test;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\payment\Entity\Payment;
use Drupal\payment\Entity\PaymentMethodConfiguration;
use Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper;
use Drupal\payment\Plugin\Payment\Method\FilteredPaymentMethodManager;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to test plugin selector plugins based on AdvancedPluginSelectorBase.
 */
class AdvancedPluginSelectorBasePluginSelectorForm implements ContainerInjectionInterface, FormInterface {

  use DependencySerializationTrait;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface
   */
  protected $pluginSelectorManager;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * Constructs a new class instance.
   */
  function __construct(PaymentMethodManagerInterface $payment_method_manager, PluginSelectorManagerInterface $plugin_selector_manager) {
    $this->paymentMethodManager = $payment_method_manager;
    $this->pluginSelectorManager = $plugin_selector_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.payment.method'), $container->get('plugin.manager.payment.plugin_selector'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_test_payment_method_element';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_id = NULL, $tree = FALSE) {
    if ($form_state->has('plugin_selector')) {
      $plugin_selector = $form_state->get('plugin_selector');
    }
    else {
      $payment = Payment::create([
        'bundle' => 'payment_unavailable',
      ]);
      $payment_method_manager = new FilteredPaymentMethodManager($this->paymentMethodManager, $payment, \Drupal::currentUser());
      $allowed_plugin_ids = [];
      foreach ($this->paymentMethodManager->getDefinitions() as $plugin_definition) {
        if (strpos($plugin_definition['id'], 'payment_basic') === 0) {
          $allowed_plugin_ids[] = $plugin_definition['id'];
        }
      }
      $payment_method_manager->setPluginIdFilter($allowed_plugin_ids);
      $plugin_selector = $this->pluginSelectorManager->createInstance($plugin_id);
      $plugin_selector->setPluginManager($payment_method_manager, new DefaultPluginDefinitionMapper());
      $plugin_selector->setRequired();
      $form_state->set('plugin_selector', $plugin_selector);
    }

    $form['payment_method'] = $plugin_selector->buildSelectorForm([], $form_state);
    // Nest the selector in a tree if that's required.
    if ($tree) {
      $form['tree'] = array(
        '#tree' => TRUE,
      );
      $form['tree']['payment_method'] = $form['payment_method'];
      unset($form['payment_method']);
    }
    $form['actions'] = array(
      '#type' => 'actions',
    );
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface $plugin_selector */
    $plugin_selector = $form_state->get('plugin_selector');
    $plugin_form = isset($form['tree']) ? $form['tree']['payment_method'] : $form['payment_method'];
    $plugin_selector->validateSelectorForm($plugin_form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface $plugin_selector */
    $plugin_selector = $form_state->get('plugin_selector');
    $plugin_form = isset($form['tree']) ? $form['tree']['payment_method'] : $form['payment_method'];
    $plugin_selector->submitSelectorForm($plugin_form, $form_state);
    \Drupal::state()->set('payment_test_method_form_element', $plugin_selector->getSelectedPlugin());
  }
}
