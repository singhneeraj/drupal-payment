<?php

/**
 * Contains \Drupal\payment\PaymentMethodController.
 */

namespace Drupal\payment;

/**
 * A payment method controller, e.g. the logic behind a payment method.
 *
 * @see payment_method_controller_load()
 * @see payment_method_controller_load_multiple()
 *
 * All other payment methods need to extend this class. This is a singleton
 * class. See payment_method_controller_load().
 *
 * @see Payment
 * @see PaymentMethod
 */
class PaymentMethodController {

  /**
   * Default values for the controller_data property of a PaymentMethod that
   * uses this controller.
   */
  public $controller_data_defaults = array();

  /**
   * An array with ISO 4217 currency codes that this controller supports.
   *
   * Keys are ISO 4217 currency codes. Values are associative arrays with keys
   * "minimum" and "maximum", whose values are the minimum and maximum amount
   * supported for the specified currency. Leave empty to allow all currencies.
   *
   * @var array
   */
  public $currencies = array();

  /**
   * A human-readable plain text description of this payment method controller.
   *
   * @var string
   */
  public $description = '';

  /**
   * The machine name.
   *
   * This will be set by payment_method_controller_load_multiple() as a
   * shorthand for get_class($payment_method_controller).
   *
   * @see payment_method_controller_load_multiple()
   *
   * @var string
   */
  public $name = '';

  /**
   * The function name of the payment configuration form elements.
   *
   * Note that this is not a form ID and because the form will not be called
   * using drupal_get_form(), it can only be altered by altering the form
   * payment_form(). The validate callback is expected to be a function with
   * the same name, but suffixed with "_validate". If this function exists, it
   * will be called automatically. $form_state['payment'] contains the Payment
   * that is added or edited. All method-specific information should be added
   * to it in the validate callback. The payment will be saved automatically
   * using entity_save().
   *
   * The function accepts its parent form element and &$form_state as
   * parameters. It should return an array of form elements.
   *
   * @see payment_element_info()
   * @see payment_form_method_process()
   * @see payment_form_process_method_controller_payment_configuration()
   * @see paymentmethodbasic_payment_configuration_form_elements()
   *
   * @var string
   */
  public $payment_configuration_form_elements_callback = '';

  /**
   * The function name of the payment method configuration form elements.
   *
   * Note that this is not a form ID and because the form will not be called
   * using drupal_get_form(), it can only be altered by altering the form
   * payment_form_payment_method(). The validate callback is expected to be a
   * function with the same name, but suffixed with "_validate". If this
   * function exists, it will be called automatically.
   * $form_state['payment_method'] contains the PaymentMethod that is added or
   * edited. All controller-specific information should be added to it in the
   * validate callback. The payment method will be saved automatically using
   * entity_save().
   *
   * The function accepts its parent form element and &$form_state as
   * parameters. It should return an array of form elements.
   *
   * @see payment_form_payment_method()
   * @see paymentmethodbasic_payment_method_configuration_form_elements()
   *
   * @var string
   */
  public $payment_method_configuration_form_elements_callback = '';

  /**
   * The human-readable plain text title.
   *
   * @var array
   */
  public $title = '';

  /**
   * Execute a payment.
   *
   * Note that payments may be executed even if their owner is not logged into
   * the site. This means that if you need to do access control in your
   * execute() method, you cannot use global $user.
   *
   * @param Payment $payment
   *
   * @return boolean
   *   Whether the payment was successfully executed or not.
   */
  function execute(Payment $payment) {}

  /**
   * Validate a payment against a payment method and this controller. Don't
   * call directly. Use PaymentMethod::validate() instead.
   *
   * @see PaymentMethod::validate()
   *
   * @param Payment $payment
   * @param PaymentMethod $payment_method
   * @param boolean $strict
   *   Whether to validate everything a payment method needs or to validate the
   *   most important things only. Useful when finding available payment methods,
   *   for instance, which does not require unimportant things to be a 100%
   *   valid.
   *
   * @throws PaymentValidationException
   */
  function validate(Payment $payment, PaymentMethod $payment_method, $strict) {
    // Confirm the payment method is enabled, and thus available in general.
    if (!$payment_method->enabled) {
      throw new PaymentValidationPaymentMethodDisabledException(t('The payment method is disabled.'));
    }

    if (!$payment->currency_code) {
      throw new PaymentValidationMissingCurrencyException(t('The payment has no currency set.'));
    }

    $currencies = $payment_method->controller->currencies;

    // Confirm the payment's currency is supported.
    if (!empty($this->currencies) && !isset($this->currencies[$payment->currency_code])) {
      throw new PaymentValidationUnsupportedCurrencyException(t('The currency is not supported by this payment method.'));
    }

    // Confirm the payment's description is set and valid.
    if (empty($payment->description)) {
      throw new PaymentValidationDescriptionMissing(t('The payment description is not set.'));
    }
    elseif (drupal_strlen($payment->description) > 255) {
      throw new PaymentValidationDescriptionTooLong(t('The payment description exceeds 255 characters.'));
    }

    // Confirm the finish callback is set and the function exists.
    if (empty($payment->finish_callback) || !function_exists($payment->finish_callback)) {
      throw new PaymentValidationMissingFinishCallback(t('The finish callback is not set or not callable.'));
    }

    // Confirm the payment amount is higher than the supported minimum.
    $minimum = isset($currencies[$payment->currency_code]['minimum']) ? $currencies[$payment->currency_code]['minimum'] : PAYMENT_MINIMUM_AMOUNT;
    if ($payment->totalAmount(TRUE) < $minimum) {
      throw new PaymentValidationAmountBelowMinimumException(t('The amount should be higher than !minimum.', array(
        '!minimum' => payment_amount_human_readable($minimum, $payment->currency_code),
      )));
    }

    // Confirm the payment amount does not exceed the maximum.
    if (isset($currencies[$payment->currency_code]['maximum']) && $payment->totalAmount(TRUE) > $currencies[$payment->currency_code]['maximum']) {
      throw new PaymentValidationAmountExceedsMaximumException(t('The amount should be lower than !maximum.', array(
        '!maximum' => payment_amount_human_readable($currencies[$payment->currency_code]['maximum'], $payment->currency_code),
      )));
    }
  }

  /**
   * Returns an array with the names of all available payment method
   * controllers that inherit of this one.
   *
   * return array
   */
  static function descendants() {
    $descendants = array();
    foreach (payment_method_controllers_info() as $controller_name) {
      if (is_subclass_of($controller_name, get_called_class())) {
        $descendants[] = $controller_name;
      }
    }

    return $descendants;
  }
}