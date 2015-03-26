<?php
	class OperationsController extends Zend_Controller_Action {
	
		public function init(){
		
		}
		
		public function indexAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');
			
			$this->view->title = 'Opérations';
			
    		$auth = Zend_Auth::getInstance()->getIdentity();
			$role = $auth->role;
			$this->view->role = $role;
			$entite_id = $auth->entite_id;
	    	
	    	$mFiche = new Model_Fiche();
	    	if( $role == "forthac" ){
	    		$nb = $mFiche->count();
	    		$nb_p = $mFiche->countprojet();
	    	}elseif( $role == 'branche' ){
				//$nb = count( $mFiche->getByBranche($entite_id) );
				$nb_p = count( $mFiche->getByBranche($entite_id, 1) );
				$nb = count( $mFiche->getByBranche($entite_id, 1) ) + count( $mFiche->getByBranche($entite_id, 0) );
			}else{
	    		$nb = $mFiche->count( $entite_id );
	    		$nb_p = $mFiche->countprojet( $entite_id );
	    	}
	    	$this->view->nb = $nb;
	    	$this->view->nb_p = $nb_p;
	    				
		}
		
		public function newAction(){
	    	
	    	$onglets = array( 'Etape 1', 'Etape 2', 'Etape 3', 'Etape 4', 'Etape 5' );
			$this->view->ong = $onglets;
	    	
	    	if($this->_request->isPost()){
	    			    		
	    		$entreprise = $this->_getParam('entreprise');
	    		$c_entreprise = $this->_getParam('c_entreprise');
	    		$org_ref = $this->_getParam('org_ref');
	    		$c_org_ref = $this->_getParam('c_org_ref');
	    		
	    		$greta_ref = $this->_getParam('greta_ref');
	    		$c_greta_ref = $this->_getParam('c_greta_ref');
	    		
	    		
	    		$org_del = $this->_getParam('org_del');
	    		$c_org_del = $this->_getParam('c_org_del');
	    		
	    		$mOperation = new Model_Fiche();
	    		
	    		//creation fiche
	    		$operation_id = $mOperation->create();
	    		
	    		//liaisons contacts fiche
	    		$mOperation->addcontact($operation_id, $entreprise, $c_entreprise);
	    		$mOperation->addcontact($operation_id, $org_ref, $c_org_ref);
	    		$mOperation->addcontact($operation_id, $org_del, $c_org_del);
	    		if($greta_ref=='' || $greta_ref > 0)
	    		{
	    			$mOperation->addcontact($operation_id, $greta_ref, $c_greta_ref);
	    		}
	    		
	    		$this->_redirect("/operations/visu/num/$operation_id");
	    		
	    	}else{
	    		
		    	$mEntite = new Model_Entite();
		    	$mContact = new Model_Contact();
		    	$auth = Zend_Auth::getInstance()->getIdentity();
		    	$role_id= $auth->role;
		    	if($role_id != 'forthac')
		    	{
					$entite_id= $auth->entite_id;
		    	}else{
		    		$entite_id = 0;
		    	}
		    	$listeEntreprises = $mEntite->getByTypeEntiteActives('entreprise');
		    	$this->view->listeEntreprises = $listeEntreprises;
		    	
		    	$listeOrgRef = $mEntite->getByTypeEntiteActives('organisme référent',$entite_id);
		    	$this->view->listeOrgRef = $listeOrgRef;
		    	
		    	$listeGretaRef = $mEntite->getByTypeEntiteActives('greta',$entite_id);
		    	$this->view->listeGretaRef = $listeGretaRef;
		    	 
		    	
		    	$listeDel = $mEntite->getByTypeEntiteActives('délégation');
		    	$this->view->listeDel = $listeDel;
		    	
		    	$this->view->title = 'Création d\'une nouvelle opération';
		    	
	    	}
	    	
	    }
	    
	    public function visuAction(){

	    	if( $this->_getParam('num') > 0 ){

	    		$operation_id = $this->_getParam('num');

				$mContactsFiche = new Model_ContactsFiche();
				$mOperations = new Model_Fiche();
				$auth = Zend_Auth::getInstance()->getIdentity();
				
				$entite_id = $auth->entite_id;
				$role = $auth->role;

				if( $role != 'forthac' && $role != 'branche' ){
					if( !$mContactsFiche->get($operation_id, $entite_id) ){
						$this->_redirect( '/operations/' );
					}
				}elseif( $role == 'branche' ){
					$operations = $mOperations->getByBranche($entite_id);
					$test=0;
					foreach( $operations as $operation ){
						if( $operation_id == $operation['fiche_id'] ) $test++;
					}
					if( $test == 0 ) $this->_redirect ('/operations/');
				}
	    				
			$contactFiche = new Model_ContactsFiche();
			$mEntite = new Model_Entite();
            $entites = $contactFiche->get($operation_id,0,0);
           
             foreach( $entites as $entite ){
           	 $id_entite = $entite['entite_id'];
        
	         $entiteDonnees = $mEntite->getEntreprisesById($id_entite);
	           	if(count($entiteDonnees) >0)
			           	{
			           		$entite_nom_auto = $entiteDonnees[0]['entite_nom'];
							$this->view->entite_id_auto = $entiteDonnees[0]['entite_id'];
			           	}
	              }
	    		$this->view->title = "Opération n°".$operation_id." (entreprise ".$entite_nom_auto.")";

				$operation = $mOperations->get($operation_id);

				if( $operation['fiche_acces_candidats'] == 1 ){
					$onglets = array(
						'visu1/num/'.$operation_id		=>	'Informations générales',
						'visu2/num/'.$operation_id		=>	'Contacts',
						'visu3/num/'.$operation_id		=>	'Démarches/Titres',
						'visu4/num/'.$operation_id		=>	'Binômes',
						'visu5/num/'.$operation_id		=>	'Candidats'
					);
				}else{
					$onglets = array(
						'visu1/num/'.$operation_id		=>	'Informations générales',
						'visu2/num/'.$operation_id		=>	'Contacts',
						'visu3/num/'.$operation_id		=>	'Démarches/Titres',
						'visu4/num/'.$operation_id		=>	'Binômes'
					);
				}

				/*********/
				
				$this->view->onglets = $onglets;
				$this->view->controller = $this->_request->getParam('controller');
				/*********/

			}

		}

		public function visu1Action(){

			Zend_Layout::getMvcInstance()->disableLayout();

			$auth = Zend_Auth::getInstance()->getIdentity();
			$this->view->role = $auth->role;

			if( $this->_getParam('num') > 0 ){

				$operation_id = $this->_getParam('num');

				$mOperation = new Model_Fiche();
				$mObjectif = new Model_Objectif();
				$fDates = new Fonctions_Dates();

				$operation = $mOperation->get($operation_id);
				
						
			$contactFiche = new Model_ContactsFiche();
			$mEntite = new Model_Entite();
            $entites = $contactFiche->get($operation_id,0,0);
           
             foreach( $entites as $entite ){
           	 $id_entite = $entite['entite_id'];
        
	         $entiteDonnees = $mEntite->getEntreprisesById($id_entite);
	           	if(count($entiteDonnees) >0)
			           	{
			           		$this->view->entite_nom_auto = $entiteDonnees[0]['entite_nom'];
							$this->view->entite_id_auto = $entiteDonnees[0]['entite_id'];
			           	}
	              }

				$operation->fiche_date_creation = $fDates->formatDate($operation->fiche_date_creation);
				$operation->fiche_date_modif = $fDates->formatDate($operation->fiche_date_modif);
				$operation->fiche_date_meo = $fDates->formatDate($operation->fiche_date_meo);
				$this->view->operation = $operation;

				$this->view->objectifs = $mObjectif->fetchAll();
				$objectif = $mObjectif->get( $operation->objectif_id );
				if( $objectif != null ){
					$this->view->objectif = $objectif;
				}else{
					$this->view->objectif = array('objectif_id' => '', 'objectif_libelle' => '');
				}

			}

		}

		public function visu2Action(){

			Zend_Layout::getMvcInstance()->disableLayout();
			
			$auth = Zend_Auth::getInstance()->getIdentity();
			$this->view->role = $auth->role;

			if( $this->_getParam('num') > 0 ){

				$operation_id = $this->_getParam('num');

				$mOperation = new Model_Fiche();
				$mEntite = new Model_Entite();
				$mContact = new Model_Contact();
				$mCivilite = new Model_Civilite();

				$this->view->operation = $mOperation->get($this->_getParam('num'));

				$entites = $mOperation->getEntites( $operation_id );

				//entreprise
				if( isset( $entites['entreprise_id'] ) && $entites['entreprise_id'] != '' ){
					$entreprise = $mEntite->get( $entites['entreprise_id'] );
					$this->view->entreprise = $entreprise;
				}else{
					$this->view->entreprise = '';
				}
				if( isset( $entites['contact_entreprise_id'] ) && $entites['contact_entreprise_id'] != '' ){
					$contact_entreprise = $mContact->getPersonne( $entites['contact_entreprise_id'] );
					$civ = $mCivilite->get($contact_entreprise['civilite_id']);
					$contact_entreprise['civilite'] = $civ->civilite_abrege;
					if( $contact_entreprise['civilite'] == 'nc' ) $contact_entreprise['civilite'] = "";
					$this->view->contactEntreprise = $contact_entreprise;
				}else{
					$this->view->contactEntreprise = '';
				}

				//org référent
				if( isset( $entites['org_ref_id'] ) && $entites['org_ref_id'] != '' ){
					$org_ref = $mEntite->get( $entites['org_ref_id'] );
					$this->view->org_ref = $org_ref;
				}else{
					$this->view->org_ref = '';
				}
				if( isset( $entites['contact_org_ref_id'] ) && $entites['contact_org_ref_id'] != '' ){
					$contact_org_ref = $mContact->getPersonne( $entites['contact_org_ref_id'] );
					$civ = $mCivilite->get($contact_org_ref['civilite_id']);
					$contact_org_ref['civilite'] = $civ->civilite_abrege;
					if( $contact_org_ref['civilite'] == 'nc' ) $contact_org_ref['civilite'] = "";
					$this->view->contactOrgRef = $contact_org_ref;
				}else{
					$this->view->contactOrgRef = '';
				}
				//GRETA
				if( isset( $entites['greta_ref_id'] ) && $entites['greta_ref_id'] != '' ){
					$greta_ref = $mEntite->get( $entites['greta_ref_id'] );
					

					
					$this->view->greta_ref = $greta_ref;
				}else{
					$this->view->greta_ref = '';
				}
				if( isset( $entites['contact_greta_ref_id'] ) && $entites['contact_greta_ref_id'] != '' ){
					$contact_greta_ref = $mContact->getPersonne( $entites['contact_greta_ref_id'] );
					$civ = $mCivilite->get($contact_greta_ref['civilite_id']);
					$contact_greta_ref['civilite'] = $civ->civilite_abrege;
					if( $contact_greta_ref['civilite'] == 'nc' ) $contact_greta_ref['civilite'] = "";
					$this->view->contactgretaRef = $contact_greta_ref;
				}else{
					$this->view->contactgretaRef = '';
				}
				
				
				

				//déléagtion
				if( isset( $entites['del_id'] ) && $entites['del_id'] != '' ){
					$del = $mEntite->get( $entites['del_id'] );
					$this->view->org_del = $del;
				}else{
					$this->view->org_del = '';
				}
				if( isset( $entites['contact_del_id'] ) && $entites['contact_del_id'] != '' ){
					$contact_org_del = $mContact->getPersonne( $entites['contact_del_id'] );
					$civ = $mCivilite->get($contact_org_del['civilite_id']);
					$contact_org_del['civilite'] = $civ->civilite_abrege;
					if( $contact_org_del['civilite'] == 'nc' ) $contact_org_del['civilite'] = "";
					$this->view->contactOrgDel = $contact_org_del;
				}else{
					$this->view->contactOrgDel = '';
				}

				//liste entreprises
				$this->view->entreprises = $mEntite->getEntreprisesActif();

				//liste org referent
				$auth = Zend_Auth::getInstance()->getIdentity();
		    	$role_id= $auth->role;
		    	if($role_id != 'forthac')
		    	{
					$entite_id= $auth->entite_id;
		    	}else{
		    		$entite_id = 0;
		    	}
		    
				$this->view->organismes_ref = $mEntite->getOrganismesReferentsActif($entite_id);
				
				$this->view->greta = $mEntite->getgretaReferentsActif();
				
				
				

				//liste delegations
				$this->view->delegations = $mEntite->getDelegationsVisible();

			}

		}

		public function visu3Action(){

			Zend_Layout::getMvcInstance()->disableLayout();
			
			$auth = Zend_Auth::getInstance()->getIdentity();
			$this->view->role = $auth->role;

			if( $this->_getParam('num') > 0 ){

				$operation_id = $this->_getParam('num');

				$mOperation = new Model_Fiche();
				$mDemarches = new Model_Demarche();
				$mMetiers = new Model_Metier();
				$fDates = new Fonctions_Dates();

				$this->view->operation = $mOperation->get( $operation_id );

				//démarche / titre
				$metiers = $mOperation->getMetiers($operation_id);
				foreach( $metiers as &$metier ){

					//titres
					$titre = $mMetiers->getTitre($metier['metier_id']);
					$metier['titre'] = $titre['bloc1_lib'];
					if( $titre['bloc2_id'] > 0 ){
						$metier['titre'] .= ' / '.$titre['bloc2_lib'];
					}

					if( $metier['metier_date_envoi_dossiers'] != '' ) $metier['metier_date_envoi_dossiers'] = $fDates->formatDate($metier['metier_date_envoi_dossiers']);

				}

				$this->view->metiers = $metiers;
				
				$this->view->demarches = $mDemarches->fetchAll();

			}

		}

		public function visu4Action(){

			Zend_Layout::getMvcInstance()->disableLayout();

			$auth = Zend_Auth::getInstance()->getIdentity();
			$this->view->role = $auth->role;

			if( $this->_getParam('num') > 0 ){

				$operation_id = $this->_getParam('num');

				$mOperation = new Model_Fiche();
				$mMetiers = new Model_Metier();

				$metiers = $mOperation->getMetiers($operation_id);
				foreach( $metiers as &$metier ){

					//titres
					$titre = $mMetiers->getTitre($metier['metier_id']);
					$demarche = $mMetiers->getDemarche($metier['metier_id']);
					$metier['titre'] = $titre['bloc1_lib'];
					$metier['abrege'] = $demarche['demarche_abrege'];
					if( $titre['bloc2_id'] > 0 ){
						$metier['titre'] .= ' / '.$titre['bloc2_lib'];
					}

				}
				$this->view->metiers = $metiers;


			}

		}

		public function visu5Action(){

			Zend_Layout::getMvcInstance()->disableLayout();

			$auth = Zend_Auth::getInstance()->getIdentity();
			$this->view->role = $auth->role;

			if( $this->_getParam('num') > 0 ){

				$operation_id = $this->_getParam('num');

				$mOperation = new Model_Fiche();
				$mMetiers = new Model_Metier();
				$mCandidatMetiers = new Model_CandidatMetier();
						
			$nb_c_positionnement = 0;
			$nb_c_formation = 0;
			$nb_c_evaluation = 0;
			$nb_c_admissible =0;
			$nb_c_certifie = 0;
			$nb_c_abandon =0;
			$nb_total = 0;
				$this->view->operation = $mOperation->get($operation_id);

				$metiers = $mOperation->getMetiers($operation_id);
				foreach( $metiers as &$metier ){

					//titres
					$titre = $mMetiers->getTitre($metier['metier_id']);
					$demarche = $mMetiers->getDemarche($metier['metier_id']);
					$metier_id_base = $metier['metier_id'];
					$metier['titre'] = $titre['bloc1_lib'];
					$metier['abrege'] = $demarche['demarche_abrege'];
					
			$nb_c_positionnement += count( $mCandidatMetiers->fetchAll( " etat_id = 1 AND metier_id = '".$metier_id_base."'"  ) );
			$nb_c_formation += count( $mCandidatMetiers->fetchAll( " etat_id = 2 AND metier_id = '".$metier_id_base."'"  ) );
			$nb_c_evaluation += count( $mCandidatMetiers->fetchAll( " etat_id = 3 AND metier_id = '".$metier_id_base."'"  ) );
			$nb_c_admissible += count( $mCandidatMetiers->fetchAll( " etat_id = 4 AND metier_id = '".$metier_id_base."'"   ) );
			$nb_c_certifie += count( $mCandidatMetiers->fetchAll( " etat_id = 5 AND metier_id = '".$metier_id_base."'"  ) );
			$nb_c_abandon += count( $mCandidatMetiers->fetchAll( " etat_id = 6 AND metier_id = '".$metier_id_base."'" ) );
					
					
					if( $titre['bloc2_id'] > 0 ){
						$metier['titre'] .= ' / '.$titre['bloc2_lib'];
					}

				}

				$this->view->metiers = $metiers;
				
		
				
			$nb_total =$nb_c_positionnement+$nb_c_formation+$nb_c_evaluation+$nb_c_admissible+$nb_c_certifie+$nb_c_abandon;
			
			$this->view->nb_c_positionnement = $nb_c_positionnement;
			$this->view->nb_c_formation = $nb_c_formation;
			$this->view->nb_c_evaluation = $nb_c_evaluation;
			$this->view->nb_c_admissible = $nb_c_admissible;
			$this->view->nb_c_certifie = $nb_c_certifie;
			$this->view->nb_c_abandon = $nb_c_abandon;
			$this->view->nb_candidatures = $nb_total ;

			}

		}

		public function addcandidatAction(){

			if( $this->_request->getParam('operation_id') > 0 ){

				$operation_id = $this->_request->getParam('operation_id');

				$this->view->title = "Ajouter un candidat à l'opération n°$operation_id";
				$this->view->operation_id = $operation_id;

			}

		}

		public function addnewcandidatAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/suggestion.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/suggestion_entreprises.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');

			if( $this->_request->getParam('operation_id') > 0 ){

				$operation_id = $this->_request->getParam('operation_id');


				$mCivilites = new Model_Civilite();
				$mOperations = new Model_Fiche();
				$mMetiers = new Model_Metier();
				$mPersonnes = new Model_Personne();
				$mCandidats = new Model_Candidat();
				$mContacts= new Model_Contact();
				$mCandidatMetiers = new Model_CandidatMetier();

				$fDates = new Fonctions_Dates();

				if( $this->_request->isPost() ){

					$d = $_POST;
					
					
					//personne
					$date_creation = date( "Y-m-d" );
					$personne_id = $mPersonnes->add( $d['civilite_id'], $d['personne_nom'], $d['personne_prenom'],  $d['personne_date_naissance'], '', '', '', $d['personne_poste'], $date_creation, $d['entite_id'] );
					//$personne_id = $mPersonnes->add($civilite_id, $nom, $prenom, $date_naissance, $tel, $port, $mail, $poste, $date_creation, $entite_id);

					//candidat
					if( $d['candidat_anciennete'] != '' ) $anciennete = $fDates->unformatDate( $d['candidat_anciennete'] );
					$data = array(
						'candidat_code'			=>	$d['candidat_code'],
						'candidat_anciennete'	=>	$anciennete,
						'candidat_cursus'		=>	$d['candidat_cursus'],
						'candidat_contrat'		=>	$d['candidat_contrat'],
						'personne_id'			=>	$personne_id
					);
					$candidat_id = $mCandidats->insert( $data );

					//candidat metier
					$evaluateur_id = $mMetiers->getEvaluateurIDDefault($d['metier_id']);
					$expert_id = $mMetiers->getExpertIDDefault($d['metier_id']);
					
					$demarche = $mMetiers->getDemarche($d['metier_id']);
					
					if($demarche['demarche_abrege'] == 'ccsp' || $demarche['demarche_abrege'] == 'cqpbranche')
					{
					$etatbase = 10;
					}else{
					$etatbase = 1;	
					}
					$data = array(
						'candidat_id'	=>	$candidat_id,
						'metier_id'		=>	$d['metier_id'],
						'etat_id'		=>	$etatbase,
						'tuteur_id'		=>	$evaluateur_id,
						'expert_id'		=>	$expert_id
					);
					
					$mCandidatMetiers->insert($data);


				$operation_id = $this->_request->getParam('operation_id');
				$this->_redirect('operations/visu/num/'.$operation_id.'#ui-tabs-5');

				}else{

					$civilites = $mCivilites->fetchAll();
					$select_civilite = "";
					$select = "";
					foreach( $civilites as $civilite ){
						if( $civilite['civilite_abrege'] == 'nc' ) $select = " selected ";
						$select_civilite .= '<option value="'.$civilite['civilite_id'].'" '.$select.' >'.ucfirst( $civilite['civilite_abrege'] ).'</option>';
					}

					$metiers = $mOperations->getMetiers( $operation_id );
					$s_metiers = "";
					foreach( $metiers as $metier ){
						$titre = $mMetiers->getTitre($metier['metier_id']);
						if( isset( $titre['bloc2_lib'] ) ) $bloc2 = ' / '.$titre['bloc2_lib'];
						else $bloc2 = "";
						$s_metiers .= '<option value="'.$metier['metier_id'].'" >'.ucwords( $titre['demarche_abrege'].' - '.$titre['bloc1_lib'].$bloc2 ).'</option>';
					}
					$this->view->metiers = $s_metiers;

					$this->view->title = "Créer un nouveau candidat pour l'opération n°$operation_id";
					$this->view->operation_id = $operation_id;
					$this->view->civilites = $select_civilite;
					$this->view->cursus = 'non';
					
					
			$contactFiche = new Model_ContactsFiche();
			$mEntite = new Model_Entite();
            $entites = $contactFiche->get($operation_id,0,0);
           
             foreach( $entites as $entite ){
           	 $id_entite = $entite['entite_id'];
        
	         $entiteDonnees = $mEntite->getEntreprisesById($id_entite);
	           	if(count($entiteDonnees) >0)
			           	{
			           		$this->view->entite_nom_auto = $entiteDonnees[0]['entite_nom'];
							$this->view->entite_id_auto = $entiteDonnees[0]['entite_id'];
			           	}
	              }

				}
			}

		}

		public function addexistcandidatAction(){

			$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl()."/css/ui.jqgrid.css");
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/i18n/grid.locale-fr.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.jqGrid.min.js','text/javascript');

			if( $this->_request->getParam('operation_id') > 0 ){

				$operation_id = $this->_request->getParam('operation_id');

				$mCandidats = new Model_Candidat();
				$mOperations = new Model_Fiche();
				$mMetiers = new Model_Metier();

				$this->view->title = "Ajouter un candidat existant à l'opération n°$operation_id";
				$this->view->operation_id = $operation_id;

				$metiers = $mOperations->getMetiers( $operation_id );
				$s_metiers = "";
				foreach( $metiers as $metier ){
					$titre = $mMetiers->getTitre($metier['metier_id']);
					if( isset( $titre['bloc2_lib'] ) ) $bloc2 = ' / '.$titre['bloc2_lib'];
					else $bloc2 = "";
					$s_metiers .= '<option value="'.$metier['metier_id'].'" >'.ucwords( $titre['demarche_abrege'].' - '.$titre['bloc1_lib'].$bloc2 ).'</option>';
				}
				$this->view->metiers = $s_metiers;

			}

		}

		public function delcandidatAction(){

			if( $this->_request->getParam('candidat_metier_id') > 0 ){

				$candidat_metier_id = $this->_request->getParam('candidat_metier_id');

				$mResultats = new Model_Resultat();
				$mCandidatMetiers = new Model_CandidatMetier();
				$mMetiers = new Model_Metier();

				$metier = $mCandidatMetiers->getMetier($candidat_metier_id);
				$operation_id = $metier['fiche_id'];

				if( $this->_request->isPost() ){

					$del = $this->_request->getPost('del');
					if ( $del == 'Oui' ) {

						//supprimer
						$mCandidatMetiers->delete( " candidat_metier_id = $candidat_metier_id " );

						$this->_redirect('/operations/visu/num/'.$operation_id.'#ui-tabs-5');
					}else{
						$this->_redirect('/operations/visu/num/'.$operation_id.'#ui-tabs-5');
					}

				}else{

					$this->view->title = "Suppression d'un candidat de l'opération";
					if( count( $mResultats->fetchAll(" candidat_metier_id = $candidat_metier_id ") ) > 0 ){
						$this->view->question = "Vous ne pouvez pas enlever ce candidat : il a des résultats pour cette opération.";
						$this->view->resultats = "true";
						$this->view->operation_id = $operation_id;
					}else{
						$this->view->candidat_metier_id = $candidat_metier_id;
						$this->view->question = "Êtes-vous sûr d'enlever ce candidat de l'opération ?";
						$this->view->resultats = "false";
					}

				}

			}

		}
	    
	}