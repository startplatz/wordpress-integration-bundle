WordPress Integration Bundle
============================

Open-Source Bundle to integrate WordPress in a Symfony2 Project.

Features:
* Using WordPress and Symfony2 in parallel
* Using layout from WordPress as a Twig - Template by annotation
* Use Symfony Controller output as WordPress Shortcodes


Setup Symfony
-------------

get the bundle by composer from packagist.org:

    "require": {
        "php": ">=5.3.3",
        "symfony/symfony": "2.3.*",
        "sensio/framework-extra-bundle": "2.3.*",
        "doctrine/dbal": "2.3.*",
        "startplatz/wordpress-integration-bundle": "dev-master"
        ...
    }


activate the bundle in your app/AppKernel.php

    ...
    new Startplatz\Bundle\WordpressIntegrationBundle\StartplatzWordpressIntegrationBundle(),
    ....



enable a route that should be handled by WordPress in your routing configuration (e.g. app/config/routing.yml)

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
Your WordPress Installation should be located in the `web` - folder of your Symfony2 - Project

To complete the integration you should update the globals - cache by calling the Symfony Console Command:

    app/console startplatz:wordpress-integration:build-global-names-cache

Configuration
-------------

app/config.yml:

    startplatz_wordpress_integration:
        table_prefix: wp_
        wordpress_root_dir: %kernel.root_dir%/../web
        wordpress_dbal_connection: doctrine.dbal.wordpress_connection

* **table_prefix**: table prefix for WordPress (default: wp_)
* **wordpress_root_dir**: Directory, where Wordpress is installed - mandatory, no default value
* **wordpress_dbal_connection**: Doctrine-Dbal-Connection-Service for WordPress database (default: doctrine.dbal.wordpress_connection, to use the standard doctrine connection use doctrine.dbal.default_connection as Service ID)


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
