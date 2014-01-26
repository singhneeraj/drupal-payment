<?php

/**
 * @file
 * Contains \Drupal\payment\Test\Hook\MenuUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\Menu;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\Menu
 */
class MenuUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\Menu.
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\Menu unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->service = new Menu();
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $items = $this->service->invoke();
    $this->assertInternalType('array', $items);
    foreach ($items as $item) {
      $this->assertInternalType('array', $item);
      $this->assertArrayHasKey('route_name', $item);
      $this->assertArrayHasKey('title', $item);
    }
  }
}
