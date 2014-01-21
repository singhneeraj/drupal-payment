<?php

/**
 * @file
 * Contains \Drupal\payment\Test\Hook\ElementInfoUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\Core\Render\Element;
use Drupal\payment\Hook\ElementInfo;
use Drupal\Tests\UnitTestCase;

/**
 * Tests \Drupal\payment\Hook\ElementInfo.
 */
class ElementInfoUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\ElementInfo.
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\ElementInfo unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc
   */
  public function setUp() {
    $this->service = new ElementInfo();
  }

  /**
   * @covers \Drupal\payment\Hook\ElementInfo::invoke()
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
