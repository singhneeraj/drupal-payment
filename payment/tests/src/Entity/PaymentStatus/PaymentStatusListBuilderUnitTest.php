<?php

/**
 * @file
 * Contains \Drupal\payment\Tests\Entity\PaymentStatus\PaymentStatusListBuilderUnitTest.
 */

namespace Drupal\payment\Tests\Entity\PaymentStatus;

use Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder
 */
class PaymentStatusListBuilderUnitTest extends UnitTestCase {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityStorage;

  /**
   * The entity type.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * The list builder under test.
   *
   * @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder
   */
  protected $listBuilder;

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'name' => '\Drupal\payment\Entity\PaymentStatus\PaymentStatusListBuilder unit test',
      'group' => 'Payment',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->entityStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->entityType = $this->getMock('\Drupal\Core\Entity\EntityTypeInterface');

    $this->listBuilder = new PaymentStatusListBuilder($this->entityType, $this->entityStorage);
  }

  /**
   * @covers ::render
   *
   * @expectedException \Exception
   */
  function testRender() {
    $this->listBuilder->render();
  }

}
