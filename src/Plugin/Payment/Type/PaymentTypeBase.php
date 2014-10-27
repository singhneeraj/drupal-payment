<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Type\PaymentTypeBase.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Entity\PaymentInterface;
use Drupal\payment\Event\PaymentEvents;
use Drupal\payment\Event\PaymentTypePreResumeContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Defines base payment type.
 */
abstract class PaymentTypeBase extends PluginBase implements ContainerFactoryPluginInterface, PaymentTypeInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The payment this type is of.
   *
   * @var \Drupal\payment\Entity\PaymentInterface
   */
  protected $payment;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventDispatcherInterface $event_dispatcher) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->eventDispatcher = $event_dispatcher;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('event_dispatcher'));
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function getPayment() {
    return $this->payment;
  }

  /**
   * {@inheritdoc}
   */
  public function setPayment(PaymentInterface $payment) {
    $this->payment = $payment;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  function resumeContext() {
    $event = new PaymentTypePreResumeContext($this->getPayment());
    $this->eventDispatcher->dispatch(PaymentEvents::PAYMENT_TYPE_PRE_RESUME_CONTEXT, $event);
    // @todo Invoke Rules event.
    $this->doResumeContext();
  }

  /**
   * Performs the actual context resumption.
   */
  abstract protected function doResumeContext();

}
