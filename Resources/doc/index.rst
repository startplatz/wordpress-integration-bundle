============

Make sure Composer is installed globally, as explained in the
`installation chapter`_ of the Composer documentation.

----------------------------------

Open a command console, enter your project directory and execute:

.. code-block:: bash

    $ composer require wordpress-integration-bundle

Applications that don't use Symfony Flex
----------------------------------------

Step 1: Download the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~~~

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: terminal

    $ composer require startplatz/wordpress-integration-bundle

Step 2: Enable the Bundle
~~~~~~~~~~~~~~~~~~~~~~~~~

Then, enable the bundle by adding it to the list of registered bundles
in the ``config/bundles.php`` file of your project::

    // config/bundles.php
    return [
        // ...
        <vendor>\<bundle-name>\<bundle-long-name>::class => ['all' => true],
    ];

.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md