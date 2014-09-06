<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\PaymentLineItemsInputUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Element;

use Drupal\payment\Element\PaymentLineItemsInput;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Element\PaymentLineItemsInput
 *
 * @group Payment
 */
class PaymentLineItemsInputUnitTest extends UnitTestCase {

  /**
   * The element under test.
   *
   * @var \Drupal\payment\Element\PaymentLineItemsInput
   */
  protected $element;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $configuration = array();
    $plugin_id = $this->randomMachineName();
    $plugin_definition = array();
    $this->element = new PaymentLineItemsInput($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * @covers ::getInfo
   */
  public function testGetInfo() {
    $info = $this->element->getInfo();
    $this->assertInternalType('array', $info);
    foreach ($info['#process'] as $callback) {
      $this->assertTrue(is_callable($callback));
    }
  }

}
