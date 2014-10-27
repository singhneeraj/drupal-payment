<?php

/**
 * @file
 * Contains \Drupal\payment\Event\PaymentEventsUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Event;

use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Event\PaymentEvents
 *
 * @group Payment
 */
class PaymentEventsUnitTest extends UnitTestCase {

  /**
   * Tests constants with event names.
   */
  public function testEventNames() {
    $class = new \ReflectionClass('\Drupal\payment\Event\PaymentEvents');
    foreach ($class->getConstants() as $event_name) {
      // Make sure that every event name is properly namespaced.
      $this->assertSame(0, strpos($event_name, 'drupal.payment.'));
    }
  }

}
