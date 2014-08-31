<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\Hook\EntityExtraFieldInfoUnitTest.
 */

namespace Drupal\payment_reference\Tests\Hook;

use Drupal\payment_reference\Hook\EntityExtraFieldInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment_reference\Hook\EntityExtraFieldInfo
 *
 * @group Payment
 */
class EntityExtraFieldInfoUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\EntityExtraFieldInfo
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
    $this->assertArrayHasKey('line_items', $fields['payment']['payment_reference']['form']);
    $this->assertArrayHasKey('payment_method', $fields['payment']['payment_reference']['form']);
  }
}
