<?php

array_push($GHModInfo,
	array(	"name" => 'Dates',
  'author' => 'E. Angenault',
  'desc' => 'Dates management',
  'ver'	 => '3.0',
  'status' => 'core',
  'publish' => 'GHDate2Posix,GHPosix2Date,GHEasternday,GHNowStr,GHCleanDate,GHStr2Date,GHSimpleTime,GHTimestamp',
  'history' => array(
    "2010/03/31 10:30	Passage en charset utf-8"
  )
	)
);

date_default_timezone_set('Europe/Paris');

function GHCheckModuleDates() {
  // 
return 'ok';
}
 
function GH_Now($Args,$Data) {
global $GHCfg;

	$Default = array(
		'name' => $GHCfg['fmt_date']
	);
	
	$Arg = GH_GetArgs($Args,$Default);
return array(date($Arg['name']));
}

function GHStrTime($val,$locale="") {
$res = array();
	
	setlocale(LC_TIME,$locale);
	switch ($val) {
		case 'a':
			for($d=0;$d<7;$d++) {
				array_push($res,strftime('%a',mktime(0,0,0,1,$d+3,1971)));
			}
			break;
		case 'b':
			array_push($res,'');
			for($m=1;$m<=12;$m++) {
				array_push($res,strftime('%b',mktime(0,0,0,$m,1,1971)));
			}
			break;
	}
return $res;
}

function GHDate2YMD($date,$format='Y/m/d H:i:s') {
    if ($date=='') 
	    return array(0,0,0,0,0,0);

    // Moyen le plus sur : on traite chacun des cas
    $Y = $M = $D = $h = $m = $s =0;
    // Nettoyage de s�parateur 
    $date = preg_replace('/[\-\.]/','/',$date);
    $date = preg_replace('/[\+]/',' ',$date);
    $format = preg_replace('/[\-\.]/','/',$format);
    $format = preg_replace('/[\+]/',' ',$format);
    switch($format) {
			case 'Y/m/d':
			    list($Y, $M, $D) = sscanf($date, "%d/%d/%d");
			    break;
			case 'Y/m/d H:i':
					list($Y, $M, $D, $h,$m) = sscanf($date, "%d/%d/%d %d:%d");
			    break;
			case 'Y/m/d H:i:s':
			    list($Y, $M, $D, $h,$m,$s) = sscanf($date, "%d/%d/%d %d:%d:%d");
			    break;
			case 'Ymd':
			    $Y = substr($date,0,4);
			    $M = substr($date,4,2);
			    $D = substr($date,6,2);
			    break;
			default:
			    return array(-1,-1,-1,-1,-1,-1);
    }
if ($Y<1900)
	$Y += 1900;
return array($Y,$M,$D,$h,$m,$s);
}

function GHDate2Posix($date) {
	// fractions de secondes ?
	$frac=0;
	if (($r = strpos($date,'.'))>0) {
		list($date,$frac) = explode('.',$date);
		$frac = (float) ".$frac";
	}
	// on simplifie $date
	$date = str_replace(
		array(' ','/','-',':'),
		array('','','',''), $date );
		$Y = (int) substr($date,0,4);
		$M = (int) substr($date,4,2);
		$D = (int) substr($date,6,2);
		$h = (int) substr($date,8,2);
		$m = (int) substr($date,10,2);
		$s = (int) substr($date,12,2);
		
		// recadrage en posix
		if ($Y==0) $Y=1970;
		if ($M==0) $M=1;
		if ($D==0) $D=1;
		
		// posix
		$posix = mktime($h,$m,$s,$M,$D,$Y);
		if ($posix<0)
			$posix=0;
return $posix+$frac;
}

// Simple fonction de test
function GH_Dates2YMD($arg,$Data)
{
	$Default = array(
	);

	$Infos = GHGetArray($arg,$Data);
	$Res = array("date\tformat\thour\tminute\tseconde\tmonth\tday\tyear");
	foreach ($Infos as $i) {
	    array_push($Res,$i['date']."\t".$i['format']."\t".implode("\t",GHDate2YMD($i['date'],$i['format']))); 
	}
return $Res;
}

function GHPosix2Date($posix,$format='Y-m-d H:i') {
global $GHCfg,$GHApp;		
	if ($posix=='') 
		return;
return date($format,$posix);
}

function GHDateFormat($date,$format='') {
	$date = GHPosix2Date(GHDate2Posix($date),$format);
	$days= array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
	if ($GLOBALS['GHLang']=='FR') {
		$date = str_replace ( $days,array('Lundi', 'Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'),$date);
		$date = str_replace ( $months,array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'),$date);
	}
return $date ;
}

// obsolete
function GHPosix2Time($posix) {
return GHSeconds2Time($posix);
}

function GHSeconds2Time($posix) {
return sprintf("%d:%02d:%02d",($posix)/3600, ($posix % 3600)/60,$posix % 60 );
}

function GHYMD2Posix($Date) {
	list($Y,$M,$D,$h,$m,$s) = $Date;
return mktime($h,$m,$s,$M,$D,$Y);
}

function GHPosix2YMD($posix,$TZ='') {
	if ($TZ!='') {
		$default = date_default_timezone_get();
		if ($TZ==$default)
			$TZ='';
		else 
			$TZ = GHSetTimezone($TZ);			
	}
	list($s,$m,$h,$D,$M,$Y,) = localtime($posix);
	if ($TZ!='')
		date_default_timezone_set($default);
return array($Y+1900,$M+1,$D,$h,$m,$s);
}
// fonction de recherche de timezone
function  GHSetTimezone($tz) {
	if (@date_default_timezone_set($tz))
		return $tz;
# Sinon on tente une recherche
	$TZ = timezone_identifiers_list();
	$tz = str_replace(array('-','_'),array('',''),strtolower($tz));
	foreach (timezone_identifiers_list() as $t) {
		list($country,$city) = explode('/',strtolower($t));
		if (str_replace(array('-','_'),array('',''),strtolower($city))==$tz) {
			date_default_timezone_set($t);
			return $t;
		}
	}
// sinon on est toujours en heure locale (et non pas GMT!)
return date_default_timezone_get();
}

// transformation d'une date de fuseau en date locale
function GHDate2local($date,$timezone) {
	$default = date_default_timezone_get();
	$tz = GHSetTimezone($timezone);
	// on ramene la date en GMT
	$GMT = gmdate('Y/m/d/H/i/s',GHDate2Posix($date));
	list ($Y,$M,$D,$h,$m,$s) = explode('/',$GMT);
	date_default_timezone_set($default);
	$local = gmmktime($h,$m,$s,$M,$D,$Y);
	list($s,$m,$h,$D,$M,$Y) = localtime($local);
	return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$Y+1900,$M+1,$D,$h,$m,$s);
}

/* Fonction de conversion avec le niveau inferieur */
function GHYMD2Str($Date) {
	list($Y,$M,$D,$h,$m,$s) = $Date;
return sprintf("%04d%02d%02d%02d%02d%02d",$Y,$M,$D,$h,$m,$s);
}

function GHNowStr() {
		list($s,$m,$h,$D,$M,$Y) = localtime();
return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$Y+1900,$M+1,$D,$h,$m,$s);
}

function GHTimestamp() {
return mktime();
}

function GHDate($date) {
return str_replace(array('/','-',':',' '),array('','','',''),$date);
}


function GH_DaysOfWeek($arg,$Data) {
	$days = array('su','mo','tu','we','th','fr','sa','su');

	$Default = array(
		'out' => 'dd'
	);
	
	$Args	=	GH_GetArgs($arg,$Default);
	$Infos = GH_GetArray($Args,$Data);
	$Res = array($Args['out']);
	foreach ($Infos as $i) {
		$date = str_replace(array('/','-','.',' ',':'),array('','','','',''),$i['date']);
		$day = GHTCDayOfWeek($date);
		$day = $days[$day];
		if (!in_array($day,$Res)) 
			array_push($Res,$day);
	}
return $Res;
}

// Extrait les informations de t�ches
// Les taches recencense l'ensemble des objets
// TSK est une tache theorique a effectuer
// JOB est un lien vers la definition
function GHSimpleTime($posix) {
	if ($posix=='') return;
	$U = array( 
		'Y' => 365.25*24*3600,
		'M' => 	30*24*3600,
		'W' => 	7*24*3600,
		'D' => 24*3600,
		'h' => 3600,
		'm' => 60,
		's' => 1 );
		
	$date = '';
	foreach ($U as $k=>$v) {
		$t = (int) ($posix/$v);
		if ($t>0) {
			$date .= "$t$k ";
		}
		$posix -= $t*$v;
	}
	
return $date;
}

// met en forme une chaine en date
// on ne gere pas les dates textes
// car ca devrait etre gere dans le module LANG
function GHStr2Date($date,$format='YYYY-MM-DD hh:mm:ss') {
return GHYMD2Date(GHStr2YMD($date),$format);
}

function GHStr2YMD($date,$format='YYYY-MM-DD hh:mm:ss') {
	// il ne devrait y avoir qu'un espace
	$date = trim($date);
	$d=''; $t=''; $f='';	
	// est ce qu'il y a une fraction
	if (($r = strpos($date,'.'))>0) {
		list($date,$frac) = explode('.',$date);
		$Res['c'] = $frac;
	}
	$Res['Y'] = 0;
	$Res['M'] = 1;
	$Res['D'] = 1;
	$Res['h'] = 0;
	$Res['m'] = 0;
	$Res['s'] = 0;
	// est ce qu'il y a une date et une heure ?
	$Info = explode(' ',$date);
	foreach ($Info as $i) {
		// est ce qu'il y a des : ?
		if (strpos($i,':')) {
			// c'est une heure
			$H = split(':',$i);
			if (count($H)>2) 
			  $Res['s'] = $H[2];
			if (count($H)>1) 
			  $Res['m'] = $H[1];
			if (count($H)>0) 
			  $Res['h'] = $H[0];
		}
		else {
			// c'est une date
			// on decoupe par ce qui n'est pas un numerique
			$Date = array(); $last = '';
			for ($d=0;$d<strlen($i);$d++) {
			    $c = substr($i,$d,1);
			    if (strpos(' 0123456789',$c))
			      $last .= $c;
			    else {
			      if ($last != '') 
				array_push($Date,$last);
			      $last = '';
			    }
			}
		      if ($last != '') 
			array_push($Date,$last);

			if ($last != '') 
			  array_push($Date,$last);
			if (isset($Date[0])) 
			  $Res['Y'] = $Date[0];
			if (isset($Date[1])) 
			  $Res['M'] = $Date[1];
			if (isset($Date[2])) 
			  $Res['D'] = $Date[2];
		}
	}
return array(  $Res['Y'], $Res['M'], $Res['D'], $Res['h'], $Res['m'], $Res['s'], $Res['c'] ); 
}


function GHDate2ISO($date) {
		$Y = (int) substr($date,0,4);
		$M = (int) substr($date,4,2);
		$D = (int) substr($date,6,2);
		$h = (int) substr($date,8,2);
		$m = (int) substr($date,10,2);
		$s = (int) substr($date,12,2);
return sprintf("%04d-%02d-%02d %02d:%02d:%02d",$Y,$M,$D,$h,$m,$s);
}

function GHCleanDate($date) {
	$date = str_replace(' ','',$date);
	$date = str_replace('-','',$date);
	$date = str_replace('/','',$date);
	$date = str_replace(':','',$date);
return $date;
}

function GHTrimDate($date) {
	$date = GHCleanDate($date);
	$l = strlen($date)-1;
	while ($date[$l]=='0') $l--;
return substr($date,0,$l+1);
}


function GHYear($date) {
		$y = substr($date,0,4);
		if (($y<1970) or ($y>2100)) {
			$time = localtime();
			$y = 1900+$time[5];
		}
		
return $y;
}
?>