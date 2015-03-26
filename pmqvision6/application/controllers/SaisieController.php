<?php
class SaisieController extends Zend_Controller_Action
{
	public function init(){
		
	}
	
	public function indexAction(){

		if(isset($_GET['enregistrement']))
		{
			
			?>
			<SCRIPT LANGUAGE="JavaScript">document.location.href="<?php echo $this->_request->getBaseUrl();?>/saisie/index/metier/<?php echo $this->_request->getParam( 'candidat_metier_id' );?>";</SCRIPT>';
			<?php 
		}
		
		
		$this->view->title = "Saisie des notes";

		$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/datepicker.js','text/javascript');
		$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/saisie.js');
		$this->view->headLink()->appendStylesheet($this->_request->getBaseUrl().'/css/saisie.css');
		
		$candidat_metier_id = $this->_getParam('metier');
		$this->view->candidat_metier_id = $candidat_metier_id;

		$mCandidatMetiers = new Model_CandidatMetier();
		$mMetiers = new Model_Metier();
		$mFiches = new Model_Fiche();
		$mCandidats = new Model_Candidat();
		$fDates = new Fonctions_Dates();
		$mResultats = new Model_Resultat();
		$mOutils = new Model_Outil();

		$Demarche = $mCandidatMetiers->getDemarche( $candidat_metier_id );
		$demarche = $Demarche['demarche_abrege'];
		$this->view->demarche = $demarche;

		$candidat_metier = $mCandidatMetiers->get( $candidat_metier_id );

		$this->view->candidat_id = $candidat_metier['candidat_id'];

		$metier = $mMetiers->get( $candidat_metier['metier_id'] );
		$this->view->fiche_id = $metier['fiche_id'];

		//liste des candidats
		$candidats = $mFiches->getCandidats( $metier['fiche_id'] );
		$listeCandidats = "";
		foreach( $candidats as $candidat ){
			$s = "";
			if( $candidat['candidat_metier_id'] == $candidat_metier_id ) $s = ' selected="selected" ';
			$listeCandidats .= '<option value="'.$candidat['candidat_metier_id'].'" '.$s.' >'.ucwords( $candidat['personne_nom'].' '.$candidat['personne_prenom'] ).'</option>';
		}
		$this->view->listeCandidats = $listeCandidats;

		//informations candidat
		$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
		$this->view->civilite = ucfirst( $candidat['civilite_libelle'] );
		$this->view->nom = ucfirst( $candidat['personne_nom'] );
		$this->view->prenom = ucfirst( $candidat['personne_prenom'] );
		$this->view->anciennete = $fDates->getNbYears( $candidat['candidat_anciennete'] );
		$this->view->age = $fDates->getNbYears( $candidat['personne_date_naissance'] );
		$this->view->contrat = ucfirst( $candidat['candidat_contrat'] );
		$this->view->poste = ucfirst( $candidat['personne_poste'] );
		$this->view->date_creation = $fDates->formatDate( $candidat['personne_date_creation'] );
		$this->view->cursus = $candidat['candidat_cursus'];
		//titre vise
		$Titre = $mMetiers->getTitre($candidat_metier['metier_id']);
		$t = $Titre['bloc1_lib'];
		if( isset( $Titre['bloc2_lib'] ) ) $t .= ' / '.$Titre['bloc2_lib'];
		$this->view->titre = $t;
		//autres titres
		$metiers = $mCandidatMetiers->getOtherMetier($candidat_metier['candidat_id'], $candidat_metier['metier_id']);
		if($metiers != false){
			$listeTitres = "";
			foreach( $metiers as $m ){
				$t1 = $mMetiers->getTitre($m['metier_id']);
				$listeTitres .= '<li>';
				$listeTitres .= ucfirst( $t1['demarche_abrege'] ).' '.$t1['bloc1_ab'];
				if( isset( $t1['bloc2_lib'] ) ) $listeTitres .= ' - '.$t1['bloc2_lib'];
				$listeTitres .= '</li>';
			}
			$this->view->titres = '<ul>'.$listeTitres.'</ul>';
		}else{
			$this->view->titres = "Aucune";
		}

		$this->view->passage_max = $mResultats->getLastPassage($candidat_metier_id);

		$getInfoDemarche = new Fonctions_XmlDemarche($Demarche['demarche_id'], $metier['metier_xml'], $Titre['bloc1_ab'], $Titre['bloc2_id']);
		/******************CQP***********************/
		if( $demarche == 'cqp' ){

			$infoDem = $getInfoDemarche->getDemarche();

			$this->view->listeCapacitesBase = $infoDem['capacites_base'];
			$this->view->listeQuestionBase = $infoDem['question_base'];
			$this->view->listeLivretBase = $infoDem['livret_base'];
			$this->view->nbQuestion = count($infoDem['question_base']);

		/******************CCSP*********************/
		}elseif( $demarche == 'ccsp' ){

			$infoDem = $getInfoDemarche->getDemarche();
			$this->view->listeQuestion = $infoDem['questions'];
			$this->view->listeObservations = $infoDem['observations'];
			$this->view->listeEntretiens = $infoDem['entretiens'];

		/****************DIPLOMES***********************/
		}elseif( $demarche == 'diplome' ){
			
			
			$infoDem = $getInfoDemarche->getDemarche();
			$this->view->listeModule = $infoDem['liste_module'];
		//	$this->view->listeObservations = $infoDem['observations_diplome'];
		//	$this->view->listeEntretiens = $infoDem['entretiens_diplome'];
			//echo 'Diplôme';
		}
		
		elseif( $demarche == 'cqpbranche' ){
			$infoDem = $getInfoDemarche->getDemarche();
			$this->view->listeEntretiensBranche = $infoDem['capacites_base'];
			//echo 'Diplôme';
		}
		

		$this->view->demarche = $demarche;

		/****************************resultats****************************/
		$resultat = $mResultats->getLast($candidat_metier_id);
		$this->view->resultat_id = $resultat['resultat_id'];
		if( $resultat == false ){
			$mResultats->add($candidat_metier_id);
			$this->_redirect( '/saisie/index/metier/'.$candidat_metier_id );
		}else{
			$resultats = $mResultats->getResultatsCandidat($candidat_metier_id);
			//si resultats existent
			if( !empty($resultats) ){

//				$this->view->resultat_id = $resultats[0]->resultat_id;

				switch( $demarche ){
					case 'cqpbranche' :
						foreach( $resultats as $resultat ){
							$outil = $mOutils->get( $resultat->outil_id );
							switch( $outil['outil_libelle'] ){
								case 'debutFormationBanche' :
									$this->view->resultatIdDebutFormationBranche = $resultat->resultat_outil_id;
									$this->view->date_debut_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
									break;
								case 'finFormationBanche' :
										$this->view->resultatIdFinFormationBranche = $resultat->resultat_outil_id;
										$this->view->date_fin_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
										break;
								case 'entretienNoteBanche' :
									$this->view->resultatIdentretiensBranche = $resultat->resultat_outil_id;
									$this->view->date_entretien_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultEnt = explode('@',$resultat->resultat_valeur);
									foreach($resultEnt as $idx => $entNote){
										$getNote = explode('_', $entNote);
										$ent['notes'][$idx] = $getNote[0];
									}
									case 'entretienCommentaireBanche' :
										$this->view->resultatIdentretiensCommentaireBranche = $resultat->resultat_outil_id;
										$resultEnt = explode('@£$@',$resultat->resultat_valeur);
										foreach($resultEnt as $idx => $entCommentaire){
											$ent['commentaire'][$idx] = $entCommentaire;
										}	
									
									$this->view->entretienBranche = $ent;
									break;
							}
						}
						break;
					case 'cqp' :
						foreach( $resultats as $resultat ){
							$outil = $mOutils->get( $resultat->outil_id );
							switch( $outil['outil_libelle'] ){
								case 'livret' :
									$this->view->roIdLivret = $resultat->resultat_outil_id;
									$this->view->dateLivret = Fonctions_Dates::formatDate($resultat->resultat_date);
									break;
									
								case 'livretacquis' :
									$this->view->roIdLivretAquis = $resultat->resultat_outil_id;
									$livret['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultLivret = explode('@',$resultat->resultat_valeur);
									foreach($resultLivret as $idx => $LivretNote){
										$getNote = explode('_', $LivretNote);
										 $livretacquis['notes'][$idx] = $getNote[0];
										 $livretacquis['totaux'][$idx] = $getNote[1];
									}
									$this->view->livretacquisNote = $livretacquis;
									break;	
									
								case 'questionnaire' :
									$this->view->roIdQuestionnaire = $resultat->resultat_outil_id;
									$quest['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultQuest = explode('@',$resultat->resultat_valeur);
									foreach($resultQuest as $idx => $questNote){
										$getNote = explode('_', $questNote);
										$quest['notes'][$idx] = $getNote[0];
									}
									$this->view->questionnaire = $quest;
									break;
									
								
									
									
								case 'observation' :
									$this->view->roIdObservation = $resultat->resultat_outil_id;
									$obs['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultObs = explode('@',$resultat->resultat_valeur);
									foreach($resultObs as $idx => $obsNote){
										$getNote = explode('_', $obsNote);
										$obs['notes'][$idx] = $getNote[0];
										$obs['totaux'][$idx] = $getNote[1];
									}
									$this->view->observation = $obs;
									break;
								case 'entretien' :
									$this->view->roIdEntretien = $resultat->resultat_outil_id;
									$ent['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultEnt = explode('@',$resultat->resultat_valeur);
									foreach($resultEnt as $idx => $entNote){
										$getNote = explode('_', $entNote);
										$ent['notes'][$idx] = $getNote[0];
									}
									$this->view->entretien = $ent;
									break;
							}
						}
						break;
					case 'ccsp' :
						foreach( $resultats as $resultat ){
							$outil = $mOutils->get( $resultat->outil_id );
							switch( $outil['outil_libelle'] ){
								case 'questionnaire' :
									$this->view->roIdQuestionnaire = $resultat->resultat_outil_id;
									$quest['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultQuest = explode('@',$resultat->resultat_valeur);
									foreach($resultQuest as $idx => $questNote){
										$getNote = explode('_', $questNote);
										$quest['notes'][$idx] = $getNote[0];
										$quest['actif'][$idx] = $getNote[2];
									}
									$this->view->questionnaire = $quest;
									break;
								case 'observation' :
									$this->view->roIdObservation = $resultat->resultat_outil_id;
									$obs['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultObs = explode('@',$resultat->resultat_valeur);
									foreach($resultObs as $idx => $obsNote){
										$getNote = explode('_', $obsNote);
										$obs['notes'][$idx] = $getNote[0];
										$obs['actif'][$idx] = $getNote[2];
									}
									$this->view->observation = $obs;
									break;
								case 'entretien' :
									$this->view->roIdEntretien = $resultat->resultat_outil_id;
									$ent['date'] = Fonctions_Dates::formatDate($resultat->resultat_date);
									$resultEnt = explode('@',$resultat->resultat_valeur);
									foreach($resultEnt as $idx => $entNote){
										$getNote = explode('_', $entNote);
										$ent['notes'][$idx] = $getNote[0];
										$ent['actif'][$idx] = $getNote[2];
									}
									$this->view->entretien = $ent;
									break;
								case 'compréhension orale' :
									$this->view->roIdCo = $resultat->resultat_outil_id;
									$this->view->coDate = null;
									$this->view->coValue = $resultat->resultat_valeur;
									break;
								case 'expression orale' :
									$this->view->roIdEo = $resultat->resultat_outil_id;
									$this->view->eoDate = null;
									$this->view->eoValue = $resultat->resultat_valeur;
									break;
								case 'compréhension écrite' :
									$this->view->roIdCe = $resultat->resultat_outil_id;
									$this->view->ceDate = null;
									$this->view->ceValue = $resultat->resultat_valeur;
									break;
								case 'expression écrite' :
									$this->view->roIdEe = $resultat->resultat_outil_id;
									$this->view->eeDate = null;
									$this->view->eeValue = $resultat->resultat_valeur;
									break;
								case 'raisonnement cognitif, logique et numérique' :
									$this->view->roIdRcln = $resultat->resultat_outil_id;
									$this->view->rclnDate = null;
									$this->view->rclnValue = $resultat->resultat_valeur;
									break;
								case 'repères spatio-temporels' :
									$this->view->roIdRst = $resultat->resultat_outil_id;
									$this->view->rstDate = null;
									$this->view->rstValue = $resultat->resultat_valeur;
									break;
									
								case 'compréhension orale reperage' :
									$this->view->roIdCore = $resultat->resultat_outil_id;
									$this->view->coDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->coValuere = $resultat->resultat_valeur;
									break;
								case 'expression orale reperage' :
									$this->view->roIdEore = $resultat->resultat_outil_id;
									$this->view->eoDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->eoValuere = $resultat->resultat_valeur;
									break;
								case 'compréhension écrite reperage' :
									$this->view->roIdCere = $resultat->resultat_outil_id;
									$this->view->ceDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->ceValuere = $resultat->resultat_valeur;
									break;
								case 'expression écrite reperage' :
									$this->view->roIdEere = $resultat->resultat_outil_id;
									$this->view->eeDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->eeValuere = $resultat->resultat_valeur;
									break;
								case 'raisonnement cognitif, logique et numérique reperage' :
									$this->view->roIdRclnre = $resultat->resultat_outil_id;
									$this->view->rclnDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->rclnValuere = $resultat->resultat_valeur;
									break;
								case 'repères spatio-temporels reperage' :
									$this->view->roIdRstre = $resultat->resultat_outil_id;
									$this->view->rstDatere = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->rstValuere = $resultat->resultat_valeur;
									$this->view->date_reperage = Fonctions_Dates::formatDate($resultat->resultat_date);
									break;	
							}
						}
						break;
					case 'diplome' :
						foreach( $resultats as $resultat ){
							$outil = $mOutils->get( $resultat->outil_id );
							switch( $outil['outil_libelle'] ){
								
								case 'livret1Diplome' :
								
									$this->view->resultatIdLivret1 = $resultat->resultat_outil_id;
									$this->view->date_livret1diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
									break;
									
									
									case 'livret2Diplome' :
									$this->view->resultatIdLivret2 = $resultat->resultat_outil_id;
									$this->view->date_livret2diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
									break;
									
								
									case 'questionDiplome' :
										$this->view->resultatIdQuestion = $resultat->resultat_outil_id;
										$this->view->date_question_diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
										$resultObs = explode('@',$resultat->resultat_valeur);
										foreach($resultObs as $idx => $obsNote){
											$getNote = explode('_', $obsNote);
											$obs['notes'][$idx] = $getNote[0];
											$obs['totaux'][$idx] = $getNote[1];
										}
										$this->view->listeQuestionNote =$obs;
										break;
									
									case 'observationDiplome' :
										$this->view->resultatIdObservation = $resultat->resultat_outil_id;
										$this->view->date_observation_diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
										$resultObs = explode('@',$resultat->resultat_valeur);
										foreach($resultObs as $idx => $obsNote){
											$getNote = explode('_', $obsNote);
											$obs['notes'][$idx] = $getNote[0];
										}
										$this->view->listeObservationNote =$obs;
										break;
									
									case 'entretienDiplome' :
										$this->view->resultatIdEntretien = $resultat->resultat_outil_id;
										$this->view->date_entretien_diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
										$resultObs = explode('@',$resultat->resultat_valeur);
										foreach($resultObs as $idx => $obsNote){
											$getNote = explode('_', $obsNote);
											$obs['notes'][$idx] = $getNote[0];
										}
										$this->view->listeEntretienNote =$obs;
										break;
										
										
									case 'bilanDiplome' :
											$this->view->resultatIdBilan = $resultat->resultat_outil_id;
											$this->view->date_bilan_diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
											$resultObs = explode('@',$resultat->resultat_valeur);
											foreach($resultObs as $idx => $obsNote){
												$getNote = explode('_', $obsNote);
												$obs['notes'][$idx] = $getNote[0];
											}
											$this->view->listeBilanNote =$obs;
											break;
									
									case 'commentaireDiplome' :
												$this->view->resultatIdCommentaire = $resultat->resultat_outil_id;
												$this->view->commentairediplome = $resultat->resultat_valeur;
												break;
										
									
							}
						}
						
						break;
				}

			}else{
				$this->view->res = false;
				if( $this->_request->isPost() ){

					$mCandidatMetiers = new Model_CandidatMetier();
					$mMetiers = new Model_Metier();
					$mResultats = new Model_Resultat();
					$mResultatOutils = new Model_ResultatOutil();
					$mOutils = new Model_Outil();
					$mEtats = new Model_Etat();

					if( $_FILES && empty($_POST['file']) ){
						$filename = './temp/'.$_FILES['filename']['name'];
						move_uploaded_file($_FILES['filename']['tmp_name'], $filename);
						$file = str_replace('index.php', '', $_SERVER['SCRIPT_FILENAME']).substr($filename,1,strlen($filename));
					}else{
						$file = $_POST['file'];
					}

					$this->view->file = $file;

					try {
						$db = new PDO('odbc:Driver={Microsoft Access Driver (*.mdb)};Dbq='.$file.';Uid=Admin;PWD=REDIP', '', '');
					}
					catch(Exception $e) {
						echo 'Erreur : '.$e->getMessage().'<br />';
						echo 'N° : '.$e->getCode();
					}

					$query = "SELECT Id_Stagi, nom, Prenom, Entreprise, Ville, CP FROM stagiaires";
					$select = $db->prepare($query);
					$select->execute();
					$result = $select->fetchAll();

					$this->view->candidats = "";
					foreach( $result as $candidat ){
						$this->view->candidats .= '<option value="'.$candidat[0].'" >'.utf8_encode( ucwords( $candidat[2].' '.$candidat[1].' - '.$candidat[3].' ( '.$candidat[4].' - '.$candidat[5].' ) ' ) ).'</option>';
					}

					if( isset( $_POST['candidat_id_access'] ) ){

						//recup resultats net
						$query = "SELECT * FROM stagiaires WHERE Id_Stagi LIKE ".$_POST['candidat_id_access'];
						$select = $db->prepare($query);
						$select->execute();
						$result = $select->fetch();
						$resultats_net = $result['Resultat_Net1'];
						$date = $fDates->unformatDate( $result['Date_result1'] );

						$query = "SELECT * FROM Resultats1 WHERE Id_Stagi LIKE ".$_POST['candidat_id_access'];
						$select = $db->prepare($query);
						$select->execute();
						$result = $select->fetchAll();

						$candidat_metier_id = $this->_request->getParam('metier');

						$metier = $mCandidatMetiers->getMetier($candidat_metier_id);

						$titre = $mMetiers->getTitre($metier['metier_id']);

						switch( $titre['niveaux'] ){
							case 'Notion@Maîtrise' :

								//////////////***************BRUT**************////////////
								//init des tableaux
								$notion_brut = array();
								$notion_brut_ponderation = array();
								$maitrise_brut = array();
								$maitrise_brut_ponderation = array();
								//pour chaque question
								foreach( $result as $note ){
									//format $note : id@niveaux@mod1@mod2@....
									$question = explode('@', $note['Id_Question']);
									if( count($question) > 1 ){
										//niveau
										$res = $question[1];
										//on prend seulement les ids module
										$i=2;
										while( isset( $question[$i] ) ){
											//si niveau = notion
											if( $question[1] == 'N' ){
												if( !isset( $notion_brut[$question[$i]] ) ){
													$notion_brut[$question[$i]] = 0;
													$notion_brut_ponderation[$question[$i]] = 0;
												}
												$tab_n = explode( '%', $note['Points'] );
												$notion_brut[$question[$i]] += $tab_n[1];
												$notion_brut_ponderation[$question[$i]]++;
											}
											//si niveau = maitrise
											elseif( $question[1] == 'A' ){
												if( !isset( $maitrise_brut[$question[$i]] ) ){
													$maitrise_brut[$question[$i]] = 0;
													$maitrise_brut_ponderation[$question[$i]] = 0;
												}
												$tab_n = explode( '%', $note['Points'] );
												$maitrise_brut[$question[$i]] += $tab_n[1];
												$maitrise_brut_ponderation[$question[$i]]++;
											}
											$i++;
										}
									}
								}

								//ajout des moduiles vides manquants
								$t=0;
								foreach( $maitrise_brut as $key => $m ){
									if( $t<$key ){
										$t = $key;
									}
								}
								$i=1;
								while( $i != $t ){
									if( !isset( $maitrise_brut[$i] ) ) $maitrise_brut[$i] = 0;
									if( !isset( $maitrise_brut_ponderation[$i] ) ) $maitrise_brut_ponderation[$i] = 0;
									$i++;
								}
								$t=0;
								foreach( $notion_brut as $key => $n ){
									if( $t<$key ){
										$t = $key;
									}
								}
								$i=1;
								while( $i != $t ){
									if( !isset( $notion_brut[$i] ) ) $notion_brut[$i] = 0;
									if( !isset( $notion_brut_ponderation[$i] ) ) $notion_brut_ponderation[$i] = 0;
									$i++;
								}

								//tri
								ksort($notion_brut);
								ksort($notion_brut_ponderation);
								ksort($maitrise_brut);
								ksort($maitrise_brut_ponderation);

								//moyennes notion
								foreach( $notion_brut as $key => &$value ){
									if( $value != 0 && $notion_brut_ponderation[$key] != 0 ) $value = $value / $notion_brut_ponderation[$key];
									$value = round( $value, 2 );
								}
								//moyennes maitrise
								foreach( $maitrise_brut as $key => &$value ){
									if( $value != 0 && $maitrise_brut_ponderation[$key] != 0 ) $value = $value / $maitrise_brut_ponderation[$key];
									$value = round( $value, 2 );
								}

								//////////////***************NET**************////////////
								//init tableaux
								$maitrise_net = array();
								$notion_net = array();
								//explode en tableau
								$resultats_net = explode('%', $resultats_net);
								foreach( $resultats_net as $key => $rn ){
									switch( $titre['bloc1_ab'] ){
										case 'BP PIPP' :
											//premiere moitiee = maitrise
											if( $key < 23 ){
												$maitrise_net[] = $rn;
											}
											//seconde moitiee = notion
											elseif( $key >= 24 && $key < 47 ){
												$notion_net[] = $rn;
											}
											break;
										case 'CAP CSI' :
											//premiere moitiee = maitrise
											if( $key > 23 && $key <= 43 ){
												$maitrise_net[] = $rn;
											}
											//seconde moitiee = notion
											elseif( $key >= 48 && $key < 68 ){
												$notion_net[] = $rn;
											}
											break;
										case 'CAP PAP' :
											//premiere moitiee = maitrise
											if( $key > 23 && $key <= 41 ){
												$maitrise_net[] = $rn;
											}
											//seconde moitiee = notion
											elseif( $key >= 48 && $key < 66 ){
												$notion_net[] = $rn;
											}
											break;
									}
								}

								$res = $mResultats->getLast($candidat_metier_id);

								$resultat_id = $res['resultat_id'];

								$outil_m_brut = $mOutils->fetchRow(" outil_libelle LIKE 'maitrise brut' ");
								$outil_n_brut = $mOutils->fetchRow(" outil_libelle LIKE 'notion brut' ");
								$outil_m_net = $mOutils->fetchRow(" outil_libelle LIKE 'maitrise net' ");
								$outil_n_net = $mOutils->fetchRow(" outil_libelle LIKE 'notion net' ");

								//brut
									//maitrise
									$mResultatOutils->add($outil_m_brut['outil_id'], $resultat_id, implode('@', $maitrise_brut), $date);
									//notion
									$mResultatOutils->add($outil_n_brut['outil_id'], $resultat_id, implode('@', $notion_brut), $date);
								//net
									//calcul pour l etat du candidat
									$test=0;
									foreach( $maitrise_net as $mn ){
										if( $mn < 2 ) $test++;
									}
									foreach( $notion_net as $nn ){
										if( $nn < 2 ) $test++;
									}
									//maitrise
									$mResultatOutils->add($outil_m_net['outil_id'], $resultat_id, implode('@', $maitrise_net), $date);
									//notion
									$mResultatOutils->add($outil_n_net['outil_id'], $resultat_id, implode('@', $notion_net), $date);
									//recup etats
									$etat_non_diplome = $mEtats->fetchRow(" etat_libelle = 'non diplômé' ");
									$etat_diplome = $mEtats->fetchRow(" etat_libelle = 'diplômé' ");
									//enregistremtn etat du candidat
									if( $test == 0 ){	//diplome
										$mCandidatMetiers->updateEtat($candidat_metier_id, $etat_diplome['etat_id']);
									}else{	//non dilpomé
										$mCandidatMetiers->updateEtat($candidat_metier_id, $etat_non_diplome['etat_id']);
									}

								break;
							case 'Résultats' :
								//////////////***************BRUT**************////////////
								//init des tableaux
								$resultat_brut = array();
								$resultat_brut_ponderation = array();
								//pour chaque question
								foreach( $result as $note ){
									//format $note : id@niveaux@mod1@mod2@....
									$question = explode('@', $note['Id_Question']);
									if( count($question) > 1 ){
										//niveau
										$res = $question[1];
										//on prend seulement les ids module
										$i=2;
										while( isset( $question[$i] ) ){
											//si niveau
											if( $question[1] == 'I' ){
												if( !isset( $resultat_brut[$question[$i]] ) ){
													$resultat_brut[$question[$i]] = 0;
													$resultat_brut_ponderation[$question[$i]] = 0;
												}
												$tab_n = explode( '%', $note['Points'] );
												$resultat_brut[$question[$i]] += $tab_n[1];
												$resultat_brut_ponderation[$question[$i]]++;
											}
											$i++;
										}
									}
								}
								//tri
								ksort($resultat_brut);
								ksort($resultat_brut_ponderation);
								//moyennes resultat brut
								foreach( $resultat_brut as $key => &$value ){
									$value = $value / $resultat_brut_ponderation[$key];
									$value = round( $value, 2 );
								}

								//////////////***************NET**************////////////
								//init tableaux
								$resultat_net = array();
								//explode en tableau
								$resultats_net = explode('%', $resultats_net);
								foreach( $resultats_net as $key => $rn ){
									switch( $titre['bloc1_ab'] ){
										case 'CAP AEM' :
											if( $key < 20 ){
												$resultat_net[] = $rn;
											}
											break;
									}
								}

								$res = $mResultats->getLast($candidat_metier_id);

								$resultat_id = $res['resultat_id'];

								$outil_r_brut = $mOutils->fetchRow(" outil_libelle LIKE 'résultats brut' ");
								$outil_r_net = $mOutils->fetchRow(" outil_libelle LIKE 'résultats net' ");

								//brut
									//
									$mResultatOutils->add($outil_r_brut['outil_id'], $resultat_id, implode('@', $resultat_brut), $date);
								//net
									//calcul pour l etat du candidat
									$test=0;
									foreach( $resultat_net as $rn ){
										if( $rn < 2 ) $test++;
									}
									//
									$mResultatOutils->add($outil_r_net['outil_id'], $resultat_id, implode('@', $resultat_net), $date);
									//recup etats
									$etat_non_diplome = $mEtats->fetchRow(" etat_libelle = 'non diplômé' ");
									$etat_diplome = $mEtats->fetchRow(" etat_libelle = 'diplômé' ");
									//enregistremtn etat du candidat
									if( $test == 0 ){	//diplome
										$mCandidatMetiers->updateEtat($candidat_metier_id, $etat_diplome['etat_id']);
									}else{	//non dilpomé
										$mCandidatMetiers->updateEtat($candidat_metier_id, $etat_non_diplome['etat_id']);
									}

								break;
						}

						$this->_redirect('/operations/visu/num/'.$metier['fiche_id'].'/#ui-tabs-5');

					}
					
				}
			}
		}
		/******************************************************/
		
	}
	
	public function insertAction(){
		
		$this->_helper->viewRenderer->setNoRender();
		Zend_Layout::getMvcInstance()->disableLayout();

		$mResultatOutils = new Model_ResultatOutil();
		$mDemarches = new Model_Demarche();
		$mOutils = new Model_Outil();
		$mEtats= new Model_Etat();
		$mCandidatsMetier = new Model_CandidatMetier();
		$fDates = new Fonctions_Dates();

		$demarche = $mDemarches->fetchRow( " demarche_abrege = '".$this->_request->getParam('demarche')."' " );

	
		switch( $this->_request->getParam('demarche') ){
			case 'cqp' :

				$candidat_metier = $mCandidatsMetier->get($this->_request->getParam('candidat_metier_id'));

				//livret
				$date = $fDates->unformatDate( $this->_request->getParam('date_livret') );
				$livret_id = $this->_request->getParam('resultatIdLivret');
				if( $livret_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdLivret'), NULL, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'livret' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), NULL, $date );
				}

				//questionnaire
				$date = $fDates->unformatDate( $this->_request->getParam('date_questionnaire') );
				$questionnaire_id = $this->_request->getParam('resultatIdQuestionnaire');
				$questionnaire = $this->_request->getParam('questionnaire');
				$questionnaireTotal = $this->_request->getParam('questionnaireTotal');
				$i=0; $temp = array();
				while( $i < count($questionnaire) ){
					if( !is_numeric($questionnaire[$i]) ) $questionnaire[$i]=0;
					if( $questionnaire[$i] < 0 ) $questionnaire[$i]=0;
					if( $questionnaire[$i] > $questionnaireTotal[$i] ) $questionnaire[$i] = $questionnaireTotal[$i];
					$temp[] = $questionnaire[$i].'_'.$questionnaireTotal[$i];
					$i++;
				}
				$questionnaire = implode('@', $temp);
				if( $questionnaire_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdQuestionnaire'), $questionnaire, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'questionnaire' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $questionnaire, $date );
				}
				
				
				$date = $fDates->unformatDate( $this->_request->getParam('date_livret') );
				$livretAcquis_id = $this->_request->getParam('resultatIdLivretAquis');
				$livretAcquis = $this->_request->getParam('livretAquis');
				$livretAcquisTotal = $this->_request->getParam('livretAcquisTotal');
				$livretAquisPosition = $this->_request->getParam('livretAquisPosition');

				$i=0; $temp = array();
				while( $i < count($livretAcquis) ){
					if( !is_numeric($livretAcquis[$i]) ) $livretAcquis[$i]=0;
					if( $livretAcquis[$i] < 0 ) $livretAcquis[$i]=0;
					if( $livretAcquis[$i] > $livretAcquisTotal[$i] ) $livretAcquis[$i] = $livretAcquisTotal[$i];
					if($livretAcquisTotal[$i] == ''){$livretAcquisTotal[$i]='0';}
					if($livretAcquis[$i] == ''){$livretAcquis[$i]='0';}
					$temp[] = $livretAcquis[$i].'_'.$livretAcquisTotal[$i].'_'.$livretAquisPosition[$i];
					$i++;
				}			
				
				$livretAcquis = implode('@', $temp);

				if( $livretAcquis_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdLivretAquis'), $livretAcquis, $date );
				}else{
					if($livretAcquis !='')
					{
						$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'livretacquis' " );
						$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $livretAcquis, $date );
					}
				}
				
				

				//observation
				$date = $fDates->unformatDate( $this->_request->getParam('date_observation') );
				$observation_id = $this->_request->getParam('resultatIdObservation');
				$observations = $this->_request->getParam('observations');
				$observationsTotal = $this->_request->getParam('observationsTotal');
				$i=0; $temp = array();
				while( $i < count($observations) ){
					if( !is_numeric($observations[$i]) ) $observations[$i]=0;
					if( $observations[$i] < 0 ) $observations[$i]=0;
					if( $observations[$i] > $observationsTotal[$i] ) $observations[$i] = $observationsTotal[$i];
					$temp[] = $observations[$i].'_'.$observationsTotal[$i];
					$i++;
				}
				$observations = implode( '@', $temp );
				if( $observation_id > 0 ){
					$mResultatOutils->modif($this->_request->getParam('resultatIdObservation'), $observations, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'observation' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $observations, $date);
				}
				
				//entretien
				$date = $fDates->unformatDate( $this->_request->getParam( 'date_entretien' ) );
				$entretien_id = $this->_request->getParam('resultatIdEntretien');
				$entretiens = $this->_request->getParam('entretiens');
				$entretiens = implode('@', $entretiens);
				$today = date("Y-m-d");
				if( $entretien_id > 0 ){
					$mResultatOutils->modif($this->_request->getParam('resultatIdEntretien'), $entretiens, $date);
					$r = $mResultatOutils->fetchRow( " resultat_outil_id = ".$this->_request->getParam('resultatIdEntretien') );
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'bilan' " );
					$routil = $mResultatOutils->fetchRow( " resultat_id = ".$r['resultat_id']." AND outil_id = ".$outil['outil_id'] );

					$t=0;
					foreach( $this->_request->getParam('entretiens') as $e ){
						if( $e != 1 ){
							$t++;
						}
					}
					if( $t > 0 ){	// toutes capacites ne sont pas maitrisées
						//fichiers
						$files=0;
						$dir = './documents/candidats/'.$candidat_metier['candidat_id'].'/';
						if (is_dir($dir)) {
							if ( $dh = opendir($dir) ) {
								while (($file = readdir($dh)) !== false) {
									if($file != "." && $file != ".." && !is_dir($dir.$file)){
										$files++;
									}
								}
								closedir($dh);
							}
						}
						if( $candidat_metier['candidat_metier_formation_remarque'] != '' && $candidat_metier['candidat_metier_formation_duree_estimee'] > 0 && $candidat_metier['candidat_metier_formation_duree_realisee'] > 0 && $files > 0 ){
							if( isset( $routil ) ){
								$data = array(
									'resultat_valeur'	=>	$entretiens,
									'resultat_date'		=>	$today
								);
								$where = " resultat_outil_id = ".$routil['resultat_outil_id'];
								$mResultatOutils->update($data, $where);
							}else{
								$data = array(
									'outil_id'			=>	$outil['outil_id'],
									'resultat_id'		=>	$r['resultat_id'],
									'resultat_valeur'	=>	$entretiens,
									'resultat_date'		=>	$today
								);
								$mResultatOutils->insert($data);
							}
						}
					}else{	//toutes les capacites maitrisees
						if( $candidat_metier['candidat_metier_formation_remarque'] != '' && $candidat_metier['candidat_metier_formation_duree_estimee'] == 0 && $candidat_metier['candidat_metier_formation_duree_realisee'] == 0 ){

							if( isset( $routil ) ){
								$data = array(
									'resultat_valeur'	=>	$entretiens,
									'resultat_date'		=>	$today
								);
								$where = " resultat_outil_id = ".$routil['resultat_outil_id'];
								$mResultatOutils->update($data, $where);
							}else{
								$data = array(
									'outil_id'			=>	$outil['outil_id'],
									'resultat_id'		=>	$r['resultat_id'],
									'resultat_valeur'	=>	$entretiens,
									'resultat_date'		=>	$today
								);
								$mResultatOutils->insert($data);
							}
						}
					}
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'entretien' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretiens, $date);
					
					$t=0;
					foreach( $this->_request->getParam('entretiens') as $e ){
						if( $e != 1 ){
							$t++;
						}
					}
					if( $t > 0 ){	// toutes capacites ne sont pas maitrisées
						//fichiers
						$files=0;
						$dir = './documents/candidats/'.$candidat_metier['candidat_id'].'/';
						if (is_dir($dir)) {
							if ( $dh = opendir($dir) ) {
								while (($file = readdir($dh)) !== false) {
									if($file != "." && $file != ".." && !is_dir($dir.$file)){
										$files++;
									}
								}
								closedir($dh);
							}
						}
						if( $candidat_metier['candidat_metier_formation_remarque'] != '' && $candidat_metier['candidat_metier_formation_duree_estimee'] > 0 && $candidat_metier['candidat_metier_formation_duree_realisee'] > 0 && $files > 0 ){
							$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'bilan' " );
							$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretiens, $today);
						}
					}else{	//toutes les capacites maitrisees
						if( $candidat_metier['candidat_metier_formation_remarque'] != '' && $candidat_metier['candidat_metier_formation_duree_estimee'] == 0 && $candidat_metier['candidat_metier_formation_duree_realisee'] == 0 ){
							$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'bilan' " );
							$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretiens, $today);
						}
					}
				}

				//etat
				$e = 'admissible';
				foreach( $this->_request->getParam('entretiens') as $note ){
					if( $note != 1 ){
						$e = 'en formation';
					}
				}
				$etat = $mEtats->getByLibelle($e);
				$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);

				break;
				
				
				
				case 'cqpbranche' :
				

					$candidat_metier = $mCandidatsMetier->get($this->_request->getParam('candidat_metier_id'));
				
					//Date de debut de fomation
					$date = $fDates->unformatDate( $this->_request->getParam('date_debut_formation_branche') );
					if($date !='')
					{
						$e = 'en formation';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
					}
					$DebutFormationBranche_id = $this->_request->getParam('resultatIdDebutFormationBranche');
					if( $DebutFormationBranche_id > 0 ){
						$mResultatOutils->modif( $this->_request->getParam('resultatIdDebutFormationBranche'), NULL, $date );
					}else{
						$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'debutFormationBanche' " );
						$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), NULL, $date );
					}
					
					//Date de fin de formation
					$date = $fDates->unformatDate( $this->_request->getParam('date_fin_formation_branche') );
					if($date !='')
					{
						$e = 'entretien';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
					}
					
					$FinFormationBranche_id = $this->_request->getParam('resultatIdFinFormationBranche');
					if( $FinFormationBranche_id > 0 ){
						$mResultatOutils->modif( $this->_request->getParam('resultatIdFinFormationBranche'), NULL, $date );
					}else{
						$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'finFormationBanche' " );
						$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), NULL, $date );
					}
					
					
					
					
					//entretien Note
					$date = $fDates->unformatDate( $this->_request->getParam('date_entretien_branche') );
					if($date !='')
					{
						$e = 'en cours acquisition';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
					}
						
					$entretienBranche_id = $this->_request->getParam('resultatIdentretiensBranche');
					$entretienBranche = implode( '@',  $this->_request->getParam('entretiensBranche') );
					
					$nb_capacites = count($this->_request->getParam('entretiensBranche'));
					$total_note = 0;
					foreach ($this->_request->getParam('entretiensBranche') as $note_tempo)
					{
						$total_note = $total_note+$note_tempo;
					}
					 if($total_note == $nb_capacites)
					 {
					 	$e = 'admissible';
					 	$etat = $mEtats->getByLibelle($e);
					 	$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
					 	
					 }
					if( $entretienBranche_id > 0 ){
						$mResultatOutils->modif($this->_request->getParam('resultatIdentretiensBranche'), $entretienBranche, $date);
					}else{
						$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'entretienNoteBanche' " );
						$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretienBranche, $date);
					}
					

					//entretien Commentaires
					$date = $fDates->unformatDate( $this->_request->getParam('date_entretien_branche') );
					$entretienBranche_id = $this->_request->getParam('resultatIdentretiensCommentaireBranche');
					$entretienBranche = implode( '@£$@',  $this->_request->getParam('commentairesBranche') );
					if( $entretienBranche_id > 0 ){
						$mResultatOutils->modif($this->_request->getParam('resultatIdentretiensCommentaireBranche'), $entretienBranche, $date);
					}else{
						$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'entretienCommentaireBanche' " );
						$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretienBranche, $date);
					}
					
					
						
					

				
					$etat = $mEtats->getByLibelle($e);
					$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
				
					break;
				
				

			case 'ccsp' :
				if($this->_request->getParam('date_reperage')!='' )
				{
						$e = 'en repérage';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
				}
				if($this->_request->getParam('date_questionnaire')!='' )
				{
						$e = 'en positionnement';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
				}
				
				if($this->_request->getParam('date_questionnaire')!='' && $this->_request->getParam('date_observation')!='')
				{
						$e = 'en formation';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
					
				}
				if($this->_request->getParam('date_entretien')!='' && $this->_request->getParam('date_questionnaire')!='' && $this->_request->getParam('date_observation')!='')
				{
					echo $this->_request->getParam('date_entretien');
						$e = 'en évaluation';
						$etat = $mEtats->getByLibelle($e);
						$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);	
				}
				
				
				
				//questionnaire
				$date = $fDates->unformatDate( $this->_request->getParam('date_questionnaire') );
				$questionnaire_id = $this->_request->getParam('resultatIdQuestionnaire');
				$questionnaire = $this->_request->getParam('questionnaire_ccsp');
				$questionnaireTotal = $this->_request->getParam('questionnaireTotal_ccsp');
				$questionnaireActif = $this->_request->getParam('questionnaireActif_ccsp');
				$i=0; $temp = array();
				while( $i < count($questionnaire) ){
					if( !is_numeric($questionnaire[$i]) ) $questionnaire[$i]=0;
					if( $questionnaire[$i] < 0 ) $questionnaire[$i]=0;
					if( $questionnaire[$i] > $questionnaireTotal[$i] ) 
					$questionnaire[$i] = $questionnaireTotal[$i];
					if($questionnaireActif[$i] =='on' || $questionnaire[$i] > 0){$questionnaireActif[$i] ='actif';}
					$temp[] = $questionnaire[$i].'_'.$questionnaireTotal[$i].'_'.$questionnaireActif[$i] ;
					$i++;
				}
				$questionnaire = implode('@', $temp);
				if( $questionnaire_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdQuestionnaire'), $questionnaire, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'questionnaire' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $questionnaire, $date );
				}

				//observation
				$date = $fDates->unformatDate( $this->_request->getParam('date_observation') );
				$observation_id = $this->_request->getParam('resultatIdObservation');
				$observations = $this->_request->getParam('observations_ccsp');
				$observationsTotal = $this->_request->getParam('observationsTotal_ccsp');
				$observationsActif = $this->_request->getParam('observationsActif_ccsp');
				$i=0; $temp = array();
				while( $i < count($observations) ){
					if( !is_numeric($observations[$i]) ) $observations[$i]=0;
					if( $observations[$i] < 0 ) $observations[$i]=0;
					if( $observations[$i] > $observationsTotal[$i] ) 
					$observations[$i] = $observationsTotal[$i];
				if($observationsActif[$i] =='on' || $observations[$i] > 0 ){$observationsActif[$i] ='actif';}
					$temp[] = $observations[$i].'_'.$observationsTotal[$i].'_'.$observationsActif[$i];
					$i++;
				}
				$observations = implode( '@', $temp );
				
				if( $observation_id > 0 ){
					$mResultatOutils->modif($this->_request->getParam('resultatIdObservation'), $observations, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'observation' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $observations, $date);
				}

				//entretien
				$date = $fDates->unformatDate( $this->_request->getParam('date_entretien') );
				$entretien_id = $this->_request->getParam('resultatIdEntretien');
				$entretiens = $this->_request->getParam('entretiens_ccsp');
				$entretiensTotal = $this->_request->getParam('entretiensTotal_ccsp');
				$i=0; $temp = array();
				while( $i < count($entretiens) ){
					if( !is_numeric($entretiens[$i]) ) $entretiens[$i]=0;
					if( $entretiens[$i] < 0 ) $entretiens[$i]=0;
					if( $entretiens[$i] > $entretiensTotal[$i] ) $entretiens[$i] = $entretiensTotal[$i];
					if($observationsActif[$i] =="actif" || $questionnaireActif[$i] =="actif"){$actif="_actif";}else{$actif='_off';};
					$temp[] = $entretiens[$i].'_'.$entretiensTotal[$i].$actif;
					$i++;
				}
				$entretiens = implode( '@', $temp );
				if( $entretien_id > 0 ){
					$mResultatOutils->modif($this->_request->getParam('resultatIdEntretien'), $entretiens, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'entretien' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id' ), $entretiens, $date);
				}

				//comprehension orale
				$date = '0000-00-00';
				$co_id = $this->_request->getParam('resultatIdCo');
				$co = $this->_request->getParam('value_co');
				if( $co_id > 0 ){
					$mResultatOutils->modif($co_id, $co, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'compréhension orale' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $co, $date);
				}

				//expression orale
				$date = '0000-00-00';
				$eo_id = $this->_request->getParam('resultatIdEo');
				$eo = $this->_request->getParam('value_eo');
				if( $eo_id > 0 ){
					$mResultatOutils->modif($eo_id, $eo, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'expression orale' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $eo, $date);
				}

				//comprehension ecrite
				$date = '0000-00-00';
				$ce_id = $this->_request->getParam('resultatIdCe');
				$ce = $this->_request->getParam('value_ce');
				if( $ce_id > 0 ){
					$mResultatOutils->modif($ce_id, $ce, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'compréhension écrite' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $ce, $date);
				}

				//expression ecrite
				$date = '0000-00-00';
				$ee_id = $this->_request->getParam('resultatIdEe');
				$ee = $this->_request->getParam('value_ee');
				if( $ee_id > 0 ){
					$mResultatOutils->modif($ee_id, $ee, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'expression écrite' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $ee, $date);
				}

				//raisonnement cognitif logique et numérique
				$date = '0000-00-00';
				$rcln_id = $this->_request->getParam('resultatIdRcln');
				$rcln = $this->_request->getParam('value_rcln');
				if( $rcln_id > 0 ){
					$mResultatOutils->modif($rcln_id, $rcln, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'raisonnement cognitif, logique et numérique' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $rcln, $date);
				}

				//reperes spacio-temporels
				$date = '0000-00-00';
				$rst_id = $this->_request->getParam('resultatIdRst');
				$rst = $this->_request->getParam('value_rst');
				if( $rst_id > 0 ){
					$mResultatOutils->modif($rst_id, $rst, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'repères spatio-temporels' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $rst, $date);
				}

				
				
				
				//comprehension orale
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$co_id = $this->_request->getParam('resultatIdCore');
				$core = $this->_request->getParam('value_core');
				if( $co_id > 0 ){
					$mResultatOutils->modif($co_id, $core, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'compréhension orale reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $core, $date);
				}

				//expression orale
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$eo_id = $this->_request->getParam('resultatIdEore');
				$eore = $this->_request->getParam('value_eore');
				if( $eo_id > 0 ){
					$mResultatOutils->modif($eo_id, $eore, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'expression orale reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $eore, $date);
				}

				//comprehension ecrite
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$ce_id = $this->_request->getParam('resultatIdCere');
				$cere = $this->_request->getParam('value_cere');
				if( $ce_id > 0 ){
					$mResultatOutils->modif($ce_id, $cere, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'compréhension écrite reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $cere, $date);
				}

				//expression ecrite
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$ee_id = $this->_request->getParam('resultatIdEere');
				$eere = $this->_request->getParam('value_eere');
				if( $ee_id > 0 ){
					$mResultatOutils->modif($ee_id, $eere, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'expression écrite reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $eere, $date);
				}

				//raisonnement cognitif logique et numérique
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$rcln_id = $this->_request->getParam('resultatIdRclnre');
				$rclnre = $this->_request->getParam('value_rclnre');
				if( $rcln_id > 0 ){
					$mResultatOutils->modif($rcln_id, $rclnre, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'raisonnement cognitif, logique et numérique reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $rclnre, $date);
				}

				//reperes spacio-temporels
				$date = $fDates->unformatDate( $this->_request->getParam('date_reperage') );
				$rst_id = $this->_request->getParam('resultatIdRstre');
				$rstre = $this->_request->getParam('value_rstre');
				if( $rst_id > 0 ){
					$mResultatOutils->modif($rst_id, $rstre, $date);
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'repères spatio-temporels reperage' " );
					$mResultatOutils->add($outil['outil_id'], $this->_request->getParam( 'resultat_id'), $rstre, $date);
				}
				
				
				
				if($co!='')
				{
				$e = 'certifié';
				$etat = $mEtats->getByLibelle($e);
				$mCandidatsMetier->updateEtat($_POST['candidat_metier_id'], $etat[0]['etat_id']);
				}
				
				
				
				break;
				
			case 'diplome' :

				//livret 1
				$date = $fDates->unformatDate( $this->_request->getParam('date_livret1diplome') );
				$livret1Diplome_id = $this->_request->getParam('resultatIdLivret1');
				if( $livret1Diplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdLivret1'), NULL, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'livret1Diplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), NULL, $date );
				}
				
			
				
				//livret 2
				$date = $fDates->unformatDate( $this->_request->getParam('date_livret2diplome') );
				$livret2Diplome_id = $this->_request->getParam('resultatIdLivret2');
				if( $livret2Diplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdLivret2'), NULL, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'livret2Diplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), NULL, $date );
				}
				
				
				//questionnaire
				$date = $fDates->unformatDate( $this->_request->getParam('date_question_diplome') );
				
				
				
				
				$questionnaire = $this->_request->getParam('questionnaire_diplome');
				$questionnaireTotal = $this->_request->getParam('questionnaire_diplome_total');
				$i=0; $temp = array();
				while( $i < count($questionnaire) ){
					if( !is_numeric($questionnaire[$i]) ) $questionnaire[$i]=0;
					if( $questionnaire[$i] < 0 ) $questionnaire[$i]=0;
					if( $questionnaire[$i] > $questionnaireTotal[$i] ) $questionnaire[$i] = $questionnaireTotal[$i];
					$temp[] = $questionnaire[$i].'_'.$questionnaireTotal[$i];
					$i++;
				}
				
				$questionnaire = implode('@', $temp);
				
				$questionDiplome_id = $this->_request->getParam('resultatIdQuestion');
				if( $questionDiplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdQuestion'), $questionnaire, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'questionDiplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $questionnaire, $date );
				}
				
				
				
				
				
				//observation
				$observations = $this->_request->getParam('ObservationNote');
				$observations = implode('@', $observations);
				
				$date = $fDates->unformatDate( $this->_request->getParam('date_observation_diplome') );
				$observationDiplome_id = $this->_request->getParam('resultatIdObservation');
				if( $observationDiplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdObservation'), $observations, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'observationDiplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $observations, $date );
				}
				
				
				//entretien
				
				$entretiens = $this->_request->getParam('EntretienNote');
				$entretiens = implode('@', $entretiens);
				$date = $fDates->unformatDate( $this->_request->getParam('date_entretien_diplome') );
				$entretienDiplome_id = $this->_request->getParam('resultatIdEntretien');
				if( $entretienDiplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdEntretien'), $entretiens, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'entretienDiplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $entretiens, $date );
				}
				$entregistrement=$this->_request->getParam( 'enregistrement' );
				$candidat_metier_id = $this->_request->getParam('candidat_metier_id');
				
				if($this->_request->getParam('date_entretien_diplome') !="")
				{
					$etat = $mEtats->getByLibelle( 'admissible' );
					$mCandidatsMetier->updateEtat($candidat_metier_id, $etat[0]['etat_id']);
				}
				
				
				//bilan
				
				$bilans = $this->_request->getParam('BilanNote');
				$bilans = implode('@', $bilans);
				$date = $fDates->unformatDate( $this->_request->getParam('date_bilan_diplome') );
				$bilanDiplome_id = $this->_request->getParam('resultatIdBilan');
				if( $bilanDiplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdBilan'), $bilans, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'bilanDiplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $bilans, $date );
				}
				$entregistrement=$this->_request->getParam( 'enregistrement' );
				$candidat_metier_id = $this->_request->getParam('candidat_metier_id');
				
				$bilans = $this->_request->getParam('BilanNote');
				$bilans = implode('@', $bilans);
				$obs = array();
				$total_notes_tableau = 0;
				$resultBil = explode('@',$bilans);
				foreach($resultBil as $idx => $bilNote){
					$getNote = explode('_', $bilNote);
					$obs['notes'][$idx] = $getNote[0];
					$total_notes_tableau +=$getNote[0];
				}
				
				$total_note = count($obs['notes']);
				
				if($total_notes_tableau ==$total_note  && $this->_request->getParam('date_bilan_diplome') !="")
				{
					$etat = $mEtats->getByLibelle( 'certifié' );
					$mCandidatsMetier->updateEtat($candidat_metier_id, $etat[0]['etat_id']);
				}
				
				if($total_notes_tableau < $total_note  && $this->_request->getParam('date_bilan_diplome') !="")
				{
					$etat = $mEtats->getByLibelle( 'en formation' );
					$mCandidatsMetier->updateEtat($candidat_metier_id, $etat[0]['etat_id']);
				}
				
				
				
				
				
				
				
				
				
				//commentaire
				$commentaire = $this->_request->getParam('commentairediplome') ;
				$commentaireDiplome_id = $this->_request->getParam('resultatIdCommentaire');
				if($commentaireDiplome_id > 0 ){
					$mResultatOutils->modif( $this->_request->getParam('resultatIdCommentaire'), $commentaire, $date );
				}else{
					$outil = $mOutils->fetchRow( " demarche_id = ".$demarche['demarche_id']." AND outil_libelle LIKE 'commentaireDiplome' " );
					$mResultatOutils->add( $outil['outil_id'], $this->_request->getParam('resultat_id'), $commentaire,  $date);
				}
				
					
				
				
				$this->_redirect( '/saisie/index/metier/'.$this->_request->getParam( 'candidat_metier_id' )."?enregistrement=$entregistrement&candidat_metier_id=".$candidat_metier_id );
				
				
				break;
		}

		$this->_redirect( '/saisie/index/metier/'.$this->_request->getParam( 'candidat_metier_id' ) );

	}

	public function addpassageAction(){

		$candidat_metier_id = $this->_request->getParam('candidat_metier_id');

		$mResultats = new Model_Resultat();
		$mResultatsOutil = new Model_ResultatOutil();
		$mEtats = new Model_Etat();
		$mCandidatsMetier = new Model_CandidatMetier();

		$etat = $mEtats->getByLibelle( 'en évaluation' );

		$passage_max = $mResultats->getLastPassage($candidat_metier_id);

		if( $passage_max >= 2 ){
			echo '<script type="text/javascript" >alert("Impossible d\'ajouter un nouveau passage !");</script>';
		}else{
			$resultats = $mResultats->getResultatsCandidat($candidat_metier_id);
			

			$resultat_id = $mResultats->add($candidat_metier_id);
			foreach( $resultats as $resultat ){
				$mResultatsOutil->add($resultat->outil_id, $resultat_id, $resultat->resultat_valeur, $resultat->resultat_date);
			}
			$mCandidatsMetier->updateEtat($candidat_metier_id, $etat[0]['etat_id']);
			
		}

		$this->_redirect( "/saisie/index/metier/$candidat_metier_id" );

	}

}