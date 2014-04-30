<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentStatus.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\payment\Entity\PaymentStatusInterface;
use Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment status routes.
 */
class PaymentStatus extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\PaymentStatusManagerInterface
   */
  protected $paymentStatusManager;

  /**
   * Constructs a new class instance.
   */
  public function __construct(EntityManagerInterface $entity_manager, PaymentStatusManagerInterface $payment_status_manager) {
    $this->entityManager = $entity_manager;
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * Displays a payment status add form.
   *
   * @return array
   */
  public function add() {
    $payment_status = $this->entityManager->getStorage('payment_status')->create(array());

    return drupal_get_form($this->entityManager->getFormObject('payment_status', 'default')->setEntity($payment_status));
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
      '#header' => array(t('Title'), t('Description'), t('Operations')),
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
          '#markup' => drupal_render($indentation) . $definition['label'],
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
