<?php

namespace Arii\TimeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DaysController extends Controller
{
    
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
