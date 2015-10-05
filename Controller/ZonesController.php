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
        $sql = $this->container->get('arii_core.sql');                  
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
       
        $qry = $sql->Select(array('tz.ID','tt.NAME','tt.DESCRIPTION','tz.CODE','tz.PARENT_ID','tz.ISO','tz.TYPE_ID','tz.LATITUDE','tz.LONGITUDE'))
                .$sql->From(array('TC_ZONES tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tt.TABLE'=>'ZONES', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tt.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->render_sql($qry,'ID','CODE,NAME,COMMENT,ISO,TYPE_ID,LATITUDE,LONGITUDE');
    }

    public function formAction() {
        $sql = $this->container->get('arii_core.sql');                  
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $id = $request->query->get( 'id' );
        
        $qry = $sql->Select(array('tz.ID','tt.NAME','tt.DESCRIPTION','tt.COMMENT','tz.CODE','tz.PARENT_ID','tz.ISO','tz.TYPE_ID','tz.LATITUDE','tz.LONGITUDE'))
                .$sql->From(array('TC_ZONES tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tt.TABLE'=>'ZONES', 'tt.LOCALE'=>$locale, 'tz.ID'=>$id))
                .$sql->OrderBy(array('tt.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('form');
        $data->render_sql($qry,'tz.ID','CODE,NAME,DESCRIPTION,COMMENT,ISO,TYPE_ID,LATITUDE,LONGITUDE');
    }
    
    public function treeAction() {
        $sql = $this->container->get('arii_core.sql');                  
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
       
        $qry = $sql->Select(array('tz.ID','tt.NAME','tz.CODE','tz.PARENT_ID'))
                .$sql->From(array('TC_ZONES tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tt.TABLE'=>'ZONES', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tt.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('tree');
//        $data->event->attach("beforeRender",array($this,"form_render"));
        header('Content-type: text/xml;');
        $data->render_sql($qry,'ID','NAME','CODE','PARENT_ID');
    }
}
