<?php

/**
 * @file
 * Contains \Drupal\payment\Controller\PaymentMethod.
 */

namespace Drupal\payment\Controller;

use Drupal\Core\Access\AccessInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for payment method routes.
 */
class PaymentMethod extends ControllerBase implements AccessInterface, ContainerInjectionInterface {

  /**
   * The current user;
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The payment method configuration plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager
   * @param \Drupal\Core\Form\FormBuilderInterface  $form_builder
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(EntityManagerInterface $entity_manager, PaymentMethodManagerInterface $payment_method_manager, PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager, FormBuilderInterface $form_builder, UrlGeneratorInterface $url_generator, AccountInterface $current_user) {
    $this->entityManager = $entity_manager;
    $this->formBuilder = $form_builder;
    $this->paymentMethodManager = $payment_method_manager;
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
    $this->urlGenerator = $url_generator;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity.manager'), $container->get('plugin.manager.payment.method'), $container->get('plugin.manager.payment.method_configuration'), $container->get('form_builder'), $container->get('url_generator'), $container->get('current_user'));
  }

  /**
   * Enables a payment method.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function enable(PaymentMethodInterface $payment_method) {
    $payment_method->enable();
    $payment_method->save();

    return new RedirectResponse($this->urlGenerator->generateFromRoute('payment.payment_method.list', array(), array(
      'absolute' => TRUE,
    )));
  }

  /**
   * Disables a payment method.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function disable(PaymentMethodInterface $payment_method) {
    $payment_method->disable();
    $payment_method->save();

    return new RedirectResponse($this->urlGenerator->generateFromRoute('payment.payment_method.list', array(), array(
      'absolute' => TRUE,
    )));
  }

  /**
   * Lists all available payment method plugins.
   *
   * @return array
   *   A renderable array.
   */
  public function listPlugins() {
    $rows = array();
    foreach ($this->paymentMethodManager->getDefinitions() as $plugin_id => $definition) {
      /** @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface $class */
      $class = $definition['class'];
      $row = array(
        'label' => array(
          '#markup' => $definition['label'],
        ),
        'status' => array(
          '#markup' => $definition['active'] ? $this->t('Enabled') : $this->t('Disabled'),
        ),
        'operations' => array(
          '#type' => 'operations',
          '#links' => $class::getOperations($plugin_id),
        ),
      );
      if (!$definition['active']) {
        $row['#attributes']['class'] = array('payment-method-disabled');
      }
      $rows[$plugin_id] = $row;
    }

    return array(
      '#attached' => array(
        'css' => array(
          $this->drupalGetPath('module', 'payment') . '/css/payment.css',
        ),
      ),
      '#attributes' => array(
        'class' => array('payment-method-list'),
      ),
      '#header' => array($this->t('Name'), $this->t('Status'), $this->t('Operations')),
      '#type' => 'table',
    ) + $rows;
  }

  /**
   * Displays a list of available payment method plugins.
   *
   * @return string
   */
  public function select() {
    $definitions = $this->paymentMethodConfigurationManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    $access_controller = $this->entityManager->getAccessController('payment_method');
    $items = array();
    foreach ($definitions as $plugin_id => $definition) {
      $access = $access_controller->createAccess($plugin_id);
      if ($access) {
        $href = $this->urlGenerator->getPathFromRoute('payment.payment_method.add', array(
          'plugin_id' => $plugin_id,
        ));
        $items[] = array(
          'title' => $definition['label'],
          'link_path' => $href,
          'description' => $definition['description'],
          'localized_options' => array(),
        );
      }
    }
    $rendered_content = $this->theme('admin_block_content', array(
      'content' => $items,
    ));

    return array(
      '#markup' => $rendered_content,
    );
  }

  /**
   * Checks access to self::select().
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return string
   *   self::ALLOW, self::DENY, or self::KILL.
   */
  public function selectAccess(Request $request) {
    $definitions = $this->paymentMethodConfigurationManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    $access_controller = $this->entityManager->getAccessController('payment_method');
    foreach (array_keys($definitions) as $plugin_id) {
      if ($access_controller->createAccess($plugin_id, $this->currentUser)) {
        return static::ALLOW;
      }
    }
    return static::DENY;
  }

  /**
   * Wraps theme().
   */
  protected function theme($hook, $variables = array()) {
    return theme($hook, $variables);
  }

  /**
   * Displays a payment method add form.
   *
   * @param string $plugin_id
   *
   * @return array
   */
  public function add($plugin_id) {
    $payment_method = $this->entityManager->getStorageController('payment_method')->create(array(
      'pluginId' => $plugin_id,
    ));

    return $this->formBuilder->getForm($this->entityManager->getFormController('payment_method', 'default')->setEntity($payment_method));
  }

  /**
   * Checks access to self::add().
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return string
   *   self::ALLOW, self::DENY, or self::KILL.
   */
  public function addAccess(Request $request) {
    $plugin_id = $request->attributes->get('plugin_id');

    return $this->entityManager->getAccessController('payment_method')->createAccess($plugin_id, $this->currentUser) ? self::ALLOW : self::DENY;
  }

  /**
   * Displays a payment method clone form.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface $payment_method
   *
   * @return array
   */
  public function duplicate(PaymentMethodInterface $payment_method) {
    /** @var \Drupal\payment\Entity\PaymentMethodInterface $clone */
    $clone = $payment_method
      ->createDuplicate();
    $payment_method->setLabel($this->t('!label (duplicate)', array(
        '!label' => $payment_method->label(),
      )));

    return $this->formBuilder->getForm($this->entityManager->getFormController('payment_method', 'default')->setEntity($clone));
  }

  /**
   * Wraps drupal_get_path().
   */
  protected function drupalGetPath($type, $name) {
    return drupal_get_path($type, $name);
  }
}
