<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Wordpress;

interface ShortCode {

    public function execute($attributes, $content = null);

}