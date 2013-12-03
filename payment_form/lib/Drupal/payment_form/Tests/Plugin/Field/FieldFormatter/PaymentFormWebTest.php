<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\Field\FieldFormatter\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\Field\FieldFormatter;

use Drupal\field\Field;
use Drupal\field\FieldInterface;
use Drupal\payment\Generate;
use Drupal\payment\Payment;
use Drupal\payment_form\Plugin\Payment\Type\PaymentForm;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\field\formatter\PaymentForm.
 */
class PaymentFormWebTest extends WebTestBase {

  /**
   * The payment method used for testing.
   *
   * @var \Drupal\payment\Entity\PaymentMethod
   */
  protected $paymentMethod;

  /**
   * The payment entity storage controller.
   *
   * @var \Drupal\payment\Entity\PaymentStorageController
   */
  protected $paymentStorage;

  /**
   * The plugin ID of the status to set the payment to.
   *
   * @var string
   */
  protected $statusPluginId = 'payment_cancelled';

  /**
   * The user to add the field to.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  public static $modules = array('entity_reference', 'field', 'payment', 'payment_form');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Field\FieldFormatter\PaymentForm web test',
      'group' => 'Payment Form Field',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->paymentStorage = \Drupal::entityManager()->getStorageController('payment');

    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_entity', array(
      'cardinality' => FieldInterface::CARDINALITY_UNLIMITED,
      'entity_type' => 'user',
      'name' => $field_name,
      'type' => 'payment_form',
    ))->save();
    entity_create('field_instance', array(
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
    ));
    foreach (Generate::createPaymentLineItems() as $i => $line_item) {
      $this->user->{$field_name}[$i]->plugin_id = $line_item->getPluginId();
      $this->user->{$field_name}[$i]->plugin_configuration = $line_item->getConfiguration();
    }
    $this->user->save();

    // Create a payment method.
    $this->paymentMethod = Generate::createPaymentMethod(2, 'payment_basic');
    $this->paymentMethod->setPluginConfiguration(array(
      'status' => $this->statusPluginId,
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
    $path = 'user/' . $user->id();
    $this->drupalPostForm($path, array(), t('Pay'));
    $this->assertUrl($path);
    $this->assertResponse('200');
    // This is supposed to be the first and only payment.
    $payment = $this->paymentStorage->load(1);
    if ($this->assertTrue((bool) $payment)) {
      $this->assertTrue($payment->getPaymentType() instanceof PaymentForm);
      $this->assertIdentical($payment->getStatus()->getPluginId(), $this->statusPluginId);
    }
  }
}
