<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Plugin\Field\FieldType\PaymentReferenceItemList.
 */

namespace Drupal\payment_reference\Plugin\Field\FieldType;

use Drupal\Core\Field\ConfigFieldItemList;

/**
 * Provides a payment reference field item list.
 *
 * This class removes the functionality to add a default value through the user
 * interface.
 */
class PaymentReferenceItemList extends ConfigFieldItemList {

  /**
   * {@inheritdoc}
   */
  public function defaultValuesForm(array &$form, array &$form_state) {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValuesFormValidate(array $element, array &$form, array &$form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function defaultValuesFormSubmit(array $element, array &$form, array &$form_state) {
  }

}
