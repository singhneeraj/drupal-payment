<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\MethodSelector\AdvancedPaymentMethodSelectorBase.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a default base for most payment method selectors.
 *
 * This class takes care of everything, except the actual selection element.
 */
abstract class AdvancedPaymentMethodSelectorBase extends PaymentMethodSelectorBase {

  /**
   * The form element ID.
   *
   * @see self::getElementId
   *
   * @var string
   */
  protected $elementId;

  /**
   * The previously selected payment methods.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   */
  protected $selectedPaymentMethods = [];

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, AccountInterface $current_user, PaymentMethodManagerInterface $payment_method_manager, TranslationInterface $string_translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $current_user, $payment_method_manager);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('current_user'), $container->get('plugin.manager.payment.method'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $available_payment_methods = $this->getAvailablePaymentMethods();
    if (count($available_payment_methods) == 0) {
      $callback_method = 'buildNoAvailablePaymentMethods';
    }
    elseif (count($available_payment_methods) == 1) {
      $callback_method = 'buildOneAvailablePaymentMethod';
    }
    else {
      $callback_method = 'buildMultipleAvailablePaymentMethods';
    }

    $form['container'] = array(
      '#attributes' => array(
        'class' => array('payment-method-selector-' . Html::getId($this->getPluginId())),
      ),
      '#available_payment_methods' => $available_payment_methods,
      '#process' => array(array($this, $callback_method)),
      '#tree' => TRUE,
      '#type' => 'container',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $payment_method_id = NestedArray::getValue($values, array_merge($form['container']['#parents'], array('select', 'container', 'payment_method_id')));
    // If a (different) payment method was chosen, rebuild the form.
    if (!$this->getPaymentMethod() && $payment_method_id || $this->getPaymentMethod() && $payment_method_id != $this->getPaymentMethod()->getPluginId()) {
      $form_state->setRebuild();
      // Keep track of all previously selected payment methods so their
      // configuration does not get lost.
      if (!isset($this->selectedPaymentMethods[$payment_method_id])) {
        $this->selectedPaymentMethods[$payment_method_id] = $this->paymentMethodManager->createInstance($payment_method_id);
        $this->selectedPaymentMethods[$payment_method_id]->setPayment($this->getPayment());
      }
      $this->setPaymentMethod($this->selectedPaymentMethods[$payment_method_id]);
    }
    // If no (different) payment method was chosen, delegate validation to the
    // payment method.
    elseif ($this->getPaymentMethod()) {
      $this->getPaymentMethod()->validateConfigurationForm($form['container']['payment_method_form'], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->getPaymentMethod()) {
      $this->getPaymentMethod()->submitConfigurationForm($form['container']['payment_method_form'], $form_state);
    }
  }

  /**
   * Implements form API's #submit.
   */
  public function rebuildForm(array $form, FormStateInterface $form_state) {
    $form_state->setRebuild();
  }

  /**
   * Implements form AJAX callback.
   */
  public static function ajaxRebuildForm(array &$form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $form_parents = array_slice($triggering_element['#array_parents'], 0, -3);
    $root_element = NestedArray::getValue($form, $form_parents);

    return $root_element['payment_method_form'];
  }

  /**
   * Builds the payment method configuration form elements.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  protected function buildPaymentMethodForm(FormStateInterface $form_state) {
    $element = array(
      '#attributes' => array(
        'class' => array('payment-method-selector-' . Html::getId($this->getPluginId()) . '-payment-method-form'),
      ),
      '#id' => $this->getElementId(),
      '#type' => 'container',
    );
    if ($this->getPaymentMethod()) {
      $element += $this->getPaymentMethod()->buildConfigurationForm([], $form_state);
    }

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for when there are no available payment methods.
   */
  public function buildNoAvailablePaymentMethods(array $element, FormStateInterface $form_state, array $form) {
    $element['select']['container'] = array(
      '#type' => 'container',
    );
    $element['select']['container']['payment_method_id'] = array(
      '#type' => 'value',
      '#value' => NULL,
    );
    $element['select']['message'] = array(
      '#markup' => $this->t('There are no available payment methods.'),
    );

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for one payment method.
   */
  public function buildOneAvailablePaymentMethod(array $element, FormStateInterface $form_state, array $form) {
    $payment_method = reset($element['#available_payment_methods']);

    // Use the only available payment method if no other was configured
    // before, or the configured payment method is not available.
    if (is_null($this->getPaymentMethod()) || $this->getPaymentMethod()->getPluginId() != $payment_method->getPluginId()) {
      $this->setPaymentMethod($payment_method);
    }

    $element['select']['container'] = array(
      '#type' => 'container',
    );
    $element['select']['container']['payment_method_id'] = array(
      '#type' => 'value',
      '#value' => $this->getPaymentMethod()->getPluginId(),
    );
    $element['payment_method_form'] = $this->buildPaymentMethodForm($form_state);

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for multiple payment methods.
   */
  public function buildMultipleAvailablePaymentMethods(array $element, FormStateInterface $form_state, array $form) {
    $payment_methods = $element['#available_payment_methods'];

    $element['select'] = $this->buildSelector($element, $form_state, $payment_methods);
    $element['payment_method_form'] = $this->buildPaymentMethodForm($form_state);

    return $element;
  }

  /**
   * Builds the form elements for the actual payment method selector.
   *
   * @param array $root_element
   *   The plugin's root element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form's state.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[] $payment_methods
   *   The available payment methods.
   *
   * @return array
   *   The selector's form elements.
   */
  protected function buildSelector(array $root_element, FormStateInterface $form_state, array $payment_methods) {
    $build['container'] = array(
      '#attributes' => array(
        'class' => array('payment-method-selector-' . Html::getId($this->getPluginId() . '-selector')),
      ),
      '#type' => 'container',
    );
    $build['container']['payment_method_id'] = array(
      '#markup' => 'This element must be overridden to provide the payment method ID.',
    );
    $root_element_parents = $root_element['#parents'];
    $change_button_name = array_shift($root_element_parents) . ($root_element_parents ? '[' . implode('][', $root_element_parents) . ']' : NULL) . '[select][container][change]';
    $build['container']['change'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'ajaxRebuildForm'),
      ),
      '#attributes' => array(
        'class' => array('js-hide')
      ),
      '#limit_validation_errors' => array(array_merge($root_element['#parents'], array('select', 'payment_method_id'))),
      '#name' => $change_button_name,
      '#submit' => array(array($this, 'rebuildForm')),
      '#type' => 'submit',
      '#value' => $this->t('Choose payment method'),
    );

    return $build;
  }

  /**
   * Retrieves the element's ID from the form's state.
   *
   * @return string
   */
  protected function getElementId() {
    if (!$this->elementId) {
      $this->elementId = Html::getUniqueId($this->getPluginId());
    }

    return $this->elementId;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod(PaymentMethodInterface $payment_method) {
    $this->selectedPaymentMethods[$payment_method->getPluginId()] = $payment_method;

    return parent::setPaymentMethod($payment_method);
  }

}
