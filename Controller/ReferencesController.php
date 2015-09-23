<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ReferencesController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:References:index.html.twig');
    }

    public function gridAction() {
        $sql = $this->container->get('arii_core.sql');                  
        $qry = $sql->Select(array('tr.ID','tr.NAME','tr.DESCRIPTION','tr.PARENT_ID','tz.NAME as ZONE'))
                .$sql->From(array('TC_REFERENCES tr'))
                .$sql->LeftJoin('TC_ZONES tz',array('tr.ZONE_ID','tz.ID'))
                .$sql->OrderBy(array('tr.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
//        $data->event->attach("beforeRender",array($this,"form_render"));
        $data->render_sql($qry,'ID','NAME,COMMENT');
    }

    public function treeAction() {
        $sql = $this->container->get('arii_core.sql');                  
        $qry = $sql->Select(array('tr.ID','tr.NAME','tr.DESCRIPTION','tr.PARENT_ID','tz.NAME as ZONE'))
                .$sql->From(array('TC_REFERENCES tr'))
                .$sql->LeftJoin('TC_ZONES tz',array('tr.ZONE_ID','tz.ID'))
                .$sql->OrderBy(array('tr.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('tree');
//        $data->event->attach("beforeRender",array($this,"form_render"));
        $data->render_sql($qry,'ID','NAME','COMMENT','PARENT_ID');
    }

}
