<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class RulesController extends Controller
{
    public function indexAction()
    {
        return $this->render('AriiTimeBundle:Rules:index.html.twig');
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
                'tt.NAME','tt.DESCRIPTION','tt.COMMENT','tz.UPDATED'))
                .$sql->From(array('TC_RULES tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tz.ID' => $id, 'tt.TABLE' => 'RULES', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tz.NAME','tz.ID'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('form');
        $data->render_sql($qry,'tz.ID','ID,NAME,RULE,DESCRIPTION,UPDATED');
    }

    public function gridAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        
        $qry = $sql->Select(array('tz.ID','tz.RULE',
                'tt.NAME','tt.DESCRIPTION','tt.COMMENT'))
                .$sql->From(array('TC_RULES tz'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tz.ID'))
                .$sql->Where(array('tt.TABLE' => 'RULES', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tz.NAME'));

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->render_sql($qry,'ID','NAME,DESCRIPTION,RULE');
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
    
    public function testAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $rule =$request->query->get( 'rule' );

        // Appel du service timecode
        $tc = $this->container->get('arii_time.timecode');
        $year = 2000;
        $grid = "<?xml version='1.0' encoding='utf-8' ?>\n<rows>\n";
        for($i=0;$i<=30;$i++) {
            $date = $tc->Date2ISO($tc->Reference($rule,$year+$i));
            $grid .= "<row><cell>$date</cell></row>\n";            
        }
        $grid .= "</rows>";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent( $grid );
        return $response;
        
        print_r($tc->Reference($rule));
        exit();
        foreach ($tc->Calendar($rule) as $day=>$status) {
            $y = substr($day,0,4);
            if ($y!=$year) continue;
            if ($status=='o') continue;            
            $date = $tc->Date2ISO($tc->Date2ISO($day));
            $grid .= "<row><cell>$date</cell><cell>$status</cell></row>\n";
        }
        $grid .= "</rows>";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent( $grid );
        return $response;
    }

    public function saveAction() {
        $request = $this->getRequest();
        $id =$request->request->get( 'ID' );
        $rule =$request->request->get( 'RULE' );
        $name =$request->request->get( 'NAME' );

/* pour debug        
        $id =$request->query->get( 'ID' );
        $name =$request->query->get( 'NAME' );
        $rule =$request->query->get( 'RULE' );
*/
        
        // connexion base de données
        $em = $this->getDoctrine()->getManager();
        
        $rules = new \Arii\TimeBundle\Entity\Rules();
        if ($id!="") {
            $rules = $em->getRepository("AriiTimeBundle:Rules")->find($id);
            
            // Suppression des dates deja calculées
            $days = $em->getRepository("AriiTimeBundle:Holidays")->findBy(array('rule'=>$id));
            foreach ($days as $d) {
                $em->remove($d);
                $em->flush();
            }
        }
        
        $rules->setName($name);
        $rules->setRule($rule);
        $rules->setUpdated(new \DateTime());
        
        $em->persist($rules);
        
        // On rajoute les dates
        // Appel du service timecode
        $tc = $this->container->get('arii_time.timecode');
        $year = 2000;
        for($i=0;$i<50;$i++) {
            $date = $tc->Date2ISO($tc->Reference($rule,$year+$i));
            $days = new \Arii\TimeBundle\Entity\Holidays();
            $days->setRule($rules);
            $days->setDay(new \DateTime($date));
            $em->persist($days);
        }
        
        $em->flush();
        
        print $this->get('translator')->trans('Rule saved');
        exit();
    }
    
    public function scheduleAction() {
        $sql = $this->container->get('arii_core.sql'); 
        $sql->setDriver($this->container->getParameter('database_driver'));
        
        $request = $this->getRequest();
        $locale = $request->getLocale();
        $rule =$request->query->get( 'rule' );

        // Appel du service timecode
        $tc = $this->container->get('arii_time.timecode');
        $year = 2010;
        $grid = "<?xml version='1.0' encoding='utf-8' ?>\n<data>\n";
        for($i=0;$i<10;$i++) {
            $grid .= "<event>";
            $date = $tc->Date2ISO($tc->Reference($rule,$year+$i));
            $grid .= "<text>$rule</text><start_date>$date</start_date><end_date>$date</end_date><color>red</color>\n";            
            $grid .= "</event>";
        }
        $grid .= "</data>";
        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml');
        $response->setContent( $grid );
        return $response;
    }

}
