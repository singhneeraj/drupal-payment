<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\PaymentAccessControllerUnitTest.
 */

namespace Drupal\payment\Tests\Entity;

use Drupal\payment\Entity\PaymentAccessController;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentAccessController
 */
class PaymentAccessControllerUnitTest extends UnitTestCase {

  /**
   * The access controller under test.
   *
   * @var \Drupal\payment\Entity\PaymentAccessController
   */
  protected $accessController;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\PaymentAccessController unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');
    $this->accessController = new PaymentAccessController($entity_type);
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
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
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
      ->with('payment.payment.' . $operation . '.any')
      ->will($this->returnValue(TRUE));
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
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
      array('payment.payment.' . $operation . '.any', FALSE),
      array('payment.payment.' . $operation . '.own', TRUE),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));
    $payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
      ->disableOriginalConstructor()
      ->getMock();
    $payment->expects($this->at(0))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id));
    $payment->expects($this->at(1))
      ->method('getOwnerId')
      ->will($this->returnValue($owner_id + 1));

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
    $this->assertFalse($method->invokeArgs($this->accessController, array($payment, $operation, $language_code, $account)));
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCheckCreateAccess() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $context = array();

    $class = new \ReflectionClass($this->accessController);
    $method = $class->getMethod('checkCreateAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessController, array($account, $context)));
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
