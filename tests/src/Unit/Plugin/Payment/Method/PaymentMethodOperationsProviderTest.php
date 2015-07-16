<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\Method\PaymentMethodOperationsProviderTest.
 */

namespace Drupal\Tests\plugin\Unit;

use Drupal\payment\Plugin\Payment\Method\PaymentMethodOperationsProvider;

/**
 * @coversDefaultClass \Drupal\payment\Plugin\Payment\Method\PaymentMethodOperationsProvider
 *
 * @group Plugin
 */
class PaymentMethodOperationsProviderTest extends DefaultPluginTypeOperationsProviderTest {

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Plugin\Payment\Method\PaymentMethodOperationsProvider
   */
  protected $sut;

  public function setUp() {
    parent::setUp();

    $this->sut = new PaymentMethodOperationsProvider($this->stringTranslation);
  }

}
