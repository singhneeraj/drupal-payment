<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm..
 */

namespace Drupal\payment\Entity\PaymentMethodConfiguration;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment method configuration form.
 */
class PaymentMethodConfigurationForm extends EntityForm {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The payment method configuration manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The payment method configuration storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $paymentMethodConfigurationStorage;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userStorage;

  /**
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_method_configuration_storage
   *   The payment method configuration storage.
   * @param \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager
   *   The payment method configuration manager.
   */
  function __construct(TranslationInterface $string_translation, FormBuilderInterface $form_builder, AccountInterface $current_user, EntityStorageInterface $user_storage, EntityStorageInterface $payment_method_configuration_storage, PaymentMethodConfigurationManagerInterface $payment_method_configuration_manager) {
    $this->currentUser = $current_user;
    $this->formBuilder = $form_builder;
    $this->paymentMethodConfigurationManager = $payment_method_configuration_manager;
    $this->paymentMethodConfigurationStorage = $payment_method_configuration_storage;
    $this->stringTranslation = $string_translation;
    $this->userStorage = $user_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($container->get('string_translation'), $container->get('form_builder'), $container->get('current_user'), $entity_manager->getStorage('user'), $entity_manager->getStorage('payment_method_configuration'), $container->get('plugin.manager.payment.method_configuration'));
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method_configuration */
    $payment_method_configuration = $this->getEntity();
    $definition = $this->paymentMethodConfigurationManager->getDefinition($payment_method_configuration->getPluginId());
    $payment_method_configuration_plugin = $this->paymentMethodConfigurationManager->createInstance($payment_method_configuration->getPluginId(), $payment_method_configuration->getPluginConfiguration());
    $form_state['payment_method_configuration_plugin'] = $payment_method_configuration_plugin;
    $form['type'] = array(
      '#type' => 'item',
      '#title' => $this->t('Type'),
      '#markup' => $definition['label'],
    );
    $form['status'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $payment_method_configuration->status(),
    );
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $payment_method_configuration->label(),
      '#maxlength' => 255,
      '#required' => TRUE,
    );
    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $payment_method_configuration->id(),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#machine_name' => array(
        'source' => array('label'),
        'exists' => array($this, 'paymentMethodConfigurationIdExists'),
      ),
      '#disabled' => !$payment_method_configuration->isNew(),
    );
    $owner_label = '';
    if ($payment_method_configuration->getOwner()) {
      $owner_label = $payment_method_configuration->getOwner()->label();
    }
    elseif ($this->currentUser instanceof UserInterface) {
      $owner_label = $this->currentUser->label();
    }
    $form['owner'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Owner'),
      '#default_value' => $owner_label,
      '#maxlength' => 255,
      '#autocomplete_route_name' => 'user.autocomplete',
      '#required' => TRUE,
    );
    $form['plugin_form'] = $payment_method_configuration_plugin->formElements($form, $form_state);

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
    $values = $form_state['values'];
    $owner = $this->userStorage->loadByProperties(array(
      'name' => $values['owner'],
    ));
    if (!$owner) {
      $this->formBuilder->setError($form['owner'], $form_state, $this->t('The username %name does not exist.', array(
        '%name' => $values['owner'],
      )));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $payment_method_configuration, array $form, array &$form_state) {
    /** @var \Drupal\payment\Entity\PaymentMethodConfigurationInterface $payment_method_configuration */
    parent::copyFormValuesToEntity($payment_method_configuration, $form, $form_state);
    $values = $form_state['values'];
    $users = $this->userStorage->loadByProperties(array(
      'name' => $values['owner'],
    ));
    /** @var \Drupal\user\UserInterface $owner */
    $owner = reset($users);
    /** @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationInterface $payment_method_configuration_plugin */
    $payment_method_configuration_plugin = $form_state['payment_method_configuration_plugin'];
    $payment_method_configuration->setLabel($values['label']);
    $payment_method_configuration->setStatus($values['status']);
    $payment_method_configuration->setOwnerId($owner->id());
    $payment_method_configuration->setPluginConfiguration($payment_method_configuration_plugin->getConfiguration());
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, array &$form_state) {
    parent::save($form, $form_state);
    $payment_method = $this->getEntity();
    $payment_method->save();
    drupal_set_message($this->t('@label has been saved.', array(
      '@label' => $payment_method->label()
    )));
    $form_state['redirect_route'] = new Url('payment.payment_method_configuration.list');
  }

  /**
   * Checks if a payment method with a particular ID already exists.
   *
   * @param string $id
   *
   * @return bool
   */
  public function paymentMethodConfigurationIdExists($id) {
    return (bool) $this->paymentMethodConfigurationStorage->load($id);
  }

}
