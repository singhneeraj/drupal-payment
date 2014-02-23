<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Plugin\Field\FieldWidget\PaymentReference.
 */

namespace Drupal\payment_reference\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\field\FieldInstanceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a payment reference field widget.
 *
 * @FieldWidget(
 *   description = @Translation("Allows users to select existing unused payments, or to add a new payment on the fly."),
 *   field_types = {
 *     "payment_reference"
 *   },
 *   id = "payment_reference",
 *   label = @Translation("Payment reference"),
 *   multiple_values = "false"
 * )
 */
class PaymentReference extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a new class instance.
   *
   * @param array $plugin_id
   *   The plugin_id for the widget.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the widget is associated.
   * @param array $settings
   *   The widget settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct($plugin_id, array $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, AccountInterface $current_user) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings);
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $container->get('current_user'));
  }

  /**
   * Implements hook_field_widget_form().
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {
    if (!($this->fieldDefinition instanceof FieldInstanceInterface)) {
      throw new \RuntimeException('This widget can only be used on configurable fields.');
    }

    $element['payment_id'] = array(
      '#default_value' => isset($items[$delta]) ? $items[$delta]->target_id : NULL,
      '#field_instance_config_id' => $this->fieldDefinition->id(),
      // The requested user account may contain a string numeric ID.
      '#owner_id' => (int) $this->currentUser->id(),
      '#payment_line_items_data' => $this->getFieldSetting('line_items_data'),
      '#payment_currency_code' => $this->getFieldSetting('currency_code'),
      '#required' => $this->fieldDefinition->isRequired(),
      '#type' => 'payment_reference',
    );

    return $element;
  }

}
