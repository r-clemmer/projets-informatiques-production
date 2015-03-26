<?php
class Fonctions_XmlDemarche{
	
	private $demarche_label;
	private $demarche_id;
	private $demarche_abrege;
	private $specialite;
	private $version;
	private $xml;
	
	public function __construct($demarche_id, $version, $demarche_abrege, $specialite){
		
		$this->version = $version;
		$this->demarche_id = $demarche_id;
		$this->demarche_abrege = $demarche_abrege;
		$this->specialite = $specialite;
		
	}
	
	public function getDemarche(){
		switch($this->demarche_id){
			case 1:
				$this->demarche_label = 'CQP';
				$this->parseXml();
				return $this->getCqp($this->demarche_abrege, $this->specialite);
				break;
			case 2:
				$this->demarche_label = 'DIPLOME';
				$this->parseXml();
				break;
			case 3:
				$this->demarche_label = 'CCSP';
				$this->parseXml();
				return $this->getCcsp($this->demarche_abrege, $this->specialite);
				break;
		}
	}
	
	private function parseXml(){
		$this->xml = simplexml_load_file('./xml/'.strtoupper($this->demarche_label).'_'.$this->version.'.xml', "SimpleXMLElement", LIBXML_COMPACT);
	}
	
	private function getCqp($cqp_libelle, $specialite){
		foreach($this->xml->cqp as $currentCqp){
			foreach($currentCqp->attributes() as $key => $value){
				if((string) $value == $cqp_libelle){
					$cqp = $currentCqp;
					foreach($currentCqp->ventilations->ventilation as $questionnaire){
						//echo 'spec : '.$questionnaire['num_specialite'].' ';
						
						if((string)$questionnaire['num_specialite'] == $specialite){
							
							$question = $questionnaire;
							$capacites = $currentCqp->capacites;
							break;
						}
					}
					break 2;
				}
			}
		}
		unset($cqps);
		$listeQuestion = explode('@',$questionnaire['totaux']);
		$infosXml['questions'] = $listeQuestion;
		$infosXml['capacites'] = $capacites;
		
		//Zend_Debug::dump($infosXml['questions']);
		
		return $infosXml;
	}
	
	private function getCcsp($ccsp_libelle, $specialite){
		
	}
}