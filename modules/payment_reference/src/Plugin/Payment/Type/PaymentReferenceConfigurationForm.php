<?php

/**
 *
 */

namespace Drupal\payment_reference\Plugin\Payment\Type;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\payment\Plugin\Payment\DefaultPluginDefinitionMapper;
use Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface;
use Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the configuration form for the payment_reference payment type plugin.
 */
class PaymentReferenceConfigurationForm extends ConfigFormBase {

  /**
   * The payment method manager.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface
   */
  protected $paymentMethodManager;

  /**
   * The plugin selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface
   */
  protected $pluginSelectorManager;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   * @param \Drupal\payment\Plugin\Payment\Method\PaymentMethodManagerInterface $payment_method_manager
   * @param \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorManagerInterface $plugin_selector_manager
   */
  public function __construct(ConfigFactoryInterface $config_factory, TranslationInterface $string_translation, PaymentMethodManagerInterface $payment_method_manager, PluginSelectorManagerInterface $plugin_selector_manager) {
    parent::__construct($config_factory);
    $this->paymentMethodManager = $payment_method_manager;
    $this->pluginSelectorManager = $plugin_selector_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('string_translation'),
      $container->get('plugin.manager.payment.method'),
      $container->get('plugin.manager.payment.plugin_selector')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'payment_reference_configuration';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['payment_reference.payment_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('payment_reference.payment_type');

    $form['plugin_selector'] = $this->getPluginSelector($form_state)->buildSelectorForm([], $form_state);

    $limit_allowed_plugins_id = Html::getUniqueId('limit_allowed_plugins');
    $form['limit_allowed_plugins'] = array(
      '#default_value' => $config->get('limit_allowed_plugins'),
      '#id' => $limit_allowed_plugins_id,
      '#title' => $this->t('Limit allowed payment methods'),
      '#type' => 'checkbox',
    );
    $allowed_plugin_ids = $config->get('allowed_plugin_ids');
    $options = [];
    foreach ($this->paymentMethodManager->getDefinitions() as $definition) {
      $options[$definition['id']] = $definition['label'];
    }
    $form['allowed_plugin_ids'] = array(
      '#default_value' => $allowed_plugin_ids,
      '#multiple' => TRUE,
      '#options' => $options,
      '#states' => array(
        'visible' => array(
          '#' . $limit_allowed_plugins_id => array(
            'checked' => TRUE,
          ),
        ),
      ),
      '#title' => $this->t('Allowed payment methods'),
      '#type' => 'select',
    );

    return $form + parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->getPluginSelector($form_state)->validateSelectorForm($form['plugin_selector'], $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $plugin_selector = $this->getPluginSelector($form_state);
    $plugin_selector->submitSelectorForm($form['plugin_selector'], $form_state);
    $selected_plugin = $plugin_selector->getSelectedPlugin();
    $config = $this->config('payment_reference.payment_type');
    $values = $form_state->getValues();
    $config->set('plugin_selector_id', $selected_plugin->getPluginId());
    if ($selected_plugin instanceof ConfigurablePluginInterface) {
      $selected_plugin_configuration = $selected_plugin->getConfiguration();
    }
    else {
      $selected_plugin_configuration = [];
    }
    $config->set('plugin_selector_configuration', $selected_plugin_configuration);
    $config->set('limit_allowed_plugins', $values['limit_allowed_plugins']);
    $config->set('allowed_plugin_ids', $values['allowed_plugin_ids']);
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * Gets the plugin selector.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\payment\Plugin\Payment\PluginSelector\PluginSelectorInterface
   */
  protected function getPluginSelector(FormStateInterface $form_state) {
    $config = $this->config('payment_reference.payment_type');
    if ($form_state->has('plugin_selector')) {
      $plugin_selector = $form_state->get('plugin_selector');
    }
    else {
      $plugin_selector = $this->pluginSelectorManager->createInstance('payment_radios');
      $mapper = new DefaultPluginDefinitionMapper();
      $plugin_selector->setPluginManager($this->pluginSelectorManager, $mapper);
      $plugin_selector->setLabel($this->t('Payment method selector'));
      $plugin_selector->setRequired();
      $plugin_selector->setSelectedPlugin($this->pluginSelectorManager->createInstance($config->get('plugin_selector_id')));
      $form_state->set('plugin_selector', $plugin_selector);
    }

    return $plugin_selector;
  }

}
