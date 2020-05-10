<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse;

class PassthruController extends AbstractController
{

    /**
     * @Route("/{path}", name="startplatz_wordpress_passthru", requirements={"path"=".*"})
     * @WordpressResponse
     */
    public function passthruAction(Request $request)
    {
        return $request->attributes->get('_wordpressResponse');
    }

}