<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Method\PaymentMethodConfigurationOperationsProvider.
 */

namespace Drupal\payment\Plugin\Payment\Method;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\OperationsProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides operations for payment methods based on config entities.
 */
abstract class PaymentMethodConfigurationOperationsProvider implements OperationsProviderInterface, ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The payment method configuration list builder.
   *
   * @var \Drupal\Core\Entity\EntityListBuilderInterface
   */
  protected $paymentMethodConfigurationListBuilder;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new class instance.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_method_configuration_storage
   *   The payment method configuration storage.
   * @param \Drupal\Core\Entity\EntityListBuilderInterface $payment_method_configuration_list_builder
   *   The payment method configuration list builder.
   */
  public function __construct(RequestStack $request_stack, TranslationInterface $string_translation, EntityStorageInterface $payment_method_configuration_storage, EntityListBuilderInterface $payment_method_configuration_list_builder) {
    $this->paymentMethodConfigurationListBuilder = $payment_method_configuration_list_builder;
    $this->paymentMethodConfigurationStorage = $payment_method_configuration_storage;
    $this->requestStack = $request_stack;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($container->get('request_stack'), $container->get('string_translation'), $entity_manager->getStorage('payment_method_configuration'), $entity_manager->getListBuilder('payment_method_configuration'));
  }

  /**
   * Gets the payment method configuration entity for this plugin.
   *
   * @param string $plugin_id
   *   This plugin's ID.
   *
   * @return \Drupal\payment\Entity\PaymentMethodConfigurationInterface
   */
  abstract protected function getPaymentMethodConfiguration($plugin_id);

  /**
   * {@inheritdoc}
   */
  public function getOperations($plugin_id) {
    $payment_method_configuration_operations = $this->paymentMethodConfigurationListBuilder->getOperations($this->getPaymentMethodConfiguration($plugin_id));

    $titles = array(
      'edit' => $this->t('Edit configuration'),
      'delete' => $this->t('Delete configuration'),
      'enable' => $this->t('Enable configuration'),
      'disable' => $this->t('Disable configuration'),
    );
    $operations = array();
    foreach ($payment_method_configuration_operations as $name => $payment_method_configuration_operation) {
      if (array_key_exists($name, $titles)) {
        $operations[$name] = $payment_method_configuration_operation;
        $operations[$name]['title'] = $titles[$name];
        $operations[$name]['query']['destination'] = $this->requestStack->getCurrentRequest()->attributes->get('_system_path');
      }
    }

    return $operations;
  }

}
