# Omnipay: Gvp

**Gvp (Garanti, Denizbank, TEB, ING, Şekerbank, TFKB) gateway for Omnipay payment processing library**

[![Latest Stable Version](https://poser.pugx.org/yasinkuyu/omnipay-gvp/v/stable)](https://packagist.org/packages/yasinkuyu/omnipay-gvp) 
[![Total Downloads](https://poser.pugx.org/yasinkuyu/omnipay-gvp/downloads)](https://packagist.org/packages/yasinkuyu/omnipay-gvp) 
[![Latest Unstable Version](https://poser.pugx.org/yasinkuyu/omnipay-gvp/v/unstable)](https://packagist.org/packages/yasinkuyu/omnipay-gvp) 
[![License](https://poser.pugx.org/yasinkuyu/omnipay-gvp/license)](https://packagist.org/packages/yasinkuyu/omnipay-gvp)

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements Gvp (Turkey Payment Gateways) support for Omnipay.


Gvp (Garanti, Denizbank, TEB, ING, Şekerbank, TFKB) sanal pos hizmeti için omnipay kütüphanesi.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "yasinkuyu/omnipay-gvp": "~2.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* Gvp
    - Garanti
    - Denizbank 
    - TEB 
    - ING 
    - Şekerbank 
    - TFKB 

Gateway Methods

* authorize($options) - authorize an amount on the customer's card
* capture($options) - capture an amount you have previously authorized
* purchase($options) - authorize and immediately capture an amount on the customer's card
* refund($options) - refund an already processed transaction
* void($options) - generally can only be called up to 24 hours after submitting a transaction

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Unit Tests

PHPUnit is a programmer-oriented testing framework for PHP. It is an instance of the xUnit architecture for unit testing frameworks.

## Sample App
   

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project, or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/yasinkuyu/omnipay-gvp/issues),
or better yet, fork the library and submit a pull request.
