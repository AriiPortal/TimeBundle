<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class CalendarsController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:Calendars:index.html.twig');
    }

    public function toolbarAction()
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        return $this->render('AriiTimeBundle:Rules:grid_toolbar.xml.twig',array(), $response );
    }
    
    public function formAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $id = $request->query->get( 'id' );
        
        $qry = $sql->Select(array('tz.ID','tz.RULE',
                'tt.NAME','tt.DESCRIPTION','tt.COMMENT'))
                .$sql->From(array('TC_CALENDARS tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tz.ID' => $id, 'tt.TABLE' => 'CALENDARS', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tz.NAME','tz.ID'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('form');
        $data->render_sql($qry,'tz.ID','NAME,RULE,DESCRIPTION');
    }

    public function gridAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        
        $qry = $sql->Select(array('tz.ID','tz.RULE',
                'tt.NAME','tt.DESCRIPTION','tt.COMMENT'))
                .$sql->From(array('TC_CALENDARS tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tt.TABLE' => 'CALENDARS', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tz.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->render_sql($qry,'ID','NAME,DESCRIPTION,RULE');
    }
    
    public function testAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $rule =$request->query->get( 'rule' );

        // Appel du service timecode
        $tc = $this->container->get('arii_time.timecode');
        $year = '2015';
        $grid = "<?xml version='1.0' encoding='utf-8' ?>\n<rows>\n";
        foreach ($tc->Calendar($rule,$year) as $day=>$status) {
            $y = substr($day,0,4);
            if ($y!=$year) continue;
            if ($status=='o') continue;            
            $date = $tc->Date2ISO($day);
            $grid .= "<row><cell>$date</cell><cell>$status</cell></row>\n";
        }
        $grid .= "</rows>";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent( $grid );
        return $response;
    }

    public function scheduleAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $rule =$request->query->get( 'rule' );

        // Appel du service timecode
        $tc = $this->container->get('arii_time.timecode');
        $year = '2015';
        $grid = "<?xml version='1.0' encoding='utf-8' ?>\n<data>\n";
        foreach ($tc->Calendar($rule,$year) as $day=>$status) {
            $y = substr($day,0,4);
            $date = $tc->Date2ISO($day);
            $grid .= "<event>";
            $grid .= "<text>$date [$status]</text><start_date>$date</start_date><end_date>$date</end_date><color>$status</color>\n";
            $grid .= "</event>";
        }
        $grid .= "</data>";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent( $grid );
        return $response;
    }


}
