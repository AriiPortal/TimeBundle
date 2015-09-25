<?php
// src/Arii/TimeBundle/Service/AriiTimecode.php
/*
 * Recupere les données et fournit un tableau pour les composants DHTMLx
 */ 
namespace Arii\TimeBundle\Service;

class AriiTimecode
{
    public function __construct () {
    }

    // Les dates sont au format YYYYMMDDhhmmss
    // Les calculs se font en JULIEN
    public function Calendar($rule,$year='',$years=1,$we='67',$type='woc') {

        if ($year=='') {
            $Now = getdate();
            $year = $Now['year'];
        }

        # On indique les jours de planification
        # si le bloc contient des dates
        $Data = array();
        if (count($Data)>0) {
            // $Infos = $this->_GetArray($Args,$Data);
            $Infos = array();
            foreach ($Infos as $i) {
                    $d = $i['date'];
                    if (isset($i['type']))
                            $InitDay[$d]=$i['type'];
                    else 
                            $InitDay[$d]='o';
            }
        }
        # Sinon on cree l'ann�e 
        else {
            $sy = $year;
            $nb = $years;
            $first = GregoriantoJd(1,1,$sy-1);
            $end = GregoriantoJd(12,31,$sy+$nb);
            for($i=$first;$i<=$end;$i++) {
                $Date = cal_from_jd($i,CAL_GREGORIAN);
                $date = $this->TCYMD2Date($Date['year'],$Date['month'],$Date['day'],0,0,0);
                $InitDay[$date]='o';
            }
        }
        
        # On decoupe par operations booleennes
        # Par defaut c'est un ajout sur un tableau vide
        $Last = array(); $bool = '+=';
        do {
            $Day = $InitDay;

            $nextrule = "";
            $nextbool = "";
            if (preg_match('/([\+\-\.\=\!]\=)/',$rule,$Match)) {
                $nextbool = $Match[1];
                $ope = strpos($rule,$nextbool);
                if ($ope>0) {
                    $nextrule = substr($rule,$ope+2);
                    $rule = substr($rule,0,$ope);
                }
            }

            # On commence par g�rer la base calendaire
            if (preg_match('/^(\!*[a-zA-Z0-9]+)/',$rule,$Match)) {
                $base = $Match[1];
                # On supprime la base de la regle 
                $rule = substr($rule,strlen($base));

                # Precise t on des week-ends ?
                if (preg_match('/([0-9]+)/',$base,$Match)) {
                    $we = $Match[1];
                    $base = str_replace($we,'',$base);
                }

                $ClosedDays = $this->TCGetList($base);
                foreach ($ClosedDays as $i) {
                    if (isset($i['date'])) {
                        $d = $i['date'];
                        $Day[$d]='c';
                    }
                }
            }

            # On met en place les week-end
            foreach (array_keys($Day) as $d) {
                $j = $this->TCDayOfWeek($d);
                if (strpos(" $we","$j")>0) {
                    $Day[$d]='w';
                }
            }

            $Base = $Day;
            # Supression des simplifications
            $rule = preg_replace('/h(\d*)m(\d*)/','h\\1_m\\2D',$rule);
            $rule = preg_replace('/([\+\-\=\_])(h)(\d*)/','\\1\\2D\\3',$rule);
            $rule = preg_replace('/([\+\-\=\_])(m)(\d*)/','\\1\\2h\\3',$rule);
            $rule = str_replace('~~','#',$rule);

            # Cas des index seuls, un index est obligatoire dans une periode
            $rule = preg_replace('/([\+\-\=\_\#])([BD])(\d+)/','\\1\\2M\\3',$rule);

            # Tant que ca ressemble a une regle
            $iter=0;            
            while ((strlen($rule)>0 && preg_match('/^(\!*)([\+\-\=\_\#]{0,1})(\d*[\~\,\!\d]*)([@BDMYhms]{0,1})([\d!]*)([A-Za-z]*)([\d\~\,\!]*)/',$rule,$Match) && ($iter<10))) {
                $iter++;
                array_shift($Match);

                list($reverse,$sens,$times,$unit,$group,$period,$index) = $Match;
                $todo = implode("",$Match);

                $this->SysLog(
                    array(	"Module"	=> basename(__FILE__),
                            "Line"	=>	__LINE__,
                            "Type"	=>	'V',
                            "sens" => $sens,
                            "times" => $times,
                            "unit" => $unit,
                            "group" => $group,
                            "period" => $period,
                            "index" => $index )
                    );

                if ($group=='')
                        $group = 1;

                # Cas de l'inclusion
                if ($unit=='@') {
                    $Daylist = $this->TCGetList($period);

                    # On cree un tableau des jours ouvres
                    $Open = array();
                    foreach ($Daylist as $i) {
                        $d = $i['date'];
                        array_push($Open,$d);
                    }

                    # 2 possiblit�s : ajout ou l'insertion dans les dates courantes
                    if ($sens=='+') {
                        foreach (array_keys($Day) as $d) {
                            if (in_array($d,$Open))
                                $Day[$d] = 'o';
                            else
                                if ($Day[$d]=='o')
                                        $Day[$d] = '';
                        }
                    }
                    else {
                        foreach (array_keys($Day) as $d) {
                            if (in_array($d,$Open)) {
                                if ($Day[$d]=='o')
                                        $Day[$d] = 'o';
                            }
                            else {
                                if ($Day[$d]=='o')
                                        $Day[$d]='';
                            }
                        }
                    }
                }
                else {
                    # PERIODE
                    switch ($period) {
                        case 'D':
                            switch ($unit) {
                                case 'h':
                                    $Index = $this->TCIndex($index,0,24);
                                    foreach (array_keys($Day) as $d) {
                                        if ($Day[$d]=='o') {
                                            $Day[$d]='';
                                            foreach($Index as $h) {
                                                if ($h==24)
                                                    $h=0;
                                                $d = $this->TCSetHour($d,$h);
                                                $Day[$d]='o';
                                            }
                                        }						
                                    }
                                break;					
                            }
                            break;
                        case 'h':
                            switch ($unit) {
                                case 'm':
                                    $Index = $this->TCIndex($index,0,60);
                                    foreach (array_keys($Day) as $d) {
                                        if ($Day[$d]=='o') {
                                            $Day[$d]='';
                                            foreach($Index as $min) {
                                                $d = $this->TCSetMinutes($d,$min);
                                                $Day[$d]='o';
                                            }
                                        }						
                                    }
                                break;					
                            }
                            break;
                        case 'W':
                            # L'index est sur 7 jours
                            $Index = $this->TCIndex($index,1,7);
                            switch ($unit) {
                                case 'D':
                                        switch ($sens) {
                                            case '_':
                                                foreach (array_keys($Day) as $d) {
                                                    $j = $this->TCDayOfWeek($d);
                                                        if (!in_array($j,$Index)) {
                                                            if ($Base[$d]=='o')
                                                                    $Day[$d]='';
                                                            else 
                                                                    $Day[$d]=$Base[$d];
                                                        }
                                                    }
                                                break;
                                                case '+':
                                                /* en fonction de la base
                                                        foreach (array_keys($Base) as $d) {
                                                                $j = $this->TCDayOfWeek($d);
                                                                if (in_array($j,$Index)) {
                                                                        $Day[$d]='o';
                                                                }
                                                                else {
                                                                        if ($Base[$d]=='o')
                                                                                $Day[$d]='';
                                                                        else 
                                                                                $Day[$d]=$Base[$d];
                                                                }
                                                        }
                                                */
                                                /* +: on decale au prochain jour de la semaine */
                                                        foreach (array_keys($Day) as $d) {
                                                                if ($Day[$d]=='o') {
                                                                        // on se decale vers le prochain
                                                                        if ($Base[$d]=='o')
                                                                                $Day[$d]='';
                                                                        else 
                                                                                $Day[$d]=$Base[$d];
                                                                        // on se decale vers le prochain
                                                                        while (!in_array($this->TCDayOfWeek($d),$Index)) {
                                                                                $d = $this->TCNextDay($d);
                                                                        }
                                                                        $Day[$d]='o';
                                                                }
                                                        }
                                                        break;
                                        }
                                        break;
                                    case 'B':
                                            # On cree une liste de semaine
                                            $i = 0; $last=-1;
                                            # on peut traiter les differentes dates en comparant l'index
                                            # on doit comparer mois par mois
                                            # Attention, il faudra certainement voir le + et le _
                                            $Weeks = array();
                                            foreach (array_keys($Day) as $d) {
                                                    # On ne traite que les jours ouvr�s
                                                    if ($Day[$d]=='o') {
                                                            # Jour de la semaine
                                                            $j = $this->TCWeekOfYear($d);

                                                            # Si le mois est diff�rent du dernier, on reinitialise
                                                            if ($j!=$last) {
                                                                    $last = $j;
                                                                    $Day = $this->TCProcess($index,$Day,$Weeks);

                                                                    # On supprime la semaine pour traiter la suivante
                                                                    $Weeks = array();
                                                            }
                                                            array_push($Weeks,$d);
                                                    }
                                            }
                                            $Day = $this->TCProcess($index,$Day,$Weeks);
                                            break;
                            }
                            break;

                        case 'M':
                                switch ($unit) {
                                        case 'B':
                                                # En fonction du sens
                                                switch ($sens) {
                                                        case '#':
                                                                # On part des derni�res dates selectionnes et on tire jusqu'au nouvelles dates
                                                                $End = $this->TCBusinessList($Base,$index);
                                                                # On ne prend que les jours selectionn�s
                                                                $Ends = array();
                                                                foreach (array_keys($End) as $d) {
                                                                        if ($End[$d]=='o') {
                                                                                array_push($Ends,$d);
                                                                        }
                                                                }
                                                                # A ce niveau on est cens� avoir 2 listes avec le meme nombre d'elements
                                                                # Attention, on a decalage avec un FR+BM99#BM5, il faut donc recaler les 2 listes
                                                                $i = 0;
                                                                list($first) = array_keys($Day);
                                                                if ($first>$Ends[0])
                                                                        $i++;
                                                                $New = array();
                                                                foreach (array_keys($Day) as $d) {
                                                                        if ($Day[$d]=='o') {
                                                                                $i++;
                                                                                # On g�re la liste des jours entre 2 dates
                                                                                if (isset($Ends[$i])) {
                                                                                        list($sy,$sm,$sd) = $this->TCDate2YMD($d);
                                                                                        list($ey,$em,$ed) = $this->TCDate2YMD($Ends[$i]);
                                                                                        $New = array_merge($New,GHTCFill('0',$sy,$ey,$sm,$em,$sd,$ed));
                                                                                }
                                                                        }
                                                                }
                                                                foreach ($New as $d) {
                                                                                        $Day[$d]='o';
                                                                }
                                                                break;
                                                        case '+':
                                                                // cas particulier du jour hors limit
                                                                $Day = $this->TCBusinessList($Day,$index,'+');
                                                                break;
                                                        default:
                                                                $Day = $this->TCBusinessList($Day,$index);
                                                }
                                                break;

                                        # Jour calendaire du mois	
                                        case 'D':
                                                # On distingue un _DMd d'un +DMd
                                                # Si le sens est _, on met dans le tableau les jours ouvr�s
                                                # Si le sens est +, on prend en compte l'ensemble des jours
                                                # On force tous les jours
                                                # Donc on liste les annees et les mois et on initialise
                                                $Years = array(); $Months = array();
                                                foreach (array_keys($Day) as $d) {
                                                                list($Y,$M,$D,$h,$m,$s) = $this->TCDate2YMD($d);
                                                                if (!in_array($Y,$Years))
                                                                        array_push($Years,$Y);
                                                                if (!in_array($M,$Months))
                                                                        array_push($Months,$M);
                                                }
                                                sort($Years); sort($Months);
                                                # Fort de ces petites listes, on applique 
                                                # Doit on prendre soin de l'heure ? a priori non.
                                                foreach ($Years as $y) {
                                                        foreach ($Months as $m) {
                                                                $eom = $this->EndOfMonth($m,$y);
                                                                $Index = $this->TCIndex($index,1,$eom);
                                                                for($j=1;$j<=$eom;$j++) {
                                                                        $date = $this->TCYMD2Date($y,$m,$j,0,0,0);
                                                                        if ((($sens=='_') && (isset($Day[$date])) && ($Day[$date]=='o')) 
                                                                                || ($sens!='_')) {
                                                                                if (in_array($j,$Index)) {
                                                                                        # Le jour est ouvr�.
                                                                                        $Day[$date]='o';
                                                                                }
                                                                                else {
                                                                                        # Le jour est supprim� si il est ouvert
                                                                                        if (isset($Day[$date]) && ($Day[$date]=='o'))
                                                                                                $Day[$date]='';
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                }
                                        break;
                                }
                                break;
                        case 'Y':
                                switch ($unit) {
                                        case 'D':
                                                foreach (array_keys($Day) as $d) {
                                                        if ($Day[$d]=='o') {
                                                                # L'index est sur 366 jours max
                                                                $Index = $this->TCIndex($index,1,366);
                                                                $j = $this->TCDayOfYear($d);
                                                                if (!in_array($j,$Index))
                                                                        $Day[$d]='';
                                                        }
                                                }
                                                break;
                                        case 'B':
                                                // Le ni�me jour ouvr� de l'ann�e
                                                $Index = $this->TCIndex($index,0,366);
                                                // Attention, dans le cas < 1, c'est l'ann�e pr�c�dente
                                                $ly = -1;
                                                foreach (array_keys($Day) as $d) {
                                                        list($y,$m,$j) = $this->TCDate2YMD($d);
                                                        # Si l'ancienne ann�e est diff�rente de la nouvelle
                                                        # on reindexe
                                                        if ($y != $ly) {
                                                                $n=0;
                                                                $ly = $y;
                                                        }
                                                        if ($Day[$d]=='o') {
                                                                $n++;
                                                        }
                                                        if (!in_array($n,$Index)) 
                                                                if ($Day[$d]=='o')
                                                                        $Day[$d]='';
                                                }
                                                break;					
                                        case 'M':
                                                # 2 cas : + pour tous les jours et _ pour les jours dans la liste

                                                # Methode simplifi�e :
                                                # On calcule l'index avec et sans regroupement
                                                $Index = $this->TCIndex($index,1,12);
                                                if ($group>1) {
                                                        $Mestres = array();
                                                        for($s=0;$s<(12/$group);$s++) {
                                                                foreach($Index as $i) {
                                                                        array_push($Mestres,($s*$group)+$i);
                                                                }
                                                        }
                                                        $Index = $Mestres;
                                                }
                                                if ($sens == '_') {
                                                        # Gestion des regroupements
                                                        foreach (array_keys($Day) as $d) {
                                                                if ($Day[$d]=='o') {
                                                                        $m = $this->TCGetMonth($d);
                                                                        if (!in_array($m,$Index)) {
                                                                                $Day[$d]='';
                                                                        }
                                                                }
                                                        }

                                                }
                                                else {
                                                        # A REVOIR !!
                                                        foreach (array_keys($Day) as $d) {
                                                                # on selectionne si $m est dans l'index
                                                                # et tout ce qui est entre la derniere date
                                                                # et la date a jouer
                                                                list($sy,$sm,$sd) = $this->TCDate2YMD($d);
                                                                if (in_array($sm,$Index)) {
                                                                        for($i=1;$i<=$this->EndOfMonth($sm,$sy);$i++) {
                                                                                $new = $this->TCYMD2Date($sy,$sm,$i,0,0,0);
                                                                                $Day[$new]='o';
                                                                        }
                                                                }
                                                                else {
                                                                        if ($Day[$d]=='o') {
                                                                                $Day[$d]='';
                                                                        }
                                                                }
                                                        }									
                                                }

                                                break;					
                                }
                                break;
                        default:
            # ==========================================================
            #	Pas de periode
            # ==========================================================
                                                    switch ($unit) {
                                                            case 'D':
                                                                    if ($sens=='-')
                                                                            $times*= -1;
                                                                    # on ajoute ou on recule
                                                                    $New = array();
                                                                    foreach (array_keys($Day) as $d) {
                                                                            # Le jour est d�cal�
                                                                            if ($Day[$d]=='o') {
                                                                                    $day = $this->TCMoveDay($d,$times);
                                                                                    $Day[$d]='';
                                                                                    array_push($New,$day);
                                                                            }
                                                                    }
                                                                    foreach ($New as $d) {
                                                                            $Day[$d]='o';
                                                                    }
                                                                    break;
                                                    }
                                                    switch ($unit) {
                                                            case 'B':
                                                                    # on ajoute ou on recule
                                                                    # on gere le cas du "0" qui signifie "si besoin"
                                                                    # on a besoin d'utliser l'index
                                                                    # on cree un tableau New et un tableau Old
                                                                    # le Day est r�serv� pour les tests
                                                                    # Dates sert pour l'indexation
                                                                    $Old = array(); $New = array(); 
                                                                    $Dates = array();
                                                                    # On recupere l'init si on est en +
                                                                    foreach (array_keys($Base) as $d) {
                                                                            if ($Base[$d]=='o') {
                                                                                    array_push($Dates,$d);
                                                                            }
                                                                    }
                                                                    sort($Dates);
                                                                    $Index = $this->TCIndex($times,0,999);
                                                                    foreach (array_keys($Day) as $d)  {
                                                                            if ($Day[$d]=='o') {

                                                                                            // on supprime le jour 
                                                                                            if (isset($Base[$d])) {
                                                                                                    if ($Base[$d]=='o')
                                                                                                            $Day[$d]='';
                                                                                                    else 
                                                                                                            $Day[$d]=$Base[$d];
                                                                                            }
                                                                                            else {
                                                                                                    $Day[$d]='';
                                                                                            }

                                                                            foreach ($Index as $times) {
                                                                            # La on traite le cas du 0
                                                                                    if (($times==0) && (!in_array($d,$Dates))) {
                                                                                            $move = 1;
                                                                                    }
                                                                                    else
                                                                                            $move = $times;

                                                                                    # On positionne le jour dans la liste des dates
                                                                                    $i = 0;
                                                                                    while (isset($Dates[$i]) && ($d >= $Dates[$i]) && ($i < count($Dates))) {
                                                                                            $i++;
                                                                                    }
                                                                                    if ($sens=='-')
                                                                                            $move *= -1;
                                                                                    $i--;							
                                                                                    // a ce moment on se trouve juste avant un jour ferie
                                                                                    // => dans le cas -0B, c 'est fini
                                                                                    if (($times==0) && ($sens=='-')) {
                                                                                                    array_push($New,$Dates[$i]);																							
                                                                                    }
                                                                                    else {											
                                                                                    if ($i>=0) {
                                                                                                    $n = $i+$move;
                                                                                                    if (isset($Dates[$n])) 
                                                                                                            $new = $Dates[$n];
                                                                                                    else 
                                                                                                            $new = '';

                                                                                                    # 2 possiblites : decalage ou extension
                                                                                                    if ($sens=='#') {
                                                                                                            # On g�re la liste des jours entre 2 dates
                                                                                                            list($sy,$sm,$sd) = $this->TCDate2YMD($d);
                                                                                                            list($ey,$em,$ed) = $this->TCDate2YMD($new);
                                                                                                            $New = array_merge($New,GHTCFill('0',$sy,$ey,$sm,$em,$sd,$ed));
                                                                                                    }
                                                                                                    else {
                                                                                                            array_push($New,$new);
                                                                                                    }
                                                                                            }
                                                                                    }
                                                                            }
                                                                            }
                                                                            // $move=$times;
                                                                    }
                                                                    foreach ($New as $n) {
                                                                            $Day[$n] = 'o';
                                                                    }
                                                                    break;
                                                    }	
                                    }
                            }

                            # On supprime le bout de r�gle
                            $rule = substr($rule,strlen($todo));

                            # Mode reverse
                            if ($reverse=='!') {

                                    foreach (array_keys($Day) as $k) {
                                            if ($Day[$k]=='c') {
                                                            $Day[$k] = 'o';
                                            }
                                            elseif ($Day[$k]=='o') {
                                                            $Day[$k] = 'c';
                                            }
                                    }
                            }

                    }


                    # Suppression des valeurs vides
                    array_unique($Day);
                    foreach(array_keys($Day) as $d) {
                            if ($Day[$d]=='')
                                    unset($Day[$d]);
                    }

                    $rule = $nextrule;
                    # Le resultat est le traitement des dates
                    # + l'operation sur la derniere liste
                    switch ($bool) {
                            case '+=':
                                    # on ajoute LAST + RES
                                    # On recupere la liste "date type"
                                    foreach(array_keys($Day) as $k) {
                                            $t1 = $Day[$k];
                                            if (isset($Last[$k])) 
                                                    $t2 = $Last[$k];
                                            else
                                                    $t2 = '';
                                            # Si les jours sont ouvr�s sur l'une ou l'autre, le resultat est ouvr�
                                            if (($t1=='o') || ($t2=='o')) {
                                                    $Last[$k]='o';
                                            } 
                                            else {
                                                    $Last[$k]=$t1;
                                            }
                                    }
                                    break;
                    }	


                    # Suppression des doublons
                    array_unique($Last);

                    $bool = $nextbool;

            } while ($rule!='');
        return $Last;
    }

    public function Reference($rule,$year='',$we='67') {
        
        if ($year=='') {
            $Now = getdate();
            $year = $Now['year'];
        }

        // extraction du weekend
        if (preg_match('/^\d+\_/',$rule,$Match)) {
            if (isset($Match[0])) {
                $prefix = $Match[0];
                $l = strlen($prefix);
                $we = substr($prefix,0,$l-1);
                $rule = substr($rule,$l);
            }
        }

        // simplification des calendriers mixtes
        $rule = str_replace('MYc','Mc',$rule);

        $iter=0;
        while ((strlen($rule)>0 && preg_match('/^(\!*)([\+\-\=\_\#]{0,1})(\d*)([@BDMYLhms]{0,1})([\d!]*)([A-Za-z]*)([\d\~\,\!]*)/',$rule,$Match) && ($iter<10))) {
            $iter++;
            array_shift($Match);
            list($reverse,$sens,$times,$unit,$group,$period,$index) = $Match;

            $todo = implode("",$Match);

            // est ce que c'est un groupe ?
            switch ($period) {
                case 'W':
                    switch ($unit) {
                        case 'D':	
                            // on d�cale la date jusqu'� l'index
                            // il faudra gerer le 0DW
                            if ($sens == '-') $s = -1;
                                    else $s = 1;
                            do {
                                    $date = $this->TCMoveDay($date,$s);
                                    $dow = $this->TCDayOfWeek($date);
                            } while ($index!=$dow);
                            break;
                    }
                    break;
                // mois dans ann�e chinoise
                case 'c':
                    switch ($unit) {
                        // trouver le mois chinois
                        case 'M':	
                            list( $y,$m,$d, $hour, $minutes, $seconds ) = $this->TCChineseMonth($year,$index);
                            $date = $this->TCYMD2Date( $y,$m,$d, $hour, $minutes, $seconds );
                        break;
                    }	
                    break;
                default:
                    switch ($unit) {
                    case 'B':
                            // on calcule le prochaine jour
                            $n=0; $begin = $date;
                            if ($sens == '-') $s = -1;
                                    else						$s = 1;
                             $t=$times * $s; 
                            do {
                                    $date = $this->TCMoveDay($date,$t);
                                    if ($t==0) $t=1*$s;
                                    $dow = $this->TCDayOfWeek($date);
                                    $n++;
                            } while (strpos(" $we","$dow")>0);
                            // cas du "au plus proche"
                            if ($sens=='=') {
                                    // on calcule la date inverse
                                    $s *= -1;
                                    $t = $times * $s; $date2 = $begin; $n2= 0;
                                    do {
                                            $date2 = $this->TCMoveDay($date2,$t);
                                            if ($t==0) $t=1*$s;
                                            $dow = $this->TCDayOfWeek($date2);
                                            $n2++;
                                    } while (strpos(" $we","$dow")>0);
                                    // si n2 est inferieur a n, la date devient la nouvelle date
                                    if ($n2<$n)
                                            $date = $date2;
                            }
                            break;
                    case 'D':
                            if ($sens=='-') $times *= -1;
                            $date = $this->TCMoveDay($date,$times);
                            break;
                    case 'L':
                            if ($sens=='-') $times *= -1;
                            if ($year % 4==0) $times=1;
                            else $times=0;
                            $date = $this->TCMoveDay($date,$times);
                            break;
                    case '@':
                            // la on teste si une liste est bufferis�e ou si on applique la fonction
                            // La date de paques ?
                            if ($period=='E') { 
                                    list($y,$m,$d) =  $this->Easternday($year);
                                    $date = $this->TCYMD2Date( $y,$m,$d, 0, 0, 0 ); 
                            }
                            elseif ($period=='S') {
                                    list($y,$m,$d) = $this->GetSeason($year,$unit);
                                    $date = $this->TCYMD2Date( $y,$m,$d, 0, 0, 0 ); 
                            }
                            // Calendrier lunaire
                            // pas le temps de faire mieux
                            elseif ($period=='L') { 
                                    $Lunar = array( 2007	=> $this->TCYMD2Date( 2007, 2, 18, 0, 0, 0 ),
                                            2008	=> $this->TCYMD2Date( 2008, 2, 7, 0, 0, 0 ),
                                            2009	=> $this->TCYMD2Date( 2009, 1, 26, 0, 0, 0 ),
                                            2010	=> $this->TCYMD2Date( 2009, 2, 14, 0, 0, 0 ),
                                            2011	=> $this->TCYMD2Date( 2011, 2, 3, 0, 0, 0 ),
                                            2012	=> $this->TCYMD2Date( 2012, 1, 23, 0, 0, 0 ),
                                            2013	=> $this->TCYMD2Date( 2013, 2, 10, 0, 0, 0 ),
                                            2014	=> $this->TCYMD2Date( 2014, 1, 31, 0, 0, 0 ),
                                            2015	=> $this->TCYMD2Date( 2015, 2, 19, 0, 0, 0 ),
                                            2016	=> $this->TCYMD2Date( 2016, 2, 8, 0, 0, 0 ),
                                            2017	=> $this->TCYMD2Date( 2017, 1, 28, 0, 0, 0 ),
                                            2018	=> $this->TCYMD2Date( 2018, 2, 16, 0, 0, 0 ),
                                            2019	=> $this->TCYMD2Date( 2019, 2, 5, 0, 0, 0 ),
                                            2020	=> $this->TCYMD2Date( 2020, 1, 25, 0, 0, 0 )
                                            );
//									if ( ($year>2007) && ($year<2021))
                                            $date = $Lunar[$year];
                            }
                            break;
                    default:
                            // Est ce que c'est une date fixe ?
                            // avec une annee ?
                            $date = '';
                            if (preg_match('/(\d\d\d\d)(\d\d)(\d\d)/',$times,$Match)) {
                                    if (($Match[1]==$year) && ($Match[2]>0) && ($Match[2]<13) && ($Match[3]>0) && ($Match[3]<32)) {
                                            $date = sprintf('%04d%02d%02d000000',$year,$Match[2],$Match[3]);
                                    }
                            }																	
                            elseif (preg_match('/(\d\d)(\d\d)/',$times,$Match)) {
                                    if (($Match[1]>0) && ($Match[1]<13) && ($Match[2]>0) && ($Match[2]<32)) {
                                            $date = sprintf('%04d%02d%02d000000',$year,$Match[1],$Match[2]);
                                    }
                            }									
                    }
            }
            // on supprime la regle
            $rule = substr($rule,strlen($todo));
        }
    return $date;	
    }

/* ========================= */
/* bibliotheque de fonctions */
/* ========================= */
    
    private function Easternday($annee) {

            $G = $annee % 19;
            $C = (int)($annee / 100);
            $H = (int)($C - ($C / 4) - ((8*$C+13) / 25) + 19*$G + 15) % 30;
            $I = (int)$H - (int)($H / 28)*(1 - (int)($H / 28)*(int)(29 / ($H + 1))*((int)(21 - $G) / 11));
            $J = ($annee + (int)($annee/4) + $I + 2 - $C + (int)($C/4)) % 7;
            $L = $I - $J;
            $m = 3 + (int)(($L + 40) / 44);
            $d = $L + 28 - 31 * ((int)($m / 4));
            $y = $annee;

    return array($y,$m,$d);
    }

    ///////////////////////////////
    // Conversion
    // Date => YMD
    private function TCDate2YMD($date) {
            $date = preg_replace('/[ \/\\\-]/','//',$date);
            $Y = (int) substr($date,0,4);
            $M = (int) substr($date,4,2);
            $D = (int) substr($date,6,2);
            $h = (int) substr($date,8,2);
            $m = (int) substr($date,10,2);
            $s = (int) substr($date,12,2);
    return array($Y,$M,$D,$h,$m,$s);
    }

    // Date => Julian
    private function TCDate2Julian($date) {
            list($y,$m,$d) = $this->TCDate2YMD($date);
    return GregorianToJD($m,$d,$y);
    }

    // YMD => Date
    private function TCYMD2Date($Y,$M,$D,$h,$m,$s) {
    return sprintf("%04d%02d%02d%02d%02d%02d",$Y,$M,$D,$h,$m,$s);
    }

    // YMD => Julian
    private function TCYMD2Julian($Y,$M,$D,$h,$m,$s) {
    return GregorianToJD($M, $D, $Y);
    }

    // Julian => Date
    private function TCJulian2Date($julian) {
            $j = (int) $julian;
            $time = round(($julian-$j)*86400);

            list($M,$D,$Y) = explode('/',JDTogregorian($j));
            $h=$m=$s=0;
            if ($time>0) {
                    $h = (int) ($time/3600);
                    $time -= $h*3600;
                    $m = (int) ($time/60);
                    $s = $time % 60;
            }

    return $this->TCYMD2Date($Y,$M,$D,$h,$m,$s);
    }

    // Next Day
    private function TCNextDay($date) {
            $j = $this->TCDate2Julian($date);
            $j++;
    return $this->TCJulian2Date($j);
    }

    private function PIsWE($posix,$we) {
            $day = (int) ($posix / (3600*24)) + 5;
            $dow = $day % 7;
            if (strpos($we,"$dow")===false)
                    return 0;

    return 1;
    }

    ///////////////////////////////
    // Présentation
    ///////////////////////////////

    public function Date2ISO($date) {
        $Y = (int) substr($date,0,4);
        $M = (int) substr($date,4,2);
        $D = (int) substr($date,6,2);
        $h = (int) substr($date,8,2);
        $m = (int) substr($date,10,2);
        $s = (int) substr($date,12,2);
    return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$Y,$M,$D,$h,$m,$s);
    }

    public function YMD2Date($date,$format='YYYY-MM-DD hh:mm:ss') {
            list($Res['Y'],$Res['M'],$Res['D'],$Res['h'],$Res['m'],$Res['s'],$Res['c']) = $date;
            // Premier passage tout simple pour obtenir le format simplifié 
            $newdate = '';
            $part = '';
            $i = 0; $n = strlen($format);
            while ($i<$n) {
              $c = substr($format,$i,1);
              $part=$c;
              while (($i+1<$n) && (substr($format.' ',$i+1,1)==$c)) {
                $part .= $c;
                $i++;
              }
              if (strpos(' YMDhmsc',$c)) {
                $len = strlen($part);
                // cas particulier 
                if (($c=='M') and ($len>2)) {
                  $months = array('', 'January','February','March','April','May','June','July','August','September','October','November','December');
                  $m = $Res[$c];
                  $newdate  .= '{"'.trim(substr($months[$m].'         ', 0, $len )).'"}';
                }
                else
                  $newdate  .= substr( '0000'.$Res[$c], $len * -1 );
              }
              else {
                $newdate .= $part;
              }
              $i++;
            }
    return $newdate;
    }

    public function FormatDate($date,$format='YYYY-MM-DD') {
        $date = trim($date);
        if ($date=='')
            return;
        if (strpos($date,' ')>0)
            list($date,$time)=explode(' ',$date);
        //le format est en majuscule
        $format = strtoupper($format);
        // on supprime les separateurs exotiques
        $F = explode('-',strtr($format,'/:.','---'));
        $D = explode('-',strtr($date,'/:.','---'));
        for($i=0;$i<count($D);$i++) {
            switch ($F[$i]) {
                case 'YY':
                    $D[$i]+=1900;
                case 'YYYY':
                    $y = $D[$i];
                    break;
                case 'MM':
                    $m = $D[$i];
                    break;
                case 'DD':
                    $d = $D[$i];
                    break;
            }
        }
        // test de time
        // si 4 lettres, alors heure+minutes
        if (isset($time) && (strlen($time)==4))
            $time = substr($time,0,2).':'.substr($time,2,2).':00';
        else 
            $time = '00:00:00';

    return sprintf('%04d/%02d/%02d',$y,$m,$d).' '.$time;
    }

# Cette fonction renvoit la liste des jours ouvr�s d'un mois � partir d'un index
# On ajoute le se
public function TCBusinessList($Day,$index,$sens='_') {

	$i = 0; $last=0;
	# on peut traiter les differentes dates en comparant l'index
	# on doit comparer mois par mois
	$Mois = array();
	foreach (array_keys($Day) as $d) {
		# On ne traite que les jours ouvr�s
		if ($Day[$d]=='o') {
			# Mois 
			$m = $this->TCGetMonth($d);
	
			# Si le mois est diff�rent du dernier, on reinitialise
			if ($m!=$last) {
				$last = $m;

				$Day = $this->TCProcess($index,$Day,$Mois,$sens);
				# On supprime le mois pour traiter le suivant 
				$Mois = array();
			}
			array_push($Mois,$d);
		}
	}
	$Day = $this->TCProcess($index,$Day,$Mois);
	
return $Day;
}

# Affcte les nouvelles valeurs indexees 
public function TCProcess($index,$Day,$New,$sens='_') {
	if (count($New)==0) 
		return $Day;

	$Index = $this->TCIndex($index,1,count($New),$sens);

	$i = 1;
	foreach ($New as $m) {
			if (!in_array($i,$Index))
				$Day[$m]='';
			$i++;
	}
return $Day;
}

public function TCIndex($index,$min=1,$max=999,$sens='_') {
	$not=0;
	if ($index=='') {
		$index = "$min~$max";
	}
	if ($index[0]=='!') {
		$not = 1;
		$index = substr($index,1);
	}
	// Plage de dates
	$Range = array();
	foreach (explode(",",$index) as $g) {
		$R = explode("~",$g);
		
		# On compte les elements
		# Si un seul : 1 indice
		if (count($R)==1) {
			if ($R[0]=='') {
				for($i=$min;$i<=$max;$i++) {
					array_push($Range,$i);
				}			
			}
			else {
				$d = $this->TCIndexLimit($R[0],$min,$max,$sens);
				array_push($Range,$d);
			}
		}
		else {
			if ($R[0]=='')
				$R[0]=$min;
			if ($R[1]=='')
				$R[1]=$max;
	
			$d = $this->TCIndexLimit($R[0],$min,$max);
			$f = $this->TCIndexLimit($R[1],$min,$max);
			
			# on traite les cas 97~4 en ~4,97~
			if ($d<=$f) {
				for($i=$d;$i<=$f;$i++) {
					array_push($Range,$i);
				}
			}
			else {
				for($i=$min;$i<=$f;$i++) {
					array_push($Range,$i);
				}
				for($i=$d;$i<=$max;$i++) {
					array_push($Range,$i);
				}
			}
		}
	}
	if ($not==0) 
		return $Range;
	
	$Reverse = array();
	for($i=$min;$i<=$max;$i++) {
		if (!in_array($i,$Range))
			array_push($Reverse,$i);
	}
	
return $Reverse;
}
# G�re les bornes
public function TCIndexLimit($index,$min,$max,$sens='_') {

	if ($index<$min)
		return $max;

/* |  MOIS     |   MOIS INVERSE   |  SEMAINE ou ANNEE   | INVERSE SEMAINE OU ANNEE
   0          50                 100                   500                           1000      
*/
	if (($max < 50) && ($index>50))
		return $max - (100-$index);

	if (($max < 500) && ($index>500))
			return $max - (1000-$index);

	if (($index>$max) && ($sens=='+'))
		return $max;
		
return $index;
}

public function _TCChecksum2($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year']
	);
	$Args	=	GH_GetArgs($args,$Default);

	$Check = '';
	$Infos = $this->_GetArray($Args,$Data);
	$year = $Args['year'];
	$lm=0;$ld=0;$n=0;$p=0;
	foreach($Infos as $i) {
		if (isset($i['type']))
			$type=$i['type'];
		else
			$type='o';
		if ((substr($i['date'],0,4)==$Args['year']) && ($type=='o')) {
			$m = substr($i['date'],4,2)*1;
			$d = substr($i['date'],6)*1;
			# nouveau mois ?
			if ($m<>$lm) {
				$Check .= $this->TCCheckAdd($p,$n);
				$n = 1;
				$p = $d;
			}
			else  {
				# jour suivant ? Si oui, on ajoute la periode
				if ($d-$ld==1) {
					$n++;
				}
				else {
					$Check .= $this->TCCheckAdd($p,$n);
					$n = 1;
					$p = $d;
				}
			}
			$ld = $d;
			$lm = $m;
		}
	}
	$Check .= $this->TCCheckAdd($p,$n);
	
return $Check;
}

public function _TCChecksum($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year']
	);
	$Args	=	GH_GetArgs($args,$Default);
	
	$Infos = $this->_GetArray($Args,$Data);
	$year = $Args['year'];
	$Check = array();
	foreach($Infos as $i) {
		if (isset($i['type']))
			$type=$i['type'];
		else
			$type='o';
		$d = $this->CleanDate($i['date']);
		if ((substr($d,0,4)==$Args['year']) && ($type=='o')) {
			array_push($Check,substr($d,4));
		}
	}
return TCChecksum($Check);
}

// recalcul en fonction de la date, l'ensemble des jours est pris en compte
// en entree : date + type
// a priori 365/6 jours
public function _TCCheckCal($args,$Data) {
global $GHCfg;
	$Now = getdate();
	$Default = array (
	);
	$Args	=	GH_GetArgs($args,$Default);
	
	$Infos = $this->_GetArray($Args,$Data);
	
	$Res=array("id	year	checksum");
	foreach($Infos as $i) {
		if (isset($i['type']))
			$type=$i['type'];
		else
			$type='o';
		$d = $this->CleanDate($i['date']);
		$year = substr($d,0,4);
		if (!isset($Check[$year]))	{
			$Check[$year] = array();
			$id[$year] = $i['id'];
		}
		array_push($Check[$year],substr($d,4).$type);
	}
	foreach ($Check as $y => $tab) {
		array_push($Res,$id[$y]."	$y	".TCChecksum($tab));
	}
	
return $Res;
}

// ajout de l'ann�e
// si l'ann�e est pr�cis�e, on compl�te
function TCChecksum($Check) {
	sort($Check);
	$check = join('',$Check);
	preg_replace('/\s*/','',$check);
	$crc = str_replace('-',0,crc32($check));
return base_convert($crc,10,36);
}

public function TCCheckAdd($p,$n) {
	$Str = ' abcdefghijklmnopqrstuvwxyz.:+-!ABCDEFGHIJKLMNOPQRTUVWXYZ';
	$r = '';
	if (($p==0) || ($n==0))
		return '';
	if ($p>0)
		$r .= $Str[$p];
	if ($n>1)
		$r .= $n;
return $r;
}

public function TCDayOfYear($date) {
	list($a,$m,$j) = $this->TCDate2YMD($date);
return GregorianToJD($m, $j, $a) - GregorianToJD(1, 1, $a)+1;
}

public function TCDayOfWeek($date) {
return ($this->TCDate2Julian($date) % 7)+1;
}

public function TCMoveDay($date,$move) {
	list($Y,$M,$D,$h,$m,$s) = $this->TCDate2YMD($date);
	$jd = GregorianToJD($M, $D, $Y)+$move+($h*3600+$m*60+$s)/86400;
return $this->TCJulian2Date($jd);
}

// pas bonne si on depasse le mois !!!!
public function TCMoveHour($date,$move) {
	list($Y,$M,$D,$h,$m,$s) = $this->TCDate2YMD($date);
	$h += $move;
	$d = (int) ($h/24);
	$jd = GregorianToJD($M, $D, $Y)+($h*3600+$m*60+$s)/86400;
return $this->TCJulian2Date($jd);
}

public function EndOfMonth($m,$a) {
return cal_days_in_month(CAL_GREGORIAN, $m, $a); 
}

public function TCGetMonth($date) {
return (int) substr($date,4,2);
}
public function TCGetYear($date) {
return (int) substr($date,0,4);
}
public function TCGetDay($date) {
return (int) substr($date,6,2);
}

public function TCSetHour($date,$hour) {
	list($Y,$M,$D,$h,$m,$s) = $this->TCDate2YMD($date);
return $this->TCYMD2Date($Y,$M,$D,$hour,$m,$s);
}
public function TCSetMinutes($date,$min) {
	list($Y,$M,$D,$h,$m,$s) = $this->TCDate2YMD($date);
return $this->TCYMD2Date($Y,$M,$D,$h,$min,$s);
}

// Anneee
public function _TCYear($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year'],
		'we'	=> '67'
	);
	$Args	=	GH_GetArgs($args,$Default);

return array_merge(array('date'),GHTCFill($Args['we'],$Args['year'],$Args['year']));
}

public function TCFill($we='67',$year,$yend,$mstart=1,$mend=12,$dstart=1,$dend=31) {
	$first = GregorianToJD($mstart,$dstart,$year);
	$end = GregorianToJD($mend,$dend,$yend);
	$Res = array();
	$dow = $this->TCDayOfWeek($first);
	for($i=$first;$i<=$end;$i++) {
		$dow = ($i % 7)+1;
		if (!strpos(" $we","$dow")>0) {
			$Date = cal_from_jd($i,CAL_GREGORIAN);
			array_push($Res,GHTCYMD2Date($Date['year'],$Date['month'],$Date['day'],0,0,0));
		}
	}
return $Res;
}

public function TCGetList($base) {
	if ($base=='') return array();
	
	if (!isset($GLOBALS['template'][$base]))
		return array();
	$Base = explode("\n",$GLOBALS['template'][$base]);
	$Infos = $this->_GetArray(array(),$Base);

return $Infos;
}

public function TCWeekOfYear($d) {
    $i = jdtounix($this->TCDate2Julian($d)); 
return date ('W', $i);
}

public function _Form2TC($args,$Data) {

	$Default = array (
		'base' => '',
		'months' => '',
		'days' => '',
		'business' => '',
		'week' => ''
	);
	$Args	=	GH_GetArgs($args,$Default);

	$res = $Args['base'];
	// Mois
	if (($Args['months']!='') && ($Args['months']!='{NULL}')) {
		$res .= '_MY'.$Args['months'];
	}
	if (($Args['days']!='') && ($Args['days']!='{NULL}')) {
		$res .= '_DM'.$Args['days'];
	}
	if (($Args['business']!='') && ($Args['business']!='{NULL}')) {
		$res .= '_BM'.$Args['business'];
	}
	if (($Args['week']!='') && ($Args['week']!='{NULL}')) {
		$res .= '_DW'.$Args['week'];
	}

return $res;
}

function TCOptimize($res) {
	
}

public function _Gregorian2Jd($d,$m,$y,$gmt=0) {
//	$j = (367*y) - ( (7($y+(($m+9)/12)))/4 ) + ((275M)/9) + $d + 1721013.5 + $gmt /24 - 0.5sign(100*$y+$m-190002.5) + 0.5;
return 0;
}

function TCBase($rule) {
	if (preg_match('/^(\!*[a-zA-Z0-9\@]+)/',$rule,$Match)) {
		return preg_replace('/^\@/','',$Match[1]);
	}
return;
}

function TCValidName($sg) {
	if ($sg == 'US0+1D') {
		return '+@US+1D';
	}

	if (preg_match('/^DM_(\d*)DW(\d*)/',$sg,$Match)) {
		$g = (int) $Match[1];
		if ($g==0) {
			$g=1;
		}
		return '+DM'.((($g-1)*7)+1).'~'.($g*7).'_DW'.$Match[2];
	}
	if (preg_match('/^[DB]M/',$sg)) {
		$sg = '+'.$sg;
	}
	if (preg_match('/^(\d*)DW(\d*)/',$sg,$Match)) {
		$g = (int) $Match[1];
		if ($g==0) {
			$g=1;
		}
		return '+DM'.((($g-1)*7)+1).'~'.($g*7).'_DW'.$Match[2];
	}

return $sg;
}

/*
public function _GetSeason
2002 20 19:16 21 13:24 23 04:55 22 01:14 
2003 21 01:00 21 19:10 23 10:47 22 07:04 
2004 20 06:49 21 00:57 22 16:30 21 12:42 
2005 20 12:33 21 06:46 22 22:23 21 18:35 
2006 20 18:26 21 12:26 23 04:03 22 00:22 
2007 21 00:07 21 18:06 23 09:51 22 06:08 
2008 20 05:48 20 23:59 22 15:44 21 12:04 
2009 20 11:44 21 05:45 22 21:18 21 17:47 
2010 20 17:32 21 11:28 23 03:09 21 23:38 
2011 20 23:21 21 17:16 23 09:04 22 05:30 
2012 20 05:14 20 23:09 22 14:49 21 11:11 
2013 20 11:02 21 05:04 22 20:44 21 17:11 
2014 20 16:57 21 10:51 23 02:29 21 23:03 
*/

function gh_index_cal__day($args,$Data) {

	$Res = array();
	array_push($Res,array_shift($Data));
	foreach ($Data as $d) {
		list($CAL,$DAY) = explode("\t",$d);
		array_push($Res,$CAL."\t".$CAL.substr($DAY,4));
	}
return $Res;
}

// on passe l'ann�e et la saison
// 1 : printemps
// 2 : �t�
// 3 : automne
// 4 : hiver
// et eventuellement le fuseau horaire
public function GetSeason($year,$n,$utc=0) {
	$file = str_replace('.php','',__FILE__).'/solstices_and_equinoxes.tsv';
	$Infos = $this->_GetArray(array(),explode("\n",GHGetContent($file)));
	$m = $n*3;
	$d=1;
	$hour=$min=0;
	// cas de la saison precedente
	if ( $m==0 ) {
		$m = 12;
		$year--;
	}
	$y=0;
	foreach ($Infos as $i) {
		if ( ($i['year']==$year) && ($i['month']==$m) ) {
			$y = $year;
			$d = $i['day'];
			list($hour,$min) = explode(':',$i['time']);
			continue;
		}
	}
	if ( $year==0 ) {
		$this->SysLog(
			array(	"Module"	=> basename(__FILE__),
				"Line"	=>	__LINE__,
				"Type"	=>	'E',
				"Error" => 	'Out of range',
				"Year" => $year )
			);
			return array(1970,1,1,0,0,0);
	}
return array($year,$m,$d,$hour,$min,0);
}

public function GetNewMoon($year,$month=1,$day=1) {
	$file = str_replace('.php','',__FILE__).'/new_moons.tsv';
	$Infos = $this->_GetArray(array(),explode("\n",GHGetContent($file)));
	foreach ($Infos as $i) {
		if ( ($i['year']==$year) && ($i['month']==$month) && ($i['day']==$day) ) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],0);
		}
	}
	$this->SysLog(
		array(	"Module"	=> basename(__FILE__),
			"Line"	=>	__LINE__,
			"Type"	=>	'E',
			"Error" => 	'Out of range',
			"Year" => $year )
		);
return array(1970,1,1,0,0,0);
}

// donne la prochaine lune
// peut etre optimis�
public function GetNextMoon($Y,$M=1,$D=1,$h=0,$m=0,$s=0,$n=1) {
	$file = str_replace('.php','',__FILE__).'/new_moons.tsv';
	$Infos = $this->_GetArray(array(),explode("\n",GHGetContent($file)));
	// date de reference
	$ref = $this->YMD2Posix( array($Y, $M, $D, $h, $m, $s ) );	
	$l=0;
	foreach ($Infos as $i) {
		$date = $this->YMD2Posix( array( $i['year'], $i['month'], $i['day'], $i['hour'], $i['min'] ) );

		if ($date>$ref) $l++;
		
		if ($l==$n) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],0);
		}
	}
	$this->SysLog(
		array(	"Module"	=> basename(__FILE__),
			"Line"	=>	__LINE__,
			"Type"	=>	'E',
			"Error" => 	'Out of range',
			"Year" => $year )
		);
return array(1970,1,1,0,0,0);
}

public function GetChineseCal($year) {
	$file = str_replace('.php','',__FILE__).'/chinese_calendar.tsv';
	$Infos = $this->_GetArray(array(),explode("\n",GHGetContent($file)));
	foreach ($Infos as $i) {
		if ($i['year']==$year) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],$i['sec']);
		}
	}
	$this->SysLog(
		array(	"Module"	=> basename(__FILE__),
			"Line"	=>	__LINE__,
			"Type"	=>	'E',
			"Error" => 	'Out of range',
			"Year" => $year )
		);
return array(1970,1,1,0,0,0);
}

// Mois chinois
// seulement en posix
public function TCChineseMonth($year,$month) {
	// on trouve le solstice d'hiver precedent
	$YMD = $this->GetSeason($year, 0);

	// on se decale de 8h pour avoir l'heure de Chine
	list ( $Y,$M,$D, $h, $m, $s) = $this->Posix2YMD( $this->YMD2Posix($YMD)+8*3600 );

	// mois embolismique
	$e = $this->Embol($year);

	// est ce qu'il y a une lune intercalaire ?
	$n=$month;
	if (($e > 0) and ($e < $month)) {
		$n++;
	}

	// on recupere la deuxieme prochaine lune
	$YMD = $this->GetNextMoon($Y,$M,$D,$h,$m,$s,$n+1);

	// on decale de 8h
	$t = $this->YMD2Posix($YMD)+8*3600;

return $this->Posix2YMD($t);
}

// mois embolistique
public function Embol($year) {
$data = "1900	8
1903	5
1906	4
1909	2
1911	6
1914	5
1917	2
1919	7
1922	5
1925	4
1928	2
1930	6
1933	5
1936	3
1938	7
1941	6
1944	4
1947	2
1949	7
1952	5
1955	3
1957	8
1960	6
1963	4
1966	3
1968	7
1971	5
1974	4
1976	8
1979	6
1982	4
1984	10
1987	6
1990	5
1993	3
1995	8
1998	5
2001	4
2004	2
2006	7
2009	5
2012	4
2014	9
2017	6
2020	4
2023	2
2025	6
2028	5
2031	3
2033	11
2036	6
2039	5
2042	2
2044	7
2047	5
2050	3";
	$Data = explode("\n",$data);
	foreach ($Data as $d) {
		list($y,$m) = explode("\t",$d);
		if ($y==$year)
			return (int) $m;
	}
return 0;
}

private function SysLog($test) {
return;
print "<pre>";
    print_r($test);
    print "</pre>";
}
}
