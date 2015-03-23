<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Element\PaymentReference.
 */

namespace Drupal\payment_reference\Element;

use Drupal\Component\Utility\Random;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreExpirableInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\payment\Element\PaymentReferenceBase;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface;
use Drupal\payment\QueueInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides a payment reference element.
 *
 * @FormElement("payment_reference")
 */
class PaymentReference extends PaymentReferenceBase {

  /**
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface
   */
  protected $paymentQueue;

  /**
   * Creates a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_storage
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   * @param \Drupal\Core\Utility\LinkGeneratorInterface $link_generator
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface $plugin_selector_manager
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\QueueInterface $payment_queue
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, RequestStack $request_stack, EntityStorageInterface $payment_storage, TranslationInterface $string_translation, DateFormatter $date_formatter, LinkGeneratorInterface $link_generator, RendererInterface $renderer, AccountInterface $current_user, PluginSelectorManagerInterface $plugin_selector_manager, PaymentMethodManagerInterface $payment_method_manager, Random $random, QueueInterface $payment_queue) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $request_stack, $payment_storage, $string_translation, $date_formatter, $link_generator, $renderer, $current_user, $plugin_selector_manager, $payment_method_manager, $random);
    $this->paymentQueue = $payment_queue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($configuration, $plugin_id, $plugin_definition, $container->get('request_stack'), $entity_manager->getStorage('payment'), $container->get('string_translation'), $container->get('date.formatter'), $container->get('link_generator'), $container->get('renderer'), $container->get('current_user'), $container->get('plugin.manager.payment.plugin_selector'), $container->get('plugin.manager.payment.method'), new Random(), $container->get('payment_reference.queue'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getPaymentQueue() {
    return $this->paymentQueue;
  }

}
