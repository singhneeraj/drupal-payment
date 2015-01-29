<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\Payment.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\payment\PaymentAwareInterface;
use Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface as PluginPaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface as PluginPaymentStatusInterface;
use Drupal\user\UserInterface;

/**
 * Defines a payment entity.
 *
 * @ContentEntityType(
 *   base_table = "payment",
 *   bundle_label = @Translation("Payment type"),
 *   handlers = {
 *     "access" = "Drupal\payment\Entity\Payment\PaymentAccessControlHandler",
 *     "form" = {
 *       "delete" = "Drupal\payment\Entity\Payment\PaymentDeleteForm",
 *       "edit" = "Drupal\payment\Entity\Payment\PaymentEditForm",
 *       "update_status" = "Drupal\payment\Entity\Payment\PaymentStatusForm",
 *       "capture" = "Drupal\payment\Entity\Payment\PaymentCaptureForm",
 *       "refund" = "Drupal\payment\Entity\Payment\PaymentRefundForm"
 *     },
 *     "list_builder" = "Drupal\payment\Entity\Payment\PaymentListBuilder",
 *     "view_builder" = "Drupal\payment\Entity\Payment\PaymentViewBuilder",
 *     "storage" = "Drupal\payment\Entity\Payment\PaymentStorage",
 *     "storage_schema" = "Drupal\payment\Entity\Payment\PaymentStorageSchema",
 *   },
 *   entity_keys = {
 *     "bundle" = "bundle",
 *     "id" = "id",
 *     "uuid" = "uuid",
 *   },
 *   field_ui_base_route = "payment.payment_type",
 *   id = "payment",
 *   label = @Translation("Payment"),
 *   links = {
 *     "canonical" = "/payment/{payment}",
 *     "complete" = "/payment/{payment}/complete",
 *     "edit-form" = "/payment/{payment}/edit",
 *     "delete-form" = "/payment/{payment}/delete",
 *     "update-status-form" = "/payment/{payment}/update-status",
 *     "capture-form" = "/payment/{payment}/capture",
 *     "refund-form" = "/payment/{payment}/refund"
 *   }
 * )
 */
class Payment extends ContentEntityBase implements PaymentInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type, $bundle = FALSE, $translations = array()) {
    // Unserialize the values for fields that are stored in the base table,
    // because the entity storage does not do that.
    $property_names = ['payment_method', 'payment_type'];
    foreach ($property_names as $property_name) {
      if (isset($values[$property_name])) {
        foreach ($values[$property_name] as &$item_values) {
          $item_values['plugin_configuration'] = unserialize($item_values['plugin_configuration']);
        }
      }
    }
    parent::__construct($values, $entity_type, $bundle, $translations);
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function label($langcode = NULL) {
    return $this->getPaymentType()->paymentDescription($langcode);
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentType() {
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    $field_item = $this->get('payment_type')[0];

    return $field_item->getContainedPluginInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrency() {
    return $this->get('currency')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrencyCode($currency_code) {
    $this->set('currency', $currency_code);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrencyCode() {
    return $this->get('currency')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setLineItems(array $line_items) {
    foreach ($line_items as $line_item) {
      $this->setLineItem($line_item);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLineItem(PaymentLineItemInterface $line_item) {
    $line_item->setPayment($this);
    $line_items = $this->getLineItems();
    $line_items[] = $line_item;
    foreach (array_values($line_items) as $delta => $line_item) {
      $this->get('line_items')[$delta]->setValue($line_item);
    }


    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function unsetLineItem($name) {
    $line_items = $this->getLineItems();
    foreach ($line_items as $delta => $line_item) {
      if ($line_item->getName() == $name) {
        unset($line_items[$delta]);
      }
    }
    $this->get('line_items')->setValue($line_items);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItems() {
    $line_items = [];
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    foreach ($this->get('line_items') as $field_item) {
      /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
      $line_item = $field_item->getContainedPluginInstance();
      if ($line_item) {
        $line_items[$line_item->getName()] = $line_item;
      }
    }

    return $line_items;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItem($name) {
    $line_items = $this->getLineItems();
    foreach ($line_items as $delta => $line_item) {
      if ($line_item->getName() == $name) {
        return $line_item;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getLineItemsByType($plugin_id) {
    $line_items = [];
    foreach ($this->getLineItems() as $name => $line_item) {
      if ($line_item->getPluginId() == $plugin_id) {
        $line_items[$name] = $line_item;
      }
    }

    return $line_items;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentStatuses(array $statuses) {
    /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface[] $statuses */
    foreach (array_values($statuses) as $delta => $status) {
      $status->setPayment($this);
      $this->get('payment_statuses')[$delta]->setValue($status, FALSE);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentStatus(PluginPaymentStatusInterface $status) {
    $previous_status = $this->getPaymentStatus();
    $status->setPayment($this);
    // Prevent duplicate statuses.
    if (!$this->getPaymentStatus() || $this->getPaymentStatus()->getPluginId() != $status->getPluginId()) {
      $payment_statuses = $this->getPaymentStatuses();
      $payment_statuses[] = $status;
      foreach (array_values($payment_statuses) as $delta => $payment_status) {
        $this->get('payment_statuses')[$delta]->setValue($payment_status);
      }
    }
    /** @var \Drupal\payment\EventDispatcherInterface $event_dispatcher */
    $event_dispatcher = \Drupal::service('payment.event_dispatcher');
    $event_dispatcher->setPaymentStatus($this, $previous_status);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentStatuses() {
    $payment_statuses = [];
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    foreach ($this->get('payment_statuses') as $field_item) {
      $payment_statuses[] = $field_item->getContainedPluginInstance();
    }

    return array_filter($payment_statuses);
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentStatus() {
    $deltas = [];
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    foreach ($this->get('payment_statuses') as $delta => $field_item) {
      $deltas[] = $delta;
    }
    $field_item = $this->get('payment_statuses')[max($deltas)];

    return $field_item->getContainedPluginInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function setPaymentMethod(PluginPaymentMethodInterface $payment_method) {
    $payment_method->setPayment($this);
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    $field_item = $this->get('payment_method')[0];
    $field_item->setContainedPluginInstance($payment_method);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPaymentMethod() {
    /** @var \Drupal\payment\Plugin\Field\FieldType\PluginBagItemInterface $field_item */
    $field_item = $this->get('payment_method')[0];

    return $field_item->getContainedPluginInstance();
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($id) {
    $this->owner[0]->setValue($id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $user) {
    $this->owner[0]->setValue($user->id());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->owner[0]->get('target_id')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->owner[0]->get('entity')->getValue();
  }

  /**
   * {@inheritdoc}
   */
  public function getAmount() {
    /** @var \Drupal\currency\Math\MathInterface $math */
    $math = \Drupal::service('currency.math');
    $total = 0;
    foreach ($this->getLineItems() as $line_item) {
      $total = $math->add($total, $line_item->getTotalAmount());
    }

    return $total;
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    if ($this->getPaymentMethod()) {
      return $this->getPaymentMethod()->executePayment();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['bundle'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Bundle'))
      ->setReadOnly(TRUE);
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time the payment was last edited.'));
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time the payment was created.'))
      ->setDisplayOptions('view', array(
      'type' => 'timestamp',
      'weight' => 0,
    ))
      ->setDisplayConfigurable('view', TRUE);
    $fields['currency'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Currency'))
      ->setRequired(TRUE)
      ->setDefaultValue(0)
      ->setSetting('target_type', 'currency')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', array(
        'type' => 'entity_reference_label',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'options_select',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Payment ID'))
      ->setReadOnly(TRUE);
    $fields['owner'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Payer'))
      ->setDefaultValue(0)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDefaultValueCallback('Drupal\payment\Entity\Payment::getCurrentUserId')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', array(
        'type' => 'author',
        'weight' => 0,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'entity_reference_autocomplete',
        'weight' => 0,
        'settings' => array(
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ),
      ))
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['line_items'] = BaseFieldDefinition::create('payment_line_item')
      ->setLabel(t('Line items'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED);
    $fields['payment_method'] = BaseFieldDefinition::create('payment_method')
      ->setLabel(t('Payment method'))
      ->setDisplayOptions('view', array(
        'type' => 'payment_plugin_label',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE);
    $fields['payment_statuses'] = BaseFieldDefinition::create('payment_status')
      ->setLabel(t('Payment statuses'))
      ->setCardinality(BaseFieldDefinition::CARDINALITY_UNLIMITED);
    $fields['payment_type'] = BaseFieldDefinition::create('payment_type')
      ->setLabel(t('Payment type'))
      ->setDisplayOptions('view', array(
        'type' => 'payment_plugin_label',
        'weight' => 0,
      ))
      ->setDisplayConfigurable('view', TRUE);
    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('Universally Unique ID'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTranslatedField($name, $langcode) {
    $field_item_list = parent::getTranslatedField($name, $langcode);
    $plugin_bag_field_names = ['line_items', 'payment_method', 'payment_statuses', 'payment_type'];
    if (in_array($name, $plugin_bag_field_names)) {
      foreach ($field_item_list as $field_item) {
        $plugin_instance = $field_item->get('plugin_instance')->getValue();
        if ($plugin_instance instanceof PaymentAwareInterface) {
          $plugin_instance->setPayment($this);
        }
      }
    }

    return $field_item_list;
  }

  /**
   * Default value callback for 'owner' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getCurrentUserId(EntityInterface $entity, FieldDefinitionInterface $field_definition) {
    return array(\Drupal::currentUser()->id());
  }

}
