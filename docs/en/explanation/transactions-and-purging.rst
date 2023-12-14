Transactions and purging
========================

This package provides executors for ``doctrine/orm``, ``doctrine/mongodb-odm``
and ``doctrine/phpcr-odm``.

The executors purge the database, then load the fixtures. The ORM
implementation wraps these two steps in a database transaction, which
provides a nice additional property: atomicity.
Because of that transaction, the loading either succeeds or fails
cleanly, meaning nothing is actually changed in the database if the
loading fails. It delegates the purging to a separate class that can be
configured to either use a ``TRUNCATE`` or a ``DELETE`` statement to
empty tables.

Not all RDBMS have the capability to allow ``TRUNCATE`` statements
inside transactions though. Notably, MySQL will produce the infamous
"There is no active transaction" message when we attempt to close a
transaction that was already `implicitly closed`_.

.. _implicitly closed: https://www.doctrine-project.org/projects/doctrine-migrations/en/stable/explanation/implicit-commits
