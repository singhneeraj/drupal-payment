<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Hook\ElementInfoUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\Core\Render\Element;
use Drupal\payment\Hook\ElementInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\ElementInfo
 *
 * @group Payment
 */
class ElementInfoUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\ElementInfo|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->service = new ElementInfo();
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $elements = $this->service->invoke();
    $this->assertInternalType('array', $elements);
    foreach ($elements as $element) {
      $this->assertInternalType('array', $element);
      $this->assertSame(0, count(Element::children($element)));
    }
  }
}
