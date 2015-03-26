<?php
class Fonctions_XmlDemarche{
	
	private $demarche_label;
	private $demarche_id;
	private $bloc1_ab;
	private $specialite;
	private $version;
	private $xml;
	
	public function __construct($demarche_id, $version, $bloc1_ab, $specialite,$type_capacite = ''){
		
		$this->version = $version;
		$this->demarche_id = $demarche_id;
		$this->bloc1_ab = $bloc1_ab;
		$this->specialite = $specialite;
		$this->type_capacite = $type_capacite;
		
	}
	
	public function getDemarche(){
		switch($this->demarche_id){
			case 1:
				$this->demarche_label = 'CQP';
				$this->parseXml();
				return $this->getCqp($this->bloc1_ab, $this->specialite);
				break;
			case 2:
				$this->demarche_label = 'DIPLOME';
				$this->parseXml();
				return $this->getDiplome($this->bloc1_ab, $this->specialite);
				break;
			case 3:
				$this->demarche_label = 'CCSP';
				$this->parseXml();
				return $this->getCcsp($this->bloc1_ab);
				break;
			case 4:
				$this->demarche_label = 'CQPBRANCHE';
				$this->parseXml();
				return $this->getCqpBranche($this->bloc1_ab, $this->specialite);
				break;
		}
	}
	
	private function parseXml(){
		$this->xml = simplexml_load_file('./xml/'.strtoupper($this->demarche_label).'_'.$this->version.'.xml', "SimpleXMLElement", LIBXML_NOCDATA);
	}
	
	
	

	private function getCqpBranche($bloc1_ab, $specialite){
		foreach($this->xml->cqpbranche as $currentCqp){
			foreach($currentCqp->attributes() as $key => $value){
				if((string) $value == $bloc1_ab){
					$cqp = $currentCqp;
					$numero_cqp = 'B'.$cqp['num'];
					$numero_specialite = 'B'.$specialite;
					break 2;
				}
			}
		}
		$question_base_total = array();
		$livret_base = array();
		$question_base	= array();
		$capacites_base = array();
	
		if($numero_specialite ==''){$numero_specialite =0;}
		//var_dump($capacites);
	
	
		$query_titre = "SELECT * FROM pmqvision5.titres
			WHERE `num_cqp_xml` ='".$numero_cqp."' AND num_spe_xml='".$numero_specialite."'";
		$result_titre = mysql_query($query_titre);
		$compte_titre = 0;
		while($row_titre = mysql_fetch_array($result_titre))
		{
			$id_titre = $row_titre["id_titres"];
		}
		if($this->type_capacite !='')
		{
			$type_capacite_sql = " AND classement_capacite.type_capacite like '".$this->type_capacite."'";
		}else{
			$type_capacite_sql = "";
		}
		$query_capacite = "SELECT * FROM pmqvision5.capacites LEFT JOIN pmqvision5.classement_capacite
			ON capacites.nom_capacite =classement_capacite.nom_capacite  WHERE  (`id_titres` ='".$id_titre."'
			AND `outils` LIKE 'entretien' ".$type_capacite_sql." AND capacites.nom_capacite NOT LIKE 'C999')
			GROUP BY ordre_general
			ORDER BY  ordre_general
;
			";
		$result_capacite = mysql_query($query_capacite);
		$compte_capacite=0;
		$compte_capacite_aquis = 0;
		while($row_capacite = mysql_fetch_array($result_capacite))
		{
			$capacites_base[$compte_capacite]['nom'] = $row_capacite["nom_capacite"];
			$capacites_base[$compte_capacite]['num'] = $row_capacite["id_capacite"];
			$capacites_base[$compte_capacite]['intitule_question'] = utf8_encode($row_capacite["intitule_question"]);
			$capacites_base[$compte_capacite]['position'] = $row_capacite["position"];
			$capacites_base[$compte_capacite]['id_titres'] = $row_capacite["id_titres"];
			$capacites_base[$compte_capacite]['type_capacite'] = $row_capacite["type_capacite"];
			$capacites_base[$compte_capacite]['ordre_individuel'] = $row_capacite["ordre_individuel"];
			$capacites_base[$compte_capacite]['type_acquis'] = $row_capacite["type_acquis"];
			$capacites_base[$compte_capacite]['ordre_general'] = $row_capacite["ordre_general"];
			$capacites_base[$compte_capacite]['capacite'] = utf8_encode($row_capacite["intitule_capacite"]);
			$compte_capacite++;
		}
		unset($cqps);
		$infosXml['capacites_base'] =  $capacites_base;
		return $infosXml;
	}
	
	
	
	private function getCqp($bloc1_ab, $specialite){
		foreach($this->xml->cqp as $currentCqp){
			foreach($currentCqp->attributes() as $key => $value){
				if((string) $value == $bloc1_ab){
					$cqp = $currentCqp;
				$numero_cqp = $cqp['num'];
				$numero_specialite = $specialite; 
				
				

					
					break 2;
				}
			}
		}
	$question_base_total = array();
	$livret_base = array();
	$question_base	= array();
	$capacites_base = array();
		
if($numero_specialite ==''){$numero_specialite =0;}
		//var_dump($capacites);
		

			$query_titre = "SELECT * FROM pmqvision5.titres 
			WHERE `num_cqp_xml` ='".$numero_cqp."' AND num_spe_xml='".$numero_specialite."'";
			$result_titre = mysql_query($query_titre);
			$compte_titre = 0;	
			while($row_titre = mysql_fetch_array($result_titre))
								{
									$id_titre = $row_titre["id_titres"];
								}
		if($this->type_capacite !='')
		{
			$type_capacite_sql = " AND classement_capacite.type_capacite like '".$this->type_capacite."'";
		}else{
			$type_capacite_sql = "";
		}
			$query_capacite = "SELECT * FROM pmqvision5.capacites LEFT JOIN pmqvision5.classement_capacite 
			ON capacites.nom_capacite =classement_capacite.nom_capacite  WHERE  (`id_titres` ='".$id_titre."' 
			AND `outils` LIKE 'questionnaire' ".$type_capacite_sql." AND capacites.nom_capacite NOT LIKE 'C999')
			OR (`id_titres` ='".$id_titre."' AND `outils` LIKE 'livret'  ".$type_capacite_sql." AND capacites.nom_capacite NOT LIKE 'C999')
			OR (`id_titres` ='".$id_titre."' AND `outils` LIKE 'observation'  ".$type_capacite_sql." AND capacites.nom_capacite NOT LIKE 'C999')
			GROUP BY ordre_general 
			ORDER BY  ordre_general 
;
			";
			$result_capacite = mysql_query($query_capacite);
			$compte_capacite=0;	
			$compte_capacite_aquis = 0;
			while($row_capacite = mysql_fetch_array($result_capacite))
								{
													$capacites_base[$compte_capacite]['nom'] = $row_capacite["nom_capacite"];
													$capacites_base[$compte_capacite]['num'] = $row_capacite["id_capacite"];
													$capacites_base[$compte_capacite]['intitule_question'] = utf8_encode($row_capacite["intitule_question"]);
													$capacites_base[$compte_capacite]['position'] = $row_capacite["position"];
													$capacites_base[$compte_capacite]['id_titres'] = $row_capacite["id_titres"];
													$capacites_base[$compte_capacite]['type_capacite'] = $row_capacite["type_capacite"];
													$capacites_base[$compte_capacite]['ordre_individuel'] = $row_capacite["ordre_individuel"];
													$capacites_base[$compte_capacite]['type_acquis'] = $row_capacite["type_acquis"];
													$capacites_base[$compte_capacite]['ordre_general'] = $row_capacite["ordre_general"];
													$capacites_base[$compte_capacite]['capacite'] = utf8_encode($row_capacite["intitule_capacite"]);
													$compte_capacite++;									
								}							
								
								
			$query_question = "SELECT * FROM pmqvision5.capacites 
			LEFT JOIN pmqvision5.classement_capacite 
			ON capacites.nom_capacite =classement_capacite.nom_capacite  WHERE `id_titres` ='".$id_titre."' 
			AND `outils` LIKE 'questionnaire' 
			GROUP BY position 
			ORDER BY  position ";
			$result_question = mysql_query($query_question);
			$compte_question=0;	
			while($row_question = mysql_fetch_array($result_question))
								{
									$question_base[$compte_question]['nom'] = utf8_encode($row_question["nom_capacite"]);
									$question_base[$compte_question]['position'] = $row_question["position"];
									$question_base[$compte_question]['note'] = $row_question["note"];
									$question_base[$compte_question]['id_titres'] = $row_question["id_titres"];
									$question_base[$compte_question]['type_capacite'] = $row_question["type_capacite"];
									$question_base[$compte_question]['ordre_individuel'] = $row_question["ordre_individuel"];
									$question_base[$compte_question]['type_acquis'] = $row_question["type_acquis"];
									$question_base[$compte_question]['ordre_general'] = $row_question["ordre_general"];
									$question_base[$compte_question]['intitule_question'] = utf8_encode($row_question["intitule_question"]);
									$question_base[$compte_question]['capacite'] = utf8_encode($row_question["intitule_capacite"]);
									$compte_question++;
								}
								
	 		$query_livret = "SELECT * FROM pmqvision5.capacites 
			LEFT JOIN pmqvision5.classement_capacite 
			ON capacites.nom_capacite =classement_capacite.nom_capacite  WHERE `id_titres` ='".$id_titre."' 
			AND `outils` LIKE 'livret' 
			AND classement_capacite.type_capacite = 'aquis'
			GROUP BY position 
			ORDER BY  position ";
			$result_livret = mysql_query($query_livret);
			$compte_livret=0;	
			while($row_livret = mysql_fetch_array($result_livret))
								{
									$livret_base[$compte_livret]['nom'] = utf8_encode($row_livret["nom_capacite"]);
									$livret_base[$compte_livret]['position'] = $row_livret["position"];
									$livret_base[$compte_livret]['note'] = $row_livret["note"];
									$livret_base[$compte_livret]['id_titres'] = $row_livret["id_titres"];
									$livret_base[$compte_livret]['type_capacite'] = $row_livret["type_capacite"];
									$livret_base[$compte_livret]['ordre_individuel'] = $row_livret["ordre_individuel"];
									$livret_base[$compte_livret]['type_acquis'] = $row_livret["type_acquis"];
									$livret_base[$compte_livret]['ordre_general'] = $row_livret["ordre_general"];
									$livret_base[$compte_livret]['intitule_question'] = utf8_encode($row_livret["intitule_question"]);
									$livret_base[$compte_livret]['capacite'] = utf8_encode($row_livret["intitule_capacite"]);
									$compte_livret++;
								}						
																
	
			$query_question_total = "SELECT SUM(note) AS total ,COUNT(nom_capacite) as nb_capacite FROM pmqvision5.capacites 
			WHERE `id_titres` ='".$id_titre."' 
			AND `outils` LIKE 'questionnaire' 
			GROUP BY nom_capacite
			ORDER BY  position ";
			$result_question_total = mysql_query($query_question_total);
			$compte_question_tolal=0;	
			while($row_question_total = mysql_fetch_array($result_question_total))
								{
									$question_base_total[$compte_question_tolal]['total']= utf8_encode($row_question_total["total"]);
									$question_base_total[$compte_question_tolal]['nb_capacite']= utf8_encode($row_question_total["nb_capacite"]);
									$compte_question_tolal++;
								}				
						
		unset($cqps);
		$infosXml['capacites_base'] =  $capacites_base;
		$infosXml['livret_base'] =  $livret_base;
		$infosXml['capacites_total_base'] =  $question_base_total;
		$infosXml['question_base'] =  $question_base;

		return $infosXml;
	}

	private function getDiplome($bloc1_ab, $specialite,$module =0){
	
		
		foreach($this->xml->diplome as $currentDiplome){
			foreach($currentDiplome->attributes() as $key => $value){
				if((string) $value == $bloc1_ab){
					$diplome = $currentDiplome;
					$numero_diplome= $diplome['num'];
					$numero_specialite = $specialite;
		
		
		
						
					break 2;
				}
			}
		}
		
		$liste_unite = array();
		
		$liste_module = array();
		
		$sql_module  = "SELECT * from pmqvision5.diplomemodule 
		LEFT JOIN pmqvision5.diplomeunite ON pmqvision5.diplomemodule.id_diplome_unite = pmqvision5.diplomeunite.id_diplome_unite
		LEFT JOIN pmqvision5.diplometitre ON pmqvision5.diplomemodule.id_titre = pmqvision5.diplometitre.id_diplome_titre
		WHERE pmqvision5.diplometitre.num_diplome_xml = '".$numero_diplome."' 
		 ORDER BY pmqvision5.diplomeunite.classement, pmqvision5.diplomemodule.classement
		
		";
		
		
		$result_module = mysql_query($sql_module);
		$compte_module = 0;
		while($row_module = mysql_fetch_array($result_module))
		{
		
			
			$liste_module[$compte_module]['nom_module'] = utf8_encode($row_module['nom_module']);
			$liste_module[$compte_module]['nb_question'] = utf8_encode($row_module['nb_question']);
			$liste_module[$compte_module]['questionnaire'] = utf8_encode($row_module['questionnaire']);
			$liste_module[$compte_module]['observation'] = utf8_encode($row_module['observation']);
			$liste_module[$compte_module]['technique'] = utf8_encode($row_module['technique']);
			$liste_module[$compte_module]['bilan'] = utf8_encode($row_module['bilan']);
			$liste_module[$compte_module]['nom_unite'] = utf8_encode($row_module['nom_unite']);
			$liste_module[$compte_module]['numero_unite'] = utf8_encode($row_module['numero_unite']);
			$liste_module[$compte_module]['id_unite'] = utf8_encode($row_module['id_diplome_unite']);
			
			$compte_module++;
			
		}
		
		
		//$infosXml['liste_unite'] = $liste_unite;
		
		$infosXml['liste_module'] = $liste_module;
		$infosXml['diplome_num']= $numero_diplome;
		return $infosXml;
		
	}
	
	private function getCcsp($bloc1_ab){
		foreach($this->xml->ccsp as $current){
			foreach($current->attributes() as $key => $value){
				if((string) $value == $bloc1_ab){
					$ccsp = $current;
					$questionnaire = trim( $ccsp->questions );
					$modules = $ccsp->modules;
					$ventilation = $ccsp->ventilations->ventilation;
				}
			}
		}
			$query_module = "SELECT * FROM pmqvision5.modules WHERE notation ='oui'  ORDER BY ordre";
			$result_module = mysql_query($query_module);
			$compte_savoir=0;	
					while($row_module = mysql_fetch_array($result_module))
						{
							$query_savoir = "SELECT * FROM pmqvision5.savoirs WHERE code_module LIKE '".$row_module["code"]."' AND (support_eval = 'observation' OR support_eval = 'question') ORDER BY code";
							$result_savoir = mysql_query($query_savoir);
								while($row_savoirs = mysql_fetch_array($result_savoir))
								{
									$listeQuestions[$compte_savoir]['name'] = $row_savoirs['code'].utf8_encode($row_savoirs['code_module']).' - '.utf8_encode($row_savoirs['libelle_savoir']);
									$listeQuestions[$compte_savoir]['value'] = $row_savoirs['note'];
									$listeQuestions[$compte_savoir]['module'] = $row_savoirs['code_module'];
									$listeQuestions[$compte_savoir]['support_eval'] = $row_savoirs['support_eval'];
									$listeQuestions[$compte_savoir]['libelle_module'] = utf8_encode($row_module['libelle_module']);
									$compte_savoir++;
								}
						}
	       
		/*
		foreach( $temp as $index => $t ){
			$question = explode( ':', $t );
			$listeQuestions[$index]['name'] = 'test';
			$listeQuestions[$index]['value'] = $question[1];
		}
		*/
						
						
		$infosXml['questions'] = $listeQuestions;
		$infosXml['observations'] = $listeQuestions;
		$infosXml['entretiens'] = $listeQuestions;
		$infosXml['ventilation'] = $ventilation;
		$infosXml['modules'] = $modules;

		return $infosXml;
	}

	
}