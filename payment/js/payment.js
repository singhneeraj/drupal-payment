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

})(jQuery, Drupal, drupalSettings);
