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
        $request = $this->getRequest();
        $id = $request->query->get( 'id' );
        $locale = $request->getLocale();
        
        $sql = $this->container->get('arii_core.sql');                  
        $sql->setDriver($this->container->getParameter('database_driver'));
        $qry = $sql->Select(array('tr.ID','tt.NAME','tt.DESCRIPTION','tr.RULE'))
                .$sql->From(array('TC_REFERENCES_RULES trr'))
                .$sql->LeftJoin('TC_RULES tr',array('trr.RULE_ID','tr.ID'))
                .$sql->LeftJoin('TC_TRANSLATIONS tt',array('tt.ID_TABLE','tr.ID'))
                .$sql->Where(array('trr.REFERENCE_ID'=>$id,'tt.TABLE' => 'RULES', 'tt.LOCALE'=>$locale))
                .$sql->OrderBy(array('tr.NAME'));
/*
print $qry;
print "<hr/>";
print exit();
 */
        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('grid');
        $data->event->attach("beforeRender",array($this,"rules_render"));
        $data->render_sql($qry,'ID','NAME,DESCRIPTION,RULE');
    }

    function rules_render($data) {
        $data->set_value("ID","R".$data->get_value("ID"));
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

    public function add_ruleAction() {
        $request = Request::createFromGlobals();
        $ref_id = $request->query->get( 'ref_id' );
        $rule_id = $request->query->get( 'rule_id' );

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('data');
        
        $data->sql->query("INSERT INTO TC_REFERENCES_RULES (REFERENCE_ID,RULE_ID) VALUES($ref_id, $rule_id)");
        $id = $data->sql->get_new_id();
        print $this->get('translator')->trans('Rule added')." [$id]";
        exit();
    }

    public function del_ruleAction() {
        $request = Request::createFromGlobals();
        $ref_id = $request->query->get( 'ref_id' );
        $rule_id = $request->query->get( 'rule_id' );

        $db = $this->container->get('arii_core.db');
        $data = $db->Connector('data');
        
        $data->sql->query("DELETE FROM TC_REFERENCES_RULES WHERE REFERENCE_ID=$ref_id and RULE_ID=$rule_id");
        $id = $data->sql->get_new_id();
        print $this->get('translator')->trans('Rule suppressed');
        exit();
    }

}
