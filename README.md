wordpress-integration-bundle
============================

Open-Source Bundle to integrate WordPress in a Symfony Project.

Features:
* Using layout from WordPress as a Twig - Template by annotaion
* 


Setup
-----

get the bundle by composer:

    "repositories": [
        {
            "type": "git",
            "url": "git@github.com:startplatz/wordpress-integration-bundle.git"
        }
    ],


activate the bundle in your app/AppKernel.php

    ...
    new Startplatz\Bundle\WordpressIntegrationBundle\StartplatzWordpressIntegrationBundle(),
    ....



enable a route that should be handled by WordPress in your routing configuration (e.g. app/config/routing.yml)

    wordpress:
        resource: "@StartplatzWordpressIntegrationBundle/Controller/PassthruController.php"
        type: annotation
    home:
        path: /

