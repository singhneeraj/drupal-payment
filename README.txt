ABOUT
-----

Allows users to set up payment methods and connects them to other modules that
allow their users to make payments.

In addition to this README file, also see the online handbook at
http://drupal.org/node/1807610.

- Payment is an API to connect payment methods to modules that allow users to
  make payments. This means that it doesn't do anything by itself, apart from
  providing other modules with the ability to make payments.
- Payment Form Field contains a field that displays a payment form when viewing
  the entity it is attached to.
- Payment Reference Field contains a field that allow users to make a payment
  while adding a new entity.


GENERAL CONFIGURATION
---------------------
To get started, you need to create at least one payment method. In order to do
that, you need to enable at least one module that provides payment method
plugins. Any other module that works with Payment and requires payments to be
made, such as a web shop, can then use this payment method automatically.
