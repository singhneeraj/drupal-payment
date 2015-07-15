<?php

/**
 * @file
 * Contains \Drupal\payment\Plugin\Field\FieldType\LineItemItem.
 */

namespace Drupal\payment\Plugin\Field\FieldType;

/**
 * Provides a plugin collection for payment line item plugins.
 *
 * @FieldType(
 *   id = "plugin:payment_line_item"
 * )
 */
class LineItemItem extends PaymentAwarePluginCollectionItem {}
