<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentTypeUi.
 */

namespace Drupal\payment;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Plugin\Payment\Type\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment type routes.
 */
class PaymentTypeUi implements ContainerInjectionInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment type plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\Manager
   */
  protected $paymentTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\payment\Plugin\Payment\Type\Manager $payment_type_manager
   *   The payment type plugin manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityManagerInterface $entity_manager, Manager $payment_type_manager, AccountInterface $current_user) {
    $this->moduleHandler = $module_handler;
    $this->entityManager = $entity_manager;
    $this->paymentTypeManager = $payment_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'), $container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.type'), $container->get('current_user'));
  }

  /**
   * Displays a list of available payment types.
   *
   * @return array
   *   A render array.
   */
  public function listing() {
    $table = array(
      '#empty' => t('There are no available payment types.'),
      '#header' => array(t('Type'), t('Description'), t('Operations')),
      '#type' => 'table',
    );
    $definitions = $this->paymentTypeManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    foreach ($definitions as $plugin_id => $definition) {
      $class = $definition['class'];
      $operations = $class::getOperations($plugin_id);
      if ($this->moduleHandler->moduleExists('field_ui')) {
        $admin_path = $this->entityManager->getAdminPath('payment', $plugin_id);
        if ($this->currentUser->hasPermission('administer payment fields')) {
          $operations['manage-fields'] = array(
            'title' => t('Manage fields'),
            'href' => $admin_path . '/fields',
          );
        }
        if ($this->currentUser->hasPermission('administer payment form display')) {
          $operations['manage-form-display'] = array(
            'title' => t('Manage form display'),
            'href' => $admin_path . '/form-display',
          );
        }
        if ($this->currentUser->hasPermission('administer payment display')) {
          $operations['manage-display'] = array(
            'title' => t('Manage display'),
            'href' => $admin_path . '/display',
          );
        }
      }
      $table[$plugin_id]['label'] = array(
        '#markup' => $definition['label'],
      );
      $table[$plugin_id]['description'] = array(
        '#markup' => isset($definition['description']) ? $definition['description'] : NULL,
      );
      $table[$plugin_id]['operations'] = array(
        '#links' => $operations,
        '#type' => 'operations',
      );
    }

    return $table;
  }
}
