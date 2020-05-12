WordPress Integration Bundle
============================

Open-Source Bundle to integrate WordPress in a Symfony2 Project.

Features:
* Using WordPress and Symfony in parallel
* Using layout from WordPress as a Twig - Template by annotation
* Use Symfony Controller output as WordPress Shortcodes


Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require wordpress-integration-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require startplatz/wordpress-integration-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Startplatz\Bundle\WordpressIntegrationBundle\StartplatzWordpressIntegrationBundle::class => ['all' => true],
];
```

Manual Steps
------------

enable a route that should be handled by WordPress in your routing configuration (e.g. config/routing.yaml)

    ....
    wordpress:
        resource: "@StartplatzWordpressIntegrationBundle/Controller/PassthruController.php"
        type: annotation

Note: all your other routes should be configured in advance.

For Apache 2 User:
Take the sample .htaccess file to your document root and adjust it to your needs e.g. Error Handling etc.
Note: It is better to prevent WordPress from changing this file!

For other HTTP-Server you need to setup the rules based on the Apache rules

Setup WordPress
---------------
Your WordPress Installation should be located in the `public` - folder of your Symfony2 - Project

To complete the integration you should update the globals - cache by calling the Symfony Console Command:

    app/console startplatz:wordpress-integration:build-global-names-cache

Configuration
-------------

Add file `config/startplatz_wordpress_integration.yaml` (if it is not done by flex yet)

```
startplatz_wordpress_integration:
    wordpress_root_dir: '%kernel.project_dir%/../public'
```

* **wordpress_root_dir**: Directory, where Wordpress is installed - mandatory, no default value

Implementation
--------------

### Using WordPress output as Twig - Template ###

#### Usage in Controller and Twig Template ####

defining the WordPress URL as template via annotation

    <?php

    namespace Acme\Bundle\WebsiteBundle\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\Controller;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse;

    class DefaultController extends Controller
    {
        /**
         * @Route("/",name="show_index")
         * @WordpressResponse("/url-from-wordpress/", createTwigTemplate=true)
         * @Template
         */
        public function indexAction()
        {
            return array();
        }
    }

Your Twig Template should look like:

    {% extends template_from_string(app.request.get('_wordpressTemplate')) %}

    {% block something %}
        Hello world!
    {% endblock %}

Note: The twig function `template_from_string()` is not activated by default. See the documentation http://twig.sensiolabs.org/doc/functions/template_from_string.html for this.

#### Define blocks in WordPress ####

In your WordPress page (or post) you can define as many blocks as you want. The WordPress Integration Bundle translates the wildcards like `%%SOMETHING%%` to the block definitions `{%block something %}{% endblock %}`

There are some default blocks that are integrated every time:

    {% block additionalHead %}{% endblock %}
    {% block additionalBody %}{% endblock %}
    {% block robots %}index,follow{% endblock %}
