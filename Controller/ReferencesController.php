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

    public function listAction() {
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->sort('NAME,YEAR_FROM');
        $data->render_table('TC_REFERENCES','ID','NAME,DESCRIPTION,YEAR_FROM,YEAR_TO');
    }

    public function formAction() {
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('form');
        header('Content-Type: text/xml');
        $data->render_table('TC_REFERENCES','ID','NAME,DESCRIPTION,COMMENT,YEAR_FROM,YEAR_TO');
    }

    public function rulesAction() {
        $request = Request::createFromGlobals();
        $id = $request->query->get( 'id' );
        
        $sql = $this->container->get('arii_core.sql');                  
        $sql->setDriver($this->container->getParameter('database_driver'));
        $qry = $sql->Select(array('tr.ID','tr.NAME','tr.RULE'))
                .$sql->From(array('TC_RULES tr'))
                .$sql->LeftJoin('TC_REFERENCES_RULES trr',array('trr.RULE_ID','tr.ID'))
                .$sql->Where(array('trr.REFERENCE_ID'=>$id))
                .$sql->OrderBy(array('tr.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->render_sql($qry,'ID','NAME,COMMENT');
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
