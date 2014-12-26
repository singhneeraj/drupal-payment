(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.paymentMethodConfigurationBasic = {
    attach: function (context) {
      var $context = $(context);
      var $details = $context.find('details[id$="-execute"]');
      $details.drupalSetSummary(function (context) {
        var payment_status_id = $details.find('select[id$="-execute-execute-status-id"]').val();
        return Drupal.t('Sets payments to %status.', {
          '%status' : drupalSettings.payment.payment_method_configuration_basic[payment_status_id]
        });
      });
      var i;
      var operations = ['capture', 'refund'];
      for (i in operations) {
        var $details = $context.find('details[id$="-' + operations[i] + '"]');
        $details.drupalSetSummary(function (details) {
          var $details = $(details);
          if ($details.find('input[type=checkbox]').is(":checked")) {
            var payment_status_id = $details.find('select[id$="-status-id"]').val();
            return Drupal.t('Sets payments to %status.', {
              '%status' : drupalSettings.payment.payment_method_configuration_basic[payment_status_id]
            });
          }
          else {
            return Drupal.t('Disabled.');
          }
        });
      }
    }
  };

  /**
   * Binds a listener on dialog creation to handle the payment completion link.
   */
  $(window).on('dialog:aftercreate', function (e, dialog, $element, settings) {
    $element.on('click.dialog', '.payment-reference-complete-payment-link', function (e) {
      dialog.close('complete-payment');
    });
  });

  /**
   * Refreshes all payment references.
   */
  Drupal.PaymentReferenceRefreshButtons = function() {
    $('.payment_reference-refresh-button').each(function() {
      if (!drupalSettings.PaymentReferencePaymentAvailable[drupalSettings.ajax[this.id].wrapper]) {
        $(this).trigger('mousedown');
      }
    });
  }

  /**
   * Sets an interval to refresh all payment references.
   */
  setInterval(Drupal.PaymentReferenceRefreshButtons, 30000);

})(jQuery, Drupal, drupalSettings);
