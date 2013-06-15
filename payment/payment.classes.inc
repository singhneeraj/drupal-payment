<?php

/**
 * @file
 * The API and related functions for executing and managing payments.
 */

/**
 * Base class with common functionality.
 */
class PaymentCommon {
  function __construct(array $properties = array()) {
    foreach ($properties as $property => $value) {
      $this->$property = $value;
    }
  }
}

/**
 * A single payment. Contains all payment-specific data.
 *
 * @see PaymentMethod
 * @see PaymentMethodController
 */
class Payment extends PaymentCommon {

  /**
   * The machine name of the context that created this Payment, such as the
   * webshop module.
   *
   * @var string
   */
  public $context = '';

  /**
   * Information about this payment that is specific to the context that
   * created it, such as the webshop module.
   *
   * @var array
   */
  public $context_data = array();

  /**
   * The ISO 4217 currency code of the payment amount.
   *
   * @var string
   */
  public $currency_code = 'XXX';

  /**
   * The general description of this payment. Not to be confused with line item
   * descriptions.
   *
   * @var string
   */
  public $description = '';

  /**
   * Arguments to pass on to t() when translating $this->description.
   *
   * @var array
   */
  public $description_arguments = array();

  /**
   * The name of a function to call when payment execution is completed,
   * regardless of the payment status. It receives one argument:
   * - $payment Payment
   *   The Payment object.
   * The callback does not need to return anything and is free to redirect the
   * user or display something.
   * Use Payment::context_data to pass on arbitrary data to the finish callback.
   *
   * @var string
   */
  public $finish_callback = '';

  /**
   * An array with PaymentLineItem objects.
   *
   * @see Payment::setLineItem()
   *
   * @var array
   */
  public $line_items = array();

  /**
   * The payment method used for this payment.
   *
   * @var PaymentMethod
   */
  public $method = NULL;

  /**
   * Information about this payment that is specific to its payment method.
   *
   * @var array
   */
  public $method_data = array();

  /**
   * The internal ID of this payment.
   *
   * @var integer
   */
  public $pid = 0;

  /**
   * The status log. Contains PaymentStatusItem objects ordered by datetime. Do
   * not set directly, but use Payment::setStatus() instead.
   *
   * @see Payment::setStatus()
   *
   * @var array
   */
  public $statuses = array();

  /**
   * The UID of the user this payment belongs to.
   *
   * @var integer
   */
  public $uid = NULL;

  /**
   * Constructor.
   *
   * @param array $properties
   *   An associative array. Keys are property names and values are property
   *   values.
   *
   * @return NULL
   */
  function __construct(array $properties = array()) {
    global $user;

    parent::__construct($properties);

    if (is_null($this->uid)) {
      $this->uid = $user->uid;
    }
    if (!$this->statuses) {
      // We purposefully avoid Payment::setStatus(), because this is the
      // payment's first status.
      $this->statuses[] = new PaymentStatusItem(PAYMENT_STATUS_NEW);
    }
  }

  /**
   * Execute the actual payment.
   *
   * @return NULL
   */
  function execute() {
    // Preprocess the payment.
    module_invoke_all('payment_pre_execute', $this);
    if (module_exists('rules')) {
      rules_invoke_event('payment_pre_execute', $this);
    }
    // Execute the payment.
    if ($this->method) {
      try {
        $this->method->validate($this);
        $this->setStatus(new PaymentStatusItem(PAYMENT_STATUS_PENDING));
        $this->method->controller->execute($this);
      }
      // An exception was thrown during validation.
      catch (PaymentValidationException $e) {
        $this->setStatus(new PaymentStatusItem(PAYMENT_STATUS_FAILED));
      }
    }
    // There is no payment method.
    else {
      $this->setStatus(new PaymentStatusItem(PAYMENT_STATUS_FAILED));
    }
    // This is only called if the payment execution didn't redirect the user
    // offsite. Otherwise it's the payment method return page's responsibility.
    $this->finish();
  }

  /**
   * Finish the payment after its execution.
   *
   * @return NULL
   */
  function finish() {
    entity_save('payment', $this);
    module_invoke_all('payment_pre_finish', $this);
    if (module_exists('rules')) {
      rules_invoke_event('payment_pre_finish', $this);
    }
    call_user_func($this->finish_callback, $this);
  }

  /**
   * Set a line item.
   *
   * @see Payment::getLineItems()
   *
   * @param PaymentLineItem $line_item
   *
   * @return Payment
   */
  function setLineItem(PaymentLineItem $line_item) {
    $this->line_items[$line_item->name] = $line_item;

    return $this;
  }

  /**
   * Get line items.
   *
   * @param string $name (optional)
   *   The requested line item(s)'s machine name, as possibly defined in
   *   hook_payment_line_item_info(). If $name is NULL, then all line items
   *   will be returned.
   *
   * @return array
   *   An array with matching PaymentLineItem objects.
   */
  function getLineItems($name = NULL) {
    if (is_null($name)) {
      return payment_line_item_get_all($name, $this);
    }
    elseif ($line_item_info = payment_line_item_info($name)) {
      return call_user_func($line_item_info->callback, $name, $this);
    }
    else {
      return payment_line_item_get_specific($name, $this);
    }
  }

  /**
   * Get the total payment amount.
   *
   * @param boolean $tax
   *   Whether to include taxes or not.
   * @param array $line_items
   *   An array with PaymentLineItem objects to calculate the total from.
   *   Leave empty to use $this->line_items.
   *
   * @return float
   */
  function totalAmount($tax, array $line_items = NULl) {
    $total = 0;
    if (is_null($line_items)) {
      $line_items = $this->line_items;
    }
    foreach ($line_items as $line_item) {
      $total += $line_item->totalAmount($tax);
    }

    return $total;
  }

  /**
   * Set the payment status.
   *
   * @param PaymentStatusItem $status_item
   *
   * @return Payment
   */
  function setStatus(PaymentStatusItem $status_item) {
    $previous_status_item = $this->getStatus();
    $status_item->pid = $this->pid;
    // Prevent duplicate status items.
    if ($this->getStatus()->status != $status_item->status) {
      $this->statuses[] = $status_item;
    }
    foreach (module_implements('payment_status_change') as $module_name) {
      call_user_func($module_name . '_payment_status_change', $this, $previous_status_item);
      // If a hook invocation has added another log item, a new loop with
      // invocations has already been executed and we don't need to continue
      // with this one.
      if ($this->getStatus()->status != $status_item->status) {
        return;
      }
    }
    if (module_exists('rules')) {
      rules_invoke_event('payment_status_change', $this, $previous_status_item);
    }

    return $this;
  }

  /**
   * Get the current payment status.
   *
   * @return PaymentStatusItem
   */
  function getStatus() {
    return end($this->statuses);
  }

  /**
   * Get available/valid payment methods for this payment.
   *
   * @param array $payment_methods
   *   An array with the PaymentMethod objects to check for. Use an empty
   *   array to check the availability of all payment methods.
   *
   * @return array
   *   An array with PaymentMethod objects usable for Payment in its current
   *   state, keyed by PMID.
   */
  function availablePaymentMethods(array $payment_methods = array()) {
    if (!$payment_methods) {
      $payment_methods = entity_load('payment_method', FALSE);
    }
    $available = array();
    foreach ($payment_methods as $payment_method) {
      try {
        $payment_method->validate($this, FALSE);
        $available[$payment_method->pmid] = $payment_method;
      }
      catch (PaymentValidationException $e) {
      }
    }

    return $available;
  }
}

/**
 * Entity API controller for payment entities.
 */
class PaymentEntityController extends EntityAPIController {

  /**
   * Implements EntityAPIController::load().
   */
  function load($ids = array(), $conditions = array()) {
    $entities = parent::load($ids, $conditions);
    foreach ($entities as $payment) {
      // Cast non-string scalars to their original types, because some backends
      // store/return all variables as strings.
      $payment->pid = (int) $payment->pid;
      $payment->uid = (int) $payment->uid;
    }

    return $entities;
  }

  /**
   * Implements EntityAPIController::attachLoad().
   */
  function attachLoad(&$queried_entities, $revision_id = FALSE) {
    $pids = array_keys($queried_entities);

    // Load the payments's payment methods.
    $pmids = array();
    foreach ($queried_entities as $payment) {
      $pmids[] = $payment->pmid;
    }
    $methods = entity_load('payment_method', $pmids);

    // Load line items.
    $result = db_select('payment_line_item')
      ->fields('payment_line_item')
      ->condition('pid', $pids)
      ->execute();
    $line_items = array();
    while ($line_item_data = $result->fetchAssoc('name', PDO::FETCH_ASSOC)) {
      $line_item_data['description_arguments'] = unserialize($line_item_data['description_arguments']);
      $line_item_data['amount'] = (float) $line_item_data['amount'];
      $line_item_data['tax_rate'] = (float) $line_item_data['tax_rate'];
      $line_item_data['quantity'] = (int) $line_item_data['quantity'];
      $line_items[$line_item_data['pid']][$line_item_data['name']] = new PaymentLineItem($line_item_data);
    }

    // Load the payments's status items.
    $result = db_select('payment_status_item')
      ->fields('payment_status_item')
      ->condition('pid', $pids)
      ->orderBy('psiid')
      ->execute();
    $status_items = array();
    while ($data = $result->fetchObject()) {
      $status_item = new PaymentStatusItem($data->status, $data->created, $data->pid, $data->psiid);
      $status_items[$status_item->pid][] = $status_item;
    }

    // Add all data to the payments.
    foreach ($queried_entities as $payment) {
      $payment->statuses = $status_items[$payment->pid];
      $payment->line_items = isset($line_items[$payment->pid]) ? $line_items[$payment->pid] : array();
      $payment->method = $methods[$payment->pmid];
      unset($payment->pmid);
    }

    parent::attachLoad($queried_entities, $revision_id);
  }

  /**
   * Implements EntityAPIController::save().
   */
  function save($entity, DatabaseTransaction $transaction = NULL) {
    $payment = $entity;

    // Save the payment.
    $payment->pmid = $payment->method->pmid;
    $return = parent::save($payment, $transaction);
    unset($payment->pmid);

    // Save line items.
    foreach ($payment->line_items as $line_item) {
      db_merge('payment_line_item')
        ->key(array(
          'name' => $line_item->name,
          'pid' => $payment->pid,
        ))
        ->fields(array(
          'amount' => $line_item->amount,
          'amount_total' => $line_item->amount * $line_item->quantity * ($line_item->tax_rate + 1),
          'description' => $line_item->description,
          'description_arguments' => serialize($line_item->description_arguments),
          'name' => $line_item->name,
          'pid' => $payment->pid,
          'quantity' => $line_item->quantity,
          'tax_rate' => $line_item->tax_rate,
        ))
        ->execute();
    }

    // Save the payment's status items.
    $update = empty(reset($payment->statuses)->psiid) || empty(end($payment->statuses)->psiid);
    foreach ($payment->statuses as $status_item) {
      // Statuses cannot be edited, so only save the ones without a PSIID set.
      if (!$status_item->psiid) {
        $status_item->pid = $payment->pid;
        drupal_write_record('payment_status_item', $status_item);
      }
    }
    if ($update) {
      $payment->psiid_first = reset($payment->statuses)->psiid;
      $payment->psiid_last = end($payment->statuses)->psiid;
      $query = db_update('payment')
        ->condition('pid', $payment->pid)
        ->fields(array(
          'psiid_first' => reset($payment->statuses)->psiid,
          'psiid_last' => end($payment->statuses)->psiid,
        ));
        $query->execute();
    }

    return $return;
  }

  /**
   * Implements EntityAPIController::view().
   */
  function view($entities, $view_mode = 'full', $langcode = NULL, $page = NULL) {
    $build = parent::view($entities, $view_mode, $langcode, $page);
    foreach ($build['payment'] as &$payment_build) {
      $payment = $payment_build['#entity'];
      $payment_build['payment_line_items'] = payment_line_items($payment);
      $payment_build['payment_status_items'] = payment_status_items($payment);
      $payment_build['payment_method'] = array(
        '#type' => 'item',
        '#title' => t('Payment method'),
        '#markup' => check_plain($payment->method->title_generic),
      );
    }

    return $build;
  }

  /**
   * Implements EntityAPIController::delete().
   */
  function delete($ids, DatabaseTransaction $transaction = NULL) {
    parent::delete($ids, $transaction);
    db_delete('payment_line_item')
      ->condition('pid', $ids)
      ->execute();
    db_delete('payment_status_item')
      ->condition('pid', $ids)
      ->execute();
  }
}

/**
 * A payment method that essentially disables payments.
 *
 * This is a 'placeholder' method that returns defaults and doesn't really do
 * anything else. It is used when no working payment method is available, so
 * other modules don't have to check for that.
 */
class PaymentMethodUnavailable extends PaymentMethod {

  public $enabled = FALSE;
  public $name = 'payment_method_unavailable';
  public $module = 'payment';
  // Use 0, so the payment method can be used for payments, but will never
  // collide with existing payment methods.
  public $pmid = 0;

  function __construct() {
    $this->title_specific = $this->title_generic = t('Unavailable');
    $this->controller = \Drupal::service('plugin.manager.payment.payment_method')->createInstance('payment_unavailable');
  }
}

/**
 * Payment status information.
 */
class PaymentStatusInfo extends PaymentCommon {

  /**
   * A US English human-readable plain text description.
   *
   * @var string
   */
  public $description = '';

  /**
   * This status's parent status.
   *
   * @var array
   */
  public $parent = NULL;

  /**
   * The status itself.
   *
   * @var string
   */
  public $status = '';

  /**
   * A US English human-readable plain text title.
   *
   * @var string
   */
  public $title = '';

  /**
   * Get this payment status's ancestors.
   *
   * @return array
   *   The machine names of this status's ancestors.
   */
  function ancestors() {
    $ancestors = array($this->parent);
    if ($this->parent) {
      $ancestors = array_merge($ancestors, payment_status_info($this->parent)->ancestors());
    }

    return array_unique($ancestors);
  }

  /**
   * Get this payment status's children.
   *
   * @return array
   *   The machine names of this status's children.
   */
  function children() {
    $children = array();
    foreach (payment_statuses_info() as $status_info) {
      if ($status_info->parent == $this->status) {
        $children[] = $status_info->status;
      }
    }

    return $children;
  }

  /**
   * Get this payment status's descendants.
   *
   * @return array
   *   The machine names of this status's descendants.
   */
  function descendants() {
    $children = $this->children();
    $descendants = $children;
    foreach ($children as $child) {
      $descendants = array_merge($descendants, payment_status_info($child)->descendants());
    }

    return array_unique($descendants);
  }
}

/**
 * A payment line item.
 *
 * @see Payment::setLineItem()
 */
class PaymentLineItem extends PaymentCommon {

  /**
   * The payment amount, excluding tax. The number of decimals depends on the
   * ISO 4217 currency used.
   *
   * @var float
   */
  public $amount = 0.0;

  /**
   * A US English human-readable description for this line item. May contain
   * HTML.
   *
   * @var string
   */
  public $description = '';

  /**
   * Arguments to pass on to t() when translating $this->description.
   *
   * @var array
   */
  public $description_arguments = array();

  /**
   * The unique machine name (for a certain payment).
   *
   * @var string
   */
  public $name = '';

  /**
   * The tax rate that applies to PaymentLineItem::amount. Should be a float
   * between 0 and 1.
   *
   * @var float
   */
  public $tax_rate = 0.0;

  /**
   * Quantity.
   *
   * @var integer
   */
  public $quantity = 1;

  /**
   * Return this line item's unit amount.
   *
   * @param boolean $tax
   *   Whether to include taxes or not.
   *
   * @return float
   */
  function unitAmount($tax) {
    return $this->amount * ($tax ? $this->tax_rate + 1 : 1);
  }

  /**
   * Return this line item's total amount.
   *
   * @param boolean $tax
   *   Whether to include taxes or not.
   *
   * @return float
   */
  function totalAmount($tax) {
    return $this->amount * $this->quantity * ($tax ? $this->tax_rate + 1 : 1);
  }
}
/**
 * A payment status line item.
 */
class PaymentStatusItem {

  /**
   * The status itself.
   *
   * @var string
   */
  public $status = '';

  /**
   * The Unix datetime this status was set.
   *
   * @var integer
   */
  public $created = 0;

  /**
   * The PID of the payment this status item belongs to.
   *
   * @var integer
   */
  public $pid = 0;

  /**
   * The unique internal ID of this payment status item.
   *
   * @var integer
   */
  public $psiid = 0;

  function __construct($status, $created = 0, $pid = 0, $psiid = 0) {
    $this->status = $status;
    $this->created = $created ? $created : time();
    $this->pid = $pid;
    $this->psiid = $psiid;
  }
}

/**
 * Information about a line item type.
 */
class PaymentLineItemInfo extends PaymentCommon {

  /**
   * The callback function to get this line item from the Payment.
   *
   * The function accepts the machine name of the line item to get and the
   * Payment object to get it from as parameters. It should return an array,
   * optionally filled with PaymentLineItem objects.
   *
   * @see Payment::getLineItems()
   * @see payment_line_item_get_all()
   *
   * @var string
   */
  public $callback = 'payment_line_item_get_specific';

  /**
   * The unique (for this payment) machine name.
   *
   * @var string
   */
  public $name = '';

  /**
   * The human-readable plain text title.
   *
   * @var string
   */
  public $title = '';
}

/**
 * A Payment-related exception.
 */
class PaymentException extends Exception {

  /**
   * Implements Exception::__construct().
   */
  function __construct($message = '', $code = 0, Exception $previous = NULL) {
    parent::__construct($message, $code, $previous);
    payment_debug($this->getMessage(), $this->getFile(), $this->getLine());
  }
}

/**
 * Exception thrown if a payment is not valid.
 */
class PaymentValidationException extends PaymentException {}

/**
 * Exception thrown if a payment's total amount is below the required minimum.
 */
class PaymentValidationAmountBelowMinimumException extends PaymentValidationException {}

/**
 * Exception thrown if a payment's total amount exceeds the supported maximum.
 */
class PaymentValidationAmountExceedsMaximumException extends PaymentValidationException {}

/**
 * Exception thrown if a payment has no currency set.
 */
class PaymentValidationMissingCurrencyException extends PaymentValidationException {}

/**
 * Exception thrown if a payment uses a currency that is unsupported by a
 * payment method.
 */
class PaymentValidationUnsupportedCurrencyException extends PaymentValidationException {}

/**
 * Exception thrown if a payment's finish callback is not set or the function
 * does not exist.
 */
class PaymentValidationMissingFinishCallback extends PaymentValidationException {}

/**
 * Exception thrown if a payment's description is missing.
 */
class PaymentValidationDescriptionMissing extends PaymentValidationException {}

/**
 * Exception thrown if a payment's description is too long.
 */
class PaymentValidationDescriptionTooLong extends PaymentValidationException {}

/**
 * Exception thrown if a payment method is disabled.
 */
class PaymentValidationPaymentMethodDisabledException extends PaymentValidationException {}