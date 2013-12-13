<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentLineItemsInput.
 */

namespace Drupal\payment\Element;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\RemoveCommand;
use Drupal\payment\Payment;

/**
 * Provides form callbacks for the payment_line_items_input form element.
 */
class PaymentLineItemsInput {

  /**
   * An unlimited cardinality.
   */
  const CARDINALITY_UNLIMITED = -1;

  /**
   * Implements form #process callback.
   */
  public static function process(array $element, array &$form_state, array $form) {
    // Validate the element configuration.
    if ($element['#cardinality'] != self::CARDINALITY_UNLIMITED && count($element['#default_value']) > $element['#cardinality']) {
      throw new \InvalidArgumentException('The number of default line items can not be higher than the cardinality.');
    }

    static::initializeLineItemsData($element, $form_state);
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $line_items */
    $line_items = array_values(static::getLineItems($element, $form_state));

    // Build the line items.
    $element['line_items'] = array(
      '#empty' => t('There are no line items yet.'),
      '#header' => array(t('Line item'), t('Weight'), t('Operations')),
      '#tabledrag' => array(array('order', 'self', 'payment-line-item-weight')),
      '#type' => 'table',
      '#tree' => TRUE,
    );

    foreach ($line_items as $delta => $line_item) {
      $element['line_items'][$line_item->getName()] = array(
        '#attributes' => array(
          'class' => array(
            'payment-line-item',
            'payment-line-item-name-' . $line_item->getName(),
            'payment-line-item-plugin-' . $line_item->getPluginId(),
          ),
        ),
      );
      $element['line_items'][$line_item->getName()]['plugin_form'] = $line_item->formElements($form, $form_state);
      $element['line_items'][$line_item->getName()]['weight'] = array(
        '#attributes' => array(
          'class' => array('payment-line-item-weight'),
        ),
        '#default_value' => $delta,
        '#delta' => count($line_items),
        '#title' => t('Weight'),
        '#type' => 'weight',
      );
      $element['line_items'][$line_item->getName()]['delete'] = array(
        '#ajax' => array(
          'callback' => array(get_class(), 'deleteAjaxSubmit'),
          'effect' => 'fade',
          'event' => 'mousedown',
        ),
        '#limit_validation_errors' => array(),
        '#submit' => array(array(get_class(), 'deleteSubmit')),
        '#type' => 'submit',
        '#value' => t('Delete'),
      );
    }

    // "Add more line items" button.
    $element['add_more'] = array(
      '#access' => $element['#cardinality'] == self::CARDINALITY_UNLIMITED || count($line_items) < $element['#cardinality'],
      '#attributes' => array(
        'class' => array('payment-add-more'),
      ),
      '#id' => drupal_html_id('payment-add-more'),
      '#type' => 'container',
    );
    $manager = Payment::lineItemManager();
    $element['add_more']['type'] = array(
      '#options' => $manager->options(),
      '#title' => t('Type'),
      '#type' => 'select',
    );
    $element['add_more']['add'] = array(
      '#ajax' => array(
        'callback' => array(get_class(), 'addMoreAjaxSubmit'),
        'effect' => 'fade',
        'event' => 'mousedown',
        'wrapper' => $element['#id'],
      ),
      '#limit_validation_errors' => array(
        array_merge($element['#parents'], array('add_more', 'type')),
      ),
      '#submit' => array(array(get_class(), 'addMoreSubmit')),
      '#type' => 'submit',
      '#value' => t('Add a line item'),
    );

    return $element;
  }

  /**
   * Implements form #element_validate callback.
   */
  public static function validate(array $element, array &$form_state, array &$form) {
    // Reorder line items based on their weight elements.
    $line_items_data = array();
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    if ($values['line_items']) {
      foreach ($values['line_items'] as $name => $line_item_values) {
        $line_items_data[$name] = $line_item_values['weight'];
      }
      asort($line_items_data);
      foreach (static::getLineItems($element, $form_state) as $line_item) {
        $line_items_data[$line_item->getName()] = array(
          'plugin_id' => $line_item->getPluginId(),
          'plugin_configuration' => $line_item::getConfigurationFromFormValues($element['line_items'][$line_item->getName()]['plugin_form'], $form_state),
        );
      }
      static::setLineItemsData($element, $form_state, array_values($line_items_data));
    }
  }

  /**
   * Implements form #submit callback.
   */
  public static function addMoreSubmit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#array_parents'], 0, -2);
    $root_element = NestedArray::getValue($form, $parents);
    $values = NestedArray::getValue($form_state['values'], array_slice($form_state['triggering_element']['#parents'], 0, -2));
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface $line_item */
    $line_item = Payment::lineItemManager()
      ->createInstance($values['add_more']['type'])
      ->setName(static::createLineItemName($root_element, $form_state, $values['add_more']['type']));
    $line_items = static::getLineItemsData($root_element, $form_state);
    $line_items[] = array(
      'plugin_id' => $line_item->getPluginId(),
      'plugin_configuration' => $line_item->getConfiguration(),
    );
    static::setLineItemsData($root_element, $form_state, $line_items);
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Implements form AJAX callback.
   */
  public static function addMoreAjaxSubmit(array &$form, array &$form_state) {
    $parents = array_slice($form_state['triggering_element']['#array_parents'], 0, -2);
    $root_element = NestedArray::getValue($form, $parents);

    return array_intersect_key($root_element, array_flip(element_children($root_element)));
  }

  /**
   * Implements form #submit callback.
   */
  public static function deleteSubmit(array &$form, array &$form_state) {
    $root_element_parents  = array_slice($form_state['triggering_element']['#array_parents'], 0, -3);
    $root_element = NestedArray::getValue($form, $root_element_parents);
    /** @var \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[] $line_items */
    $line_items = array_values(static::getLineItems($root_element, $form_state));
    $parents = $form_state['triggering_element']['#array_parents'];
    $line_item_name = $parents[count($parents) - 2];
    $line_items_data = array_values(static::getLineItemsData($root_element, $form_state));
    foreach ($line_items as $i => $line_item) {
      if ($line_item->getName() == $line_item_name) {
        unset($line_items_data[$i]);
      }
    }
    static::setLineItemsData($root_element, $form_state, $line_items_data);
    $form_state['rebuild'] = TRUE;
  }

  /**
   * Implements form AJAX callback.
   */
  public static function deleteAjaxSubmit(array &$form, array &$form_state) {
    $root_element_parents  = array_slice($form_state['triggering_element']['#array_parents'], 0, -3);
    $root_element = NestedArray::getValue($form, $root_element_parents);
    $parents = $form_state['triggering_element']['#array_parents'];
    $line_item_name = $parents[count($parents) - 2];
    $response = new AjaxResponse();
    $response->addCommand(new RemoveCommand('#' . $root_element['#id'] . ' .payment-line-item-name-' . $line_item_name));

    return $response;
  }

  /**
   * Creates a unique line item name.
   *
   * @param array $element
   * @param array $form_state
   * @param string $name
   *   The preferred name.
   *
   * @return string
   */
  protected static function createLineItemName(array $element, array &$form_state, $name) {
    $counter = NULL;
    while (static::lineItemExists($element, $form_state, $name . $counter)) {
      $counter++;
    }

    return $name . $counter;
  }

  /**
   * Checks if a line item name already exists.
   *
   * @param array $element
   * @param array $form_state
   * @param string $name
   *
   * @return bool
   */
  protected static function lineItemExists(array $element, array &$form_state, $name) {
    foreach (static::getLineItems($element, $form_state) as $line_item) {
      if ($line_item->getName() == $name) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Stores the line items in the form's state.
   *
   * @param array $element
   * @param array $form_state
   * @param array $line_items_data
   *   Values are arrays with the following keys:
   *   - plugin_id: A string with the ID of the line item plugin instance.
   *   - plugin_configuration: An array with the configuration of the line item
   *     plugin instance.
   */
  protected static function setLineItemsData(array $element, array &$form_state, array $line_items_data) {
    $form_state['payment_line_item'][$element['#name']] = $line_items_data;
  }

  /**
   * Retrieves the line items from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return array
   *   Values are arrays with the following keys:
   *   - plugin_id: A string with the ID of the line item plugin instance.
   *   - plugin_configuration: An array with the configuration of the line item
   *     plugin instance.
   */
  public static function getLineItemsData(array $element, array &$form_state) {
    return $form_state['payment_line_item'][$element['#name']];
  }

  /**
   * Retrieves the line items from the form's state.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return \Drupal\payment\Plugin\Payment\LineItem\PaymentLineItemInterface[]
   */
  public static function getLineItems(array $element, array &$form_state) {
    $line_item_manager = Payment::lineItemManager();
    $line_items = array();
    foreach (static::getLineItemsData($element, $form_state) as $line_item_data) {
      $line_items[] = $line_item_manager->createInstance($line_item_data['plugin_id'], $line_item_data['plugin_configuration']);
    }

    return $line_items;
  }

  /**
   * Check if the form's state has been initialized for an element.
   *
   * @param array $element
   * @param array $form_state
   *
   * @return bool
   */
  protected static function initializeLineItemsData(array $element, array &$form_state) {
    if (!(isset($form_state['payment_line_item']) && array_key_exists($element['#name'], $form_state['payment_line_item']))) {
      self::setLineItemsData($element, $form_state, $element['#default_value']);
    }
  }
}
