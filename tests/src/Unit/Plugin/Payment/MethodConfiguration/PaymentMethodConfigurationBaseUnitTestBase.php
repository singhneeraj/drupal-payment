<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBaseUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Plugin\Payment\MethodConfiguration;

use Drupal\Tests\UnitTestCase;

/**
 * Provides a base for tests that cover classes that extend
 * \Drupal\idepaymental\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationBase
 */
class PaymentMethodConfigurationBaseUnitTestBase extends UnitTestCase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $moduleHandler;

  /**
   * The plugin's definition.
   *
   * @var mixed[]
   */
  protected $pluginDefinition;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->moduleHandler = $this->getMock('\Drupal\Core\Extension\ModuleHandlerInterface');

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->pluginDefinition = [
      'description' => $this->randomMachineName(),
      'label' => $this->randomMachineName(),
    ];
  }

}
