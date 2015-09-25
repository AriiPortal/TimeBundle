<?php

array_push($GHModInfo,
	array(	'name' => 'TimeCode',
  'author' => 'E. Angenault',
  'desc' => 'Date calculator',
  'ver'	 => '3.0',
  'status' => '',
  'use' => 'Dates',
  'publish' => 'TCRef,GH_TCRef,GH_TCCal',
  'history' => array (
    "2010/03/31 13:45	Documentation"
  )
	)
);

function GHCheckModuleTimecode() {
return 'ok';
}
function TCRef($rule,$year='',$we='67') {

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
							else						$s = 1;
						do {
							$date = GHTCMoveDay($date,$s);
							$dow = GHTCDayOfWeek($date);
						} while ($index!=$dow);
						break;
					}
				break;
					// mois dans ann�e chinoise
					case 'c':
						switch ($unit) {
							// trouver le mois chinois
							case 'M':	
								list( $y,$m,$d, $hour, $minutes, $seconds ) = GHTCChineseMonth($year,$index);
								$date = GHTCYMD2Date( $y,$m,$d, $hour, $minutes, $seconds );
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
							$date = GHTCMoveDay($date,$t);
							if ($t==0) $t=1*$s;
							$dow = GHTCDayOfWeek($date);
							$n++;
						} while (strpos(" $we","$dow")>0);
						// cas du "au plus proche"
						if ($sens=='=') {
							// on calcule la date inverse
							$s *= -1;
							$t = $times * $s; $date2 = $begin; $n2= 0;
							do {
								$date2 = GHTCMoveDay($date2,$t);
								if ($t==0) $t=1*$s;
								$dow = GHTCDayOfWeek($date2);
								$n2++;
							} while (strpos(" $we","$dow")>0);
							// si n2 est inferieur a n, la date devient la nouvelle date
							if ($n2<$n)
								$date = $date2;
						}
						break;
					case 'D':
						if ($sens=='-') $times *= -1;
						$date = GHTCMoveDay($date,$times);
						break;
					case 'L':
						if ($sens=='-') $times *= -1;
						if ($year % 4==0) $times=1;
						else $times=0;
						$date = GHTCMoveDay($date,$times);
						break;
					case '@':
						// la on teste si une liste est bufferis�e ou si on applique la fonction
						// La date de paques ?
							print "(($period))";
						if ($period=='E') { 
							list($y,$m,$d) =  GHEasternday($year);
							$date = GHTCYMD2Date( $y,$m,$d, 0, 0, 0 ); 
						}
						elseif ($period=='S') {
							list($y,$m,$d) = GHGetSeason($year,$unit);
							$date = GHTCYMD2Date( $y,$m,$d, 0, 0, 0 ); 
						}
						// Calendrier lunaire
						// pas le temps de faire mieux
						elseif ($period=='L') { 
							$Lunar = array( 2007	=> GHTCYMD2Date( 2007, 2, 18, 0, 0, 0 ),
															2008	=> GHTCYMD2Date( 2008, 2, 7, 0, 0, 0 ),
															2009	=> GHTCYMD2Date( 2009, 1, 26, 0, 0, 0 ),
															2010	=> GHTCYMD2Date( 2009, 2, 14, 0, 0, 0 ),
															2011	=> GHTCYMD2Date( 2011, 2, 3, 0, 0, 0 ),
															2012	=> GHTCYMD2Date( 2011, 1, 23, 0, 0, 0 ),
															2013	=> GHTCYMD2Date( 2008, 2, 10, 0, 0, 0 ),
															2014	=> GHTCYMD2Date( 2008, 1, 31, 0, 0, 0 ),
															2015	=> GHTCYMD2Date( 2008, 2, 19, 0, 0, 0 ),
															2016	=> GHTCYMD2Date( 2008, 2, 8, 0, 0, 0 ),
															2017	=> GHTCYMD2Date( 2008, 1, 28, 0, 0, 0 ),
															2018	=> GHTCYMD2Date( 2008, 2, 16, 0, 0, 0 ),
															2019	=> GHTCYMD2Date( 2008, 2, 5, 0, 0, 0 ),
															2020	=> GHTCYMD2Date( 2008, 1, 25, 0, 0, 0 )
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

// Calcul du referentiel
// Le referentiel est un calendrier constitue d'une suite de regles
function GH_TCRef($args,$Data) {

	$Now = getdate();
	$Default = array (
		'we'	=> '67',
		'rule' => '',
		'year' => $Now['year'],
		'years'=> 1
	);
	$Args	=	GH_GetArgs($args,$Default);
	$we = $Args['we'];
	
	$Infos = GH_GetArray(array(),$Data);
	if (count($Data)==0) {
		GHSysLog(
			array(	"Module"	=> basename(__FILE__),
				"Line"	=>	__LINE__,
				"Type"	=>	'E',
				"Error" => 	'Not Found',
				"Data" => 'NO DATE' )
			);

		return;
	}
	
	$Res = array('rule	year	date	desc');
	for($year=$Args['year'];$year<$Args['year']+$Args['years'];$year++) {
		foreach ($Infos as $i) {
			$rule = trim($i['rule']);
			if ($rule=='') continue;
			
			if (isset($i['we']) && ($i['we']!=''))
				$we = $i['we'];
				
			$date = TCRef($rule,$year,$we);
			
			if ($date != '') 
				array_push($Res,$i['rule']."	$year	$date	".$i['desc']);
		}
	}	
return $Res;
}

// Les dates sont au format YYYYMMDDhhmmss
// Les calculs se font en JULIEN
function GH_TCCal($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'we'	=> '67',
		'rule' => '',
		'year' => $Now['year'],
		'years'=> 1,
		'type' => 'woc'
	);
	$Args	=	GH_GetArgs($args,$Default);

	if (($Args['year']<2000) || ($Args['rule']=='')) {
		GHSysLog(
			array(	"Module"	=> basename(__FILE__),
				'Line'	=>	__LINE__,
				'Type'	=>	'E',
				'Error' => 	'Not Found',
				'year' => $Args['year'],
				'rule'=> $Args['rule'] )
			);
		return;
	}

	# On initialise lma week-ends par defaut 
	$we = $Args['we'];
	
	# On indique les jours de planification
	# si le bloc contient des dates
	if (count($Data)>0) {
		$Infos = GH_GetArray($Args,$Data);

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
		$sy = (int) $Args['year'];
		$nb = (int) $Args['years'];
		$first = GregoriantoJd(1,1,$sy-1);
		$end = GregoriantoJd(12,31,$sy+$nb);
		for($i=$first;$i<=$end;$i++) {
			$Date = cal_from_jd($i,CAL_GREGORIAN);
			$date = GHTCYMD2Date($Date['year'],$Date['month'],$Date['day'],0,0,0);
			$InitDay[$date]='o';
		}
	}
	$rule = $Args['rule'];

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
			
			$ClosedDays = GHTCGetList($base);
			foreach ($ClosedDays as $i) {
				if (isset($i['date'])) {
					$d = $i['date'];
					$Day[$d]='c';

				}
			}
		}

			# On met en place les week-end
			foreach (array_keys($Day) as $d) {
				$j = GHTCDayOfWeek($d);
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

		GHSetVar('TCName',$rule);
		# Tant que ca ressemble a une regle
		$iter=0;
		while ((strlen($rule)>0 && preg_match('/^(\!*)([\+\-\=\_\#]{0,1})(\d*[\~\,\!\d]*)([@BDMYhms]{0,1})([\d!]*)([A-Za-z]*)([\d\~\,\!]*)/',$rule,$Match) && ($iter<10))) {
			$iter++;
			array_shift($Match);

			list($reverse,$sens,$times,$unit,$group,$period,$index) = $Match;
			$todo = implode("",$Match);

		GHSysLog(
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
				$Daylist = GHTCGetList($period);
				
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
								$Index = GHTCIndex($index,0,24);
								foreach (array_keys($Day) as $d) {
									if ($Day[$d]=='o') {
										$Day[$d]='';
										foreach($Index as $h) {
											if ($h==24)
												$h=0;
											$d = GHTCSetHour($d,$h);
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
								$Index = GHTCIndex($index,0,60);
								foreach (array_keys($Day) as $d) {
									if ($Day[$d]=='o') {
										$Day[$d]='';
										foreach($Index as $min) {
											$d = GHTCSetMinutes($d,$min);
											$Day[$d]='o';
										}
									}						
								}
							break;					
						}
						break;
	
					case 'W':
						# L'index est sur 7 jours
						$Index = GHTCIndex($index,1,7);
						switch ($unit) {
							case 'D':
								switch ($sens) {
									case '_':
										foreach (array_keys($Day) as $d) {
											$j = GHTCDayOfWeek($d);
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
											$j = GHTCDayOfWeek($d);
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
												while (!in_array(GHTCDayOfWeek($d),$Index)) {
													$d = GHTCNextDay($d);
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
										$j = GHTCWeekOfYear($d);
										
										# Si le mois est diff�rent du dernier, on reinitialise
										if ($j!=$last) {
											$last = $j;
											$Day = GHTCProcess($index,$Day,$Weeks);
											
											# On supprime la semaine pour traiter la suivante
											$Weeks = array();
										}
										array_push($Weeks,$d);
									}
								}
								$Day = GHTCProcess($index,$Day,$Weeks);
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
										$End = GHTCBusinessList($Base,$index);
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
													list($sy,$sm,$sd) = GHTCDate2YMD($d);
													list($ey,$em,$ed) = GHTCDate2YMD($Ends[$i]);
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
										$Day = GHTCBusinessList($Day,$index,'+');
										break;
									default:
										$Day = GHTCBusinessList($Day,$index);
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
										list($Y,$M,$D,$h,$m,$s) = GHTCDate2YMD($d);
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
										$eom = GHEndOfMonth($m,$y);
										$Index = GHTCIndex($index,1,$eom);
										for($j=1;$j<=$eom;$j++) {
											$date = GHTCYMD2Date($y,$m,$j,0,0,0);
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
										$Index = GHTCIndex($index,1,366);
										$j = GHTCDayOfYear($d);
										if (!in_array($j,$Index))
											$Day[$d]='';
									}
								}
								break;
							case 'B':
								// Le ni�me jour ouvr� de l'ann�e
								$Index = GHTCIndex($index,0,366);
								// Attention, dans le cas < 1, c'est l'ann�e pr�c�dente
								$ly = -1;
								foreach (array_keys($Day) as $d) {
									list($y,$m,$j) = GHTCDate2YMD($d);
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
								$Index = GHTCIndex($index,1,12);
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
											$m = GHTCGetMonth($d);
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
										list($sy,$sm,$sd) = GHTCDate2YMD($d);
										if (in_array($sm,$Index)) {
											for($i=1;$i<=GHEndOfMonth($sm,$sy);$i++) {
												$new = GHTCYMD2Date($sy,$sm,$i,0,0,0);
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
										$day = GHTCMoveDay($d,$times);
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
								$Index = GHTCIndex($times,0,999);
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
													list($sy,$sm,$sd) = GHTCDate2YMD($d);
													list($ey,$em,$ed) = GHTCDate2YMD($new);
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

	$Res = array("rule	date	type");
	$K = array_keys($Last);
	sort($K);
	foreach ($K as $k) {
		$y = (int) substr($k,0,4);
		if (($y>=$Args['year']) && ($y<$Args['year']+$Args['years']) && ( strpos(' '.$Args['type'],$Last[$k])>0 ))
			array_push($Res,$Args['rule']."\t$k\t".$Last[$k]);
	}

return $Res;
}

# Cette fonction renvoit la liste des jours ouvr�s d'un mois � partir d'un index
# On ajoute le se
function GHTCBusinessList($Day,$index,$sens='_') {

	$i = 0; $last=0;
	# on peut traiter les differentes dates en comparant l'index
	# on doit comparer mois par mois
	$Mois = array();
	foreach (array_keys($Day) as $d) {
		# On ne traite que les jours ouvr�s
		if ($Day[$d]=='o') {
			# Mois 
			$m = GHTCGetMonth($d);
	
			# Si le mois est diff�rent du dernier, on reinitialise
			if ($m!=$last) {
				$last = $m;

				$Day = GHTCProcess($index,$Day,$Mois,$sens);
				# On supprime le mois pour traiter le suivant 
				$Mois = array();
			}
			array_push($Mois,$d);
		}
	}
	$Day = GHTCProcess($index,$Day,$Mois);
	
return $Day;
}

# Affcte les nouvelles valeurs indexees 
function GHTCProcess($index,$Day,$New,$sens='_') {
	if (count($New)==0) 
		return $Day;

	$Index = GHTCIndex($index,1,count($New),$sens);

	$i = 1;
	foreach ($New as $m) {
			if (!in_array($i,$Index))
				$Day[$m]='';
			$i++;
	}
return $Day;
}

function GHTCIndex($index,$min=1,$max=999,$sens='_') {
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
				$d = GHTCIndexLimit($R[0],$min,$max,$sens);
				array_push($Range,$d);
			}
		}
		else {
			if ($R[0]=='')
				$R[0]=$min;
			if ($R[1]=='')
				$R[1]=$max;
	
			$d = GHTCIndexLimit($R[0],$min,$max);
			$f = GHTCIndexLimit($R[1],$min,$max);
			
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
function GHTCIndexLimit($index,$min,$max,$sens='_') {

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

function GH_TCChecksum2($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year']
	);
	$Args	=	GH_GetArgs($args,$Default);

	$Check = '';
	$Infos = GH_GetArray($Args,$Data);
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
				$Check .= GHTCCheckAdd($p,$n);
				$n = 1;
				$p = $d;
			}
			else  {
				# jour suivant ? Si oui, on ajoute la periode
				if ($d-$ld==1) {
					$n++;
				}
				else {
					$Check .= GHTCCheckAdd($p,$n);
					$n = 1;
					$p = $d;
				}
			}
			$ld = $d;
			$lm = $m;
		}
	}
	$Check .= GHTCCheckAdd($p,$n);
	
return $Check;
}

function GH_TCChecksum($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year']
	);
	$Args	=	GH_GetArgs($args,$Default);
	
	$Infos = GH_GetArray($Args,$Data);
	$year = $Args['year'];
	$Check = array();
	foreach($Infos as $i) {
		if (isset($i['type']))
			$type=$i['type'];
		else
			$type='o';
		$d = GHCleanDate($i['date']);
		if ((substr($d,0,4)==$Args['year']) && ($type=='o')) {
			array_push($Check,substr($d,4));
		}
	}
return TCChecksum($Check);
}

// recalcul en fonction de la date, l'ensemble des jours est pris en compte
// en entree : date + type
// a priori 365/6 jours
function GH_TCCheckCal($args,$Data) {
global $GHCfg;
	$Now = getdate();
	$Default = array (
	);
	$Args	=	GH_GetArgs($args,$Default);
	
	$Infos = GH_GetArray($Args,$Data);
	
	$Res=array("id	year	checksum");
	foreach($Infos as $i) {
		if (isset($i['type']))
			$type=$i['type'];
		else
			$type='o';
		$d = GHCleanDate($i['date']);
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

function GHTCCheckAdd($p,$n) {
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

function GHTCDayOfYear($date) {
	list($a,$m,$j) = GHTCDate2YMD($date);
return GregorianToJD($m, $j, $a) - GregorianToJD(1, 1, $a)+1;
}

function GHTCDayOfWeek($date) {
return (GHTCDate2Julian($date) % 7)+1;
}

function GHTCMoveDay($date,$move) {
	list($Y,$M,$D,$h,$m,$s) = GHTCDate2YMD($date);
	$jd = GregorianToJD($M, $D, $Y)+$move+($h*3600+$m*60+$s)/86400;
return GHTCJulian2Date($jd);
}

// pas bonne si on depasse le mois !!!!
function GHTCMoveHour($date,$move) {
	list($Y,$M,$D,$h,$m,$s) = GHTCDate2YMD($date);
	$h += $move;
	$d = (int) ($h/24);
	$jd = GregorianToJD($M, $D, $Y)+($h*3600+$m*60+$s)/86400;
return GHTCJulian2Date($jd);
}

function GHEndOfMonth($m,$a) {
return cal_days_in_month(CAL_GREGORIAN, $m, $a); 
}

function GHTCGetMonth($date) {
return (int) substr($date,4,2);
}
function GHTCGetYear($date) {
return (int) substr($date,0,4);
}
function GHTCGetDay($date) {
return (int) substr($date,6,2);
}

function GHTCSetHour($date,$hour) {
	list($Y,$M,$D,$h,$m,$s) = GHTCDate2YMD($date);
return GHTCYMD2Date($Y,$M,$D,$hour,$m,$s);
}
function GHTCSetMinutes($date,$min) {
	list($Y,$M,$D,$h,$m,$s) = GHTCDate2YMD($date);
return GHTCYMD2Date($Y,$M,$D,$h,$min,$s);
}
///////////////////////////////// Conversion
// Date => YMD
function GHTCDate2YMD($date) {
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
function GHTCDate2Julian($date) {
	list($y,$m,$d) = GHTCDate2YMD($date);
return GregorianToJD($m,$d,$y);
}

// YMD => Date
function GHTCYMD2Date($Y,$M,$D,$h,$m,$s) {
return sprintf("%04d%02d%02d%02d%02d%02d",$Y,$M,$D,$h,$m,$s);
}

// YMD => Julian
function GHTCYMD2Julian($Y,$M,$D,$h,$m,$s) {
return GregorianToJD($M, $D, $Y);
}

// Julian => Date
function GHTCJulian2Date($julian) {
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

return GHTCYMD2Date($Y,$M,$D,$h,$m,$s);
}

// Next Day
function GHTCNextDay($date) {
	$j = GHTCDate2Julian($date);
	$j++;
return GHTCJulian2Date($j);
}

function GHPIsWE($posix,$we) {
	$day = (int) ($posix / (3600*24)) + 5;
	$dow = $day % 7;
	if (strpos($we,"$dow")===false)
		return 0;

return 1;
}

// Anneee
function GH_TCYear($args,$Data) {
global $GHCfg;

	$Now = getdate();
	$Default = array (
		'year'	=> $Now['year'],
		'we'	=> '67'
	);
	$Args	=	GH_GetArgs($args,$Default);

return array_merge(array('date'),GHTCFill($Args['we'],$Args['year'],$Args['year']));
}

function GHTCFill($we='67',$year,$yend,$mstart=1,$mend=12,$dstart=1,$dend=31) {
	$first = GregorianToJD($mstart,$dstart,$year);
	$end = GregorianToJD($mend,$dend,$yend);
	$Res = array();
	$dow = GHTCDayOfWeek($first);
	for($i=$first;$i<=$end;$i++) {
		$dow = ($i % 7)+1;
		if (!strpos(" $we","$dow")>0) {
			$Date = cal_from_jd($i,CAL_GREGORIAN);
			array_push($Res,GHTCYMD2Date($Date['year'],$Date['month'],$Date['day'],0,0,0));
		}
	}
return $Res;
}

function GHTCGetList($base) {
	if ($base=='') return array();
	
	if (!isset($GLOBALS['template'][$base]))
		return array();
	$Base = explode("\n",$GLOBALS['template'][$base]);
	$Infos = GH_GetArray(array(),$Base);

return $Infos;
}

function GHTCWeekOfYear($d) {
    $i = jdtounix(GHTCDate2Julian($d)); 
return date ('W', $i);
}

function GH_Form2TC($args,$Data) {

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

function GH_Gregorian2Jd($d,$m,$y,$gmt=0) {
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
function GH_GetSeason
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
function GHGetSeason($year,$n,$utc=0) {
	$file = str_replace('.php','',__FILE__).'/solstices_and_equinoxes.tsv';
	$Infos = GH_GetArray(array(),explode("\n",GHGetContent($file)));
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
		GHSysLog(
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

function GHGetNewMoon($year,$month=1,$day=1) {
	$file = str_replace('.php','',__FILE__).'/new_moons.tsv';
	$Infos = GH_GetArray(array(),explode("\n",GHGetContent($file)));
	foreach ($Infos as $i) {
		if ( ($i['year']==$year) && ($i['month']==$month) && ($i['day']==$day) ) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],0);
		}
	}
	GHSysLog(
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
function GHGetNextMoon($Y,$M=1,$D=1,$h=0,$m=0,$s=0,$n=1) {
	$file = str_replace('.php','',__FILE__).'/new_moons.tsv';
	$Infos = GH_GetArray(array(),explode("\n",GHGetContent($file)));
	// date de reference
	$ref = GHYMD2Posix( array($Y, $M, $D, $h, $m, $s ) );	
	$l=0;
	foreach ($Infos as $i) {
		$date = GHYMD2Posix( array( $i['year'], $i['month'], $i['day'], $i['hour'], $i['min'] ) );

		if ($date>$ref) $l++;
		
		if ($l==$n) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],0);
		}
	}
	GHSysLog(
		array(	"Module"	=> basename(__FILE__),
			"Line"	=>	__LINE__,
			"Type"	=>	'E',
			"Error" => 	'Out of range',
			"Year" => $year )
		);
return array(1970,1,1,0,0,0);
}

function GHGetChineseCal($year) {
	$file = str_replace('.php','',__FILE__).'/chinese_calendar.tsv';
	$Infos = GH_GetArray(array(),explode("\n",GHGetContent($file)));
	foreach ($Infos as $i) {
		if ($i['year']==$year) {
			return array($i['year'],$i['month'],$i['day'],$i['hour'],$i['min'],$i['sec']);
		}
	}
	GHSysLog(
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
function GHTCChineseMonth($year,$month) {
	// on trouve le solstice d'hiver precedent
	$YMD = GHGetSeason($year, 0);

	// on se decale de 8h pour avoir l'heure de Chine
	list ( $Y,$M,$D, $h, $m, $s) = GHPosix2YMD( GHYMD2Posix($YMD)+8*3600 );

	// mois embolismique
	$e = GHEmbol($year);

	// est ce qu'il y a une lune intercalaire ?
	$n=$month;
	if (($e > 0) and ($e < $month)) {
		$n++;
	}

	// on recupere la deuxieme prochaine lune
	$YMD = GHGetNextMoon($Y,$M,$D,$h,$m,$s,$n+1);

	// on decale de 8h
	$t = GHYMD2Posix($YMD)+8*3600;

return GHPosix2YMD($t);
}

// mois embolistique
function GHEmbol($year) {
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

?>