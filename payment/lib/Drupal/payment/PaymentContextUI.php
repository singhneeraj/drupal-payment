<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentContextUI.
 */

namespace Drupal\payment;

use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Controller\ControllerInterface;
use Drupal\payment\Plugin\payment\context\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for payment context routes.
 */
class PaymentContextUI implements ControllerInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManager
   */
  protected $entityManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The payment context manager.
   *
   * @var \Drupal\payment\Plugin\payment\context\Manager
   */
  protected $paymentContextManager;

  /**
   * Constructor.
   */
  public function __construct(ModuleHandler $module_handler, EntityManager $entity_manager, Manager $payment_context_manager) {
    $this->moduleHandler = $module_handler;
    $this->entityManager = $entity_manager;
    $this->paymentContextManager = $payment_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'), $container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.context'));
  }

  /**
   * Displays a list of available payment contexts.
   *
   * @return array
   *   A render array.
   */
  public function listing() {
    $account = \Drupal::request()->attributes->get('_account');
    $table = array(
      '#empty' => t('There are no available payment types.'),
      '#header' => array(t('Type'), t('Description'), t('Operations')),
      '#type' => 'table',
    );
    $definitions = $this->paymentContextManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    foreach ($definitions as $plugin_id => $context_definition) {
      $class = $context_definition['class'];
      $operations = $class::getOperations();
      if ($this->moduleHandler->moduleExists('field_ui')) {
        $admin_path = $this->entityManager->getAdminPath('payment', $plugin_id);
        if ($account->hasPermission('administer payment fields')) {
          $operations['manage-fields'] = array(
            'title' => t('Manage fields'),
            'href' => $admin_path . '/fields',
          );
        }
        if ($account->hasPermission('administer payment form display')) {
          $operations['manage-form-display'] = array(
            'title' => t('Manage form display'),
            'href' => $admin_path . '/form-display',
          );
        }
        if ($account->hasPermission('administer payment display')) {
          $operations['manage-display'] = array(
            'title' => t('Manage display'),
            'href' => $admin_path . '/display',
          );
        }
      }
      $table[$plugin_id]['label'] = array(
        '#markup' => $context_definition['label'],
      );
      $table[$plugin_id]['description'] = array(
        '#markup' => isset($context_definition['description']) ? $context_definition['description'] : NULL,
      );
      $table[$plugin_id]['operations'] = array(
        '#links' => $operations,
        '#type' => 'operations',
      );
    }

    return $table;
  }
}
