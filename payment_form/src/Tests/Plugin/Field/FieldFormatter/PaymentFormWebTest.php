<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Field\FieldFormatter\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldFormatter;

use Drupal\field\FieldStorageConfigInterface;
use Drupal\payment\Tests\Generate;
use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\simpletest\WebTestBase;

/**
 * \Drupal\payment\Plugin\Field\FieldFormatter\PaymentForm web test.
 *
 * @group Payment Form Field
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * The payment method configuration used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration
   */
  protected $paymentMethod;

  /**
   * The payment entity storage controller.
   *
   * @var \Drupal\payment\Entity\Payment\PaymentStorage
   */
  protected $paymentStorage;

  /**
   * The plugin ID of the status to set the payment to.
   *
   * @var string
   */
  protected $executeStatusPluginId = 'payment_pending';

  /**
   * The user to add the field to.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('entity_reference', 'field', 'filter', 'payment', 'payment_form');

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->paymentStorage = \Drupal::entityManager()->getStorage('payment');

    // Create the field and field instance.
    $field_name = strtolower($this->randomMachineName());
    entity_create('field_storage_config', array(
      'cardinality' => FieldStorageConfigInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_form',
    ))->save();
    entity_create('field_instance_config', array(
      'bundle' => 'user',
      'entity_type' => 'user',
      'field_name' => $field_name,
      'settings' => array(
        'currency_code' => 'EUR',
      ),
    ))->save();
    entity_get_display('user', 'user', 'default')
      ->setComponent($field_name, array(
        'type' => 'payment_form',
      ))
      ->save();

    // Create an entity.
    $this->user = entity_create('user', array(
      'name' => $this->randomString(),
      'status' => TRUE,
    ));
    foreach (Generate::createPaymentLineItems() as $i => $line_item) {
      $this->user->{$field_name}[$i]->get('plugin_id')->setValue($line_item->getPluginId());
      $this->user->{$field_name}[$i]->get('plugin_configuration')->setValue($line_item->getConfiguration());
    }
    $this->user->save();

    // Create a payment method.
    $this->paymentMethod = Generate::createPaymentMethodConfiguration(2, 'payment_basic');
    $this->paymentMethod->setPluginConfiguration(array(
      'execute_status_id' => $this->executeStatusPluginId,
    ));
    $this->paymentMethod->save();
  }

  /**
   * Tests the formatter().
   */
  protected function testFormatter() {
    // Make sure there are no payments yet.
    $this->assertEqual(count($this->paymentStorage->loadMultiple()), 0);
    $user = $this->drupalCreateUser(array('access user profiles'));
    $this->drupalLogin($user);
    $path = 'user/' . $this->user->id();
    $this->drupalPostForm($path, array(), t('Pay'));
    // The front page is the currently logged-in user.
    $this->assertUrl($path);
    $this->assertResponse('200');
    // This is supposed to be the first and only payment.
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $payment = $this->paymentStorage->load(1);
    if ($this->assertTrue((bool) $payment)) {
      $this->assertTrue($payment->getPaymentType() instanceof PaymentForm);
      $this->assertIdentical($payment->getPaymentStatus()->getPluginId(), $this->executeStatusPluginId);
    }
  }
}
