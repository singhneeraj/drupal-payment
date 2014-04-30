<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentType.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns responses for payment type routes.
 */
class PaymentType extends ControllerBase implements ContainerInjectionInterface {

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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The payment type plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface
   */
  protected $paymentTypeManager;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\payment\Plugin\Payment\Type\PaymentTypeManagerInterface $payment_type_manager
   *   The payment type plugin manager.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ModuleHandlerInterface $module_handler, EntityManagerInterface $entity_manager, FormBuilderInterface $form_builder, PaymentTypeManagerInterface $payment_type_manager, AccountInterface $current_user) {
    $this->moduleHandler = $module_handler;
    $this->entityManager = $entity_manager;
    $this->formBuilder = $form_builder;
    $this->paymentTypeManager = $payment_type_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('module_handler'), $container->get('entity.manager'), $container->get('form_builder'), $container->get('plugin.manager.payment.type'), $container->get('current_user'));
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
      $operations_provider = $this->paymentTypeManager->getOperationsProvider($plugin_id);
      $operations = $operations_provider ? $operations_provider->getOperations($plugin_id) : array();

      // Add the payment type's global configuration operation.
      if (isset($definition['configuration_form'])) {
        $operations['configure'] = array(
          'route_name' => 'payment.payment_type',
          'route_parameters' => array(
            'bundle' => $plugin_id,
          ),
          'title' => $this->t('Configure'),
        );
      }

      // Add Field UI operations.
      if ($this->moduleHandler->moduleExists('field_ui')) {
        if ($this->currentUser->hasPermission('administer payment fields')) {
          $operations['manage-fields'] = array(
            'title' => t('Manage fields'),
            'route_name' => 'field_ui.overview_payment',
            'route_parameters' => array(
              'bundle' => $plugin_id,
            ),
          );
        }
        if ($this->currentUser->hasPermission('administer payment form display')) {
          $operations['manage-form-display'] = array(
            'title' => t('Manage form display'),
            'route_name' => 'field_ui.form_display_overview_payment',
            'route_parameters' => array(
              'bundle' => $plugin_id,
            ),
          );
        }
        if ($this->currentUser->hasPermission('administer payment display')) {
          $operations['manage-display'] = array(
            'title' => t('Manage display'),
            'route_name' => 'field_ui.display_overview_payment',
            'route_parameters' => array(
              'bundle' => $plugin_id,
            ),
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

  /**
   * Builds the payment type's configuration form.
   *
   * @param string $bundle
   *   The payment bundle, also known as the payment type's plugin ID.
   *
   * @return array|string
   *   A renderable array or a string.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function configure($bundle) {
    $definition = $this->paymentTypeManager->getDefinition($bundle);
    if (is_null($definition)) {
      throw new NotFoundHttpException();
    }
    elseif (isset($definition['configuration_form'])) {
      return $this->formBuilder->getForm($definition['configuration_form']);
    }
    else {
      return $this->t('This payment type has no configuration.');
    }
  }
}
