(function ($, Drupal, drupalSettings) {
  /**
   * Refreshes this window's opener's payment references.
   */
  $(document).ready(function() {
    if (window.opener && window.opener.Drupal.PaymentReferenceRefreshButtons) {
      window.opener.Drupal.PaymentReferenceRefreshButtons();
    }
  });

  /**
   * Converts "close this window" messages to links.
   */
  Drupal.behaviors.PaymentReferenceWindowCloseLink = {
    attach: function(context) {
      if (window.opener) {
        $('span.payment_reference-window-close').each(function() {
          $(this).replaceWith('<a href="#" class="payment_reference-window-close">' + this.innerHTML + '</a>');
        });
        $('a.payment_reference-window-close').bind('click', function() {
          window.opener.focus();
          window.close();
        });
      }
    }
  }

  /**
   * Binds a listener on dialog creation to handle the payment completion link.
   */
  $(window).on('dialog:aftercreate', function (e, dialog, $element, settings) {
    $element.on('click.dialog', '.payment_reference-complete-payment-link', function (e) {
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
