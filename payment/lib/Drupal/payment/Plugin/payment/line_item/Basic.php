<?php

/**
 * Contains \Drupal\payment\Plugin\payment\line_item\Basic.
 */

namespace Drupal\payment\Plugin\payment\line_item;

use Drupal\Component\Utility\NestedArray;
use Drupal\payment\Plugin\payment\line_item\Base;

/**
 * A basic line item.
 *
 * @PaymentLineItem(
 *   id = "payment_basic",
 *   label = @Translation("Basic")
 * )
 */
class Basic extends Base {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + array(
      'description' => NULL,
    );
  }

  /**
   * {@inheritdoc}
   */
  function getDescription() {
    return $this->configuration['description'];
  }

  /**
   * Sets the line item description.
   *
   * @param string $description
   *
   * @return \Drupal\payment\Plugin\payment\line_item\Basic
   */
  function setDescription($description) {
    $this->configuration['description'] = $description;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function formElements(array $form, array &$form_state) {
    $elements = array(
      '#attached' => array(
        'css' => array(drupal_get_path('module', 'payment') . '/css/payment.css'),
      ),
      '#element_validate' => array(array($this, 'validate')),
      '#input' => TRUE,
      '#tree' => TRUE,
      '#type' => 'container',
    );
    $elements['amount'] = array(
      '#type' => 'currency_amount',
      '#title' => t('Amount'),
      '#default_value' => array(
        'amount' => $this->getAmount(),
        'currency_code' => $this->getCurrencyCode(),
      ),
      '#required' => TRUE,
    );
    $elements['quantity'] = array(
      '#type' => 'number',
      '#title' => t('Quantity'),
      '#default_value' => $this->getQuantity(),
      '#min' => 1,
      '#size' => 3,
      '#required' => TRUE,
    );
    $elements['description'] = array(
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => $this->getDescription(),
      '#required' => TRUE,
      '#maxlength' => 255,
    );
    $elements['clear'] = array(
      '#type' => 'markup',
      '#markup' => '<div class="clear"></div>',
    );

    return $elements;
  }

  /**
   * Implements form #element_validate callback.
   */
  public function validate(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setAmount($values['amount']['amount'])
      ->setCurrencyCode($values['amount']['currency_code'])
      ->setQuantity($values['quantity'])
      ->setDescription($values['description']);
    form_set_value($element, $this, $form_state);
  }

  /**
   * Loads additional data for this line item.
   */
  public function loadData() {
    if ($this->getPaymentId() && $this->getName()) {
      $query = db_select('payment_line_item_payment_basic', 'plipb')
        ->condition('name', $this->getName())
        ->condition('payment_id', $this->getPaymentId());
      $query->addField('plipb', 'description');
      $this->setDescription($query->execute()->fetchField());
    }

    return $this;
  }

  /**
   * Saves additional data for this line item.
   */
  public function saveData() {
    if ($this->getPaymentId() && $this->getName()) {
      db_merge('payment_line_item_payment_basic')
        ->key(array(
          'name' => $this->getName(),
          'payment_id' => $this->getPaymentId(),
        ))
        ->fields(array(
          'description' => $this->getDescription(),
        ))
        ->execute();
    }

    return $this;
  }

  /**
   * Deletes additional data for this line item.
   */
  public function deleteData() {
    if ($this->getPaymentId() && $this->getName()) {
      db_delete('payment_line_item_payment_basic')
        ->condition('name', $this->getName())
        ->condition('payment_id', $this->getPaymentId())
        ->execute();
    }

    return $this;
  }
}
