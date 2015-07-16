<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Type\PaymentTypeOperationsProviderTest.
 */

namespace Drupal\Tests\plugin\Unit;

use Drupal\payment\Plugin\Payment\Type\PaymentTypeOperationsProvider;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Type\PaymentTypeOperationsProvider
 *
 * @group Plugin
 */
class PaymentTypeOperationsProviderTest extends DefaultPluginTypeOperationsProviderTest {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Type\PaymentTypeOperationsProvider
   */
  protected $sut;

  public function setUp() {
    parent::setUp();

    $this->sut = new PaymentTypeOperationsProvider($this->stringTranslation);
  }

}
