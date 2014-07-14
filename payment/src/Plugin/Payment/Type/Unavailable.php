<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Type\Unavailable.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * An unavailable payment type.
 *
 * @PaymentType(
 *   id = "payment_unavailable",
 *   label = @Translation("Unavailable")
 * )
 */
class Unavailable extends PaymentTypeBase {

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, EventDispatcherInterface $event_dispatcher, TranslationInterface $string_translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $event_dispatcher);
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('event_dispatcher'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  protected function doResumeContext() {
    throw new NotFoundHttpException();
  }

  /**
   * {@inheritdoc}
   */
  public function paymentDescription($language_code = NULL) {
    return $this->t('Unavailable', array(), array(
      'langcode' => $language_code,
    ));
  }

}
