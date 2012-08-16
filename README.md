IRI
==============

This is a simple PHP class to ease IRI handling. Currently it just supports
parsing and relative IRI resolution. In the future I will extend it to support
validation and normalization.

The class is extensively unit tested: Tests: [![Build Status](https://secure.travis-ci.org/lanthaler/IRI.png?branch=master)](http://travis-ci.org/lanthaler/IRI)


Installation
------------

The easiest way to use IRI is to integrate it as a dependency in your project's
[composer.json](http://getcomposer.org/doc/00-intro.md) file:

    {
        "require": {
            "ml/iri": "@dev"
        }
    }

Installing is then a matter of running composer

    php composer.phar install

... and including Composer's autoloader to your project

    require('vendor/autoload.php');


Of course you can also download an [archive](https://github.com/lanthaler/IRI/downloads)
from Github.


Credits
------------

Most test cases come either directly from the [URI specification](http://tools.ietf.org/html/rfc3986),
from [Graham Klyne's](http://www.ninebynine.org/Software/HaskellUtils/Network/URITestDescriptions.html),
or [Tim Berners-Lee's](http://dig.csail.mit.edu/2005/ajar/ajaw/test/uri-test-doc.html) test suite.
