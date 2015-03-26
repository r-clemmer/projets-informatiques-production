<?php
	class AquistableauresultatsController extends Zend_Controller_Action {

		function init(){

			Zend_Layout::getMvcInstance()->setLayout('empty');

			$this->candidat_metier_id = $this->_request->getParam('id');
			$this->view->candidat_metier_id = $this->_request->getParam('id');
			$this->passage = $this->_request->getParam('passage');
			$this->view->passage = $this->_request->getParam('passage');

			if( $this->passage == 1 ) $this->view->type = "Positionnement";
			if( $this->passage == 2 ) $this->view->type = "Evaluation";

		}

		public function indexAction(){

			$front = Zend_Controller_Front::getInstance();

			$this->view->headLink()->appendStylesheet($front->getBaseUrl()."/css/tableau_resultats.css");
//			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.rotate.1-1.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.mb.flipText.js','text/javascript');

			$candidat_metier_id = $this->candidat_metier_id;

			$this->view->title = "Tableau de synthèse des résultats";

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
			$demarche = $mCandidatMetiers->getDemarche($candidat_metier_id);

			$metier = $mMetiers->get( $candidat_metier['metier_id'] );

			
			$this->view->operation_id = $metier['fiche_id'];

			$this->view->returnlink = '/operations/visu/num/'.$metier['fiche_id'].'/#ui-tabs-5';
			//$this->view->returnlib = "Retour à l'opération n°".$metier['fiche_id'];
			$this->view->returnlib = "Retour à l'opération";

			$this->view->demarche = $demarche['demarche_abrege'];

			switch ( $demarche['demarche_abrege'] ){
						case 'cqp' :
					
					$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$this->view->titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$this->view->titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					

					$this->view->nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";					


					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];
					$this->view->nom = ucwords( $s_nom );

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

					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'aquis');
					$xmldemarche_compare = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite);
					
					$demarch = $xmldemarche->getDemarche();
					$demarche_compare = $xmldemarche_compare->getDemarche();
					
					$capacites = $demarch['capacites_base'];
					$capacites_compare = $demarche_compare['capacites_base'];
					$this->view->nb_capacites = count($capacites);
					
					
					$nb_capacite_compare =  count($capacites_compare)-count($capacites);
					//$ventilations = $demarch['ventilations'];

					$question_base = $demarch['question_base'];
					$livret_base = $demarch['livret_base'];
					$this->view->capacites = $capacites;
					$nb_capacites = count($capacites);

					$resultats = $mResultats->getResultats($candidat_metier_id, $this->passage );

					if( $resultats == false )
						$this->_redirect( '/tableauresultats/index/?id='.$candidat_metier_id.'&passage=1' );

					$i = 0;
					while( $i != $nb_capacites ){
						$bilan[] = '';
						$i++;
					}
					$this->view->bilan = $bilan;

					$i=0;
					
					
					foreach( $capacites as $capacite ){
						$notes[$i] = '';
						$i++;
					}
					$i=0;
					$this->view->observations = $notes;
					$this->view->entretien = $notes;

					foreach( $resultats as $resultat ){

						$outil = $mOutils->get( $resultat->outil_id );

						switch( $outil['outil_libelle'] ){
							case 'livret' :
								$this->view->datelivret = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								break;
							case 'observation' :
								$this->view->dateobservation = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
									$t[] = $temp[1];
								}
																	
								$i = $nb_capacite_compare;
								
								
								foreach( $capacites as $capacite ){

									if( $capacite['capacite'] != 'Acquis de base' ){
										if( $t[$i] > 0 ){
																				
											$capacite_num = $capacite['ordre_individuel']-1;
											$observations[$i]['pourcent'] = round( ( $n[$i] / $t[$i] ) * 100 );
											
											
											if( $observations[$i]['pourcent'] > 69 ) $observations[$i]['caractere'] = '1';
											elseif( $observations[$i]['pourcent'] < 40 ) $observations[$i]['caractere'] = '0';
											else $observations[$i]['caractere'] = '?';
										}else{
											$observations[$i]['pourcent'] = 0;
											$observations[$i]['caractere'] = '0';
										}
									}else{
										$observations[$i]['pourcent'] = '';
										$observations[$i]['caractere'] = '';
									}
									$observations[$i]['total'] = $t[$i];

									
									$i++;
								}
								
								$this->view->observations = $observations;
								break;
							case 'questionnaire' :
								$notes = array();
								$totaux = array();
								$this->view->datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$notes[] = $temp[0];
								}
								$i=0;
								$k=0;
								$l=0;
								$note_total = array();
								$note_base =  array();
									foreach( $capacites as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										$note_total[$capacite_num]=0;
										$note_base[$capacite_num]=0;
										$k++;
									}
								
								foreach( $question_base as $question ){
									if(isset($notes[$i]))
									{

									$query_question = "SELECT * FROM pmqvision5.capacites 
									LEFT JOIN pmqvision5.classement_capacite 
									ON capacites.nom_capacite =classement_capacite.nom_capacite 
									 WHERE `id_titres` ='".$question['id_titres']."' 
									AND `outils` LIKE 'questionnaire'
									AND classement_capacite.type_capacite = 'aquis'
									AND  `position` = '".$question['position']."' 
									ORDER BY  position ";
									$result_question = mysql_query($query_question);
									$compte_question=0;	
									while($row_question = mysql_fetch_array($result_question))
										{
											if($row_question['type_capacite'] == 'aquis')
											{
												$capacite_num = $row_question['ordre_individuel']-1;
												$note_maxi = $row_question["note"];
												$note_total[$capacite_num]+=$note_maxi;

												$note_base[$capacite_num]+=$notes[$i];											
											}	
											
										}
										
										
									}
									
									$i++;								
								}
								$note_total_aquis= 0;
								$note_note_aquis= 0;
									foreach( $capacites as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										
										if($note_base[$capacite_num]!=0)
										{
											$percent = round( ( $note_base[$capacite_num]/$note_total[$capacite_num] ) * 100 );
											$note_total_aquis += $note_total[$capacite_num];
											$note_note_aquis += $note_base[$capacite_num];
											
											
										}else{
											$percent =0;
										}
											if( $percent > 69 ) $questionnaire[$capacite_num]['caractere'] = '1';
											elseif( $percent < 40 ) $questionnaire[$capacite_num]['caractere'] = '0';
											else $questionnaire[$capacite_num]['caractere'] = '?';
											$questionnaire[$capacite_num]['pourcent'] = $percent;
											$questionnaire[$capacite_num]['total'] = $note_total[$capacite_num];
											$l++;
											
									}
								$this->view->questionnaire = $questionnaire;
								
							if($note_total_aquis >0)
								{
									$this->view->questionnaire_total_acquis = ($note_note_aquis/$note_total_aquis)*100;	
								}else{
									$this->view->questionnaire_total_acquis = 0;
								}
								
								break;
								
								//livret Acquis
								case 'livretacquis' :
								$notes = array();
								$totaux = array();
								$this->view->datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$notes[] = $temp[0];
									$position[] = $temp[2];
								}
								$i=0;
								$k=0;
								$l=0;
								$note_total = array();

									foreach( $capacites as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										$note_total[$capacite_num]=0;
										$note_base[$capacite_num]=0;
										$k++;
										}
										
								$note_total_finale = 0;
								$note_maxi_finale= 0;
									
									
								foreach( $livret_base as $question ){
									if(isset($notes[$i]))
									{
									$query_question = "SELECT * FROM pmqvision5.capacites 
									LEFT JOIN pmqvision5.classement_capacite 
									ON capacites.nom_capacite =classement_capacite.nom_capacite 
									 WHERE `id_titres` ='".$question['id_titres']."' 
									AND  `position` = '".$position[$i]."' 
									AND type_capacite= 'aquis'
									AND outils = 'livret'
									ORDER BY  position ";
									$result_question = mysql_query($query_question);
									$compte_question=0;	
									while($row_question = mysql_fetch_array($result_question))
										{

												$capacite_num = $row_question['ordre_individuel']-1;
												$note_maxi = $row_question["note"];
												$note_total[$capacite_num]+=$note_maxi;
												$note_maxi_finale  +=$note_maxi;
												$note_total_finale  +=$notes[$i];
												$note_base[$capacite_num]+=$notes[$i];											
										}
																			
										
									}
									
									$i++;								
								}
								
								
								
								//reinitialisation du questionnaire
									$i=0;								
									foreach ($questionnaire as $q)
									{
										$questionnaire[$i]['pourcent'] = 0;
										$questionnaire[$i]['caractere'] = '';
										$questionnaire[$i]['total'] = 0;
										
										$i++;
									}
									foreach( $livret_base as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										
										if($note_base[$capacite_num]!=0  )
										{
											if($note_total[$capacite_num] !=0)
											{
												$percent = round( ( $note_base[$capacite_num]/$note_total[$capacite_num] ) * 100 );
											}else{
												$percent =0;
											}
										}else{
											$percent =0;
										}
											if( $percent > 69 ) $questionnaire[$capacite_num]['caractere'] = '1';
											elseif( $percent < 40 ) $questionnaire[$capacite_num]['caractere'] = '0';
											else $questionnaire[$capacite_num]['caractere'] = '?';
											$questionnaire[$capacite_num]['pourcent'] = $percent;
											$questionnaire[$capacite_num]['total'] = $capacite['note'];
											$l++;
											
									}
								

						if($note_maxi_finale >0)
								{
									$this->view->livretacquis_total = ($note_total_finale/$note_maxi_finale)*100;	
								}else{
									$this->view->livretacquis_total = 0;
								}	
									
									
								$this->view->livretacquis = $questionnaire;
								break;	
								
								
								
							case 'entretien' :
								$this->view->dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								
								if( count( $notes ) == 1 && $notes[0] == '' ){
									break;
								}else{
									
									$k=0;
									$k = $nb_capacite_compare;
								foreach( $capacites as $capacite ){
										$notes_tempo[$k] = $notes[$k];
										$k++;
										}			
									$this->view->entretien = $notes_tempo;
								}
								break;
							case 'bilan' :
								$this->view->datebilan = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								$this->view->bilan = $notes;
								break;
						}

					}
					$this->view->commentaire = $candidat_metier['candidat_metier_formation_remarque'];
					$this->view->commentaire2 = $candidat_metier['candidat_metier_formation_remarque2'];
					if( $candidat_metier['tuteur_id'] > 0 ){
						$binome = $mBinomes->get( $candidat_metier['tuteur_id'] );
						$evaluateur = $mContacts->getPersonne( $binome['contact_id'] );
						$civ = $mCivilites->get( $evaluateur['civilite_id'] );
						if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
						$s .= $evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'];
						$this->view->evaluateur = ucwords($s);
					}else{
						$this->view->evaluateur = '';
					}
					if( $candidat_metier['expert_id'] > 0 ){
						$binome = $mBinomes->get( $candidat_metier['expert_id'] );
						$expert = $mContacts->getPersonne( $binome['contact_id'] );
						$civ = $mCivilites->get( $expert['civilite_id'] );
						if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
						$s .= $expert['personne_prenom'].' '.$expert['personne_nom'];
						$this->view->expert = ucwords($s);
					}else{
						$this->view->expert = '';
					}
					$this->view->poste = $candidat['personne_poste'];

					break;
				case 'ccsp' :

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

					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite);

					$demarch = $xmldemarche->getDemarche();
					$modules = $demarch['modules'];
					$questions = $demarch['questions'];
					$ventilation = $demarch['ventilation'];
					$this->view->modules = $modules;
					$this->view->nb_modules = $modules['nb_modules'];
		            
					$resultats = $mResultats->getResultatsCandidat($candidat_metier_id);
			//si resultats existent
			if( !empty($resultats) ){
				
				
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
										$quest['total'][$idx] = $getNote[1];
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
										$obs['total'][$idx] = $getNote[1];
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
										$ent['total'][$idx] = $getNote[1];
										$ent['actif'][$idx] = $getNote[2];
									}
									$this->view->entretien = $ent;
									break;
							}
				}
			}

			
			  $query_module = "SELECT * FROM pmqvision5.modules WHERE notation ='oui' ORDER BY ordre";
			$result_module = mysql_query($query_module);
			$compte_savoir=0;	
					while($row_module = mysql_fetch_array($result_module))
						{
							$query_savoir = "SELECT * FROM pmqvision5.savoirs WHERE code_module LIKE '".$row_module["code"]."' AND (support_eval = 'observation' OR support_eval = 'question') ORDER BY code";
							$result_savoir = mysql_query($query_savoir);
								while($row_savoirs = mysql_fetch_array($result_savoir))
								{
									$listeEntretiens[$compte_savoir]['name'] = $row_savoirs['code'].utf8_encode($row_savoirs['code_module']).' - '.utf8_encode($row_savoirs['libelle_savoir']);
									$listeEntretiens[$compte_savoir]['value'] = $row_savoirs['note'];
									$listeEntretiens[$compte_savoir]['module'] = $row_savoirs['code_module'];
									$listeEntretiens[$compte_savoir]['nom_module'] = $row_module['nom'];
									$listeEntretiens[$compte_savoir]['support_eval'] = $row_savoirs['support_eval'];
									$listeEntretiens[$compte_savoir]['libelle_module'] = utf8_encode($row_module['libelle_module']);
									$compte_savoir++;
								}
						}				
			
			$infosXml['entretiens'] = $listeEntretiens;
	
			$this->view->listeEntretiens = $infosXml['entretiens'];	
			$this->view->listeQuestionaire = $quest;
			$this->view->listeObservation = $obs;	
			$this->view->listeEntretien = $ent;

			
					
					$resultats = $mResultats->getResultats($candidat_metier_id, $this->passage);

					if( $resultats == false )
						$this->_redirect( '/tableauresultats/index/?id='.$candidat_metier_id.'&passage='.($this->passage - 1) );

					foreach( $resultats as $resultat ){

						$outil = $mOutils->get( $resultat->outil_id );

						switch( $outil['outil_libelle'] ){
							case 'questionnaire' :
								$this->view->datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
									$t[] = $temp[1];
								}
								$i = 1;
								foreach( $ventilation->module as $module ){
									$m[$i]['n'] = 0;
									$m[$i]['t'] = 0;
									$notes = explode( '@', $module['ventilation'] );
									foreach( $notes as $note ){
										$m[$i]['n'] += $n[$note-1];
										$m[$i]['t'] += $t[$note-1];
									}
									$i++;
								}
								$i=0;
								foreach( $m as $module ){
									$questionnaire[$i]['pourcent'] = round( ( $module['n'] / $module['t'] ) * 100 );
									$i++;
								}
								while( $i < $modules['nb_modules'] ){
									$questionnaire[$i]['pourcent'] = "";
									$i++;
								}
								$this->view->questionnaire = $questionnaire;
								break;
							case 'observation' :
								$this->view->dateobservation = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								$n = $t = array();
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
									$t[] = $temp[1];
								}
								$i=0;
								foreach( $modules->module as $module ){
									if( $t[$i] > 0 ) $observations[$i]['pourcent'] = round( ( $n[$i] / $t[$i] ) * 100 );
									else $observations[$i]['pourcent'] = 0;
									$i++;
								}
								$this->view->observation = $observations;
								break;
							case 'entretien' :
								$this->view->dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$v = explode( '@', $resultat->resultat_valeur );
								$n = $t = array();
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
									$t[] = $temp[1];
								}
								$i=0;
								foreach( $modules->module as $module ){
									if( $n[$i] != '' ){
										$entretien[$i]['pourcent'] = round( ( $n[$i] / $t[$i] ) * 100 );
									}elseif( $n[$i] == '' ){
										$entretien[$i]['pourcent'] = '';
									}else{
										$entretien[$i]['pourcent'] = 0;
									}
									$i++;
								}
								$this->view->entretien = $entretien;
								break;
							case 'compréhension orale' :
								$this->view->co = $resultat->resultat_valeur;
								break;
							case 'expression orale' :
								$this->view->eo = $resultat->resultat_valeur;
								break;
							case 'compréhension écrite' :
								$this->view->ce = $resultat->resultat_valeur;
								break;
							case 'expression écrite' :
								$this->view->ee = $resultat->resultat_valeur;
								break;
							case 'raisonnement cognitif, logique et numérique' :
								$this->view->rcln = $resultat->resultat_valeur;
								break;
							case 'repères spatio-temporels' :
								$this->view->rst = $resultat->resultat_valeur;
								break;
								
							case 'compréhension orale reperage' :
								$this->view->core = $resultat->resultat_valeur;
								break;
							case 'expression orale reperage' :
								$this->view->eore = $resultat->resultat_valeur;
								break;
							case 'compréhension écrite reperage' :
								$this->view->cere = $resultat->resultat_valeur;
								break;
							case 'expression écrite reperage' :
								$this->view->eere = $resultat->resultat_valeur;
								break;
							case 'raisonnement cognitif, logique et numérique reperage' :
								$this->view->rclnre = $resultat->resultat_valeur;
								break;
							case 'repères spatio-temporels reperage' :
								$this->view->rstre = $resultat->resultat_valeur;
								break;	
								
						}

					}


					break;
				case 'diplome' :

					break;
			}

		}

		public function printpdfAction(){

			Zend_Layout::getMvcInstance()->disableLayout();

			require_once '../library/fpdf/fpdf.php';
			$pdf = new FPDF('L');
			$pdf->AddPage();
			$pdf->SetFont('Arial','',10);

			$candidat_metier_id = $this->candidat_metier_id;

			$title = "Tableau de synthèse des résultats";

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
			$mCandidats = new Model_Candidat();
			$mCivilites = new Model_Civilite();
			$mEntites = new Model_Entite();
			$mEtats = new Model_Etat();
			$mOperations = new Model_Fiche();
			$fStrings = new Fonctions_Strings();

			$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);
			$demarche = $mCandidatMetiers->getDemarche($candidat_metier_id);

			$metier = $mMetiers->get( $candidat_metier['metier_id'] );

			switch ( $demarche['demarche_abrege'] ){
				case 'cqp' :

					$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ) $titre .= ' - '.$s_titre['bloc2_lib'];

					$pdf->MultiCell( 80, 10, utf8_decode( $titre ), 1, 'C' );

					$pdf->SetXY(90,10);

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];

					$pdf->Cell( 80, 5, 'Candidat : '.utf8_decode( ucwords( $s_nom ) ), 'LTR', 2);

					$entreprise = $mEntites->get($candidat['entite_id']);

					$pdf->Cell( 80, 5, 'Entreprise : '.utf8_decode( ucwords( $entreprise['entite_nom'] ) ), 'LR',2);

					$branche = $mEntites->get( $candidat['parent_id'] );
					
					$pdf->Cell( 80, 5, 'Branche : '.utf8_decode( ucwords( $branche['entite_nom'] ) ), 'LBR');

					$pdf->SetXY(170,10);

					$etat = $mEtats->get( $candidat_metier['etat_id'] );

					$pdf->Cell( 40, 15, utf8_decode( ucfirst( $etat['etat_libelle'] ) ), 1,2, 'C');

					$pdf->SetXY(210,10);
					$pdf->MultiCell( 40, 7.5, utf8_decode( $title ), 1, 'C');

					$pdf->SetXY(250,10);
					$pdf->Cell(40, 15, 'image', 1);

					$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
					$org_ref = $mEntites->get( $corg_ref['entite_id'] );

					$pdf->SetXY(10,20);
					$pdf->Cell(80, 5, utf8_decode( 'Organisme référent : ' . ucwords( $fStrings->reduce( $org_ref['entite_nom'], 25 ) ) . '...' ), 1);


					break;
				case 'ccsp' :

					break;
				case 'diplome' :

					break;
			}

			$pdf->Output();

		}

	}
