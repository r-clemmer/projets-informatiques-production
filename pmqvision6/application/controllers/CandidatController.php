<?php

	class CandidatController extends Zend_Controller_Action {

		public function init(){

		}

		public function indexAction(){

		}

		public function detailsAction(){

			$mCandidats = new Model_Candidat();
			$mMetiers = new Model_Metier();
			$mCandidatMetiers = new Model_CandidatMetier();
			$this->view->role = Zend_Auth::getInstance()->getIdentity()->role;
			$candidat_id = $this->_request->getParam('id');	

			if( $candidat_id > 0 ){


				$mFormations = new Model_Formation();
				
				$mOperations = new Model_Fiche();
				$mEtat = new Model_Etat();
				$mFormations = new Model_Formation();
				$mRaisons = new Model_Raison();
				$mEntites = new Model_Entite();
				$mContacts = new Model_Contact();
				$mJurys = new Model_Jury();
				$mResultats = new Model_Resultat();

				$fDates = new Fonctions_Dates();
				$front = Zend_Controller_Front::getInstance();

				$candidat = $mCandidats->get( $candidat_id );

				$this->view->title = "Détails du candidat";

				$this->view->candidat_id = $candidat_id;
				$this->view->nom = ucwords( $candidat['civilite_libelle'].' '.$candidat['personne_prenom'].' '.$candidat['personne_nom'] );
				$this->view->code = $candidat['candidat_code'];
				$this->view->age = $fDates->getNbYears( $candidat['personne_date_naissance'] ).' ans';
				$this->view->anciennete = $fDates->getNbYears( $candidat['candidat_anciennete'] ).' ans';
				$this->view->date_creation = $fDates->formatDate( $candidat['personne_date_creation'] );
				$this->view->poste = ucwords( $candidat['personne_poste'] );
				$this->view->contrat = $candidat['candidat_contrat'];
				$this->view->entite = ucwords( $candidat['entite_nom'] );
				$this->view->entite_id = ucwords( $candidat['entite_id'] );
				$this->view->cursus = $candidat['candidat_cursus'];

				$liste_titres = "";
				$formations = array();
				$candidat_metiers = $mCandidats->getCandidatMetiers($candidat_id);
				if( count( $candidat_metiers ) > 0 ) $liste_titres .= "<ul>";
				foreach( $candidat_metiers as $candidat_metier ){
					//titres
					$titre = $mMetiers->getTitre($candidat_metier['metier_id']);
					$liste_titres .= '<li>';
					$liste_titres .= $titre['bloc1_lib'];
					if( isset( $titre['bloc2_lib'] ) ) $liste_titres .= ' / '.$titre['bloc2_lib'];
					$liste_titres .= ' ( '.$candidat_metier['etat_libelle'].' )';
					$liste_titres .= '<a href="'.$front->getBaseUrl().'/tableauresultats/index/?id='.$candidat_metier['candidat_metier_id'].'&passage=2"><span class="ui-icon ui-icon-script" style="margin: 0pt; display: inline-block; vertical-align: middle;" title="Résultats" ></span></a>';
					$liste_titres .= '</li>';
					//formations
					if( $candidat_metier['formation_id'] > 0 ){
						$formations[] = $mFormations->get($candidat_metier['formation_id']);
					}
				}
				if( count( $candidat_metiers ) > 0 ) $liste_titres .= "</ul>";
				if( empty( $liste_titres ) ) $liste_titres = ' aucun';
				$liste_formations = "";
				if( count( $formations ) > 0 ){
					$liste_formations .= '<ul>';
					foreach( $formations as $formation ){
						$liste_formations .= '<li>';
						$liste_formations .= $formation['formation_libelle'].' ( '.$formation['formation_formacode'].' )';
						$liste_formations .= '</li>';
					}
					$liste_formations .= '</ul>';
				}
				if( empty( $liste_formations ) ) $liste_formations = ' Aucune';
				$this->view->titres = $liste_titres;
				$this->view->formations = $liste_formations;

				//fichiers
				$files = "";
				$dir = './documents/candidats/'.$candidat_id.'/';
				if (is_dir($dir)) {
					if ( $dh = opendir($dir) ) {
						$files .= "<ul>";
						while (($file = readdir($dh)) !== false) {
							if($file != "." && $file != ".." && !is_dir($dir.$file)){
								$files .= '<li>';
								$files .= '<a href="'.$front->getBaseUrl().'/outils/download?dir='.$dir.$file.'" rel="'.$dir.$file.'" class="linkFileOnContent" title="Télécharger" >'.$file.'</a>';
								$files .= '</li>';
							}
						}
						$files .= "</ul>";
						closedir($dh);
					}
				}
				$this->view->files = $files;

				if( $this->_request->getParam('operation_id') ){

					$operation_id = $this->_request->getParam('operation_id');
					$this->view->operation_id = $this->_request->getParam('operation_id');

					$metiers = $mOperations->getMetiers($operation_id);
					foreach( $metiers as $metier ){
						foreach( $candidat_metiers as $candidat_metier ){
							if( $metier['metier_id'] == $candidat_metier['metier_id'] ) $cm = $candidat_metier;
						}
					}

					$candidat_metier = $cm;

					$titre = $mMetiers->getTitre($candidat_metier['metier_id']);
					if( isset( $titre['bloc2_lib'] ) ) $this->view->titre = $titre['bloc1_lib'].' / '.$titre['bloc2_lib'];
					else $this->view->titre = $titre['bloc1_lib'];
					
					$this->view->titre2  = $titre['demarche_abrege'];
					$etat = $mEtat->get($candidat_metier['etat_id']);
					if( $candidat_metier['raison_id'] > 0 ) $raison = ' ( '.$mRaisons->get( $candidat_metier['raison_id'] )->raison_libelle.' )';
					else $raison = "";
					$this->view->etat = $etat['etat_libelle'].$raison;
					$candidat_metier['candidat_metier_id'];
					$expert = $mCandidatMetiers->getExpert($candidat_metier['candidat_metier_id']);
					if( $expert != null ){
						$this->view->expert = ucwords( $expert['personne_prenom'].' '.$expert['personne_nom'] );
					}else{
						$this->view->expert = "";
					}
					
					$candidat_metier['candidat_metier_id'];
					
									$this->view->candidat_id_get = $candidat_metier['candidat_metier_id'];	
					
					$evaluateur = $mCandidatMetiers->getEvaluateur($candidat_metier['candidat_metier_id']);
					if( $evaluateur != null ){
						$this->view->evaluateur = ucwords( $evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'] );
					}else{
						$this->view->evaluateur = "";
					}
					
				$organisme = $mCandidatMetiers->getOrganisme($candidat_metier['candidat_metier_id']);
					if( $organisme != null ){
						$this->view->organisme = ucwords( $organisme['personne_prenom'].' '.$organisme['personne_nom'] );
					}else{
						$this->view->organisme = "";
					}
					

					if( $candidat_metier['candidat_metier_fiche_enquete'] == 1 ){
						$this->view->fiche_enquete = "Oui";
					}else{
						$this->view->fiche_enquete = "Non";
					}

					$s_formation = "<ul>";
					if( $candidat_metier['formation_id'] > 0 ){
						$formation = $mFormations->get($candidat_metier['formation_id']);
						$s_formation .= '<li>';
						$s_formation .= 'Libellé : '.$formation['formation_libelle'].' ( '.$formation['formation_formacode'].' )';
						$s_formation .= '</li>';
					}
					if( $candidat_metier['org_formation_id'] > 0 ){
						$entite = $mEntites->get( $candidat_metier['org_formation_id'] );
						$s_formation .= '<li>';
						$s_formation .= 'Organisme de formation : '.ucwords( $entite['entite_nom'] );
						$s_formation .= '</li>';
						if( $candidat_metier['formateur_id'] > 0 ){
							$formateur = $mContacts->getPersonne( $candidat_metier['formateur_id'] );
							$s_formation .= '<li>';
							$s_formation .= 'Formateur : '.ucwords( $formateur['personne_prenom'].' '.$formateur['personne_nom'] );
							$s_formation .= '</li>';
						}
					}
					$s_formation .= '<li>Durée estimée : '.$candidat_metier['candidat_metier_formation_duree_estimee'].' h</li>';
					$s_formation .= '<li>Durée réalisée : '.$candidat_metier['candidat_metier_formation_duree_realisee'].' h</li>';
					
							if($titre['demarche_abrege'] !='cqpbranche'){
										if( $candidat_metier['candidat_metier_formation_remarque'] != '' ) $s_formation .= '<li>Remarques 1er passage : '.$candidat_metier['candidat_metier_formation_remarque'].'</li>';
										if( $candidat_metier['candidat_metier_formation_remarque2'] != '' ) $s_formation .= '<li>Remarques 2eme passage : '.$candidat_metier['candidat_metier_formation_remarque2'].'</li>';
									 }else{
										if( $candidat_metier['candidat_metier_formation_remarque'] != '' ) $s_formation .= '<li>Commentaires en formation : '.$candidat_metier['candidat_metier_formation_remarque'].'</li>';
										if( $candidat_metier['candidat_metier_formation_remarque2'] != '' ) $s_formation .= '<li>Commentaires évaluation : '.$candidat_metier['candidat_metier_formation_remarque2'].'</li>';
									 }
				
					$s_formation .= "</ul>";
					$this->view->formation = $s_formation;

					$this->view->candidat_metier_id = $candidat_metier['candidat_metier_id'];

					$etat = $mEtat->get( $candidat_metier['etat_id'] );

					$r = $mResultats->getResultatsCandidat($candidat_metier['candidat_metier_id']);
					if( $r != null ){
						$resultat_id = $r[0]->resultat_id;
						$resultat = $mResultats->fetchRow(" resultat_id = $resultat_id");

						if( $resultat['jury_id'] > 0 ){
							$jury = $mJurys->get( $resultat['jury_id'] );
							$date = $fDates->formatDate($jury['jury_date']);
							$this->view->jury = "<ul>";
							$this->view->jury .= "<li>le $date à ".ucwords( $jury['jury_ville'] )."</li>";
							$this->view->jury .= "<li>Commentaire : ".$resultat['resultat_commentaire_jury']."</li>";
							$this->view->jury .= "</ul>";
						}else{
							$this->view->jury = "Aucun";
						}
					}else{
						$this->view->jury = "Aucun";
					}

				}
				
			$fiche = $mCandidats->getFiches($this->_request->getParam('id'));
			$operation_id= $fiche[0]['fiche_id'];
			$this->view->operation_id = $this->_request->getParam('operation_id');
				$metiers = $mOperations->getMetiers($operation_id);

					foreach( $metiers as $metier ){
						foreach( $candidat_metiers as $candidat_metier ){
							if( $metier['metier_id'] == $candidat_metier['metier_id'] ) 
							{
								$cm = $candidat_metier;
							}
						}
					}

					$candidat_metier = $cm;
			
			$this->view->candidat_metier_id = $candidat_metier['candidat_metier_id'];
			
			
			
				

			}else{
				$this->_redirect("/candidat/?candidat_metier_id=".$candidat_metier['candidat_metier_id']);
			}

		}

		/*public function addAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_entreprises.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');

			$mCivilites = new Model_Civilite();

			$civilites = $mCivilites->fetchAll();
			$select_civilite = "";
			$select = "";
			foreach( $civilites as $civilite ){
				if( $civilite['civilite_abrege'] == 'nc' ) $select = " selected ";
				$select_civilite .= '<option value="'.$civilite['civilite_id'].'" '.$select.' >'.ucfirst( $civilite['civilite_abrege'] ).'</option>';
			}

			$this->view->title = "Création d'un candidat";
			$this->view->civilites = $select_civilite;

		}*/

		public function updateAction(){

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');

			if( $this->_request->getParam('operation_id') ){

				$this->view->operation_id = $this->_request->getParam('operation_id');

				$candidat_id = $this->_request->getParam('id');

				$mCandidats = new Model_Candidat();
				$mMetiers = new Model_Metier();
				$mFormations = new Model_Formation();

				$fDates = new Fonctions_Dates();
				$front = Zend_Controller_Front::getInstance();

				$candidat = $mCandidats->get( $candidat_id );

				$this->view->title = "Détails du candidat";

				$this->view->candidat_id = $candidat_id;
				$this->view->nom = ucwords( $candidat['civilite_libelle'].' '.$candidat['personne_prenom'].' '.$candidat['personne_nom'] );
				$this->view->code = $candidat['candidat_code'];
				$this->view->age = $fDates->getNbYears( $candidat['personne_date_naissance'] ).' ans';
				$this->view->anciennete = $fDates->getNbYears( $candidat['candidat_anciennete'] ).' ans';
				$this->view->date_creation = $fDates->formatDate( $candidat['personne_date_creation'] );
				$this->view->poste = ucwords( $candidat['personne_poste'] );
				$this->view->contrat = $candidat['candidat_contrat'];
				$this->view->entite = ucwords( $candidat['entite_nom'] );
				$this->view->entite_id = ucwords( $candidat['entite_id'] );
				$this->view->cursus = $candidat['candidat_cursus'];

				$liste_titres = "";
				$formations = array();
				$candidat_metiers = $mCandidats->getCandidatMetiers($candidat_id);
				if( count( $candidat_metiers ) > 0 ) $liste_titres .= "<ul>";
				foreach( $candidat_metiers as $candidat_metier ){
					//titres
					$titre = $mMetiers->getTitre($candidat_metier['metier_id']);
					$liste_titres .= '<li>';
					$liste_titres .= $titre['bloc1_lib'];
					if( isset( $titre['bloc2_lib'] ) ) $liste_titres .= ' / '.$titre['bloc2_lib'];
					$liste_titres .= ' ( '.$candidat_metier['etat_libelle'].' )';
					$liste_titres .= '<a href="#"><span class="ui-icon ui-icon-script" style="margin: 0pt; display: inline-block; vertical-align: middle;" title="Résultats" ></span></a>';
					$liste_titres .= '</li>';
					//formations
					if( $candidat_metier['formation_id'] > 0 ){
						$formations[] = $mFormations->get($candidat_metier['formation_id']);
					}
				}
				if( count( $candidat_metiers ) > 0 ) $liste_titres .= "</ul>";
				if( empty( $liste_titres ) ) $liste_titres = ' aucun';
				$liste_formations = "";
				if( count( $formations ) > 0 ){
					$liste_formations .= '<ul>';
					foreach( $formations as $formation ){
						$liste_formations .= '<li>';
						$liste_formations .= $formation['formation_libelle'].' ( '.$formation['formation_formacode'].' )';
						$liste_formations .= '</li>';
					}
					$liste_formations .= '</ul>';
				}
				if( empty( $liste_formations ) ) $liste_formations = ' aucune';
				$this->view->titres = $liste_titres;
				$this->view->formations = $liste_formations;

				//fichiers
				/*$files = "";
				$dir = './documents/candidats/'.$candidat_id.'/';
				if (is_dir($dir)) {
					if ( $dh = opendir($dir) ) {
						$files .= "<ul>";
						while (($file = readdir($dh)) !== false) {
							if($file != "." && $file != ".." && !is_dir($dir.$file)){
								$files .= '<li>';
								$files .= '<a href="'.$front->getBaseUrl().'/outils/download?dir='.$dir.$file.'" rel="'.$dir.$file.'" class="linkFileOnContent" title="Télécharger" >'.$file.'</a>';
								$files .= '</li>';
							}
						}
						$files .= "</ul>";
						closedir($dh);
					}
				}
				$this->view->files = $files;*/

				if( $this->_request->getParam('operation_id') ){

					$operation_id = $this->_request->getParam('operation_id');

					$mCandidatMetiers = new Model_CandidatMetier();
					$mOperations = new Model_Fiche();
					$mEtats = new Model_Etat();
					$mRaisons = new Model_Raison();
					$mEntites = new Model_Entite();
					$mContacts = new Model_Contact();
					$mResultats = new Model_Resultat();
					$mJurys = new Model_Jury();

					$this->view->operation_id = $operation_id;

					$metiers = $mOperations->getMetiers($operation_id);

					$select_titres = "";
					foreach( $metiers as $metier ){
						foreach( $candidat_metiers as $candidat_metier ){
							if( $metier['metier_id'] == $candidat_metier['metier_id'] ) $cm = $candidat_metier;
						}
					}
					$candidat_metier = $cm;
					//titres
					$this->view->titre2 ='';
					foreach( $metiers as $metier ) {
						$titre = $mMetiers->getTitre($metier['metier_id']);
						$lib = $titre['bloc1_lib'];
						if( isset( $titre['bloc2_lib'] ) ) $lib .= ' - '.$titre['bloc2_lib'];
						if( $candidat_metier['metier_id'] == $metier['metier_id'] ) 
						{
							$select = " selected ";	
							$this->view->titre2  = $titre['demarche_abrege'];
						}
						else $select = "";
						$select_titres .= '<option value="'.$metier['metier_id'].'" '.$select.' >'.ucwords( $lib ).'</option>';
					}
					$this->view->select_titres = $select_titres;

					//fiche enquete
					if( $candidat_metier['candidat_metier_fiche_enquete'] == 1 ){
						$this->view->check_fe = "checked";
					}

					//expert
					$select_experts = "";
					$experts = $mMetiers->getExperts($candidat_metier['metier_id']);
					foreach( $experts as $expert ){
						$select = "";
						if( $expert['binome_id'] == $candidat_metier['expert_id'] ) $select = ' selected = "selected" ';
						$select_experts .= '<option value="'.$expert['binome_id'].'" '.$select.' >'.$expert['personne_prenom'].' '.$expert['personne_nom'].' ( '.$expert['entite_nom'].' )</option>';
					}
					$this->view->experts = $select_experts;

					//evaluateur
					$select_evaluateurs = "";
					$evaluateurs = $mMetiers->getEvaluateurs($candidat_metier['metier_id']);
					foreach( $evaluateurs as $evaluateur ){
						$select = "";
						if( $evaluateur['binome_id'] == $candidat_metier['tuteur_id'] ) $select = ' selected = "selected" ';
						$select_evaluateurs .= '<option value="'.$evaluateur['binome_id'].'" '.$select.' >'.$evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'].' ( '.$evaluateur['entite_nom'].' )</option>';
					}
					$this->view->evaluateurs = $select_evaluateurs;

					//formations
					$formations = $mFormations->fetchAll(null, " formation_formacode ASC ");
					$select_formations = '<option value="0" >Aucune</option>';
					foreach( $formations as $formation ){
						$select = "";
						if( $candidat_metier['formation_id'] == $formation['formation_id'] ) $select = ' selected="selected" ';
						$select_formations .= '<option value="'.$formation['formation_id'].'" '.$select.' >';
						$select_formations .= $formation['formation_libelle'].' ( '.$formation['formation_formacode'].' )';
						$select_formations .= '</option>';
					}
					$this->view->select_formations = $select_formations;

					$this->view->duree_estimee = $candidat_metier['candidat_metier_formation_duree_estimee'];
					$this->view->duree_realisee = $candidat_metier['candidat_metier_formation_duree_realisee'];
					$this->view->remarque = $candidat_metier['candidat_metier_formation_remarque'];
					$this->view->remarque2 = $candidat_metier['candidat_metier_formation_remarque2'];
					$this->view->candidat_metier_id = $candidat_metier['candidat_metier_id'];

					//etat
					$etat = $mEtats->get($candidat_metier['etat_id']);
					if( $etat['etat_libelle'] == "abandon" ){
						$this->view->abandon = ' checked="checked" disabled="disabled" ';
						$this->view->select_raison = '';
					}else{
						$this->view->abandon = '';
						$this->view->select_raison = ' disabled="disabled" ';
					}

					//raison
					$raisons = $mRaisons->fetchAll();
					$select_raisons = '<option value="0" >Aucune</option>';
					foreach( $raisons as $raison ){
						$select = "";
						if( $candidat_metier['raison_id'] == $raison['raison_id'] ) $select = ' selected="selected" ';
						$select_raisons .= '<option value="'.$raison['raison_id'].'" '.$select.' >'.$raison['raison_libelle'].'</option>';
					}
					$this->view->raisons = $select_raisons;

					//organisme de formation
					$orgs = $mEntites->getByTypeEntite('organisme de formation');
					$select_orgs_formation = '<option value="0" >Aucun</option>';
					foreach( $orgs as $org ){
						$select = "";
						if( $candidat_metier['org_formation_id'] == $org['entite_id'] ) $select = ' selected="selected" ';
						$select_orgs_formation .= '<option value="'.$org['entite_id'].'" '.$select.' >'.ucwords( $org['entite_nom'] ).'</option>';
					}
					$this->view->orgs_formation = $select_orgs_formation;

					//formateur
					$select_formateurs = '<option value="0" >Aucun</option>';
					if( $candidat_metier['org_formation_id'] > 0 ){
						$formateurs = $mContacts->getFormateurs( $candidat_metier['org_formation_id'] );
						foreach( $formateurs as $formateur ){
							$select = "";
							if( $candidat_metier['formateur_id'] == $formateur['contact_id'] ) $select = ' selected="selected " ';
							$select_formateurs .= '<option value="'.$formateur['contact_id'].'" '.$select.' >'.$formateur['personne_prenom'].' '.$formateur['personne_nom'].'</option>';
						}
					}
					$this->view->formateurs = $select_formateurs;

					if( $candidat_metier['org_formation_id'] > 0 ) $this->view->select_formateur = '';
					else $this->view->select_formateur = ' disabled="disabled" ';
					
				

					//jury
					$r = $mResultats->getResultatsCandidat($candidat_metier['candidat_metier_id']);
					if( $r != null ){
						$resultat_id = $r[0]->resultat_id;
						$resultat = $mResultats->fetchRow(" resultat_id = $resultat_id");
						$jurys = $mJurys->getListe( 'jury_date', 'DESC', 0, 1000 );
						$this->view->jury = '<option value="" >Aucun</option>';
						foreach( $jurys as $jury ){
							$select = '';
							if( $jury['jury_id'] == $resultat['jury_id'] ) $select = ' selected="selected" ';
							$this->view->jury .= '<option value="'.$jury['jury_id'].'" '.$select.' >'.$fDates->formatDate($jury['jury_date']).' - '.ucwords($jury['jury_ville']).'</option>';
						}
						$this->view->commentaire_jury = $resultat['resultat_commentaire_jury'];
					}else{
						$jurys = $mJurys->getListe( 'jury_date', 'DESC', 0, 1000 );
						$this->view->jury = '<option value="" >Aucun</option>';
						foreach( $jurys as $jury ){
							$this->view->jury .= '<option value="'.$jury['jury_id'].'" >'.$fDates->formatDate($jury['jury_date']).' - '.ucwords($jury['jury_ville']).'</option>';
						}
					}

				}
				
			}else{

				if( $this->_request->isPost() ){

					$mPersonnes = new Model_Personne();
					$mCandidats = new Model_Candidat();
					$fDates = new Fonctions_Dates();
					$candidat_id = $_POST["candidat_id"];
					$personne_id = $_POST["personne_id"];
					$civilite_id = $_POST["civilite_id"];
					$personne_nom = $_POST["personne_nom"];
					$personne_nom = strtolower($personne_nom);
					$personne_prenom = $_POST["personne_prenom"];
					$personne_prenom = strtolower($personne_prenom);
					$candidat_code = $_POST["candidat_code"];
					$personne_date_naissance = $fDates->unformatDate( $_POST["personne_date_naissance"] );
					$candidat_anciennete = $fDates->unformatDate( $_POST["candidat_anciennete"] );
					$candidat_contrat = $_POST["candidat_contrat"];
					$personne_poste = $_POST["personne_poste"];
					$candidat_cursus = $_POST["candidat_cursus"];
					if($candidat_cursus=='')
					{
						$candidat_cursus ='non';
					}
					//$resultat_commentaire_jury = $_POST["resultat_commentaire_jury"];

					$data = array(
						'candidat_code'	=>	$candidat_code,
						'candidat_anciennete'	=>	$candidat_anciennete,
						'candidat_contrat'	=>	$candidat_contrat,
						'candidat_cursus'	=>	$candidat_cursus
					);
					$where = " candidat_id = $candidat_id ";
					$mCandidats->update($data, $where);

					$data = array(
						'civilite_id'	=>	$civilite_id,
						'personne_nom'	=>	$personne_nom,
						'personne_prenom'	=>	$personne_prenom,
						'personne_date_naissance'	=>	$personne_date_naissance,
						'personne_poste'	=>	$personne_poste
					);
					$where = " personne_id = $personne_id ";
					$mPersonnes->update( $data, $where );
					$this->_redirect( '/candidat/details/?id='.$candidat_id.'&candidat_metier_id='.$candidat_metier['candidat_metier_id'] );

				}else{

					$candidat_id = $this->_request->getParam('id');

					if( $candidat_id > 0 ){

						$mCandidats = new Model_Candidat();
						$mCivilites = new Model_Civilite();
						$fDates = new Fonctions_Dates();

						$front = Zend_Controller_Front::getInstance();

						$candidat = $mCandidats->get( $candidat_id );

						$this->view->title = "Modification du candidat";

						$this->view->candidat_id = $candidat_id;

						$this->view->civilite = "";
						$civs = $mCivilites->fetchAll();
						foreach( $civs as $civ ){
							$select = "";
							if( $candidat['civilite_id'] == $civ['civilite_id'] ) $select = ' selected="selected" ';
							$this->view->civilite .= '<option value="'.$civ['civilite_id'].'" '.$select.' >'.ucfirst( $civ['civilite_libelle'] ).'</option>';
						}
						
						$selected_contrat1 = "";
						$selected_contrat2 = "";
						$selected_contrat3 = "";
						$selected_contrat4 = "";
						if($candidat['candidat_contrat'] =='Nr'){$selected_contrat1='selected="selected"';}
						if($candidat['candidat_contrat'] =='CDD'){$selected_contrat2='selected="selected"';}
						if($candidat['candidat_contrat'] =='CDI'){$selected_contrat3='selected="selected"';}
						if($candidat['candidat_contrat'] =='Contrat Pro'){$selected_contrat4='selected="selected"';}
						if($candidat['candidat_contrat'] =='Interimaire'){$selected_contrat5='selected="selected"';}
						$this->view->contrat='<option value="Nr" '.$selected_contrat1.' >Nr</option>';
						$this->view->contrat.='<option value="CDD" '.$selected_contrat2.' >CDD</option>';
						$this->view->contrat.='<option value="CDI" '.$selected_contrat3.' >CDI</option>';
						$this->view->contrat.='<option value="Contrat Pro" '.$selected_contrat4.' >Contrat Pro</option>';
						$this->view->contrat.='<option value="Interimaire" '.$selected_contrat5.' >Interimaire</option>';
						$this->view->contrat.='<option value="Autre" '.$selected_contrat5.' >Autre</option>';
						
						$this->view->nom = ucwords( $candidat['personne_nom'] );

						$this->view->prenom = ucwords( $candidat['personne_prenom'] );

						$this->view->code = $candidat['candidat_code'];

						$this->view->date_naissance = $fDates->formatDate( $candidat['personne_date_naissance'] );

						$this->view->date_anciennete = $fDates->formatDate( $candidat['candidat_anciennete'] );


						$this->view->poste = $candidat['personne_poste'];

						$this->view->candidat_id = $candidat['candidat_id'];
						$this->view->personne_id = $candidat['personne_id'];

						$this->view->cursus = $candidat['candidat_cursus'];

						//fichiers
						$files = "";
						$dir = './documents/candidats/'.$candidat_id.'/';
						if (is_dir($dir)) {
							if ( $dh = opendir($dir) ) {
								while (($file = readdir($dh)) !== false) {
									if($file != "." && $file != ".." && !is_dir($dir.$file)){
										$files .= '<li>';
										$files .= '<a href="'.$front->getBaseUrl().'/outils/download?dir='.$dir.$file.'" rel="'.$dir.$file.'" class="linkFileOnContent" title="Télécharger" >'.$file.'</a>';
										$files .= '</li>';
									}
								}
								closedir($dh);
							}
						}
						$this->view->files = $files;

					}

				}

			}

		}

		public function deleteAction(){

		}


	}