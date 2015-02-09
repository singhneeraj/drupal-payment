<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Entity\Payment\PaymentDeleteFormUnitTest.
 */

namespace Drupal\Tests\payment\Unit\Entity\Payment {

  use Drupal\Core\Url;
  use Drupal\payment\Entity\Payment\PaymentDeleteForm;
  use Drupal\Tests\UnitTestCase;
  use Symfony\Component\DependencyInjection\ContainerInterface;

  /**
   * @coversDefaultClass \Drupal\payment\Entity\Payment\PaymentDeleteForm
   *
   * @group Payment
   */
  class PaymentDeleteFormUnitTest extends UnitTestCase {

    /**
     * The entity manager.
     *
     * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * The logger.
     *
     * @var \Psr\Log\LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $logger;

    /**
     * The payment.
     *
     * @var \Drupal\payment\Entity\PaymentInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $payment;

    /**
     * The string translation service.
     *
     * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringTranslation;

    /**
     * The form under test.
     *
     * @var \Drupal\payment\Entity\Payment\PaymentDeleteForm
     */
    protected $form;

    /**
     * {@inheritdoc}
     *
     * @covers ::__construct
     */
    public function setUp() {
      $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');

      $this->logger = $this->getMock('\Psr\Log\LoggerInterface');

      $this->payment = $this->getMockBuilder('\Drupal\payment\Entity\Payment')
        ->disableOriginalConstructor()
        ->getMock();

      $this->stringTranslation = $this->getMock('\Drupal\Core\StringTranslation\TranslationInterface');
      $this->stringTranslation->expects($this->any())
        ->method('translate')
        ->will($this->returnArgument(0));

      $this->form = new PaymentDeleteForm($this->entityManager, $this->stringTranslation, $this->logger);
      $this->form->setEntity($this->payment);
    }

    /**
     * @covers ::create
     */
    function testCreate() {
      $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
      $map = [
        ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
        ['payment.logger', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->logger],
        ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
      ];
      $container->expects($this->any())
        ->method('get')
        ->willReturnMap($map);

      $form = PaymentDeleteForm::create($container);
      $this->assertInstanceOf('\Drupal\payment\Entity\Payment\PaymentDeleteForm', $form);
    }

    /**
     * @covers ::getQuestion
     */
    function testGetQuestion() {
      $id = mt_rand();
      $string = 'Do you really want to delete payment #!payment_id?';

      $this->payment->expects($this->once())
        ->method('id')
        ->will($this->returnValue($id));

      $this->stringTranslation->expects($this->once())
        ->method('translate')
        ->with($string, array(
          '!payment_id' => $id,
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

      $this->payment->expects($this->atLeastOnce())
        ->method('urlInfo')
        ->with('canonical')
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

      $this->payment->expects($this->once())
        ->method('delete');

      $form = [];
      $form_state = $this->getMock('\Drupal\Core\Form\FormStateInterface');
      $form_state->expects($this->once())
        ->method('setRedirect')
        ->with('<front>');

      $this->form->submitForm($form, $form_state);
    }

  }

}

namespace {

if (!function_exists('drupal_set_message')) {
  function drupal_set_message() {}
}

}
