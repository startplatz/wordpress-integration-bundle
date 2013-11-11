wordpress-integration-bundle
============================

Open-Source Bundle to integrate WordPress in a Symfony Project.

Features:
* Using layout from WordPress as a Twig - Template by annotaion
* 


Setup
-----

get the bundle by composer:

`<code>`
"repositories": [
    {
        "type": "git",
        "url": "git@github.com:startplatz/wordpress-integration-bundle.git"
    }
],
`<code>`

activate the bundle in your app/AppKernel.php
`<code>`
...
new Startplatz\Bundle\WordpressIntegrationBundle\StartplatzWordpressIntegrationBundle(),
....
`<code>`

enable a rout that should be handled by WordPress in your routing configuration (e.g. app/config/routing.yml)

`<code>`
wordpress:
    resource: "@StartplatzWordpressIntegrationBundle/Controller/PassthruController.php"
    type: annotation

home:
    path: /
`<code>`
