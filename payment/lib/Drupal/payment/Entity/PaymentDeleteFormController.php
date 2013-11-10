<?php

/**
 * @file
 * Contains Drupal\payment\Entity\PaymentDeleteFormController.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;

/**
 * Provides the payment deletion form.
 */
class PaymentDeleteFormController extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Do you really want to delete %label?', array(
      '%label' => $this->getEntity()->label(),
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return array(
      'route_name' => 'payment.payment.view',
      'route_parameters' => array(
        'payment' => $this->getEntity()->id(),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    $this->getEntity()->delete();
    drupal_set_message(t('%label has been deleted.', array(
      '%label' => $this->getEntity()->label(),
    )));
    $form_state['redirect'] = '<front>';
  }
}
