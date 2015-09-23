<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ZonesController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:Zones:index.html.twig');
    }

    public function gridAction() {
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->render_table('TC_ZONES','ID','CODE,NAME,COMMENT,ISO,TYPE_ID,LATITUDE,LONGITUDE');
    }

    public function formAction() {
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('form');
        $data->render_table('TC_ZONES','ID','CODE,NAME,COMMENT,ISO,TYPE_ID,LATITUDE,LONGITUDE');
    }

    public function treeAction() {
        $sql = $this->container->get('arii_core.sql');                  
        $qry = $sql->Select(array('ID','NAME','COMMENT','PARENT_ID'))
                .$sql->From(array('TC_ZONES'))
                .$sql->OrderBy(array('NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('tree');
//        $data->event->attach("beforeRender",array($this,"form_render"));
        $data->render_table('TC_ZONES','ID','NAME','COMMENT','PARENT_ID');
    }
}
