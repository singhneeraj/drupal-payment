<?php

/**
 * Contains \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase.
 */

namespace Drupal\payment\Plugin\Payment\MethodConfiguration;

use Drupal\Core\Plugin\PluginBase;
use Drupal\Component\Utility\NestedArray;

/**
 * Provides a base payment method configuration plugin.
 */
abstract class PaymentMethodConfigurationBase extends PluginBase implements PaymentMethodConfigurationInterface {

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += $this->defaultConfiguration();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
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
  public function getPluginLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDescription() {
    return $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'message_text' => '',
      'message_text_format' => 'plain_text',
    );
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
   * Sets payer message text.
   *
   * @param string $text
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function setMessageText($text) {
    $this->configuration['message_text'] = $text;

    return $this;
  }

  /**
   * Gets the payer message text.
   *
   * @return string
   */
  public function getMessageText() {
    return $this->configuration['message_text'];
  }

  /**
   * Sets payer message text format.
   *
   * @param string $format
   *   The machine name of the text format the payer message is in.
   *
   * @return \Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface
   */
  public function setMessageTextFormat($format) {
    $this->configuration['message_text_format'] = $format;

    return $this;
  }

  /**
   * Gets the payer message text format.
   *
   * @return string
   */
  public function getMessageTextFormat() {
    return $this->configuration['message_text_format'];
  }

  /**
   * {@inheritdoc}
   */
  public function formElements(array $form, array &$form_state) {
    // @todo Add a token overview, possibly when Token.module has been ported.
    $elements['#element_validate'] = array(array($this, 'formElementsValidate'));
    $elements['#tree'] = TRUE;
    $elements['message'] = array(
      '#type' => 'text_format',
      '#title' => $this->t('Payment form message'),
      '#default_value' => $this->getMessageText(),
      '#format' => $this->getMessageTextFormat(),
    );

    return $elements;
  }

  /**
   * Implements form validate callback for self::formElements().
   */
  public function formElementsValidate(array $element, array &$form_state, array $form) {
    $values = NestedArray::getValue($form_state['values'], $element['#parents']);
    $this->setMessageText($values['message']['value']);
    $this->setMessageTextFormat($values['message']['format']);
  }
}
