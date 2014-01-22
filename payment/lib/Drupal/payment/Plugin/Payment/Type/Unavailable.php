<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\Type\Unavailable.
 */

namespace Drupal\payment\Plugin\Payment\Type;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
   * The translation manager.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $translationManager;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\StringTranslation\TranslationManager $translation_manager
   *   The translation manager.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition, ModuleHandlerInterface $module_handler, TranslationManager $translation_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler);
    $this->translationManager = $translation_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, array $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('module_handler'), $container->get('string_translation'));
  }

  /**
   * {@inheritdoc}
   */
  public function resumeContext() {
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

  /**
   * Translates a string to the current language or to a given language.
   *
   * This is a wrapper so POTX can use it to extract translatable strings.
   */
  protected function t($string, array $args = array(), array $options = array()) {
    return $this->translationManager->translate($string, $args, $options);
  }
}
