<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\PaymentMethodItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

/**
 * Provides a plugin collection for payment method plugins.
 *
 * @FieldType(
 *   id = "plugin:payment_method"
 * )
 */
class PaymentMethodItem extends PaymentAwarePluginCollectionItem {}
