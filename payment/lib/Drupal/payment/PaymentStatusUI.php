<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentStatusUI.
 */

namespace Drupal\payment;

use Drupal\Core\Controller\ControllerInterface;
use Drupal\payment\Plugin\payment\status\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment status routes.
 */
class PaymentStatusUI implements ControllerInterface {

  /**
   * The payment status plugin manager.
   *
   * @var \Drupal\payment\Plugin\payment\status\Manager
   */
  protected $manager;

  /**
   * Constructor.
   */
  public function __construct(Manager $payment_status_manager) {
    $this->manager = $payment_status_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.payment.status'));
  }

  /**
   * Lists all payment statuses.
   *
   * @return array
   */
  public function listing() {
    return array(
      '#header' => array(t('Title'), t('Description')),
      '#type' => 'table',
    ) + $this->listingLevel($this->manager->hierarchy(), 0);
  }

  /**
   * Helper function for self::listing() to build table rows.
   *
   * @param array $hierarchy
   *   A payment status hierarchy as returned by
   *   \Drupal\payment\Plugin\payment\status\Manager::hierarchy().
   * @param integer $depth
   *   The depth of $hierarchy's top-level items as seen from the original
   *   hierarchy's root (this function is recursive), starting with 0.
   *
   * @return array
   */
  protected function listingLevel(array $hierarchy, $depth) {
    $rows = array();
    foreach ($hierarchy as $plugin_id => $children) {
      $definition = $this->manager->getDefinition($plugin_id);
      $rows[$plugin_id] = array(
        'label' => array(
          '#markup' => theme('indentation', array(
            'size' => $depth,
          )) . $definition['label'],
        ),
        'description' => array(
          '#markup' => $definition['description'],
        ),
      );
      $rows = array_merge($rows, $this->listingLevel($children, $depth + 1));
    }

    return $rows;
  }
}
