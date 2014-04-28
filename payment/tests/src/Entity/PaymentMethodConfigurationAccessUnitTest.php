<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentMethodConfigurationAccessUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentMethodConfigurationAccess;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfigurationAccess
 */
class PaymentMethodConfigurationAccessUnitTest extends UnitTestCase {

  /**
   * The access controller under test.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfigurationAccess
   */
  protected $accessController;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\PaymentMethodConfigurationAccess unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->accessController = new PaymentMethodConfigurationAccess($entity_type);
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithoutPermission() {
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithAnyPermission() {
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('payment.payment_method_configuration.' . $operation . '.any')
      ->will($this->returnValue(TRUE));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithOwnPermission() {
    $owner_id = mt_rand();
    $operation = $this->randomName();
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('id')
      ->will($this->returnValue($owner_id));
    $map = array(
      array('payment.payment_method_configuration.' . $operation . '.any', FALSE),
      array('payment.payment_method_configuration.' . $operation . '.own', TRUE),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method->expects($this->at(0))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id));
    $payment_method->expects($this->at(1))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id + 1));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessEnable() {
    $operation = 'enable';
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    // Enabled.
    $payment_method->expects($this->at(0))
      ->method('status')
      ->will($this->returnValue(TRUE));
    // Disabled, with permission.
    $payment_method->expects($this->at(1))
      ->method('status')
      ->will($this->returnValue(FALSE));
    $payment_method->expects($this->at(2))
      ->method('access')
      ->with('update', $account)
      ->will($this->returnValue(TRUE));
    // Disabled, without permission.
    $payment_method->expects($this->at(3))
      ->method('status')
      ->will($this->returnValue(FALSE));
    $payment_method->expects($this->at(4))
      ->method('access')
      ->with('update', $account)
      ->will($this->returnValue(FALSE));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    // Enabled.
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
    // Disabled, with permission.
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
    // Disabled, without permission.
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessDisable() {
    $operation = 'disable';
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    // Disabled.
    $payment_method->expects($this->at(0))
      ->method('status')
      ->will($this->returnValue(FALSE));
    // Enabled, with permission.
    $payment_method->expects($this->at(1))
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment_method->expects($this->at(2))
      ->method('access')
      ->with('update', $account)
      ->will($this->returnValue(TRUE));
    // Enabled, without permission.
    $payment_method->expects($this->at(3))
      ->method('status')
      ->will($this->returnValue(TRUE));
    $payment_method->expects($this->at(4))
      ->method('access')
      ->with('update', $account)
      ->will($this->returnValue(FALSE));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    // Disabled.
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
    // Enabled, with permission.
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
    // Enabled, without permission.
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessDuplicate() {
    $operation = 'duplicate';
    $language_code = $this->randomName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $access_controller = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfigurationAccess')
      ->setConstructorArgs(array($entity_type))
      ->setMethods(array('createAccess'))
      ->getMock();
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    // No create access.
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->will($this->returnValue(FALSE));
    // Create access, with view permission.
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->will($this->returnValue(TRUE));
    $payment_method->expects($this->at(2))
      ->method('access')
      ->with('view', $account)
      ->will($this->returnValue(TRUE));
    // Create access, without view permission.
    $access_controller->expects($this->at(2))
      ->method('createAccess')
      ->will($this->returnValue(TRUE));
    $payment_method->expects($this->at(4))
      ->method('access')
      ->with('view', $account)
      ->will($this->returnValue(FALSE));

    $class = new \ReflectionClass($access_controller);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    // No create access.
    $this->assertFalse($method->invokeArgs($access_controller, array($payment_method, $operation, $language_code, $account)));
    // Create access, with view permission.
    $this->assertTrue($method->invokeArgs($access_controller, array($payment_method, $operation, $language_code, $account)));
    // Create access, without view permission.
    $this->assertFalse($method->invokeArgs($access_controller, array($payment_method, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCheckCreateAccess() {
    $bundle = $this->randomName();
    $context = array();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('payment.payment_method_configuration.create.' . $bundle)
      ->will($this->returnValue(TRUE));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkCreateAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($account, $context, $bundle)));
  }

  /**
   * @covers ::getCache
   */
  public function testGetCache() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $cache_id = $this->randomName();
    $operation = $this->randomName();
    $language_code = $this->randomName();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('getCache');
    $method->setAccessible(TRUE);
    $this->assertNull($method->invokeArgs($this->accessController, array($cache_id, $operation, $language_code, $account)));
  }
}
