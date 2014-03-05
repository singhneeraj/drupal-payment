<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Hook\HookInfoUnitTest.
 */

namespace Drupal\payment\Tests\Hook;

use Drupal\payment\Hook\HookInfo;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Hook\HookInfo
 */
class HookInfoUnitTest extends UnitTestCase {

  /**
   * The service under test.
   *
   * @var \Drupal\payment\Hook\HookInfo.
   */
  protected $service;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Hook\HookInfo unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->service = new HookInfo();
  }

  /**
   * @covers ::invoke
   */
  public function testInvoke() {
    $hooks = $this->service->invoke();
    $this->assertInternalType('array', $hooks);
    foreach ($hooks as $hook) {
      $this->assertInternalType('array', $hook);
      $this->assertArrayHasKey('group', $hook);
      $this->assertSame('payment', $hook['group']);
    }
  }
}
