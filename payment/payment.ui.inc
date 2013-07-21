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
 * Return a render array containing a Payment's line items.
 *
 * @param Payment $payment
 *
 * @return array
 */
function payment_line_items(Payment $payment) {
  $rows = array();
  foreach ($payment->line_items as $name => $line_item) {
    $rows[] = array(
      'data' => array(
        t($line_item->description, $line_item->description_arguments),
        $line_item->quantity,
        payment_amount_human_readable($line_item->unitAmount(TRUE), $payment->currency_code),
        payment_amount_human_readable($line_item->totalAmount(TRUE), $payment->currency_code),
        t('!amount (!percentage%)', array(
          '!amount' => payment_amount_human_readable($line_item->amount * $line_item->tax_rate, $payment->currency_code),
          '!percentage' => $line_item->tax_rate * 100,
        )),
      ),
      'class' => array('payment-line_item-' . $name),
    );
  }
  $rows[] = array(
    'data' => array(array(
      'data' => t('Total amount'),
      'colspan' => 3,
      ), payment_amount_human_readable($payment->totalAmount(TRUE), $payment->currency_code), '',
    ),
    'class' => array('payment-line_item-total'),
  );
  $build = array(
    '#type' => 'markup',
    '#markup' => theme('table', array(
      'header' => array(t('Description'), t('Quantity'), t('Amount'), t('Total'), t('Tax')),
      'rows' => $rows,
    )),
  );

  return $build;
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
 * Implements form process callback for a payment_form_context element.
 */
function payment_form_process_context(array $element, array &$form_state, array $form) {
  $elements = \Drupal::service('plugin.manager.payment.payment_method')->createInstance($element['#payment_method_controller_name'])->paymentFormElements($element, $form_state);
  $element = array_merge($element, $elements);

  return $element;
}

/**
 * Implements form process callback for a payment_method element.
 *
 * @see payment_element_info()
 */
function payment_form_process_method(array $element, array &$form_state, array &$form) {
  $form_state['payment_parents'] = $element['#parents'];
  $payment = $form_state['payment'];

  $element['#tree'] = TRUE;

  // Get available payment methods.
  $pmid_options = array();
  $pmids = empty($element['#pmids']) ? FALSE : $element['#pmids'];
  foreach ($payment->availablePaymentMethods(entity_load('payment_method', $pmids)) as $payment_method) {
    // Cast the PMID to a string or the AJAX callback won't work.
    $pmid_options[(string) $payment_method->pmid] = check_plain($payment_method->title_generic);
  }

  // There are no available payment methods.
  if (count($pmid_options) == 0) {
    if (!$payment->pid) {
      $form['#disabled'] = TRUE;
    }
    $element['pmid_title'] = array(
      '#type' => 'item',
      '#title' => isset($element['#title']) ? $element['#title'] : NULL,
      '#markup' => t('There are no available payment methods.'),
    );
  }
  // There is one available payment method.
  elseif (count($pmid_options) == 1) {
    $pmids = array_keys($pmid_options);
    $element['pmid'] = array(
      '#type' => 'value',
      '#value' => $pmids[0],
    );
    if (isset($element['#title'])) {
      $element['pmid_title'] = array(
        '#type' => 'item',
        '#title' => $element['#title'],
        '#markup' => $pmid_options[$pmids[0]],
      );
    }
    // Default to the only available payment method.
    if (!$payment->method || $payment->method->pmid != $pmids[0]) {
      $payment->method = entity_load_single('payment_method', $pmids[0]);
    }

    $element['payment_method_controller_payment_configuration'] = array(
      '#type' => 'payment_form_context',
      '#payment_method_controller_name' => $payment_method->controller->name,
      '#callback_type' => 'payment',
    );
  }
  // There are multiple available payment methods.
  else {
    $form['#prefix'] = '<div id="payment-method-wrapper">';
    $form['#suffix'] = '</div>';
    $element['pmid'] = array(
      '#type' => 'select',
      '#title' => isset($element['#title']) ? $element['#title'] : NULL,
      '#options' => $pmid_options,
      '#default_value' => isset($payment->method) ? $payment->method->pmid : NULL,
      '#empty_value' => 'select',
      '#required' => $element['#required'],
      '#ajax' => array(
        'callback' => 'payment_form_process_method_submit_ajax_callback',
        'effect' => 'fade',
        'event' => 'change',
        'wrapper' => 'payment-method-wrapper',
      ),
      // Disable the selector for non-JS pages. This means that if we're
      // executing an AJAX callback, _triggering_element_name is set and we leave
      // the element enabled.
      '#disabled' => !empty($payment->method) && !isset($form_state['input']['_triggering_element_name']),
      '#attached' => array(
        'js' => array(drupal_get_path('module', 'payment') . '/js/payment.js'),
      ),
      '#id' => 'payment-method-pmid',
    );
    if ($payment->method) {
      $element['change'] = array(
        '#type' => 'submit',
        '#value' => t('Change payment method'),
        '#submit' => array('payment_form_process_method_submit'),
        '#limit_validation_errors' => array(),
        '#attributes' => array(
          'class' => array('js-hide')
        ),
      );
      $element['payment_method_controller_payment_configuration'] = array(
        '#type' => 'payment_form_context',
        '#payment_method_controller_name' => $payment->method->controller->name,
        '#callback_type' => 'payment',
      );
    }
  }

  // The element itself has no input, only its children, so mark it not
  // required to prevent validation errors.
  $element['#required'] = FALSE;

  return $element;
}

/**
 * Implements form validate callback for a payment_method element.
 */
function payment_form_process_method_validate(array $element, array &$form_state) {
  $pmid = drupal_array_get_nested_value($form_state['values'], array_merge($element['#parents'], array('pmid')));
  if ($pmid) {
    $payment = $form_state['payment'];
    // Another payment method was selected, so rebuild the form.
    if (!$payment->method || $payment->method->pmid != $pmid) {
      $payment->method = entity_load_single('payment_method', $pmid);
      $form_state['rebuild'] = TRUE;
    }
  }
}

/**
 * Implements form submit callback for a payment_method element.
 */
function payment_form_process_method_submit(array $form, array &$form_state) {
  $pmid = drupal_array_get_nested_value($form_state['values'], array_merge($form_state['payment_parents'], array('pmid')));
  unset($pmid);
  $form_state['payment']->method = NULL;
  $form_state['rebuild'] = TRUE;
}

/**
 * Implements form AJAX callback for a payment_method element.
 */
function payment_form_process_method_submit_ajax_callback(array $form, array &$form_state) {
  return $form;
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
  $definitions = \Drupal::service('plugin.manager.payment.payment_method')->getDefinitions();
  unset($definitions['payment_unavailable']);
  foreach ($definitions as $plugin_id => $definition) {
    $options[$plugin_id] = $definition['title'];
  }
  natcasesort($options);

  return $options;
}
