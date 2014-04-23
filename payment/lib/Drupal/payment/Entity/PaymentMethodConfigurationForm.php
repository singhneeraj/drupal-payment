<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentMethodConfigurationForm..
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\EntityFormController;
use Drupal\payment\Payment;
use Drupal\user\UserInterface;

/**
 * Provides the payment method configuration form.
 */
class PaymentMethodConfigurationForm extends EntityFormController {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    $payment_method = $this->getEntity();
    $definition = Payment::methodConfigurationManager()->getDefinition($payment_method->bundle());
    $payment_method_configuration_plugin = Payment::methodConfigurationManager()->createInstance($payment_method->bundle(), $payment_method->getPluginConfiguration());
    $form_state['payment_method_configuration_plugin'] = $payment_method_configuration_plugin;
    $form['type'] = array(
      '#type' => 'item',
      '#title' => t('Type'),
      '#markup' => $definition['label'],
    );
    $form['status'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => $payment_method->status(),
    );
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#default_value' => $payment_method->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $payment_method->id(),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#machine_name' => array(
        'source' => array('label'),
        'exists' => array($this, 'paymentMethodConfigurationIdExists'),
      ),
      '#disabled' => (bool) $payment_method->id(),
    );
    $owner_label = '';
    if ($payment_method->getOwnerId()) {
      $owner_label = $payment_method->getOwner()->label();
    }
    elseif ($this->currentUser() instanceof UserInterface) {
      $owner_label = $this->currentUser()->label();
    }
    $form['owner'] = array(
      '#type' => 'textfield',
      '#title' => t('Owner'),
      '#default_value' => $owner_label,
      '#maxlength' => 255,
      '#autocomplete_route_name' => 'user.autocomplete',
      '#required' => TRUE,
    );
    $form['plugin_form'] = $payment_method_configuration_plugin->formElements($form, $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    $values = $form_state['values'];
    if (!($account = user_load_by_name($values['owner']))) {
      form_set_error('owner', t('The username %name does not exist.', array(
        '%name' => $values['owner'],
      )));
    }

    parent::validate($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    parent::submit($form, $form_state);
    $values = $form_state['values'];
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method */
    $payment_method_configuration = $this->getEntity();
    /** @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface $payment_method_configuration_plugin */
    $payment_method_configuration_plugin = $form_state['payment_method_configuration_plugin'];
    $payment_method_configuration->setLabel($values['label'])
      ->setStatus($values['status'])
      ->setOwnerId(user_load_by_name($values['owner'])->id())
      ->setPluginConfiguration($payment_method_configuration_plugin->getConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    $payment_method = $this->entity;
    $payment_method->save();
    drupal_set_message(t('@label has been saved.', array(
      '@label' => $payment_method->label()
    )));
    $form_state['redirect_route'] = array(
      'route_name' => 'payment.payment_method_configuration.list',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $payment_method = $this->entity;
    $form_state['redirect'] = array('admin/config/services/payment/method/configuration/' . $payment_method->id() . '/delete');
  }

  /**
   * Checks if a payment method with a particular ID already exists.
   *
   * @param string $id
   *
   * @return bool
   */
  function paymentMethodConfigurationIdExists($id) {
    return (bool) entity_load('payment_method_configuration', $id);
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    if (!$this->entity->id()) {
      unset($actions['delete']);
    }

    return $actions;
  }
}
