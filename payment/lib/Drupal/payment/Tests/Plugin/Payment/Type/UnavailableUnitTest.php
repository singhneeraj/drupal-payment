<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Plugin\Payment\Type\UnavailableUnitTest.
 */

namespace Drupal\payment\Tests\Plugin\Payment\Type;

use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Plugin\Payment\Status\Unavailable.
 */
class UnavailableUnitTest extends UnitTestCase {

  /**
   * The module handler used for testing.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The payment type under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\Unavailable|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentType;

  /**
   * The translation manager used for testing.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $translationManager;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Plugin\Payment\Type\Unavailable unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->translationManager = $this->getMockBuilder('\Drupal\Core\StringTranslation\TranslationManager')
      ->disableOriginalConstructor()
      ->getMock();

    $configuration = array();
    $plugin_id = $this->randomName();
    $plugin_definition = array();
    $this->paymentType = $this->getMockBuilder('\Drupal\payment\Plugin\Payment\Type\Unavailable')
      ->setConstructorArgs(array($configuration, $plugin_id, $plugin_definition, $this->moduleHandler, $this->translationManager))
      ->setMethods(NULL)
      ->getMock();
  }

  /**
   * Tests resumeContext().
   *
   * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function testResumeContext() {
    $this->paymentType->resumeContext();
  }

  /**
   * Tests paymentDescription().
   */
  public function testPaymentDescription() {
    $this->translationManager->expects($this->once())
      ->method('translate')
      ->will($this->returnArgument(0));
    $this->assertInternalType('string', $this->paymentType->paymentDescription());
  }
}
