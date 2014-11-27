<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentLineItemsDisplay.
 */

namespace Drupal\payment\Element;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\FormElementCallbackTrait;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an element to display payment line items.
 *
 * @RenderElement("payment_line_items_display")
 */
class PaymentLineItemsDisplay extends FormElement implements ContainerFactoryPluginInterface {

  use FormElementCallbackTrait;

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $string_translation, EntityStorageInterface $currency_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->currencyStorage = $currency_storage;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $entity_manager->getStorage('currency'));
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $plugin_id = $this->getPluginId();

    return array(
      // A \Drupal\payment\Entity\PaymentInterface object (required).
      '#payment' => NULL,
      '#pre_render' => [[get_class($this), 'instantiate#preRender#' . $plugin_id]],
    );
  }

  /**
   * Implements form #pre_render callback.
   *
   * @throws \InvalidArgumentException
   */
  public function preRender(array $element) {
    if (!isset($element['#payment']) || !($element['#payment'] instanceof PaymentInterface)) {
      throw new \InvalidArgumentException('The payment does not implement \Drupal\payment\Entity\PaymentInterface.');
    }
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $element['#payment'];
    $element['table'] = array(
      '#empty' => $this->t('There are no line items.'),
      '#header' => array($this->t('Description'), $this->t('Quantity'), $this->t('Amount'), $this->t('Total')),
      '#type' => 'table',
    );
    foreach ($payment->getLineItems() as $line_item) {
      /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
      $currency = $this->currencyStorage->load($line_item->getCurrencyCode());
      $element['table']['line_item_' . $line_item->getName()] = array(
        '#attributes' => array(
          'class' => array(
            'payment-line-item',
            'payment-line-item-name-' . $line_item->getName(),
            'payment-line-item-plugin-' . $line_item->getPluginId(),
          ),
        ),
        'description' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-description'),
          ),
          '#markup' => $line_item->getDescription(),
        ),
        'quantity' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-quantity'),
          ),
          '#markup' => $line_item->getQuantity(),
        ),
        'amount' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-amount'),
          ),
          '#markup' => $currency->formatAmount($line_item->getAmount()),
        ),
        'total' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-amount-total'),
          ),
          '#markup' => $currency->formatAmount($line_item->getTotalAmount()),
        ),
      );
    }
    $currency = $this->currencyStorage->load($payment->getCurrencyCode());
    $element['table']['payment_total'] = array(
      '#attributes' => array(
        'class' => array('payment-amount'),
      ),
      'label' => array(
        '#attributes' => array(
          'class' => array('payment-amount-label'),
          'colspan' => 3,
        ),
        '#markup' => $this->t('Total amount'),
      ),
      'total' => array(
        '#attributes' => array(
          'class' => array('payment-amount-total'),
        ),
        '#markup' => $currency->formatAmount($payment->getAmount()),
      ),
    );

    return $element;
  }
}
