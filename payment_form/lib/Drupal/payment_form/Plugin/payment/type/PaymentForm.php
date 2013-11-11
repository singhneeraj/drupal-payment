<?php

/**
 * Contains \Drupal\payment_form\Plugin\payment\type\PaymentForm.
 */

namespace Drupal\payment_form\Plugin\payment\type;

use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\HttpKernel;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\payment\Plugin\payment\type\Base;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The payment form field payment type.
 *
 * @PaymentType(
 *   id = "payment_form",
 *   label = @Translation("Payment form field")
 * )
 */
class PaymentForm extends Base implements ContainerFactoryPluginInterface {

  /**
   * The field instance storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageControllerInterface
   */
  protected $fieldInstanceStorage;

  /**
   * The HTTP kernel.
   *
   * @var \Drupal\Core\HttpKernel
   */
  protected $httpKernel;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * A URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\HttpKernel $http_kernel
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   * @param \Drupal\Core\Entity\EntityStorageControllerInterface $field_instance_storage
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, HttpKernel $http_kernel, Request $request, ModuleHandlerInterface $module_handler , UrlGeneratorInterface $url_generator, EntityStorageControllerInterface $field_instance_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler);
    $this->httpKernel = $http_kernel;
    $this->request = $request;
    $this->urlGenerator = $url_generator;
    $this->fieldInstanceStorage = $field_instance_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_kernel'),
      $container->get('request'),
      $container->get('module_handler'),
      $container->get('url_generator'),
      $container->get('entity.manager')->getStorageController('field_instance')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resumeContext() {
    parent::resumeContext();

    $response = new RedirectResponse($this->urlGenerator->generateFromRoute('<front>', array(), array(
      'absolute' => TRUE,
    )));
    $response->prepare($this->request)
      ->send();
    $this->httpKernel->terminate($this->request, $response);
    exit;
  }

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    $instance = $this->fieldInstanceStorage->load($this->getFieldInstanceId());

    return $instance->label();
  }

  /**
   * Sets the ID of the field instance the payment was made for.
   *
   * @param string $field_instance_id
   *
   * @return self
   */
  public function setFieldInstanceId($field_instance_id) {
    $this->getPayment()->set('payment_form_field_instance', $field_instance_id);

    return $this;
  }

  /**
   * Gets the ID of the field instance the payment was made for.
   *
   * @return string
   */
  public function getFieldInstanceId() {
    $values =  $this->getPayment()->get('payment_form_field_instance');

    return isset($values[0]) ? $values[0]->get('target_id')->getValue() : NULL;
  }

}
