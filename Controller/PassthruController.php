<?php

namespace Startplatz\Bundle\WordpressIntegrationBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Startplatz\Bundle\WordpressIntegrationBundle\Annotation\WordpressResponse;

class PassthruController extends Controller
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