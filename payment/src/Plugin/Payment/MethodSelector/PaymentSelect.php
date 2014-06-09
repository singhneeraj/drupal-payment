<?php

/**
 * @file Contains \Drupal\payment\Plugin\Payment\MethodSelector\PaymentSelect.
 */

namespace Drupal\payment\Plugin\Payment\MethodSelector;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a payment selector using a <select> element.
 *
 * @PaymentMethodSelector(
 *   id = "payment_select",
 *   label = @Translation("Drop-down selector")
 * )
 */
class PaymentSelect extends PaymentMethodSelectorBase {

  /**
   * The previously selected payment methods.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[]
   */
  protected $selectedPaymentMethods = array();

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
  public function buildConfigurationForm(array $form, array &$form_state) {
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
      '#available_payment_methods' => $available_payment_methods,
      // The element does not actually have input, but we need the #name
      // property to be populated by form API.
      '#input' => TRUE,
      '#process' => array(array($this, $callback_method)),
      '#tree' => TRUE,
      '#type' => 'container',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
    $payment_method_id = NestedArray::getValue($form_state['values'], array_merge($form['container']['#parents'], array('select', 'payment_method_id')));
    // If a (different) payment method was chosen, rebuild the form.
    if (!$this->getPaymentMethod() && $payment_method_id || $this->getPaymentMethod() && $payment_method_id != $this->getPaymentMethod()->getPluginId()) {
      $form_state['rebuild'] = TRUE;
      // Keep track of all previously selected payment methods so their
      // configuration does not get lost.
      if (!isset($this->selectedPaymentMethods[$payment_method_id])) {
        $this->selectedPaymentMethods[$payment_method_id] = $this->paymentMethodManager->createInstance($payment_method_id);
        $this->selectedPaymentMethods[$payment_method_id]->setPayment($this->getPayment());
      }
      $this->selectedPaymentMethod = $this->selectedPaymentMethods[$payment_method_id];
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
  public function submitConfigurationForm(array &$form, array &$form_state) {
    if ($this->getPaymentMethod()) {
      $this->getPaymentMethod()->submitConfigurationForm($form['container']['payment_method_form'], $form_state);
    }
  }

  /**
   * Implements form AJAX callback.
   */
  public function ajaxSubmitConfigurationForm(array &$form, array &$form_state) {
    $form_parents = array_slice($form_state['triggering_element']['#array_parents'], 0, -2);
    $root_element = NestedArray::getValue($form, $form_parents);

    return $root_element['payment_method_form'];
  }

  /**
   * Implements form API's #submit.
   */
  public function rebuildForm(array $form, array &$form_state) {
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Builds the payment method configuration form elements.
   *
   * @param array $form_state
   *
   * @return array
   */
  protected function buildPaymentMethodForm(array &$form_state) {
    $element = array(
      '#id' => $this->getElementId($form_state),
      '#type' => 'container',
    );
    if ($this->getPaymentMethod()) {
      $element += $this->getPaymentMethod()->buildConfigurationForm(array(), $form_state);
    }

    return $element;
  }

  /**
   * Implements a form #process callback.
   *
   * Builds the form elements for when there are no available payment methods.
   *
   */
  public function buildNoAvailablePaymentMethods(array $element, array &$form_state, array $form) {
    $element['select'] = array(
      '#tree' => TRUE,
    );
    $element['select']['payment_method_id'] = array(
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
  public function buildOneAvailablePaymentMethod(array $element, array &$form_state, array $form) {
    $payment_method = reset($element['#available_payment_methods']);

    // Use the only available payment method if no other was configured
    // before, or the configured payment method is not available.
    if (is_null($this->getPaymentMethod()) || $this->getPaymentMethod()->getPluginId() != $payment_method->getPluginId()) {
      $this->selectedPaymentMethod = $payment_method;
    }

    $element['select']['payment_method_id'] = array(
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
  public function buildMultipleAvailablePaymentMethods(array $element, array &$form_state, array $form) {
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
   * @param array $form_state
   *   The form's state.
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface[] $payment_methods
   *   The available payment methods.
   *
   * @return array
   *   The selector's form elements.
   */
  protected function buildSelector(array $root_element, array &$form_state, array $payment_methods) {
    $payment_method_options = array();
    foreach ($payment_methods as $payment_method) {
      $payment_method_options[$payment_method->getPluginId()] = $payment_method->getPluginLabel();
    }
    $element['payment_method_id'] = array(
      '#ajax' => array(
        'effect' => 'fade',
        'event' => 'change',
        'trigger_as' => array(
          'name' => $root_element['#name'] . '[select][change]',
        ),
        'wrapper' => $this->getElementId($form_state),
      ),
      '#default_value' => is_null($this->getPaymentMethod()) ? NULL : $this->getPaymentMethod()->getPluginId(),
      '#empty_value' => 'select',
      '#options' => $payment_method_options ,
      '#required' => $this->isRequired(),
      '#title' => $this->t('Payment method'),
      '#type' => 'select',
    );
    $element['change'] = array(
      '#ajax' => array(
        'callback' => array($this, 'ajaxSubmitConfigurationForm'),
      ),
      '#attributes' => array(
        'class' => array('js-hide')
      ),
      '#limit_validation_errors' => array(array_merge($root_element['#parents'], array('select', 'payment_method_id'))),
      '#name' => $root_element['#name'] . '[select][change]',
      '#submit' => array(array($this, 'rebuildForm')),
      '#type' => 'submit',
      '#value' => $this->t('Choose payment method'),
    );

    return $element;
  }

  /**
   * Retrieves the element's ID from the form's state.
   *
   * @param array $form_state
   *
   * @return string
   */
  protected function getElementId(array &$form_state) {
    if (!(isset($form_state[$this->getPluginId()]) && array_key_exists(spl_object_hash($this), $form_state[$this->getPluginId()]))) {
      $form_state[$this->getPluginId()][spl_object_hash($this)]['element_id'] = drupal_html_id($this->getPluginId());
    }

    return $form_state[$this->getPluginId()][spl_object_hash($this)]['element_id'];
  }

}
