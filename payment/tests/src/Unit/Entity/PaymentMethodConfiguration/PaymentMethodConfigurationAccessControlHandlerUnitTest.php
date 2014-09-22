<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandlerUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\PaymentMethodConfiguration;

use Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler
 *
 * @group Payment
 */
class PaymentMethodConfigurationAccessControlHandlerUnitTest extends UnitTestCase {

  /**
   * The access control handler under test.
   *
   * @var \Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler
   */
  protected $accessControlHandler;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');
    $this->moduleHandler->expects($this->any())
      ->method('invokeAll')
      ->willReturn(array());

    $this->accessControlHandler = new PaymentMethodConfigurationAccessControlHandler($entity_type, $this->moduleHandler);
  }

  /**
   * @covers ::createInstance
   */
  public function testCreateInstance() {
    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('module_handler', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->moduleHandler),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $entity_type = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

    $handler = PaymentMethodConfigurationAccessControlHandler::createInstance($container, $entity_type);
    $this->assertInstanceOf('\Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler', $handler);
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithoutPermission() {
    $operation = $this->randomMachineName();
    $language_code = $this->randomMachineName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValue(FALSE));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method->expects($this->any())
      ->method('getCacheTag')
      ->willReturn(array('payment_method_configuration' => array(1)));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertFalse($method->invokeArgs($this->accessControlHandler, array($payment_method, $operation, $language_code, $account))->isAllowed());
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithAnyPermission() {
    $operation = $this->randomMachineName();
    $language_code = $this->randomMachineName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment_method_configuration.' . $operation . '.any', TRUE),
      array('payment.payment_method_configuration.' . $operation . '.own', FALSE),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->will($this->returnValueMap($map));
    $payment_method = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method->expects($this->any())
      ->method('getCacheTag')
      ->willReturn(array('payment_method_configuration' => array(1)));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessControlHandler, array($payment_method, $operation, $language_code, $account))->isAllowed());
  }

  /**
   * @covers ::checkAccess
   */
  public function testCheckAccessWithOwnPermission() {
    $owner_id = mt_rand();
    $operation = $this->randomMachineName();
    $language_code = $this->randomMachineName();
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
    $payment_method->expects($this->any())
      ->method('getCacheTag')
      ->willReturn(array('payment_method_configuration' => array(1)));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessControlHandler, array($payment_method, $operation, $language_code, $account))->isAllowed());
    $this->assertFalse($method->invokeArgs($this->accessControlHandler, array($payment_method, $operation, $language_code, $account))->isAllowed());
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccessEnable
   */
  public function testCheckAccessEnable($expected, $payment_method_configuration_status, $has_update_permission) {
    $operation = 'enable';
    $language_code = $this->randomMachineName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment_method_configuration.update.any', $has_update_permission),
      array('payment.payment_method_configuration.update.own', FALSE),
    );
    $account->expects($this->atLeastOnce())
      ->method('hasPermission')
      ->willReturnMap($map);
    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('status')
      ->will($this->returnValue($payment_method_configuration_status));
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('getCacheTag')
      ->willReturn(array(
        'payment_method_configuration' => array(1),
      ));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertSame($expected, $method->invokeArgs($this->accessControlHandler, array($payment_method_configuration, $operation, $language_code, $account))->isAllowed());

  }

  /**
   * Provides data to self::testCheckAccessEnable().
   */
  public function providerTestCheckAccessEnable() {
    return array(
      // Enabled with permission.
      array(FALSE, TRUE, TRUE),
      // Disabled with permission.
      array(TRUE, FALSE, TRUE),
      // Disabled without permission.
      array(FALSE, FALSE, FALSE),
    );
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccessDisable
   */
  public function testCheckAccessDisable($expected, $payment_method_configuration_status, $has_update_permission) {
    $operation = 'disable';
    $language_code = $this->randomMachineName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment_method_configuration.update.any', $has_update_permission),
      array('payment.payment_method_configuration.update.own', FALSE),
    );
    $account->expects($this->atLeastOnce())
      ->method('hasPermission')
      ->willReturnMap($map);
    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('status')
      ->will($this->returnValue($payment_method_configuration_status));
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('getCacheTag')
      ->willReturn(array(
        'payment_method_configuration' => array(1),
      ));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertSame($expected, $method->invokeArgs($this->accessControlHandler, array($payment_method_configuration, $operation, $language_code, $account))->isAllowed());

  }

  /**
   * Provides data to self::testCheckAccessDisable().
   */
  public function providerTestCheckAccessDisable() {
    return array(
      // Disabled with permission.
      array(FALSE, FALSE, TRUE),
      // Enabled with permission.
      array(TRUE, TRUE, TRUE),
      // Enabled without permission.
      array(FALSE, TRUE, FALSE),
    );
  }

  /**
   * @covers ::checkAccess
   *
   * @dataProvider providerTestCheckAccessDuplicate
   */
  public function testCheckAccessDuplicate($expected, $has_create_permission, $has_view_permission) {
    $operation = 'duplicate';
    $language_code = $this->randomMachineName();
    $bundle = $this->randomMachineName();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $map = array(
      array('payment.payment_method_configuration.create.' . $bundle, $has_create_permission),
      array('payment.payment_method_configuration.view.any', $has_view_permission),
    );
    $account->expects($this->any())
      ->method('hasPermission')
      ->willReturnMap($map);
    $payment_method_configuration = $this->getMockBuilder('\Drupal\payment\Entity\PaymentMethodConfiguration')
      ->disableOriginalConstructor()
      ->getMock();
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('bundle')
      ->will($this->returnValue($bundle));
    $payment_method_configuration->expects($this->atLeastOnce())
      ->method('getCacheTag')
      ->willReturn(array(
        'payment_method_configuration' => array(1),
      ));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkAccess');
    $method->setAccessible(TRUE);
    $this->assertSame($expected, $method->invokeArgs($this->accessControlHandler, array($payment_method_configuration, $operation, $language_code, $account))->isAllowed());

  }

  /**
   * Provides data to self::testCheckAccessDuplicate().
   */
  public function providerTestCheckAccessDuplicate() {
    return array(
      // No create access.
      array(FALSE, FALSE, TRUE),
      // Create access, with view permission.
      array(TRUE, TRUE, TRUE),
      // Create access, without view permission.
      array(FALSE, TRUE, FALSE),
      // No access.
      array(FALSE, FALSE, FALSE),
    );
  }

  /**
   * @covers ::checkCreateAccess
   */
  public function testCheckCreateAccess() {
    $bundle = $this->randomMachineName();
    $context = array();
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $account->expects($this->once())
      ->method('hasPermission')
      ->with('payment.payment_method_configuration.create.' . $bundle)
      ->will($this->returnValue(TRUE));

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('checkCreateAccess');
    $method->setAccessible(TRUE);
    $this->assertTrue($method->invokeArgs($this->accessControlHandler, array($account, $context, $bundle))->isAllowed());
  }

  /**
   * @covers ::getCache
   */
  public function testGetCache() {
    $account = $this->getMock('\Drupal\Core\Session\AccountInterface');
    $cache_id = $this->randomMachineName();
    $operation = $this->randomMachineName();
    $language_code = $this->randomMachineName();

    $class = new \ReflectionClass($this->accessControlHandler);
    $method = $class->getMethod('getCache');
    $method->setAccessible(TRUE);
    $this->assertNull($method->invokeArgs($this->accessControlHandler, array($cache_id, $operation, $language_code, $account)));
  }
}
