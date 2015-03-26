<?php

class Model_Resultat extends Zend_Db_Table_Abstract{
	
	protected $_name = 'resultat';
	protected $_primary = 'resultat_id';
	protected $_dependentTables = array(
		'Model_ResultatOutil',
		'Model_Formation'
	);
	protected $_referenceMap = array(
        'jury'	=> array(
            'columns'		=> 'jury_id',
            'refTableClass' => 'Model_Jury',
			'refColumns'	=> 'jury_id'
        ),
        'candidat_metier'	=> array(
            'columns'		=> 'candidat_metier_id',
            'refTableClass' => 'Model_CandidatMetier',
			'refColumns'	=> 'candidat_metier_id'
        )
    );

	public function getLast( $candidat_metier_id ){

		$lastpassage = $this->getLastPassage($candidat_metier_id);
		if( $lastpassage > 0 ) $lastpassage = $lastpassage;
		else $lastpassage = 0;

		$sql = "
			SELECT
				r.*
			FROM
				`resultat` as r
			WHERE
				r.candidat_metier_id = $candidat_metier_id
				AND r.resultat_num_passage = $lastpassage
		";
		return $resultat = $this->_db->query($sql)->fetch();
	}
    
    public function getResultatsCandidat($candidat_metier_id){

		if( $candidat_metier_id > 0 ){
			$reqLastResultat = $this->select()->setIntegrityCheck(false);
			$reqLastResultat->from('resultat AS r0', array('resultat_id'));
			$reqLastResultat->where('r0.candidat_metier_id = ?', $candidat_metier_id);
			$reqLastResultat->order('r0.resultat_id DESC');
			$lastResultat = $reqLastResultat->query()->fetch();

			if( !$lastResultat ) return false;

			$reqResultats = $this->select()->setIntegrityCheck(false);
			$reqResultats->from('resultat_outil AS ro');
			$reqResultats->where('ro.resultat_id = ?', $lastResultat['resultat_id']);

			return $reqResultats->query()->fetchAll(Zend_Db::FETCH_OBJ);
		}else{
			return false;
		}
    }

	public function getResultats($candidat_metier_id, $passage){

		if( $candidat_metier_id > 0 ){
			$reqLastResultat = $this->select()->setIntegrityCheck(false);
			$reqLastResultat->from('resultat AS r0', array('resultat_id'));
			$reqLastResultat->where('r0.candidat_metier_id = ?', $candidat_metier_id);
			$reqLastResultat->where('r0.resultat_num_passage = ?', $passage);
			$reqLastResultat->order('r0.resultat_id DESC');
			$lastResultat = $reqLastResultat->query()->fetch();

			if( !$lastResultat ) return false;

			$reqResultats = $this->select()->setIntegrityCheck(false);
			$reqResultats->from('resultat_outil AS ro');
			$reqResultats->where('ro.resultat_id = ?', $lastResultat['resultat_id']);

			return $reqResultats->query()->fetchAll(Zend_Db::FETCH_OBJ);
		}else{
			return false;
		}
    }
    
    public function add($candidat_metier_id){

		$passage_max = $this->getLastPassage($candidat_metier_id);
		$data = array(
			'resultat_num_passage'	=>	$passage_max+1,
			'candidat_metier_id'	=>	$candidat_metier_id
		);
		return $this->insert($data);

    }

	public function getLastPassage( $candidat_metier_id ){

		$sql = "
			SELECT
			  MAX( resultat_num_passage ) AS passage_max
			FROM
			  `resultat` as r
			WHERE
			  r.candidat_metier_id = $candidat_metier_id;
		";
		return $this->_db->query( $sql )->fetchObject()->passage_max;

	}
	
	public function getInfosForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array('cm0.candidat_metier_formation_duree_estimee AS form_est','cm0.candidat_metier_formation_duree_realisee AS form_real','cm0.candidat_metier_formation_remarque AS form_rem'));
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());

			//Etat
			$req->joinLeft('etat AS e0','e0.etat_id=cm0.etat_id',array('e0.etat_libelle','e0.etat_id'));
			//raison abandon
			$req->joinLeft('raison AS r0','r0.raison_id=cm0.raison_id',array('r0.raison_libelle'));
			
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			//Zend_Debug::dump($req->assemble());
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
		
		
		public function connaitreunite($unite = 0,$numero_diplome = 0){
		
			$liste_module = array();
			
			$sql_module  = "SELECT * from pmqvision5.diplomemodule
			LEFT JOIN pmqvision5.diplomeunite ON pmqvision5.diplomemodule.id_diplome_unite = pmqvision5.diplomeunite.id_diplome_unite
			LEFT JOIN pmqvision5.diplometitre ON pmqvision5.diplomemodule.id_titre = pmqvision5.diplometitre.id_diplome_titre
			WHERE pmqvision5.diplometitre.num_diplome_xml = '".$numero_diplome."'
			AND pmqvision5.diplomeunite.id_diplome_unite = '".$unite."'
			ORDER BY pmqvision5.diplomeunite.classement, pmqvision5.diplomemodule.classement
			
			";
			
			
			$result_module = mysql_query($sql_module);
			$compte_module = 0;
			while($row_module = mysql_fetch_array($result_module))
			{
			
					
				$liste_module[$compte_module]['nom_module'] = utf8_encode($row_module['nom_module']);
				$liste_module[$compte_module]['nb_question'] = utf8_encode($row_module['nb_question']);
				$liste_module[$compte_module]['nom_unite'] = utf8_encode($row_module['nom_unite']);
				$liste_module[$compte_module]['questionnaire'] = utf8_encode($row_module['questionnaire']);
				$liste_module[$compte_module]['observation'] = utf8_encode($row_module['observation']);
				$liste_module[$compte_module]['technique'] = utf8_encode($row_module['technique']);
				$liste_module[$compte_module]['bilan'] = utf8_encode($row_module['bilan']);
				$liste_module[$compte_module]['numero_unite'] = utf8_encode($row_module['numero_unite']);
				$liste_module[$compte_module]['id_unite'] = utf8_encode($row_module['id_diplome_unite']);
				$liste_module[$compte_module]['numero_module'] = utf8_encode($row_module['classement']);
					
				$compte_module++;
					
			}
			
			
			//$infosXml['liste_unite'] = $liste_unite;
			
			return $liste_module;
		
		
		
		}

		
    
}