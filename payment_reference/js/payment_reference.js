(function($) {
  /**
   * Refresh this window's opener's payment references.
   */
  $(document).ready(function() {
    if (window.opener && window.opener.Drupal.PaymentReferenceRefreshButtons) {
      window.opener.Drupal.PaymentReferenceRefreshButtons();
    }
  });

  /**
   * Convert "close this window" messages to links.
   */
  Drupal.behaviors.PaymentReferenceWindowCloseLink = {
    attach: function(context) {
      $('span.payment_reference-window-close').each(function() {
        $(this).replaceWith('<a href="#" class="payment_reference-window-close">' + this.innerHTML + '</a>');
      });
      $('a.payment_reference-window-close').bind('click', function() {
        window.opener.focus();
        window.close();
      });
    }
  }

  /**
   * Refresh all payment references.
   */
  Drupal.PaymentReferenceRefreshButtons = function() {
    $('.payment_reference-refresh-button').each(function() {
      if (!Drupal.settings.PaymentReferencePaymentAvailable[Drupal.settings.ajax[this.id].wrapper]) {
        $(this).trigger('mousedown');
      }
    });
  }

  /**
   * Set an interval to refresh all payment references.
   */
  setInterval(Drupal.PaymentReferenceRefreshButtons, 30000);
})(jQuery);