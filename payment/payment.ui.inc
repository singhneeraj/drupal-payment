<?php

/**
 * @file
 * The Payment user interface.
 */

/**
 * Menu page callback: show a payment.
 *
 * @param Payment $payment
 *
 * @return array
 */
function payment_page_payment_view(Payment $payment) {
  return entity_view('payment', array($payment));
}

/**
 * Builds common elements for a payment add/edit form.
 *
 * Note that this is not a form build callback and that this function was not
 * designed to be called using drupal_get_form(). Instead, create a real form
 * build callback that calls this function directly.
 *
 * @see payment_form_standalone()
 * @see hook_payment_form_alter()
 *
 * @param array $form_state
 * @param Payment $payment
 * @param array $pmids
 *   The PMIDs of the payment methods the user is allowed to choose from.
 * @param array $parents
 *   An array with the machine names of the form's parent elements.
 *   @todo This is too important to be an optional element. Perhaps we can set
 *   this using #process in version 2?
 *
 * @return array
 *   Form information with the following keys:
 *   - elements: the form's (renderable) elements.
 *   - submit: an array of form #submit callbacks.
 */
function payment_form_embedded(array &$form_state, Payment $payment, array $pmids = array(), array $parents = array()) {
  $form_state['payment'] = $payment;

  $elements['#parents'] = $parents;
  $elements['#element_validate'] = array('payment_form_embedded_validate');
  $elements['payment_status'] = array(
    '#access' => !payment_status_is_or_has_ancestor($payment->getStatus()->status, PAYMENT_STATUS_NEW),
    '#type' => 'select',
    '#title' => t('Status'),
    '#options' => \Drupal::service('plugin.manager.payment.status')->options(),
    '#default_value' => $payment->getStatus()->status,
    '#required' => TRUE,
    '#description' => t('Updating a payment status manually can disrupt automatic payment processing.') . (user_access('payment.payment_status.view') ? ' ' . l(t('Payment status overview.'), 'admin/config/services/payment/status') : ''),
  );
  $elements['payment_line_items'] = payment_line_items($payment);
  $elements['payment_method'] = array(
    '#access' => payment_status_is_or_has_ancestor($payment->getStatus()->status, PAYMENT_STATUS_NEW),
    '#type' => 'payment_method',
    '#title' => t('Payment method'),
    '#required' => TRUE,
    '#pmids' => $pmids,
  );
  field_attach_form('payment', $payment, $elements, $form_state);

  $submit = array('payment_form_embedded_submit');
  drupal_alter('payment_form', $elements, $form_state, $submit);

  return array(
    'elements' => $elements,
    'submit' => $submit,
  );
}

/**
 * Implements form validate callback for payment_form_embedded().
 */
function payment_form_embedded_validate(array $form, array &$form_state) {
  $payment = $form_state['payment'];
  field_attach_form_validate('payment', $payment, $form, $form_state);
  field_attach_submit('payment', $payment, $form, $form_state);

  if (empty($form_state['rebuild']) && $payment->method) {
    try {
      $payment->method->validate($payment);
    }
    catch (PaymentValidationException $e) {
      form_set_error('payment_method', $e->getMessage());
    }
  }
}

/**
 * Implements form submit callback for payment_form_embedded().
 */
function payment_form_embedded_submit(array $form, array &$form_state) {
  // The payment status element may be unavailable.
  if (isset($form_state['values']['payment_status'])) {
    $form_state['payment']->setStatus(new PaymentStatusItem($form_state['values']['payment_status']));
  }
}

/**
 * Implements form build callback: the payment add/edit form.
 *
 * To alter the payment form in general, see hook_payment_form_alter().
 *
 * @see payment_form_embedded()
 * @see hook_payment_form_alter()
 *
 * @param array $form
 * @param array $form_state
 * @param Payment $payment
 * @param array $pmids
 *   The PMIDs of the payment methods the user is allowed to choose from.
 *
 * @return array
 *   A render array.
 */
function payment_form_standalone(array $form, array &$form_state, Payment $payment, array $pmids = array()) {
  $form_info = payment_form_embedded($form_state, $payment, $pmids);
  $form = $form_info['elements'];
  $form['#submit'] = array_merge($form_info['submit'], array('payment_form_standalone_submit'));
  // The stand-alone form is a special case, where the root element from
  // payment_form_embedded() is the form root. Because of that, we have to
  // re-set the validate callback.
  $form['#validate'] = $form['#element_validate'];
  $form['actions'] = array(
    '#type' => 'actions',
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => $payment->pid ? t('Save') : t('Pay'),
  );
  if ($payment->pid) {
    $form['actions']['delete'] = array(
      '#type' => 'link',
      '#title' => t('Delete'),
      '#href' => 'payment/' . $payment->pid . '/delete',
      '#access' => payment_access('delete', $payment),
    );
  }

  return $form;
}

/**
 * Implements form submit callback for payment_form().
 */
function payment_form_standalone_submit(array $form, array &$form_state) {
  $payment = $form_state['payment'];

  // Save the payment.
  entity_save('payment', $payment);

  // Execute the payment.
  if ($payment->getStatus()->status == PAYMENT_STATUS_NEW) {
    $payment->execute();
  }
  if (payment_status_is_or_has_ancestor($payment->getStatus()->status, PAYMENT_STATUS_FAILED)) {
    $form_state['rebuild'] = TRUE;
  }

  // Redirect the user.
  if (payment_access('view', $payment)) {
    $form_state['redirect'] = 'payment/' . $payment->pid;
  }
}

/**
 * Implements form build callback: payment deletion form.
 */
function payment_form_payment_delete(array $form, array &$form_state, Payment $payment) {
  $form_state['payment'] = $payment;

  return confirm_form($form, t('Do you really want to delete payment !pid?', array(
    '!pid' => $payment->pid,
  )), 'payment/' . $payment->pid, t('Existing information that uses this payment, such as a webshop order, may become unusable. This action cannot be undone.'), t('Delete payment'));
}

/**
 * Implements form submit callback for payment_form_payment_delete().
 */
function payment_form_payment_delete_submit(array $form, array &$form_state) {
  $payment = $form_state['payment'];
  entity_delete('payment', $payment->pid);
  $form_state['redirect'] = '<front>';
  drupal_set_message(t('Payment !pid has been deleted.', array(
    '!pid' => $payment->pid,
  )));
}

/**
 * Return a render array containing a Payment's status items.
 *
 * @param Payment $payment
 *
 * @return array
 */
function payment_status_items(Payment $payment) {
  $status = payment_status_info($payment->getStatus()->status, TRUE);
  $rows = array();
  foreach (array_reverse($payment->statuses) as $status_item) {
    $status = payment_status_info($status_item->status);
    $rows[] = array($status->title, format_date($status_item->created));
  }
  $build['status_items'] = array(
    '#type' => 'markup',
    '#markup' => theme('table', array(
      'header' => array(t('Status'), t('Date')),
      'rows' => $rows,
    )),
  );

  return $build;
}

/**
 * Return payment methods for use in form elements.
 *
 * @return array
 *   Keys are payment method IDs. Values are payment method specific titles.
 */
function payment_method_options() {
  $options = array();
  foreach (entity_load('payment_method') as $payment_method) {
    $options[$payment_method->pmid] = check_plain($payment_method->title_specific);
  }
  asort($options);

  return $options;
}

/**
 * Return payment method controllerss for use in form elements.
 *
 * @return array
 *   Keys are payment method controller class names. Values are controller
 *   titles.
 */
function payment_method_controller_options() {
  $options = array();
  $definitions = \Drupal::service('plugin.manager.payment.method')->getDefinitions();
  unset($definitions['payment_unavailable']);
  foreach ($definitions as $plugin_id => $definition) {
    $options[$plugin_id] = $definition['title'];
  }
  natcasesort($options);

  return $options;
}
