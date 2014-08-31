<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Tests;

use Drupal\payment_reference\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * @coversDefaultClass \Drupal\payment_reference\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * @covers ::queue
   */
  public function testQueue() {
    $container = new Container();
    $queue = $this->getMock('\Drupal\payment\QueueInterface');
    $container->set('payment_reference.queue', $queue);
    \Drupal::setContainer($container);
    $this->assertSame($queue, PaymentReference::queue());
  }

  /**
   * @covers ::factory
   */
  public function testFactory() {
    $container = new Container();
    $factory = $this->getMock('\Drupal\payment\FactoryInterface');
    $container->set('payment_reference.payment_factory', $factory);
    \Drupal::setContainer($container);
    $this->assertSame($factory, PaymentReference::factory());
  }

}
