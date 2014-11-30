<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentStatus.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Entity\PaymentStatusInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment status routes.
 */
class PaymentStatus extends ControllerBase {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * The payment status storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentStatusStorage;

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
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface $payment_status_manager
   *   The payment status manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_status_storage
   *   The payment status storage.
   */
  public function __construct(TranslationInterface $string_translation, EntityFormBuilderInterface $entity_form_builder, RendererInterface $renderer, PaymentStatusManagerInterface $payment_status_manager, EntityStorageInterface $payment_status_storage) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->paymentStatusManager = $payment_status_manager;
    $this->paymentStatusStorage = $payment_status_storage;
    $this->renderer = $renderer;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($container->get('string_translation'), $container->get('entity.form_builder'), $container->get('renderer'), $container->get('plugin.manager.payment.status'), $entity_manager->getStorage('payment_status'));
  }

  /**
   * Displays a payment status add form.
   *
   * @return array
   */
  public function add() {
    $payment_status = $this->paymentStatusStorage->create();

    return $this->entityFormBuilder->getForm($payment_status);
  }

  /**
   * Returns the title for the edit page.
   *
   * @param \Drupal\payment\Entity\PaymentStatusInterface $payment_status
   *
   * @return string
   */
  public function editTitle(PaymentStatusInterface $payment_status) {
    return $this->t('Edit %label', array(
      '%label' => $payment_status->label(),
    ));
  }

  /**
   * Lists all payment statuses.
   *
   * @return array
   */
  public function listing() {
    return array(
      '#header' => array($this->t('Title'), $this->t('Description'), $this->t('Operations')),
      '#type' => 'table',
    ) + $this->listingLevel($this->paymentStatusManager->hierarchy(), 0);
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
   */
  protected function listingLevel(array $hierarchy, $depth) {
    $rows = array();
    foreach ($hierarchy as $plugin_id => $children) {
      $definition = $this->paymentStatusManager->getDefinition($plugin_id);
      $operations_provider = $this->paymentStatusManager->getOperationsProvider($plugin_id);
      $indentation = array(
        '#theme' => 'indentation',
        '#size' => $depth,
      );
      $rows[$plugin_id] = array(
        'label' => array(
          '#markup' => $this->renderer->render($indentation) . $definition['label'],
        ),
        'description' => array(
          '#markup' => $definition['description'],
        ),
        'operations' => array(
          '#type' => 'operations',
          '#links' => $operations_provider ? $operations_provider->getOperations($plugin_id) : array(),
        ),
      );
      $rows = array_merge($rows, $this->listingLevel($children, $depth + 1));
    }

    return $rows;
  }

}
