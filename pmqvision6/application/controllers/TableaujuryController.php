<?php
	class TableaujuryController extends Zend_Controller_Action {

		function init(){

			Zend_Layout::getMvcInstance()->setLayout('empty');

		}

		public function indexAction(){

			$front = Zend_Controller_Front::getInstance();

			$this->view->headLink()->appendStylesheet($front->getBaseUrl()."/css/tableau_resultats.css");

			$candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			$this->view->candidat_metier_id = $candidat_metier_id;

			$this->view->title = "Tableau de récapitulatif des résultats";

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
			$mCandidats = new Model_Candidat();
			$mCivilites = new Model_Civilite();
			$mEntites = new Model_Entite();
			$mOperations = new Model_Fiche();
			$mResultats = new Model_Resultat();
			$mOutils = new Model_Outil();
			$mBinomes = new Model_Binome();
			$mContacts = new Model_Contact();
			$mCivilites = new Model_Civilite();

			$fStrings = new Fonctions_Strings();
			$fDates = new Fonctions_Dates();

			$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);
			$metier = $mMetiers->get( $candidat_metier['metier_id'] );

			$this->view->returnlink = '/operations/visu/num/'.$metier['fiche_id'].'/#ui-tabs-5';
			$this->view->returnlib = "Retour à l'opération";

			$s_titre = $mMetiers->getTitre($metier['metier_id']);
			$this->view->titre = $s_titre['bloc1_lib'];
			if( isset( $s_titre['bloc2_lib'] ) ){
				$this->view->titre .= ' - '.$s_titre['bloc2_lib'];
				$specialite = $s_titre['bloc2_id'];
			}else{
				$specialite = null;
			}

			$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
			$civ = $mCivilites->get( $candidat['civilite_id'] );
			if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
			else $s_nom = "";
			$s_nom .= ' '.$candidat['personne_prenom'];
			$s_nom .= ' '.$candidat['personne_nom'];
			$this->view->nom = ucwords( $s_nom );

			$resultat = $mResultats->getLast($candidat_metier_id);

			if( $resultat['resultat_num_passage'] == 1 ){
				$this->view->type = "Positionnement";
			}else{
				$this->view->type = "Evaluation";
			}

			$entreprise = $mEntites->get($candidat['entite_id']);
			$nb = 45;
			if( strlen( $entreprise['entite_nom'] ) > $nb ) $this->view->entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
			else $this->view->entreprise = ucwords( $entreprise['entite_nom'] );

			$branche = $mEntites->get( $candidat['parent_id'] );
			$nb = 45;
			if( strlen( $branche['entite_nom'] ) > $nb ) $this->view->branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
			else $this->view->branche = ucwords( $branche['entite_nom'] );

			$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
			$org_ref = $mEntites->get( $corg_ref['entite_id'] );
			$nb = 45;
			if( strlen( $org_ref['entite_nom'] ) > $nb ) $this->view->organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
			else $this->view->organisme_referent = ucwords( $org_ref['entite_nom'] );
			
			$demarche = $mCandidatMetiers->getDemarche($candidat_metier_id);

			$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite);

			$demarch = $xmldemarche->getDemarche();
			$capacites = $demarch['capacites_base'];
			$this->view->nb_capacites = count($capacites);
			$this->view->capacites = $capacites;

			$resultat_outils = $mResultats->getResultats( $candidat_metier_id, $resultat['resultat_num_passage'] );

			foreach( $resultat_outils as $r_outil ){

				$outil = $mOutils->get( $r_outil->outil_id );

				switch( $outil['outil_libelle'] ){
					case 'entretien' :
						$notes = explode( '@', $r_outil->resultat_valeur );
						foreach( $notes as &$note ){
							if( $note == 1 ) {$note = "maîtrisée";}
							if( $note == 0 ) {$note = "non maîtrisée";}
						if( $note == 100 ) {$note = "&agrave; compléter";}
						}
						$this->view->bilan = $notes;
						break;
				}

			}

			$this->view->commentaire = $resultat['resultat_commentaire_jury'];

		}

	}