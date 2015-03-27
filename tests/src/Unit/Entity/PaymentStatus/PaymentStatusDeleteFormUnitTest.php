<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\PaymentStatus\PaymentStatusDeleteFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\PaymentStatus {

  use Drupal\Core\Url;
  use Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm
   *
   * @group Payment
   */
  class PaymentStatusDeleteFormUnitTest extends UnitTestCase {

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * The payment status.
     *
     * @var \Drupal\payment\Entity\PaymentStatusInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $paymentStatus;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The form under test.
     *
     * @var \Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm
     */
    protected $form;

    /**
     * {@inheritdoc}
     */
    public function setUp() {
      $this->logger = $this->getMock('\Psr\Log\LoggerInterface');

      $this->paymentStatus = $this->getMockBuilder('\Drupal\payment\Entity\PaymentStatus')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $this->form = new PaymentStatusDeleteForm($this->stringTranslation, $this->logger);
      $this->form->setEntity($this->paymentStatus);
    }

    /**
     * @covers ::create
     * @covers ::__construct
     */
    function testCreate() {
      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = [
        ['payment.logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger],
        ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ];
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $form = PaymentStatusDeleteForm::create($container);
      $this->assertInstanceOf('\Drupal\payment\Entity\PaymentStatus\PaymentStatusDeleteForm', $form);
    }

    /**
     * @covers ::getQuestion
     */
    function testGetQuestion() {
      $label = $this->randomMachineName();
      $string = 'Do you really want to delete %label?';

      $this->paymentStatus->expects($this->once())
        ->method('label')
        ->will($this->returnValue($label));

      $this->stringTranslation->expects($this->once())
        ->method('translate')
        ->with($string, array(
          '%label' => $label,
        ));

      $this->assertSame($string, $this->form->getQuestion());
    }

    /**
     * @covers ::getConfirmText
     */
    function testGetConfirmText() {
      $string = 'Delete';

      $this->stringTranslation->expects($this->once())
        ->method('translate')
        ->with($string);

      $this->assertSame($string, $this->form->getConfirmText());
    }

    /**
     * @covers ::getCancelUrl
     */
    function testGetCancelUrl() {
      $url = new Url($this->randomMachineName());

      $this->paymentStatus->expects($this->atLeastOnce())
        ->method('urlInfo')
        ->with('collection')
        ->willReturn($url);

      $cancel_url = $this->form->getCancelUrl();
      $this->assertSame($url, $cancel_url);
    }

    /**
     * @covers ::submitForm
     */
    function testSubmitForm() {
      $this->logger->expects($this->atLeastOnce())
        ->method('info');

      $url = new Url($this->randomMachineName());

      $this->paymentStatus->expects($this->once())
        ->method('delete');
      $this->paymentStatus->expects($this->atLeastOnce())
        ->method('urlInfo')
        ->with('collection')
        ->willReturn($url);

      $form = [];
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirectUrl')
        ->with($url);

      $this->form->submitForm($form, $form_state);
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
