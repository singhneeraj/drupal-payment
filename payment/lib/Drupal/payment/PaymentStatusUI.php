<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentStatusUI.
 */

namespace Drupal\payment;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\payment\Plugin\Payment\Status\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment status routes.
 */
class PaymentStatusUI implements ContainerInjectionInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Status\Manager
   */
  protected $paymentStatusManager;

  /**
   * Constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, Manager $payment_status_manager) {
    $this->entityManager = $entity_manager;
    $this->paymentStatusManager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.status'));
  }

  /**
   * Displays a payment status add form.
   *
   * @return array
   */
  public function add() {
    $payment_status = $this->entityManager->getStorageController('payment_status')->create(array());

    return drupal_get_form($this->entityManager->getFormController('payment_status', 'default')->setEntity($payment_status));
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
   *   \Drupal\payment\Plugin\Payment\Status\Manager::hierarchy().
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
      $class = $definition['class'];
      $rows[$plugin_id] = array(
        'label' => array(
          '#markup' => theme('indentation', array(
            'size' => $depth,
          )) . $definition['label'],
        ),
        'description' => array(
          '#markup' => $definition['description'],
        ),
        'operations' => array(
          '#type' => 'operations',
          '#links' => $class::getOperations($plugin_id),
        ),
      );
      $rows = array_merge($rows, $this->listingLevel($children, $depth + 1));
    }

    return $rows;
  }
}
