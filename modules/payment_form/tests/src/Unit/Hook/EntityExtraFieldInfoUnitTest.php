<?php

/**
 * @file
 * Contains \Drupal\Tests\payment_form\Unit\Hook\EntityExtraFieldInfoUnitTest.
 */

namespace Drupal\Tests\payment_form\Unit\Hook;

use Drupal\payment_form\Hook\EntityExtraFieldInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_form\Hook\EntityExtraFieldInfo
 *
 * @group Payment
 */
class EntityExtraFieldInfoUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment_form\Hook\EntityExtraFieldInfo
   */
  protected $service;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->stringTranslation = $this->getStringTranslationStub();

    $this->service = new EntityExtraFieldInfo($this->stringTranslation);
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $fields = $this->service->invoke();
    $this->assertInternalType('array', $fields);
    $this->assertArrayHasKey('payment_method', $fields['payment']['payment_form']['form']);
  }
}
