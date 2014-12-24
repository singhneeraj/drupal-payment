<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\ListPaymentStatuses.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handles the "list payment methods" route.
 */
class ListPaymentStatuses extends ControllerBase {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   */
  public function __construct(TranslationInterface $string_translation, RendererInterface $renderer, PaymentStatusManagerInterface $payment_status_manager) {
    $this->paymentStatusManager = $payment_status_manager;
    $this->renderer = $renderer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('string_translation'), $container->get('renderer'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * Lists all payment statuses.
   *
   * @return array
   *   A render array.
   */
  public function execute() {
    return [
      '#header' => [$this->t('Title'), $this->t('Description'), $this->t('Operations')],
      '#type' => 'table',
    ] + $this->buildListingLevel($this->paymentStatusManager->hierarchy(), 0);
  }

  /**
   * Helper function for self::listing() to build table rows.
   *
   * @param array $hierarchy
   *   A payment status hierarchy as returned by
   *   \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface::hierarchy().
   * @param integer $depth
   *   The depth of $hierarchy's top-level items as seen from the original
   *   hierarchy's root (this function is recursive), starting with 0.
   *
   * @return array
   *   A render array.
   */
  protected function buildListingLevel(array $hierarchy, $depth) {
    $rows = [];
    foreach ($hierarchy as $plugin_id => $children) {
      $definition = $this->paymentStatusManager->getDefinition($plugin_id);
      $operations_provider = $this->paymentStatusManager->getOperationsProvider($plugin_id);
      $indentation = [
        '#theme' => 'indentation',
        '#size' => $depth,
      ];
      $rows[$plugin_id] = [
        'label' => [
          '#markup' => $this->renderer->render($indentation) . $definition['label'],
        ],
        'description' => [
          '#markup' => $definition['description'],
        ],
        'operations' => [
          '#type' => 'operations',
          '#links' => $operations_provider ? $operations_provider->getOperations($plugin_id) : [],
        ],
      ];
      $rows = array_merge($rows, $this->buildListingLevel($children, $depth + 1));
    }

    return $rows;
  }

}
