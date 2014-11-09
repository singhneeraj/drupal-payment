<?php

/**
 * @file
 * Contains \Drupal\payment\Element\PaymentStatusesDisplay.
 */

namespace Drupal\payment\Element;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\currency\FormElementCallbackTrait;
use Drupal\payment\Entity\PaymentInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an element to display payment statuses.
 *
 * @RenderElement("payment_statuses_display")
 */
class PaymentStatusesDisplay extends FormElement implements ContainerFactoryPluginInterface {

  use FormElementCallbackTrait;

  /**
   * The fate formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

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
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TranslationInterface $string_translation, DateFormatter $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->dateFormatter = $date_formatter;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('string_translation'), $container->get('date.formatter'));
  }

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $plugin_id = $this->getPluginId();

    return array(
      // A \Drupal\payment\Entity\PaymentInterface object (required).
      '#payment' => NULL,
      '#pre_render' => [[get_class($this), 'instantiate#pre_render#' . $plugin_id]],
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
    $payment = $element['#payment'];
    $element['table'] = array(
      '#empty' => $this->t('There are no statuses.'),
      '#header' => array($this->t('Status'), $this->t('Date')),
      '#type' => 'table',
    );
    /** @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusInterface $status */
    foreach (array_reverse($payment->getPaymentStatuses()) as $i => $status) {
      $definition = $status->getPluginDefinition();
      $element['table']['status_' . $i] = array(
        '#attributes' => array(
          'class' => array(
            'payment-status-plugin-' . $status->getPluginId(),
          ),
        ),
        'label' => array(
          '#attributes' => array(
            'class' => array('payment-status-label'),
          ),
          '#markup' => $definition['label'],
        ),
        'created' => array(
          '#attributes' => array(
            'class' => array('payment-line-item-quantity'),
          ),
          '#markup' => $this->dateFormatter->format($status->getCreated()),
        ),
      );
    }

    return $element;
  }

}
