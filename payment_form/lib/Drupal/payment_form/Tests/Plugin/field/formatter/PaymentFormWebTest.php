<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\Plugin\field\formatter\PaymentFormWebTest.
 */

namespace Drupal\payment_form\Tests\Plugin\field\formatter;

use Drupal\payment\Generate;
use Drupal\payment_form\Plugin\payment\type\PaymentForm;
use Drupal\simpletest\WebTestBase;

/**
 * Tests \Drupal\payment\Plugin\field\formatter\PaymentForm.
 */
class PaymentFormWebTest extends WebTestBase {

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
  public static $modules = array('field_ui', 'payment', 'payment_form');

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\field\formatter\PaymentForm web test',
      'group' => 'Payment Form Field',
    );
  }

  /**
   * {@inheritdoc
   */
  protected function setUp() {
    parent::setUp();
    $this->paymentStorage = $this->container->get('plugin.manager.entity')->getStorageController('payment');

    // Create the field and field instance.
    $field_name = strtolower($this->randomName());
    entity_create('field_entity', array(
      'cardinality' => FIELD_CARDINALITY_UNLIMITED,
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
      $this->user->{$field_name}[$i]->line_item = $line_item;
    }
    $this->user->save();

    // Create a payment method.
    $plugin = $this->container->get('plugin.manager.payment.method')->createInstance('payment_basic');
    $plugin->setStatus($this->statusPluginId);
    Generate::createPaymentMethod(2, $plugin)
      ->save();
  }

  /**
   * Tests the formatter().
   */
  protected function testFormatter() {
    $user = $this->drupalCreateUser(array('access user profiles'));
    $this->drupalLogin($user);
    $path = 'user/' . $user->id();
    $this->drupalGet($path);
    $this->drupalPostForm(NULL, array(), t('Pay'));
    $this->assertUrl($path);
    $this->assertResponse('200');
    $this->drupalGet($path);
    // This is supposed to be the first and only payment.
    $payment = $this->paymentStorage->load(1);
    if ($this->assertTrue((bool) $payment)) {
      $this->assertTrue($payment->getPaymentType() instanceof PaymentForm);
      $this->assertIdentical($payment->getStatus()->getPluginId(), $this->statusPluginId);
    }
  }
}
