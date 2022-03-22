Doctrine Data Fixtures
======================

This extension provides a way to load arbitrary data into your database
from special PHP classes called "fixtures".
It can be useful for testing purposes, or for seeding a database with
initial data.

Features
--------

* support for ORM, and both ODMs (PHPCR, MongoDB);
* objects can be shared between fixtures;
* specifying the order in which fixtures are loaded;
* specifying dependencies between fixtures.

Installation
------------

The most likely scenario is that you are going to need this library for
test purposes::

    $ composer require --dev doctrine/data-fixtures

Getting Help
------------

* chat with us on `Slack <https://www.doctrine-project.org/slack>`_;
* ask a question on `StackOverflow <https://stackoverflow.com/questions/tagged/doctrine>`_;
* report a bug on `GitHub <https://github.com/doctrine/data-fixtures/issues>`_.
