<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentStatusForm.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\plugin_selector\Plugin\DefaultPluginDefinitionMapper;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodUpdatePaymentStatusInterface;
use Drupal\payment\Plugin\Payment\PaymentAwarePluginFilteredPluginManager;
use Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment status update form.
 */
class PaymentStatusForm extends EntityForm {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface
   */
  protected $pluginSelectorManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Session\AccountInterface
   *   The current user.
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The URL generator.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *    The string translator.
   * @param \Drupal\plugin_selector\Plugin\PluginSelector\PluginSelector\PluginSelectorManagerInterface $plugin_selector_manager
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status plugin manager.
   */
  function __construct(AccountInterface $current_user, UrlGeneratorInterface $url_generator, TranslationInterface $string_translation, PluginSelectorManagerInterface $plugin_selector_manager, PaymentStatusManagerInterface $payment_status_manager) {
    $this->currentUser = $current_user;
    $this->paymentStatusManager = $payment_status_manager;
    $this->pluginSelectorManager = $plugin_selector_manager;
    $this->stringTranslation = $string_translation;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_user'), $container->get('url_generator'), $container->get('string_translation'), $container->get('plugin.manager.plugin_selector.plugin_selector'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['payment_status'] = $this->getPluginSelector($form_state)->buildSelectorForm([], $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions = array($actions['submit']);

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->getPluginSelector($form_state)->validateSelectorForm($form['payment_status'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->getEntity();
    $plugin_selector = $this->getPluginSelector($form_state);
    $plugin_selector->submitSelectorForm($form['payment_status'], $form_state);
    $payment->setPaymentStatus($plugin_selector->getSelectedPlugin());
    $payment->save();

    $form_state->setRedirectUrl($payment->urlInfo());
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
      /** @var \Drupal\payment\Entity\PaymentInterface $payment */
      $payment = $this->getEntity();

      $mapper = new DefaultPluginDefinitionMapper();
      $payment_status_manager = new PaymentAwarePluginFilteredPluginManager($this->paymentStatusManager, $mapper, $payment);
      $payment_method = $payment->getPaymentMethod();
      if ($payment_method instanceof PaymentMethodUpdatePaymentStatusInterface) {
        $payment_status_manager->setPluginIdFilter($payment_method->getSettablePaymentStatuses($this->currentUser, $payment));
      }

      $plugin_selector = $this->pluginSelectorManager->createInstance('payment_select_list');
      $plugin_selector->setPluginManager($payment_status_manager, $mapper);
      $plugin_selector->setRequired();
      $plugin_selector->setLabel($this->t('Payment status'));

      $form_state->set('plugin_selector', $plugin_selector);
    }

    return $plugin_selector;
  }

}
