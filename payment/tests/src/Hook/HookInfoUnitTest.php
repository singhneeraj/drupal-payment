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

  /**
   * Tests if declared hooks are documented in payment.api.php and vice versa.
   *
   * @depends testInvoke
   */
  public function testDocumentation() {
    $declared_hooks = array();
    foreach (array_keys($this->service->invoke()) as $hook) {
      $declared_hooks[] = 'hook_' . $hook;
    }

    $api_file_path = __DIR__ . '/../../../payment.api.php';
    $tokens = token_get_all(file_get_contents($api_file_path));
    $documented_hooks = array();
    foreach ($tokens as $index => $token) {
      if (is_array($token) && $token[0] == T_FUNCTION) {
        $documented_hooks[] = $tokens[$index + 2][1];
      }
    }

    // Compare the declared and documented hooks. If they are out of sync, at
    // least one of the diffs will not be empty.
    $this->assertEmpty(array_diff($declared_hooks, $documented_hooks));
    $this->assertEmpty(array_diff($documented_hooks, $declared_hooks));
  }
}
