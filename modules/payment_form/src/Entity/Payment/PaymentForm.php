<?php

/**
 * @file
 * Contains \Drupal\payment_form\Entity\Payment\PaymentForm.
 */

namespace Drupal\payment_form\Entity\Payment;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\plugin_selector\Plugin\DefaultPluginDefinitionMapper;
use Drupal\payment\Plugin\Payment\Method\FilteredPaymentMethodManager;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment form.
 */
class PaymentForm extends ContentEntityForm {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface
   */
  protected $pluginSelectorManager;

  /**
   * Constructs a new instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface $plugin_selector_manager
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   */
  public function __construct(EntityManagerInterface $entity_manager, TranslationInterface $string_translation, AccountInterface $current_user, PluginSelectorManagerInterface $plugin_selector_manager, PaymentMethodManagerInterface $payment_method_manager) {
    parent::__construct($entity_manager);
    $this->currentUser = $current_user;
    $this->paymentMethodManager = $payment_method_manager;
    $this->pluginSelectorManager = $plugin_selector_manager;
    $this->stringTranslation = $string_translation;
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('string_translation'), $container->get('current_user'), $container->get('plugin.manager.plugin_selector.plugin_selector'), $container->get('plugin.manager.payment.method'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $payment = $this->getEntity();

    $form['line_items'] = [
      '#payment' => $payment,
      '#type' => 'payment_line_items_display',
    ];
    $form['payment_method'] = $this->getPluginSelector($form_state)->buildSelectorForm([], $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->getPluginSelector($form_state)->validateSelectorForm($form['payment_method'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();
    $plugin_selector = $this->getPluginSelector($form_state);
    $plugin_selector->submitSelectorForm($form['payment_method'], $form_state);
    $payment->setPaymentMethod($plugin_selector->getSelectedPlugin());
    $payment->save();
    $result = $payment->execute();
    if (!$result->hasCompleted()) {
      $form_state->setRedirectUrl($result->getCompletionResponse()->getRedirectUrl());
    }
  }

  /**
   * Returns an array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Only use the existing submit action.
    $actions = parent::actions($form, $form_state);
    $actions = [
      'submit' => $actions['submit'],
    ];
    $actions['submit']['#value'] = $this->t('Pay');
    $payment_method_manager = new FilteredPaymentMethodManager($this->paymentMethodManager, $this->getEntity(), $this->currentUser);
    if (count($payment_method_manager->getDefinitions()) == 0) {
      $actions['submit']['#disabled'] = TRUE;
    }

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    // Remove this override once https://drupal.org/node/2409143 has been fixed.
    $this->getFormDisplay($form_state)
      ->extractFormValues($entity, $form, $form_state);
  }

  /**
   * Gets the plugin selector.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorInterface
   */
  protected function getPluginSelector(FormStateInterface $form_state) {
    if ($form_state->has('plugin_selector')) {
      $plugin_selector = $form_state->get('plugin_selector');
    }
    else {
      $config = $this->config('payment_form.payment_type');
      $plugin_selector_id = $config->get('plugin_selector_id');
      $limit_allowed_plugins = $config->get('limit_allowed_plugins');
      $allowed_plugin_ids = $config->get('allowed_plugin_ids');
      $plugin_selector = $this->pluginSelectorManager->createInstance($plugin_selector_id);
      $mapper = new DefaultPluginDefinitionMapper();
      $payment_method_manager = new FilteredPaymentMethodManager($this->paymentMethodManager, $this->getEntity(), $this->currentUser);
      if ($limit_allowed_plugins) {
        $payment_method_manager->setPluginIdFilter($allowed_plugin_ids);
      }
      $plugin_selector->setPluginManager($payment_method_manager, $mapper);
      $plugin_selector->setRequired();
      $plugin_selector->setLabel($this->t('Payment method'));
      $form_state->set('plugin_selector', $plugin_selector);
    }

    return $plugin_selector;
  }

}
