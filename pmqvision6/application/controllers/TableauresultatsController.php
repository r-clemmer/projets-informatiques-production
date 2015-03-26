<?php
	class TableauresultatsController extends Zend_Controller_Action {

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

					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');

					$demarch = $xmldemarche->getDemarche();
					$capacites = $demarch['capacites_base'];
					$this->view->nb_capacites = count($capacites);
					//$ventilations = $demarch['ventilations'];

					$question_base = $demarch['question_base'];
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
								$i=0;
								foreach( $capacites as $capacite ){
									if( $capacite['capacite'] != 'Acquis de base' ){
										if( $t[$i] > 0 ){
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
										
										$note_total[$k]=0;
										$note_base[$k]=0;
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
									AND  `position` = '".$question['position']."' 
									ORDER BY  position ";
									$result_question = mysql_query($query_question);
									$compte_question=0;	
									while($row_question = mysql_fetch_array($result_question))
										{
											if($row_question['type_capacite'] == 'normal')
											{
												$nom_capacite = utf8_encode($row_question["nom_capacite"]);
												$capacite_num = $row_question['ordre_individuel']-1;
												$note_maxi = $row_question["note"];
												
												$note_total[$capacite_num]+=$note_maxi;
												$note_base[$capacite_num]+=$notes[$i];											
											}	
											
										}
										
										
									}
									
									$i++;								
								}

								
									foreach( $capacites as $capacite ){
										if($note_base[$l]!=0)
										{
											$percent = round( ( $note_base[$l]/$note_total[$l] ) * 100 );
										}else{
											$percent =0;
										}
											if( $percent > 69 ) $questionnaire[$l]['caractere'] = '1';
											elseif( $percent < 40 ) $questionnaire[$l]['caractere'] = '0';
											else $questionnaire[$l]['caractere'] = '?';
											$questionnaire[$l]['pourcent'] = $percent;
											$l++;
											
									}
								$this->view->questionnaire = $questionnaire;
								break;
							case 'entretien' :
								$this->view->dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								if( count( $notes ) == 1 && $notes[0] == '' ){
									break;
								}else{
									
									$k=0;
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
					
					
					//aquis de base 
					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'aquis');
					$xmldemarche_compare = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite);
					
					$demarch = $xmldemarche->getDemarche();
					$demarche_compare = $xmldemarche_compare->getDemarche();
					$capacites =  array();
					$capacites = $demarch['capacites_base'];
					$capacites_compare = $demarche_compare['capacites_base'];
					$this->view->nb_capacites = count($capacites);
					$questionnaire_aquis = array();
					$observations_acquis= array();
					
					$nb_capacite_compare =  count($capacites_compare)-count($capacites);
					//$ventilations = $demarch['ventilations'];

					$question_base = $demarch['question_base'];
					$livret_base = $demarch['livret_base'];


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
											$observations_acquis[$i]['pourcent'] = round( ( $n[$i] / $t[$i] ) * 100 );
											if( $observations_acquis[$i]['pourcent'] > 69 ) $observations_acquis[$i]['caractere'] = '1';
											elseif( $observations_acquis[$i]['pourcent'] < 40 ) $observations_acquis[$i]['caractere'] = '0';
											else $observations_acquis[$i]['caractere'] = '?';
										}else{
											$observations_acquis[$i]['pourcent'] = 0;
											$observations_acquis[$i]['caractere'] = '0';
										}
									}else{
										$observations_acquis[$i]['pourcent'] = '';
										$observations_acquis[$i]['caractere'] = '';
									}
									$observations_acquis[$i]['total'] = $t[$i];
									
									$i++;
								}
								
								
								$this->view->observations_aquis = $observations_acquis;
								
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
								$note_note_aquis = 0;
								$note_total_aquis = 0;
								
									foreach( $capacites as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										
										if($note_base[$capacite_num]!=0)
										{
											if($note_total[$capacite_num] >0)
											{
												$percent = round( ( $note_base[$capacite_num]/$note_total[$capacite_num] ) * 100 );
											}else{
												
											}
											$note_total_aquis += $note_total[$capacite_num];
											$note_note_aquis += $note_base[$capacite_num];
										}else{
											$percent =0;
										}
											if( $percent > 69 ) $questionnaire_aquis[$capacite_num]['caractere'] = '1';
											elseif( $percent < 40 ) $questionnaire_aquis[$capacite_num]['caractere'] = '0';
											else $questionnaire_aquis[$capacite_num]['caractere'] = '?';
											$questionnaire_aquis[$capacite_num]['pourcent'] = $percent;
											$questionnaire_aquis[$capacite_num]['total'] = $note_total[$capacite_num];
											$l++;
											
									}
								
								//echo $note_total_aquis;
								$this->view->questionnaire_aquis = $questionnaire_aquis;
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
												$note_base[$capacite_num]+=$notes[$i];		
												$note_maxi_finale  +=$note_maxi;
												$note_total_finale  +=$notes[$i];									
										}
																			
										
									}
									
									$i++;								
								}
								
								//reinitialisation du questionnaire
									$i=0;								
									foreach ($questionnaire_aquis as $q)
									{
										$questionnaire_aquis[$i]['pourcent'] = 0;
										$questionnaire_aquis[$i]['caractere'] = '';
										$questionnaire_aquis[$i]['total'] = 0;
										
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
											if( $percent > 69 ) $questionnaire_aquis[$capacite_num]['caractere'] = '1';
											elseif( $percent < 40 ) $questionnaire_aquis[$capacite_num]['caractere'] = '0';
											else $questionnaire_aquis[$capacite_num]['caractere'] = '?';
											$questionnaire_aquis[$capacite_num]['pourcent'] = $percent;
											$questionnaire_aquis[$capacite_num]['total'] = $capacite['note'];
											$l++;
											
									}
								
								$this->view->livretacquis = $questionnaire_aquis;
								
							if($note_maxi_finale >0)
								{
									$this->view->livretacquis_total = ($note_total_finale/$note_maxi_finale)*100;	
								}else{
									$this->view->livretacquis_total = 0;
								}	
								
								break;	
								
								
								
							case 'entretien' :
								
								$notes = explode( '@', $resultat->resultat_valeur );
								if( count( $notes ) == 1 && $notes[0] == '' ){
									break;
								}else{
									
									$o=0;
									$o = $nb_capacite_compare;
									
									$notes_tempo= array();
								foreach( $capacites as $capacite ){
										$notes_tempo[$o] = $notes[$o];
										$o++;
										}			
									
									$this->view->entretien_aquis = $notes_tempo;
								}
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
					
					$expert = '';
					$contacts = $mMetiers->getExperts($metier['metier_id']);
					
					foreach( $contacts as $contact ){
						$expert.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
					
					$this->view->expert = $expert;
					
					$binomes = '';
					$contacts = $mMetiers->getEvaluateurs($metier['metier_id']);

					foreach( $contacts as $contact ){
						$binomes.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
					
					$this->view->evaluateur = $binomes;
					
					
					$this->view->poste = $candidat['personne_poste'];

					break;
					
					//debut cqp Branche
					
					case 'cqpbranche' :
							
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
					
						$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');
					
						$demarch = $xmldemarche->getDemarche();
						$capacites = $demarch['capacites_base'];
						$this->view->nb_capacites = count($capacites);
						//$ventilations = $demarch['ventilations'];
					

						$this->view->capacites = $capacites;
					
						$nb_capacites = count($capacites);
						$resultats = $mResultats->getResultats($candidat_metier_id, $this->passage );
							
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
							

						if($candidat_metier['etat_id'] =='4')
						{
							$this->view->etat = 'Admissible';
						}
						
						if($candidat_metier['etat_id'] =='2')
						{
							$this->view->etat = 'En formation';
						}
						if($candidat_metier['etat_id'] =='13')
						{
							$this->view->etat = 'En Entretien';
						}
						if($candidat_metier['etat_id'] =='6')
						{
							$this->view->etat = 'En Abandon';
						}
						if($candidat_metier['etat_id'] =='10')
						{
							$this->view->etat = 'Inscrit';
						}
						if($candidat_metier['etat_id'] =='5')
						{
							$this->view->etat = 'Certifié';
						}
						if($candidat_metier['etat_id'] =='14')
						{
							$this->view->etat = 'En cours d\'acquisition';
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
							
						$expert = '';
						$contacts = $mMetiers->getExperts($metier['metier_id']);
							
						foreach( $contacts as $contact ){
							$expert.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
						}
							
						$this->view->expert = $expert;
							
						$binomes = '';
						$contacts = $mMetiers->getEvaluateurs($metier['metier_id']);
					
						foreach( $contacts as $contact ){
							$binomes.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
						}
							
						$this->view->evaluateur = $binomes;
							
							
						$this->view->poste = $candidat['personne_poste'];
					
						break;
						
						//fin cqp Branche
					
					
					
					
					
					
					
					
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
						
					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');

					
					$infoDem = $xmldemarche->getDemarche();
					$this->view->listeModule = $infoDem['liste_module'];
					$this->view->num_diplome = $infoDem['diplome_num'];
					
					if( $candidat['candidat_anciennete'] != '' ) $anciennete = $fDates->getNbYears( $candidat['candidat_anciennete'] );
					$this->view->anciennete = $anciennete.' ans';
					$this->view->poste = $candidat['personne_poste'];
					
					$mMetier = new Model_Metier();
							
					$contacts = $mMetier->getReferents($metier['metier_id']);
					
					foreach( $contacts as $contact ){
					$referent_vae = ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
					
					$this->view->referentvae = $referent_vae;
					$mEtat = new Model_Etat();
					$etat = $mEtat->get($candidat_metier->etat_id);
					
					$this->view->etat_libelle =  $etat->etat_libelle;
				
					
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
					
					$expertDiplome = '';
					$contacts = $mMetier->getExperts($metier['metier_id']);
						
					foreach( $contacts as $contact ){
						$expertDiplome = ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
					
					$this->view->expertDiplome = $expertDiplome;
				
					
					
					$resultats = $mResultats->getResultatsCandidat($candidat_metier_id);
					$l = array();
					$m = array();
					$n = array();
					$o = array();
					$p = array();
					$q = array();
					$g= array();
					$tableau_tempo = array();
					foreach( $resultats as $resultat ){

						$outil = $mOutils->get( $resultat->outil_id );

						switch( $outil['outil_libelle'] ){
							
							case 'livret1Diplome' :
							
								$this->view->date_livret1diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
								break;
									
									
							case 'livret2Diplome' :
								$this->view->date_livret2diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
								break;
							
							
							case 'questionDiplome' :
								$v = explode( '@', $resultat->resultat_valeur );
								$i=0;
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									
									if($temp[1] ==''){$temp[1] = 0;}
									if($temp[0] ==''){$temp[0] = 0;}
									$l[] = $temp[0];
									$l_total[] = $temp[1];

								}
								$k=0;
								$count_module = 0;
								foreach ($infoDem['liste_module'] as $modules)
								{
									$k++;
									$count1 = ($k*2)-2;
									$count2 = ($k*2)-1;
									
									
									$division = $l_total[$count1]+$l_total[$count2];
									$resultat_note = $l[$count1]+$l[$count2];
									
									
										if($division !=0)
										{
											$m[$count_module] = ($resultat_note/$division)*100;
											$m[$count_module]  = number_format($m[$count_module],2);
											$p[$count_module] = ($resultat_note/$division);
										}else{
											$m[$count_module] =0;
											$p[$count_module] = 0;
										}
									
									$count_module++;

								}
								
								$this->view->date_questiondiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
								
								$this->view->resultatquestion = $m;
								break;
							
							
							
							case 'observationDiplome' :
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
								}	
							$this->view->date_observationdiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
							$this->view->resultatobservation = $n;
							break;
							
							case 'entretienDiplome' :
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$o[] = $temp[0];
								}
								$this->view->date_entretiendiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
								$this->view->resultatentretien = $o;
								
								break;
								
							case 'bilanDiplome' :
									$v = explode( '@', $resultat->resultat_valeur );
									foreach( $v as $valeurs ){
										$temp = explode( '_', $valeurs );
										$q[] = $temp[0];
									}
									$this->view->date_bilandiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
									$this->view->resultatbilan = $q;
								
									break;
								
						case 'commentaireDiplome' :
		
							$this->view->resultatcommentaire = nl2br($resultat->resultat_valeur);
							
							
							
							
							
						}
					}
					
					
					
					break;
			}

		}

		
	}
