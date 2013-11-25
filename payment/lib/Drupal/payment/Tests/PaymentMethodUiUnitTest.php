<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\PaymentMethodUiUnitTest.
 */

namespace Drupal\payment\Tests;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\PaymentMethodUi.
 */
class PaymentMethodUiUnitTest extends UnitTestCase {

  /**
   * The entity manager used for testing.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The form builder used for testing.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $formBuilder;

  /**
   * The payment method plugin manager used for testing.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\Manager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodManager;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\PaymentMethodUi|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodUi;

  /**
   * The URL generator used for testing.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment\PaymentMethodUi unit test',
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

    $this->formBuilder = $this->getMock('\Drupal\Core\Form\FormBuilderInterface');

    $this->paymentMethodManager = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Method\Manager')
      ->disableOriginalConstructor()
      ->getMock();

    $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');
    $this->urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->will($this->returnValue('http://example.com'));

    $this->paymentMethodUi = $this->getMockBuilder('\Drupal\payment\PaymentMethodUi')
      ->setConstructorArgs(array($this->entityManager, $this->paymentMethodManager, $this->formBuilder, $this->urlGenerator))
      ->setMethods(array('t', 'theme'))
      ->getMock();
  }

  /**
   * Tests enable().
   */
  public function testEnable() {
    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('enable');
    $payment_method->expects($this->once())
      ->method('save');
    $this->paymentMethodUi->enable($payment_method);
  }

  /**
   * Tests disable().
   */
  public function testDisable() {
    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('disable');
    $payment_method->expects($this->once())
      ->method('save');
    $this->paymentMethodUi->disable($payment_method);
  }

  /**
   * Tests select().
   */
  public function testSelect() {
    $definitions = array(
      'payment_unavailable' => array(),
      'foo' => array(
        'description' => $this->randomName(),
        'label' => $this->randomName(),
      ),
      'bar' => array(
        'description' => $this->randomName(),
        'label' => $this->randomName(),
      ),
    );
    $this->paymentMethodManager->expects($this->once())
      ->method('getDefinitions')
      ->will($this->returnValue($definitions));

    $access_controller = $this->getMock('\Drupal\Core\Entity\EntityAccessControllerInterface');
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->with('foo')
      ->will($this->returnValue(TRUE));
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->with('bar')
      ->will($this->returnValue(FALSE));
    $this->entityManager->expects($this->once())
      ->method('getAccessController')
      ->will($this->returnValue($access_controller));

    $this->paymentMethodUi->select();
  }

  /**
   * Tests add().
   */
  public function testAdd() {
    $plugin_id = $this->randomName();
    $plugin = $this->getMock('\Drupal\payment\Plugin\Payment\Method\PaymentMethodInterface');

    $this->paymentMethodManager->expects($this->once())
      ->method('createInstance')
      ->with($plugin_id)
      ->will($this->returnValue($plugin));

    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('setPlugin')
      ->will($this->returnSelf());

    $storage_controller = $this->getMock('\Drupal\Core\Entity\EntityStorageControllerInterface');
    $storage_controller->expects($this->once())
      ->method('create')
      ->will($this->returnValue($payment_method));

    $form_controller = $this->getMock('\Drupal\Core\Entity\EntityFormControllerInterface');
    $form_controller->expects($this->once())
      ->method('setEntity')
      ->will($this->returnSelf());

    $this->entityManager->expects($this->once())
      ->method('getStorageController')
      ->with('payment_method')
      ->will($this->returnValue($storage_controller));

    $this->entityManager->expects($this->once())
      ->method('getFormController')
      ->with('payment_method', 'default')
      ->will($this->returnValue($form_controller));

    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($form_controller);

    $this->paymentMethodUi->add($plugin_id);
  }

  /**
   * Tests duplicate().
   */
  public function testDuplicate() {
    $payment_method = $this->getMock('\Drupal\payment\Entity\PaymentMethodInterface');
    $payment_method->expects($this->once())
      ->method('createDuplicate')
      ->will($this->returnSelf());
    $payment_method->expects($this->once())
      ->method('setLabel')
      ->will($this->returnSelf());

    $form_controller = $this->getMock('\Drupal\Core\Entity\EntityFormControllerInterface');
    $form_controller->expects($this->once())
      ->method('setEntity')
      ->will($this->returnSelf());

    $this->entityManager->expects($this->once())
      ->method('getFormController')
      ->with('payment_method', 'default')
      ->will($this->returnValue($form_controller));

    $this->formBuilder->expects($this->once())
      ->method('getForm')
      ->with($form_controller);

    $this->paymentMethodUi->duplicate($payment_method);
  }

}
