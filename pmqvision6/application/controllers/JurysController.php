<?php

	class JurysController extends Zend_Controller_Action {
	
		public function init(){
			
		}
		
		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			
			$this->view->title = "Gestion des jurys";
							$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;	
			$this->view->role = $role;
			
		}
		
		public function detailsAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			
			if( $this->_request->getParam('id') > 0 ){
				
				$jury_id = $this->_request->getParam('id');
				
				$fDates = new Fonctions_Dates();
				$mJurys = new Model_Jury();
				$mEntites = new Model_Entite();

				$jury = $mJurys->get($jury_id);

				$adresse = "";
				if( $jury['jury_adresse'] != null ) $adresse .= $jury['jury_adresse'].', ';
				$adresse .= ucwords( $jury['jury_ville'] );
				if( $jury['jury_cp'] != null ) $adresse .= " ( ".$jury['jury_cp']." )";

				$branche = $mEntites->get( $jury['branche_id'] );
				$fed_patro = $mEntites->get( $jury['fed_patron_id'] );
				$fed_salar = $mEntites->get( $jury['fed_salar_id'] );

				$this->view->title = "Détails du jury";

				$this->view->jury_id = $jury['jury_id'];
				$this->view->jury_adresse = $adresse;
				$this->view->jury_date = $fDates->formatDate( $jury['jury_date'] );
				$this->view->branche = ucwords( $branche['entite_nom'] );
				$this->view->fed_patro = ucwords( $fed_patro['entite_nom'] );
				$this->view->fed_salar = ucwords( $fed_salar['entite_nom'] );
				
				$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;	
			$this->view->role = $role;
			
				
				
			}else{
				$this->_redirect( '/jurys/' );
			}
			
		}
		
		public function addAction(){

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');

			if( $this->_request->isPost() ){
				
				$mJurys = new Model_Jury();
				$fDates = new Fonctions_Dates();
				$mMembresJury = new Model_MembreJury();
				$mTypesMembreJury = new Model_TypeMembreJury();

				$data= array(
					'jury_ville'	=> $_POST['jury_ville'],
					'jury_cp'		=> $_POST['jury_cp'],
					'jury_adresse'	=> $_POST['jury_adresse'],
					'jury_date'		=> $fDates->unformatDate( $_POST['jury_date'] ),
					'branche_id'	=> $_POST['branche_id'],
					'fed_patron_id'	=> $_POST['fed_patron_id'],
					'fed_salar_id'	=> $_POST['fed_salar_id']
				);
				$jury_id = $mJurys->insert($data);

				if( $this->_request->getParam('rep_syndic_employ_id') ){
					$type = $mTypesMembreJury->getByLibelle('représentant de syndicats d\'employeurs');
					$mMembresJury->add( $this->_request->getParam('rep_syndic_employ_id'), $jury_id, $type['type_membre_jury_id'] );
				}
				if( $this->_request->getParam('rep_syndic_salar_id') ){
					$type = $mTypesMembreJury->getByLibelle('représentant de syndicats de salariés');
					$mMembresJury->add( $this->_request->getParam('rep_syndic_salar_id'), $jury_id, $type['type_membre_jury_id'] );
				}
				if( $this->_request->getParam('presid_id') ){
					$type = $mTypesMembreJury->getByLibelle('président de jury');
					$mMembresJury->add( $this->_request->getParam('presid_id'), $jury_id, $type['type_membre_jury_id'] );
				}
				if( $this->_request->getParam('rep_org_ref_id') ){
					$type = $mTypesMembreJury->getByLibelle('représentant organisme référent');
					$mMembresJury->add( $this->_request->getParam('rep_org_ref_id'), $jury_id, $type['type_membre_jury_id'] );
				}
				if( $this->_request->getParam('rep_forthac_id') ){
					$type = $mTypesMembreJury->getByLibelle('représentant forthac');
					$mMembresJury->add( $this->_request->getParam('rep_forthac_id'), $jury_id, $type['type_membre_jury_id'] );
				}

				$this->_redirect( "/jurys/details/?id=$jury_id" );
				
			}else{

				$fDates = new Fonctions_Dates();
				$mJurys = new Model_Jury();
				$mEntites = new Model_Entite();
				$mContacts = new Model_Contact();

				$branches = $mEntites->getByTypeEntite('branche');
				$federations = $mEntites->getByTypeEntite('fédération');

				$s_branches = '';
				foreach( $branches as $branche ){
					$s_branches .= '<option value="'.$branche['entite_id'].'" >'.ucwords( $branche['entite_nom'] ).'</option>';
				}

				$s_fed_patro = '';
				foreach( $federations as $federation ){
					$s_fed_patro .= '<option value="'.$federation['entite_id'].'" >'.ucwords( $federation['entite_nom'] ).'</option>';
				}

				$s_fed_salar = '';
				foreach( $federations as $federation ){
					$s_fed_salar .= '<option value="'.$federation['entite_id'].'" >'.ucwords( $federation['entite_nom'] ).'</option>';
				}
				
				$rep_syndic_employ = '';
				$rep_syndic_salar = '';
				foreach( $mContacts->getByTypeEntite('fédération') AS $contact ){
					$rep_syndic_employ .= '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
					$rep_syndic_salar .= '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
				}

				$presid = '';
				$types = array( 'fédération', 'organisme référent', 'forthac' );
				foreach( $mContacts->getByTypeEntite( $types ) AS $contact ){
					$presid .= '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
				}

				$rep_org_ref = "";
				foreach( $mContacts->getByTypeEntite('organisme référent') AS $contact ){
					$rep_org_ref .= '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
				}

				$rep_forthac = "";
				foreach( $mContacts->getByTypeEntite('forthac') AS $contact ){
					$rep_forthac .= '<option value="'.$contact['contact_id'].'" >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
				}
				

				$this->view->title = "Création d'un jury";

				$this->view->branches = $s_branches;
				$this->view->fed_patro = $s_fed_patro;
				$this->view->fed_salar = $s_fed_salar;

				$this->view->rep_syndic_employ = $rep_syndic_employ;
				$this->view->rep_syndic_salar = $rep_syndic_salar;
				$this->view->presid = $presid;
				$this->view->rep_org_ref = $rep_org_ref;
				$this->view->rep_forthac = $rep_forthac;

			}
			
		}
		
		public function updateAction(){

			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');
			
			if( $this->_request->getParam('id') > 0 ){

				$jury_id = $this->_request->getParam('id');

				if( $this->_request->isPost() ){

					$mJurys = new Model_Jury();
					$fDates = new Fonctions_Dates();
					$mMembresJury = new Model_MembreJury();
					$mTypesMembreJury = new Model_TypeMembreJury();

					$data= array(
						'jury_ville'	=> $_POST['jury_ville'],
						'jury_cp'		=> $_POST['jury_cp'],
						'jury_adresse'	=> $_POST['jury_adresse'],
						'jury_date'		=> $fDates->unformatDate( $_POST['jury_date'] ),
						'branche_id'	=> $_POST['branche_id'],
						'fed_patron_id'	=> $_POST['fed_patron_id'],
						'fed_salar_id'	=> $_POST['fed_salar_id']
					);
					$mJurys->update($data, " jury_id = $jury_id ");

					$mMembresJury->delete( " jury_id = $jury_id " );

					if( $_POST['rep_syndic_employ_id'] ){
						$type = $mTypesMembreJury->getByLibelle("représentant de syndicats d\'employeurs");
						$mMembresJury->add($_POST['rep_syndic_employ_id'], $jury_id, $type['type_membre_jury_id']);
					}
					if( $_POST['rep_syndic_salar_id'] ){
						$type = $mTypesMembreJury->getByLibelle("représentant de syndicats de salariés");
						$mMembresJury->add($_POST['rep_syndic_salar_id'], $jury_id, $type['type_membre_jury_id']);
					}
					if( $_POST['presid_id'] ){
						$type = $mTypesMembreJury->getByLibelle("président de jury");
						$mMembresJury->add($_POST['presid_id'], $jury_id, $type['type_membre_jury_id']);
					}
					if( $_POST['rep_org_ref_id'] ){
						$type = $mTypesMembreJury->getByLibelle("représentant organisme référent");
						$mMembresJury->add($_POST['rep_org_ref_id'], $jury_id, $type['type_membre_jury_id']);
					}
					if( $_POST['rep_forthac_id'] ){
						$type = $mTypesMembreJury->getByLibelle("représentant forthac");
						$mMembresJury->add($_POST['rep_forthac_id'], $jury_id, $type['type_membre_jury_id']);
					}

					$this->_redirect( "/jurys/details/?id=$jury_id" );

				}else{

					$fDates = new Fonctions_Dates();
					$mJurys = new Model_Jury();
					$mEntites = new Model_Entite();
					$mContacts = new Model_Contact();

					$jury = $mJurys->get($jury_id);

					//Zend_Debug::dump($jury);

					$branches = $mEntites->getByTypeEntite('branche');
					$federations = $mEntites->getByTypeEntite('fédération');

					$s_branches = '';
					foreach( $branches as $branche ){
						$select = "";
						if( $branche['entite_id'] == $jury['branche_id'] ) $select = " selected ";
						$s_branches .= '<option value="'.$branche['entite_id'].'" '.$select.' >'.ucwords( $branche['entite_nom'] ).'</option>';
					}

					$s_fed_patro = '';
					foreach( $federations as $federation ){
						$select = "";
						if( $federation['entite_id'] == $jury['fed_patron_id'] ) $select = " selected ";
						$s_fed_patro .= '<option value="'.$federation['entite_id'].'" '.$select.' >'.ucwords( $federation['entite_nom'] ).'</option>';
					}

					$s_fed_salar = '';
					foreach( $federations as $federation ){
						$select = "";
						if( $federation['entite_id'] == $jury['fed_salar_id'] ) $select = " selected ";
						$s_fed_salar .= '<option value="'.$federation['entite_id'].'" '.$select.' >'.ucwords( $federation['entite_nom'] ).'</option>';
					}

					$rep_syndic_employ = '';
					$rep_syndic_salar = '';
					foreach( $mContacts->getByTypeEntite('fédération') AS $contact ){
						$select = "";
						$membre = $mJurys->getMembreByType( $jury['jury_id'], 'représentant de syndicats d\'employeurs');
						if( $contact['contact_id'] == $membre['contact_id'] ) $select = " selected ";
						$rep_syndic_employ .= '<option value="'.$contact['contact_id'].'" '.$select.' >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
						$select = "";
						$membre = $mJurys->getMembreByType( $jury['jury_id'], 'représentant de syndicats de salariés');
						if( $contact['contact_id'] == $membre['contact_id'] ) $select = " selected ";
						$rep_syndic_salar .= '<option value="'.$contact['contact_id'].'" '.$select.' >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
					}

					$presid = '';
					$types = array( 'fédération', 'organisme référent', 'forthac' );
					foreach( $mContacts->getByTypeEntite( $types ) AS $contact ){
						$select = "";
						$membre = $mJurys->getMembreByType( $jury['jury_id'], 'président de jury');
						if( $contact['contact_id'] == $membre['contact_id']  ) $select = " selected ";
						$presid .= '<option value="'.$contact['contact_id'].'" '.$select.' >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
					}

					$rep_org_ref = "";
					foreach( $mContacts->getByTypeEntite('organisme référent') AS $contact ){
						$select = "";
						$membre = $mJurys->getMembreByType( $jury['jury_id'], 'représentant organisme référent');
						if( $contact['contact_id'] == $membre['contact_id'] ) $select = " selected ";
						$rep_org_ref .= '<option value="'.$contact['contact_id'].'" '.$select.' >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
					}

					$rep_forthac = "";
					foreach( $mContacts->getByTypeEntite('forthac') AS $contact ){
						$select = "";
						$membre = $mJurys->getMembreByType( $jury['jury_id'], 'représentant forthac');
						if( $contact['contact_id'] == $membre['contact_id'] ) $select = " selected ";
						$rep_forthac .= '<option value="'.$contact['contact_id'].'" '.$select.' >'.ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ('.$contact['entite_nom'].')' ).'</option>';
					}

					$this->view->title = "Modification du jury";

					$this->view->jury_id = $jury['jury_id'];
					$this->view->jury_adresse = $jury['jury_adresse'];
					$this->view->jury_cp = $jury['jury_cp'];
					$this->view->jury_ville = $jury['jury_ville'];
					$this->view->jury_date = $fDates->formatDate( $jury['jury_date'] );
					$this->view->branches = $s_branches;
					$this->view->fed_patro = $s_fed_patro;
					$this->view->fed_salar = $s_fed_salar;

					$this->view->rep_syndic_employ = $rep_syndic_employ;
					$this->view->rep_syndic_salar = $rep_syndic_salar;
					$this->view->presid = $presid;
					$this->view->rep_org_ref = $rep_org_ref;
					$this->view->rep_forthac = $rep_forthac;

				}
				
			}else{
				$this->_redirect( '/jurys/' );
			}
			
		}
		
		public function deleteAction(){
			
			if( $this->_request->getParam('id') > 0 ){
				
				$jury_id = $this->_request->getParam('id');

				$mJurys = new Model_Jury();
				$fDates = new Fonctions_Dates();

				if( $this->_request->isPost() ){

					if( $this->_request->getParam('del') == 'Oui' ){
						$mJurys->delete( " jury_id = $jury_id " );
						$this->_redirect( '/jurys/' );
					}else{
						$this->_redirect( '/jurys/details/?id='.$jury_id );
					}

				}

				$jury = $mJurys->get( $jury_id );

				$this->view->jury = $jury;

				$this->view->nb_candidats = count( $mJurys->getCandidats($jury_id) );
				$this->view->nb_membres = count( $mJurys->getMembres($jury_id) );

				$date = $fDates->formatDate( $jury['jury_date'] );
				$lieu = ucwords( $jury['jury_ville'] );
				
				$this->view->title = "Suppression du jury du $date à $lieu";
				
			}else{
				$this->_redirect( '/jurys/' );
			}
			
		}

		public function attributecandidatsAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');

			if( $this->_request->getParam('id') > 0 ){

				$jury_id = $this->_request->getParam('id');

				$mJurys = new Model_Jury();
				$fDates = new Fonctions_Dates();

				$jury = $mJurys->get($jury_id);
				$date = $fDates->formatDate( $jury['jury_date'] );
				$lieu = ucwords( $jury['jury_ville'] );

				$this->view->title = "Attribution des candidats pour le jury du $date à $lieu";

				$this->view->jury_id = $jury['jury_id'];
				$this->view->branche_id = $jury['branche_id'];

			}else{
				$this->_redirect('/jurys/');
			}

		}
		
	}