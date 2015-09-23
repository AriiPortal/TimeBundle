<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CalendarsController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:References:index.html.twig');
    }


}
