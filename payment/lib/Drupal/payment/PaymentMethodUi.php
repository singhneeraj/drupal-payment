<?php

/**
 * @file
 * Contains \Drupal\payment\PaymentMethodUi.
 */

namespace Drupal\payment;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\payment\Entity\PaymentMethodInterface;
use Drupal\payment\Plugin\Payment\Method\Manager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Returns responses for payment method routes.
 */
class PaymentMethodUi extends ControllerBase implements ContainerInjectionInterface {

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
   * The payment method plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager
   */
  protected $paymentMethodManager;

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, Manager $payment_method_manager, FormBuilderInterface $form_builder, UrlGeneratorInterface $url_generator) {
    $this->entityManager = $entity_manager;
    $this->formBuilder = $form_builder;
    $this->paymentMethodManager = $payment_method_manager;
    $this->urlGenerator = $url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.entity'), $container->get('plugin.manager.payment.method'), $container->get('form_builder'), $container->get('url_generator'));
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
   * Displays a list of available payment method plugins.
   *
   * @return string
   */
  public function select() {
    $definitions = $this->paymentMethodManager->getDefinitions();
    unset($definitions['payment_unavailable']);
    $access_controller = $this->entityManager->getAccessController('payment_method');
    $items = array();
    foreach ($definitions as $plugin_id => $definition) {
      $access = $access_controller->createAccess($plugin_id);
      if ($access) {
        $href = $this->urlGenerator->generateFromRoute('payment.payment_method.add', array(
          'payment_method_plugin_id' => $plugin_id,
        ));
        $items[] = array(
          'title' => $definition['label'],
          'href' => $href,
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
   * Wraps theme().
   */
  protected function theme($hook, $variables = array()) {
    return theme($hook, $variables);
  }

  /**
   * Displays a payment method add form.
   *
   * @param string $payment_method_plugin_id
   *
   * @return array
   */
  public function add($payment_method_plugin_id) {
    $plugin = $this->paymentMethodManager->createInstance($payment_method_plugin_id);
    $payment_method = $this->entityManager->getStorageController('payment_method')->create(array())->setPlugin($plugin);

    return $this->formBuilder->getForm($this->entityManager->getFormController('payment_method', 'default')->setEntity($payment_method));
  }

  /**
   * Displays a payment method clone form.
   *
   * @param \Drupal\payment\Entity\PaymentMethodInterface $payment_method
   *
   * @return array
   */
  public function duplicate(PaymentMethodInterface $payment_method) {
    $clone = $payment_method
      ->createDuplicate()
      ->setLabel($this->t('!label (duplicate)', array(
        '!label' => $payment_method->label(),
      )));

    return $this->formBuilder->getForm($this->entityManager->getFormController('payment_method', 'default')->setEntity($clone));
  }
}
