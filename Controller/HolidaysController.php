<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class HolidaysController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:Holidays:index.html.twig');
    }

    public function listAction() {
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        $qry = $sql->Select(array('DAY','count(RULE_ID) as NB'))
                .$sql->From(array('TC_HOLIDAYS'))
                .$sql->GroupBy(array('DAY'))
                .$sql->OrderBy(array('DAY'));
        $data->render_sql($qry,'DAY','DAY,NB');
    }

}
