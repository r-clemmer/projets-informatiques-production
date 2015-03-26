<?php
	class StatistiquesController extends Zend_Controller_Action {
	
		function init(){
		
		}
		
		public function indexAction(){

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');

			$mEntites = new Model_Entite();
			$mOperations = new Model_Fiche();
			$mCandidatMetiers = new Model_CandidatMetier();
			$mEtats = new Model_Etat();
			$mDemarches = new Model_Demarche();

			/*************************synthese*******************************/
			$etats = $mEtats->fetchAll();
			foreach( $etats as $etat ){
				switch( $etat['etat_libelle'] ){
					case 'en positionnement' :
						$nb_c_positionnement = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
					case 'en formation' :
						$nb_c_formation = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
					case 'en évaluation' :
						$nb_c_evaluation = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
					case 'admissible' :
						$nb_c_admissible = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
					case 'certifié' :
						$nb_c_certifie = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
					case 'abandon' :
						$nb_c_abandon = count( $mCandidatMetiers->fetchAll( ' etat_id = '.$etat['etat_id'] ) );
						break;
				}
			}

			$this->view->title = 'Statistiques';

			$this->view->nb_entreprises = count( $mEntites->getByTypeEntite('entreprise') );
			$this->view->nb_operations = $mOperations->count();
			$this->view->nb_candidatures = count( $mCandidatMetiers->fetchAll() );

			$this->view->nb_c_positionnement = $nb_c_positionnement;
			$this->view->nb_c_formation = $nb_c_formation;
			$this->view->nb_c_evaluation = $nb_c_evaluation;
			$this->view->nb_c_admissible = $nb_c_admissible;
			$this->view->nb_c_certifie = $nb_c_certifie;
			$this->view->nb_c_abandon = $nb_c_abandon;
			/*****************************************************************/

			/**********************ENTREES************************************/

			//parametres de retour en cas d erreurs//

			if( isset( $_GET['params'] ) ){
				$params = unserialize( $_GET['params'] );
			}

			//operation
			if( isset( $params['in_operation'] ) && $params['in_operation'] != '' ){
				$this->view->s_in_operation = $params['in_operation'];
			}

			//dates
			if( isset( $params['in_date1'] ) && $params['in_date1'] != '' ){
				$this->view->s_in_date1 = $params['in_date1'];
			}
			if( isset( $params['in_date2'] ) && $params['in_date2'] != '' ){
				$this->view->s_in_date2 = $params['in_date2'];
			}

			/////


			//demarches
			$demarches = $mDemarches->getAll();
			$select = '<option value="">Aucune</option>';
			foreach( $demarches as $demarche ){
				$s = "";
				if( isset( $params['in_demarche'] ) && $params['in_demarche'] == $demarche['demarche_id'] ) $s = ' selected="selected" ';
				$select .= '<option value="'.$demarche['demarche_id'].'" '.$s.' >'.ucfirst( $demarche['demarche_abrege'] ).'</option>';
			}
			$this->view->select_demarches = $select;

			//org referents
			$org_referents = $mEntites->getByTypeEntite("organisme référent");
			$select = '<option value="" >Aucun</option>';
			foreach( $org_referents as $entite ){
				$s = "";
				if( isset( $params['in_org_referent'] ) && $params['in_org_referent'] == $entite['entite_id'] ) $s = ' selected="selected" ';
				$select .= '<option value="'.$entite['entite_id'].'" '.$s.' >'.ucwords( $entite['entite_nom'] ).'</option>';
			}
			$this->view->select_org_referents = $select;

			//delegations
			$delegations = $mEntites->getByTypeEntite("délégation");
			$select = '<option value="" >Aucune</option>';
			foreach( $delegations as $entite ){
				$s = "";
				if( isset( $params['in_delegation'] ) && $params['in_delegation'] == $entite['entite_id'] ) $s = ' selected="selected" ';
				$select .= '<option value="'.$entite['entite_id'].'" '.$s.' >'.ucwords( $entite['entite_nom'] ).'</option>';
			}
			$this->view->select_delegations = $select;

			//branches
			$branches = $mEntites->getByTypeEntite("branche");
			$select = '<option value="" >Aucune</option>';
			foreach( $branches as $entite ){
				$s = "";
				if( isset( $params['in_branche'] ) && $params['in_branche'] == $entite['entite_id'] ) $s = ' selected="selected" ';
				$select .= '<option value="'.$entite['entite_id'].'" '.$s.' >'.ucwords( $entite['entite_nom'] ).'</option>';
			}
			$this->view->select_branches = $select;

			//entreprises
			$entreprises = $mEntites->getByTypeEntite("entreprise");
			$select = '<option value="" >Aucune</option>';
			foreach( $entreprises as $entite ){
				$s = "";
				if( isset( $params['in_entreprise'] ) && $params['in_entreprise'] == $entite['entite_id'] ) $s = ' selected="selected" ';
				$select .= '<option value="'.$entite['entite_id'].'" '.$s.' >'.ucwords( $entite['entite_nom'] ).'</option>';
			}
			$this->view->select_entreprises = $select;

			//etats
			$select = '<option value="" >Aucun</option>';
			foreach( $etats as $etat ){
				$s = "";
				if( $etat['etat_libelle'] != 'en positionnement' && $etat['etat_libelle'] != 'en évaluation' ){
					if( isset( $params['in_etat'] ) && $params['in_etat'] == $etat['etat_id'] ) $s = ' selected="selected" ';
					$select .= '<option value="'.$etat['etat_id'].'" '.$s.' >'.$etat['etat_libelle'].'</option>';
				}
			}
			$select .= '<option value="99" '.$s.' >en cours</option>';
			$this->view->select_etats = $select;

			/********************************************************************/

			/*********************ERROR***************************************/

			if( isset( $_GET['error'] ) ){
				switch( $_GET['error'] ){
					case 1 :
						$this->view->error = "Cette opération n'existe pas.";
						break;
					case 2 :
						$this->view->error = "L'une des dates de création n'est pas valide.";
						break;
					case 3 :
						$this->view->error = "Aucun critère saisit.";
						break;
					case 4 :
						$this->view->error = "La période demandée est de plus de 1 an.";
						break;
					default:
						$this->view->error = "Aucune donnée correspondante !";
				}
			}

			/********************************************************************/

	    }

	    public function exportAction(){
			set_time_limit(0);
	    	$liste_regions = array (
				"alsace" => array(67,68),
				"aquitaine" => array(24,33,40,47,64),
				"auvergne" => array ("03",15,43,63),
				"basse-normandie" => array (14,50,61),
				"bourgogne" => array (21,58,71,89),
				"bretagne" => array (22,29,35,56),
				"centre" => array (18,28,36,37,41,45),
				"champagne-ardenne" => array ("08",10,51,52),
	    		"ColTOM"=>array(98),
				"corse" => array(20),
				"DOM-TOM" => array(97),
				"franche-comte" => array (25,39,70,90),
				"haute-normandie" => array (27,76),
				"ile-de-france" => array(75,77,78,91,92,93,94,95),
				"languedoc-roussillon" => array(11,30,34,48,66),
				"limousin" => array(19,23,87),
				"lorraine" => array (54,55,57,88),
				"midi-pyrenees" => array("09",12,31,32,46,65,81,82),
				"nord" => array(59,62),
				"loire" => array (44,49,53,72,85),
				"picardie" => array ("02",60,80),
				"poitou-charentes" => array (16,17,79,86),
				"PACA" => array("04","05","06",13,83,84),
				"rhone-alpes" => array ("01","07",26,38,42,69,73,74)
			);
	    	
			
	    	
	    	$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender(true);

			$mOperations = new Model_Fiche();
			$fDates = new Fonctions_Dates();
	    	
	    	/********* RECUPERATION DES DONNEES *********/
			$in_id_op = isset($_POST["in_operation"]) ? $_POST["in_operation"] : -1;
			$in_date_op_deb = isset($_POST["in_date1"]) ? $_POST["in_date1"] : -1;
			$in_date_op_fin = isset($_POST["in_date2"]) ? $_POST["in_date2"] : -1;
			$in_demarche = isset($_POST["in_demarche"]) ? $_POST["in_demarche"] : -1;
			$in_titre = isset($_POST["in_titre"]) ? $_POST["in_titre"] : -1;
			$in_org_referent = isset($_POST["in_org_referent"]) ? $_POST["in_org_referent"] : -1;
			$in_delegation = isset($_POST["in_delegation"]) ? $_POST["in_delegation"] : -1;
			$in_branche = isset($_POST["in_branche"]) ? $_POST["in_branche"] : -1;
			$in_entreprise = isset($_POST["in_entreprise"]) ? $_POST["in_entreprise"] : -1;
			$in_region = isset($_POST["in_region"]) ? $_POST["in_region"] : -1;
			$in_etat = isset($_POST["in_etat"]) ? $_POST["in_etat"] : -1;

			$cp_tab = array();
	  		if($in_region!= "" && $in_region!= -1){
				//$key = array_search($in_region, $liste_regions); // $key = 2;
				foreach($liste_regions AS $reg=>$val){
					if($reg == $in_region){
						$cp_tab = $val;
					}
				}
		  		
			}
			
			$in_tab = array();
	    	if($_POST["in_demarche"]!= "" && $_POST["in_demarche"]!= -1){
				array_push($in_tab,array('demarche'=>$_POST["in_demarche"]));
			}
	    	if($_POST["in_titre"]!= "" && $_POST["in_titre"]!= -1){
				array_push($in_tab,array('titre'=>$_POST["in_titre"]));
			}

			
			if( !empty($in_id_op) && $mOperations->exist($in_id_op) == 0 ) $this->_redirect('/statistiques/?error=1');
			if( (empty($in_date_op_fin) || !$fDates->checkDate($in_date_op_deb)) && !empty($in_date_op_deb) ) $this->_redirect('/statistiques/?error=2');
			if( (empty($in_date_op_deb) || !$fDates->checkDate($in_date_op_fin)) && !empty($in_date_op_fin) ) $this->_redirect('/statistiques/?error=2');
			if( !empty($in_date_op_deb) && !empty($in_date_op_fin) && $fDates->checkOrdreDate($in_date_op_deb, $in_date_op_fin) != '<' ) $this->_redirect('/statistiques/?error=2');
//			if( $fDates->getNbYearsTwoDates( $fDates->unformatDate( $in_date_op_deb ), $fDates->unformatDate( $in_date_op_fin ) ) > 1 ) $this->_redirect('/statistiques/?error=4');
//			if( empty($in_id_op) && empty($in_date_op_deb) && empty($in_date_op_fin) && empty($in_demarche) && empty($in_titre) && empty($in_or_referent) && empty($in_delegation) && empty($in_branche) && empty($in_entreprise) && empty($in_etat) ) $this->_redirect('/statistiques/?error=3');
			
			/******* RECUPERATION DES CHAMPS ************/
			$out_tab = array();
			
			if(isset($_POST["out_operations"])){
				array_push($out_tab,'op');
			}
	    	if(isset($_POST["out_organismes"])){
				array_push($out_tab,'org');
			}
	   		if(isset($_POST["out_entreprises"])){
				array_push($out_tab,'ent');
			}
	   		if(isset($_POST["out_dossiers"])){
				array_push($out_tab,'dossier');
			}
	    	if(isset($_POST["out_candidats"])){
				array_push($out_tab,'cand');
			}
	    	if(isset($_POST["out_formations"])){
				array_push($out_tab,'form');
			}
	   		if(isset($_POST["out_jurys"])){
				array_push($out_tab,'jury');
			}
	   		if(isset($_POST["out_resultats"])){
				array_push($out_tab,'result');
			}
			
	    	$obj_personne = new Model_Statistique();
			$req_results = $obj_personne->getCandidatsMetier($out_tab,$in_id_op,$in_date_op_deb,$in_date_op_fin,$in_demarche,$in_titre,$in_org_referent,$in_delegation,$in_branche,$in_entreprise,$cp_tab,$in_etat);

			//Zend_Debug::dump($req_results);
			require_once 'ExportExcel.php';
			if($req_results){
			
	
				$excel = new ExportExcel($req_results,$out_tab,$in_tab);

				$excel->getFile();
			}else {
				$this->_redirect('/statistiques/?error=');
			}
		
	    }
	    
	}
