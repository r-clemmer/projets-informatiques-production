<?php
class ImpressionController extends Zend_Controller_Action {

	function init(){
		Zend_Layout::getMvcInstance()->disableLayout();
	}

	public function indexAction(){
	}

	public function diplomeAction(){

		$candidat_metier_id = $this->_request->getParam('candidat_metier_id');
		$mCandidatMetiers = new Model_CandidatMetier();
		$mCandidats = new Model_Candidat();
		$mMetiers = new Model_Metier();
		$mTitres = new Model_Titre();
		$mResultats = new Model_Resultat();
		$mJurys = new Model_Jury();
		$mEntites = new Model_Entite();

		$fDates = new Fonctions_Dates();

		$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);
		$candidat = $mCandidats->get($candidat_metier['candidat_id']);

		//nom
		$nom = "";
		if( $candidat['civilite_abrege'] != 'nc' ){
			$nom .= ucfirst( $candidat['civilite_libelle'] ).' ';
		}
		$nom .= ucwords( $candidat['personne_prenom'] ).' '.strtoupper( $candidat['personne_nom'] );

		//titre
		$metier = $mMetiers->get($candidat_metier['metier_id']);
		$titre_tab = $mTitres->get($metier['demarche_id'], $metier['bloc1_id'], $metier['bloc2_id']);
		$titre = $titre_tab['bloc1']['libelle'];
		$type = substr($titre, 0, 4);
		$titre = str_replace('-V2','',$titre);
		$titre = str_replace(' - V2','',$titre);
		$titre = str_replace('- V2','',$titre);
		$titre = str_replace(' - V2','',$titre);
		$titre = str_replace('CQPi ', '', $titre);
		$titre = str_replace('CQP ', '', $titre);
		$titre = mb_strtoupper($titre, 'UTF-8');
		$codeRNCP = mb_strtoupper($titre_tab['bloc1']['codeRNCP'], 'UTF-8');
		
		$resultat = $mResultats->getLast($candidat_metier_id);

		//jury
		$jury = $mJurys->get($resultat['jury_id']);
			//date
			//$date = $fDates->formatDate($jury['jury_date']);
			$date = $fDates->formatDateMoisLettres($jury['jury_date']);
			//lieu
			$lieu = ucwords( $jury['jury_ville'] );
		//branche
		$entite = $mEntites->get($candidat['entite_id']);
		$branche = $mEntites->get($entite['parent_id']);
		$nom_branche = $branche['entite_nom'];

		$NSF ='';

		if($nom_branche =="textile")
		{
			$NSF = $titre_tab['bloc2']['NSFTextile'];
		}

		if($nom_branche =="couture")
		{
			$NSF = $titre_tab['bloc2']['NSFgenerique'];
		}

		if($nom_branche =="maroquinerie")
		{
			$NSF = $titre_tab['bloc2']['NSFgenerique'];
		}

		if($nom_branche =="habillement")
		{
			$NSF = $titre_tab['bloc2']['NSFHabillement'];
		}

		if($nom_branche =="cuirs et peaux")
		{
			$NSF = $titre_tab['bloc2']['NSFCuirsetPeaux'];
		}
		if($nom_branche =="chaussure")
		{
			$NSF = $titre_tab['bloc2']['NSFChaussure'];
		}

		if($NSF =='')
		{
			$NSF = $titre_tab['bloc2']['NSFgenerique'];
		}
		

		$pdf = new Zend_Pdf();
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		$pdf->pages[] = $page;
		if($metier['bloc1_id'] =='18' && $metier['bloc2_id']=='4')
		{
		
			$dateNSF = '09/01/2015';
		}
		else{
			$dateNSF = '22/01/2013';
		}
		if( $type == 'CQPi' ){

			/*********************************fixe*******************************/
			$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 11);
			$page->drawText("délivré à", 200, 345, "utf-8");
			$dateNSF = '20/01/2014';
			$page->drawText("Sous l'égide de la commission paritaire nationale de l'emploi et de la formation", 200, 310, "utf-8");


			switch( $nom_branche ){
				case 'chaussure' :
				case 'couture' :
				case 'Maroquinerie' :
					$page->drawText("de la ".ucfirst($nom_branche), 590, 310);
						break;
				case 'vente à distance' :
					$page->drawText("de la ".ucfirst($nom_branche), 590, 310);
					break;
				case 'entretien textile' :
				case 'habillement' :
					$page->drawText("de l'".ucfirst($nom_branche), 590, 310);
					break;
				case 'textile' :
					$page->drawText("du ".ucfirst($nom_branche), 590, 310);
					break;
				case 'cuirs et peaux' :
					$page->drawText("des ".ucfirst($nom_branche), 590, 310);
					break;


			}

			$page->drawText("A l'issue de la délibération du jury réuni le", 250, 258, "utf-8");
			$page->drawText("Fait à", 300, 188, "utf-8");
			$page->drawText("Le", 475, 188, "utf-8");
			$page->drawText("Le président du jury", 250, 150, "utf-8");
			$page->drawText("(signature)", 270, 130, "utf-8");
			$page->drawText("Le Titulaire", 450, 150, "utf-8");
			$page->drawText("(signature)", 455, 130, "utf-8");
			$page->drawText("La Fédération", 650, 150, "utf-8");
			$page->drawText("(cachet et signature)", 640, 130, "utf-8");
			


		}
		if($type == 'Comp')
		{
			$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 11);
			$page->drawText("de l'".ucfirst($nom_branche), 590, 310);
		}

		/****************************dynamique********************************/
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 15);
		//
		$font = $page->getFont();
		$fontSize = $page->getFontSize();
		for($i = 0; $i < strlen( $titre ); $i ++) {
            $characters [] = ord ( $titre [$i] );
        }
		$glyphs = $font->glyphNumbersForCharacters($characters);
		$widths = $font->widthsForGlyphs ( $glyphs );
		$textWidth = (array_sum ( $widths ) / $font->getUnitsPerEm ()) * $fontSize;
		$pageWidth = $page->getWidth();
		$pageHeight = $page->getHeight();
		$start = ($pageWidth/2) - ($textWidth/2);
		//
		$remplace_V = array(' - V2','-V2','- V2' );
		$titre = str_replace($remplace_V,'',$titre);
		

		if($metier['bloc2_id'] == "1" && $metier['bloc1_id'] == "18")
		{
			$codeRNCP = mb_strtoupper('Coupeur(se) Matières en Confection', 'UTF-8');
		}

		if($metier['bloc2_id'] == "2" && $metier['bloc1_id'] == "18")
		{
			$codeRNCP = mb_strtoupper('Coupeur(se) Chaussure', 'UTF-8');
		}

		if($metier['bloc2_id'] == "3" && $metier['bloc1_id'] == "18")
		{
			$codeRNCP = mb_strtoupper('Coupeur(se) Matières en Confection', 'UTF-8');
		}



		$page->drawText($codeRNCP, 200, 400, "utf-8");
		
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 18);
		
		$page->drawText($nom, 270, 345, "utf-8");
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 15);

		$page->drawText($lieu, 335, 188, "utf-8");

		$page->drawText($date, 520, 188, "utf-8");

		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 15);
		$page->drawText($date, 480, 258, "utf-8");

		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 11);
		$page->drawText("Ce CQP - Code NSF : ".$NSF, 200, 30, "utf-8");
		$page->drawText("est réputé enregistré au RNCP par arrêté du ".$dateNSF, 200, 17, "utf-8");

		$pdfdata = $pdf->render();

		header("Content-Disposition: inline; filename=diplome.pdf");
		header("Content-type: application/pdf");

		echo $pdfdata;

	}
	
	
	

		

	
	
	
	
	
	

	public function tableauresultatscqpAction(){

			$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			
			$this->passage = $this->_request->getParam('passage');
			
			
			
			$livretacquis_base_aquis = array();
			$note_affiche = array();
			
			if( $this->passage == 1 ) $type = "Positionnement";
			if( $this->passage == 2 ) $type = "Evaluation";
			$title = "Tableau de synthèse des résultats";
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

			
			$this->view->demarche = $demarche['demarche_abrege'];

			
			$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					

					$nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";					


					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];
					$nom = ucwords( $s_nom );

					$entreprise = $mEntites->get($candidat['entite_id']);
					$nb = 45;
					if( strlen( $entreprise['entite_nom'] ) > $nb ) $entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
					else $entreprise = ucwords( $entreprise['entite_nom'] );

					$branche = $mEntites->get( $candidat['parent_id'] );
					$nb = 45;
					if( strlen( $branche['entite_nom'] ) > $nb ) $branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
					else $branche = ucwords( $branche['entite_nom'] );

					$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
					$org_ref = $mEntites->get( $corg_ref['entite_id'] );
					$nb = 45;
					if( strlen( $org_ref['entite_nom'] ) > $nb ) $organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
					else $organisme_referent = ucwords( $org_ref['entite_nom'] );

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
								$datelivret = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								break;
							case 'observation' :
								$dateobservation = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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
								$observations_base = $observations;
								break;
							case 'questionnaire' :
								$notes = array();
								$totaux = array();
								$datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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
								
								
								$questionnaire_base = $questionnaire;
								break;
							case 'entretien' :
								$dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								if( count( $notes ) == 1 && $notes[0] == '' ){
									break;
								}else{
									
									$k=0;
								foreach( $capacites as $capacite ){
										$notes_tempo[$k] = $notes[$k];
										$k++;
										}			
									
									$entretien_base = $notes_tempo;
								}
								break;
							case 'bilan' :
								$datebilan = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								$bilan = $notes;
								break;
						}

					}
					$commentaire = $candidat_metier['candidat_metier_formation_remarque'];
					$commentaire2 = $candidat_metier['candidat_metier_formation_remarque2'];


					$expert = '';
					$contacts = $mMetiers->getExperts($metier['metier_id']);
						
					foreach( $contacts as $contact ){
						$expert.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
						
					$expert = $expert;
						
					$binomes = '';
					$contacts = $mMetiers->getEvaluateurs($metier['metier_id']);
					
					foreach( $contacts as $contact ){
						$binomes.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
					}
						
					$evaluateur = $binomes;
					$poste = $candidat['personne_poste'];

					
					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'aquis');
					$xmldemarche_compare = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite);
					
					$demarch = $xmldemarche->getDemarche();
					$demarche_compare = $xmldemarche_compare->getDemarche();
					
					$capacites_acquis = $demarch['capacites_base'];
					$capacites_compare = $demarche_compare['capacites_base'];
					$this->view->nb_capacites = count($capacites);
					
					
					$nb_capacite_compare =  count($capacites_compare)-count($capacites_acquis);
					//$ventilations = $demarch['ventilations'];

					$question_base = $demarch['question_base'];
					$livret_base = $demarch['livret_base'];
					$this->view->capacites = $capacites_acquis;
					$nb_capacites = count($capacites_acquis);

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
					
					
					foreach( $capacites_acquis as $capacite ){
						$notes[$i] = '';
						$i++;
					}
					$i=0;
					$this->view->observations = $notes;
					$this->view->entretien = $notes;
					
					$questionnaire_aquis = array();
					$observations_acquis = array();

					foreach( $resultats as $resultat ){

						$outil = $mOutils->get( $resultat->outil_id );

						switch( $outil['outil_libelle'] ){
							case 'livret' :
								$datelivret = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								break;
							case 'observation' :
								$v = explode( '@', $resultat->resultat_valeur );
								foreach( $v as $valeurs ){
									$temp = explode( '_', $valeurs );
									$n[] = $temp[0];
									$t[] = $temp[1];
								}
																	
								$i = $nb_capacite_compare;
								
								
								foreach( $capacites_acquis as $capacite ){

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
								$observations_base_aquis = $observations_acquis;
								

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
									foreach( $capacites_acquis as $capacite ){
										
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
								$note_total_aquis = 0;
								$note_note_aquis = 0;
								
									foreach( $capacites_acquis as $capacite ){
										
										$capacite_num = $capacite['ordre_individuel']-1;
										
										if($note_base[$capacite_num]!=0)
										{
											$percent = round( ( $note_base[$capacite_num]/$note_total[$capacite_num] ) * 100 );
										
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
								
								
								
								$questionnaire_base_aquis = $questionnaire_aquis;
								$questionnaire_base_aquis_total = ($note_note_aquis/$note_total_aquis)*100;
								break;
								
								//livret Acquis
								case 'livretacquis' :
								$notes = array();
								$totaux = array();
								$datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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

									foreach( $capacites_acquis as $capacite ){
										
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
								
								
								
								$livretacquis_base_aquis = $questionnaire_aquis;
								
								if($note_maxi_finale >0)
								{
									$livretacquis_total = ($note_total_finale/$note_maxi_finale)*100;	
								}else{
									$livretacquis_total = 0;
								}	
								
								
								
								break;	
								
								
								
							case 'entretien' :
								$dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								$notes_tempo = array();
								if( count( $notes ) == 1 && $notes[0] == '' ){
									break;
								}else{
									
									$k=0;
									$k = $nb_capacite_compare;
								foreach( $capacites_acquis as $capacite ){
										$notes_tempo[$k] = $notes[$k];
										$k++;
										}			
									$entretien_base_aquis = $notes_tempo;
								}
								break;
						
						}

					}
							
		
	//$html=ob_get_contents();
 ob_end_clean();
 $hauteur_mini = number_format((500/(count($capacites))),0);
 $hauteur_maxi  = ($hauteur_mini*(count($capacites)));
 
 
$nb_aquis = count($livretacquis_base_aquis);

 if($nb_aquis !=0)
 {
 	//
 	
 		 $acq = 0;$total_aquis =0;
			

			$total_aquis = number_format($livretacquis_total,'2');
			 

			$acq = 0;$total_obs =0;
			
			 foreach( $observations_base_aquis as $observation ):
			
 		if( isset( $observation['pourcent'] ) && $observation['total'] >0 ): 
			$acq++;
			 endif; 
			
			$total_obs = $observation['pourcent']+$total_obs;
			$total_obs = number_format($total_obs,'2');	
			
		
			
			endforeach; 
			
			if($total_obs !='0')
			{
			$total_obs = $total_obs/$acq;
			}else{
				$total_obs = 0;
			}
			
			
			$total_obs = number_format($total_obs,'2');
			
			


				 $acq = 0;$total_ques =0;
		
			
						$total_ques = number_format($questionnaire_base_aquis_total,'2');
			
			
			 $l=0;$total_note=0; $acq = 0;$total_ent =0;
				
			 $m = 0;

			 foreach( $entretien_base_aquis as $note): 
							
							$total_note+=$note;
							$l++;
							
							if($note =='100'){
								$note_affiche = '?';
							}else{
								$note_affiche = $note;
							}
							
							if($note !='100')
							{		
								$total_ent = $note_affiche+$total_ent;
								
								
							}
							
							$m++;
							 endforeach;
						 

							if($m !=0 && $total_ent != 0)
							{
			$total_ent = $total_ent/$m;
			$total_ent = $total_ent*100;
			
			$total_ent = number_format($total_ent,'2');
			}else{
				$total_ent = 0;
			}

				
 	
 	
 	
 	
 	
 	
 	
 	//
 	


if(($total_obs)<40){$total_obs_valid='0';}if(($total_obs)>69){$total_obs_valid = '1';} if(($total_obs)<=69 && ($total_obs)>=40  ){$total_obs_valid =  '?';}
if(($total_ques)<40){$total_ques_valid='0';}if(($total_ques)>69){$total_ques_valid = '1';} if(($total_ques)<=69 && ($total_ques)>=40  ){$total_ques_valid =  '?';}
if(($total_ent)<40){$total_ent_valid='0';}if(($total_ent)>69){$total_ent_valid = '1';} if(($total_ent)<=69 && ($total_ent)>=40  ){$total_ent_valid =  '?';}
if(($total_aquis)<40){$total_aquis_valid='0';}if(($total_aquis)>69){$total_aquis_valid = '1';} if(($total_aquis)<=69 && ($total_aquis)>=40  ){$total_aquis_valid =  '?';}
  

 }
 
$html = '
<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;background-color:#D3D3D3;" cellspacing="0">

		<tr>
			<td rowspan="2" style="width: 200px; text-align: center;" >
				<label style="font-size: 11px;">'.$titre.'</label>
			</td>
			<td style="width:220px;">
				<label style="font-size: 11px;">Candidat :</label>
				<label style="font-size: 11px;">'.$nom.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:100px;" >
				<label style="font-size: 11px;">'.$type.'</label>
				<br />
				
			</td>
			<td rowspan="3" style="width: 130px; text-align: center;" >
				<label style="font-size: 11px;">'.$title.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:65px;" >
				<img src="../public/img/PMQ_logo.jpg" alt="PMQ" style="width:65px;height:65px;" >
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Entreprise :</label>
				<label style="font-size: 11px;">'.$entreprise.'</label>
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Organisme référent :</label><br />
				<label style="font-size: 11px;">'.$organisme_referent.'</label>
			</td>
			<td>
				<label style="font-size: 11px;">Branche :</label>
				<label style="font-size: 11px;">'.$branche.'</label>
			</td>
		</tr>
	</table>
<br />
<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>
		
<td style="width:747px;border-width:1px;padding:0px;height:20px;vertical-align:middle;text-align:center;">
		<span style="font-size: 11px;font-weight: bold;text-align: center;vertical-align:middle;text-align:center;"><big><big>TABLEAU DE RESULTATS</big></big></span>
</td>
</tr>
</table>
		<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>

<td style="width:50px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 45px;border-spacing: 0px;">
				
			</td>
			</tr>';
			foreach( $capacites as $capacite ):
			$html.= '<tr><td style="text-align: center;width:50px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">'.$capacite['nom'].'
				</label>
			</td>
			</tr>';
			endforeach;
			 if($nb_aquis !=0)
 {
			$html.= '
			<tr><td  style="text-align: center;height: 20px;background-color:#D3D3D3;" >&nbsp;</td></tr>	
			<tr><td style="text-align: center;width:50px;height: 43px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">Total</label>
			</td>
			</tr>';
 }			
		$html.= '</table>
</td>		
		
		
<td style="width:319px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>';
		 if($nb_aquis !=0)
 				{
			$html.= '<td style="background-color:#D3D3D3;height: 45px;border-spacing: 0px;" colspan="2">';
 				}else{
 					$html.= '<td style="background-color:#D3D3D3;height: 45px;border-spacing: 0px;" >';
 				}
			$html.= '<label style="font-size: 11px;">Capacités</label>
				<br><label style="font-size: 11px;">Livret&nbsp;:
				'.$datelivret.'</label>
			</td>
			</tr>';
			foreach( $capacites as $capacite ):
			 if($nb_aquis !=0)
 				{
			$html.= '<tr><td style="text-align: center;width:319px;height: '.$hauteur_mini.'px;vertical-align:middle;" colspan="2">
				<label style="font-size: 10px;">'.$capacite['capacite'].'
				</label>
			</td>
			</tr>';
			 }else{
 			$html.= '<tr><td style="text-align: center;width:362px;height: '.$hauteur_mini.'px;vertical-align:middle;">
				<label style="font-size: 10px;">'.$capacite['capacite'].'
				</label>
			</td>
			</tr>';
 				}
			endforeach;
			
			 if($nb_aquis !=0)
 {
			$html.= '
			<tr><td style="background-color:#D3D3D3;"></td><td  style="text-align: center;height: 20px;background-color:#D3D3D3;" ><label style="font-size: 10px;">Livret<br>acquis de base</label></td></tr>	
			<tr><td style="text-align: center;width:261px;height: 30px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">Total acquis de base</label>
			</td><td style="text-align: center;width:50px;height: 30px;vertical-align:middle;background-color:#D3D3D3;" >
			<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_aquis_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_aquis.' %</label></td>
					</tr>
				</table>	
			</td>
			</tr>';
 }
		$html.= '</table>
</td>';
	
	




	if($this->passage == 1)
	{	

$html.= '<td style="width:100px;border-width:0px;padding:0px;" valign="top">';
}else{
$html.= '<td style="width:90px;border-width:0px;padding:0px;" valign="top">';
}		

$html.= '<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 45px;border-spacing: 0px;">
				<label style="font-size: 11px;">Questionnaire<br/>technique</label><br />
				<label style="font-size: 11px;">'.$datequestionnaire.'</label>
			</td>
	</tr>';
			 foreach( $questionnaire_base as $question ):
	if($this->passage == 1)
	{		 
	$html.= '<tr>
			<td  style="text-align: center;height: '.$hauteur_mini.'px;" >
				<table style="width:100%; height:30px;" >
					<tr>
						<td style="width:40px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$question['caractere'].'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$question['pourcent'].' %</label></td>
					</tr>
				</table>
			</td>
	</tr>';
	}else{
		$html.= '<tr>
			<td  style="width:90px;text-align: center;height: '.$hauteur_mini.'px;background-color:#D3D3D3;" >
				
			</td>
	</tr>';
	}
			endforeach; 
			 if($nb_aquis !=0)
 {
			$html.= '
			<tr><td  style="text-align: center;height: 20px;background-color:#D3D3D3;" ><label style="font-size: 10px;">Questionnaire<br>acquis de base</label></td></tr>	
			<tr><td style="text-align: center;width:30px;height:20px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ques_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ques.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';	
 }				
$html.= '</table>
</td>	

	
	
<td style="width:100px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
			<tr>
			<td style="background-color:#D3D3D3;height:45px;border-spacing: 0px;">
				<label style="font-size: 11px;">Observation/<br/>Projet pro</label><br />
				<label style="font-size: 11px;">'.$dateobservation.'</label>
			</td>
			</tr>';
			foreach( $observations_base as $observation ):
			$html.= '<tr><td  style="text-align: center;height:'.$hauteur_mini.'px;" >';
			if( isset( $observation['pourcent'] ) && $observation['pourcent'] != '' ){
				$html.= '<table style="width:100px; height:40px;"  >
					<tr>
						<td style="width:40px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['caractere'].'</label></td>
						<td width:55px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['pourcent'].' %</label></td>
					</tr>
				</table>';
				
			}else{
	$html.= '<table style="width:100px; height:40px;"  >
					<tr>
						<td style="width:40px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">0</label></td>
						<td width:55px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">0 %</label></td>
					</tr>
				</table>';


			}
			$html.= '</td></tr>';
			 endforeach;
			  if($nb_aquis !=0)
 {
			$html.= '
						<tr><td  style="text-align: center;height: 20px;background-color:#D3D3D3;" ><label style="font-size: 10px;">Observation<br>acquis de base</label></td></tr>	
			<tr><td style="text-align: center;width:30px;height:30px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_obs_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_obs.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';	
 }	
			
$html.= '</table>
</td>



<td style="width:95px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 45px;border-spacing: 0px;width:90px;">
				<label style="font-size: 11px;">Entretien<br/>technique</label><br />
				<label style="font-size: 11px;">'.$dateentretien;

$l=0;$total_note=0;

			$html.= '</label></td>
	</tr>';		
			foreach( $entretien_base as $note):
						 
							$total_note+=$note;
							$l++;
							
							if($note =='100'){
								$note_affiche = '?';
							}else{
								$note_affiche = $note;
							}
							
	 $html.= '<tr>
	 		<td  style="text-align: center;height: '.$hauteur_mini.'px;vertical-align:middle;" ><label style="font-size: 10px;vertical-align:middle;">
				'.$note_affiche.'</label>
			</td>		
	</tr>';
							endforeach; 
							 if($nb_aquis !=0)
 {	
	$html.= '
	<tr><td  style="text-align: center;height: 20px;background-color:#D3D3D3;" ><label style="font-size: 10px;">Entretien<br>acquis de base</label></td></tr>	
	<tr><td style="text-align: center;width:30px;height:30px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:25px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ent_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:40px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ent.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';		
 }					
$html.= '</table>
</td>	


</tr>';

if($nb_aquis >0){
$total_note = $total_note+$total_ent_valid;
$l++;
}


							if($total_note == $l)
							{
								$tempo = "Admissible";
							}
							if($total_note < $l)
							{
								$tempo = "Non Admissible";
							}
							if($total_note > $l || $note_affiche=='')
							{
								$tempo = "A compl&eacute;ter";
							}


		


	 $html.= '</table>';
	 
	  $html.= '<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>
		
<td style="width:747px;border-width:1px;padding:0px;height:20px;vertical-align:middle;text-align:center;">
		<span style="font-size: 11px;font-weight: bold;text-align: center;vertical-align:middle;text-align:center;"><big><big>Bilan : '.$tempo.'</big></big></span>
</td>
</tr>
</table>';
	 
	  $html.= '<br /><table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>
			<td style="width: 180px; background-color:#D3D3D3;height:40px;" >
				<label style="font-size: 11px;">Commentaires Positionnement</label>
			</td>
			<td style="text-align: justify;width: 555px" ><label style="font-size: 11px;">'.$commentaire.'</label></td>
		</tr>
		<tr>
			<td style="width: 180px; background-color:#D3D3D3;height:40px;" >
				<label style="font-size: 11px;">Commentaires Evaluation</label>
			</td>
			<td style="text-align: justify;width: 555px" ><label style="font-size: 11px;">'.$commentaire2.'</label></td>
		</tr>
		<tr>
			<td style="background-color:#D3D3D3;width: 180px;height:30px;">
				<label style="font-size: 11px;">Informations complémentaires</label>
			</td>
			<td style="background-color:#D3D3D3;width: 555px;height:65px;">
				<label style="font-size: 11px;">Référent évaluateur entreprise : '.$evaluateur.'<br />
				Expert métier : '.$expert.'<br />
				Poste occupé par le candidat : '.$poste.'<br />
				'.$nb_heures.'</label>
			</td>
		</tr>
	</table>';
	  


 // convert in PDF
    require_once('../library/html2pdf_v4.03/html2pdf.class.php');
    try
    {
    	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
      //$html2pdf->setModeDebug();
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($html);
        $html2pdf->Output();
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
	
	}
	
	
	public function tableauresultatscqpbrancheAction(){
	
		error_reporting(E_ERROR | E_WARNING | E_PARSE);
		
		$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			
			$this->passage = $this->_request->getParam('passage');
			
			
			
			$livretacquis_base_aquis = array();
			$note_affiche = array();
			
			if( $this->passage == 1 ) $type = "Positionnement";
			if( $this->passage == 2 ) $type = "Evaluation";
			$title = "Tableau de synthèse des résultats";
			$front = Zend_Controller_Front::getInstance();

			$this->view->headLink()->appendStylesheet($front->getBaseUrl()."/css/tableau_resultats.css");
//			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.rotate.1-1.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.mb.flipText.js','text/javascript');

			$candidat_metier_id = $this->candidat_metier_id;

			$title = "Tableau de synthèse des résultats";

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



			
			
			$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					

					$nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";					


					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];
					$nom = ucwords( $s_nom );

					$entreprise = $mEntites->get($candidat['entite_id']);
					$nb = 45;
					if( strlen( $entreprise['entite_nom'] ) > $nb ) $entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
					else $entreprise = ucwords( $entreprise['entite_nom'] );

					$branche = $mEntites->get( $candidat['parent_id'] );
					$nb = 45;
					if( strlen( $branche['entite_nom'] ) > $nb ) $branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
					else $branche = ucwords( $branche['entite_nom'] );

					$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
					$org_ref = $mEntites->get( $corg_ref['entite_id'] );
					$nb = 45;
					if( strlen( $org_ref['entite_nom'] ) > $nb ) $organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
					else $organisme_referent = ucwords( $org_ref['entite_nom'] );

					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');

					$demarch = $xmldemarche->getDemarche();
					$capacites = $demarch['capacites_base'];
					$nb_capacites = count($capacites);
					//$ventilations = $demarch['ventilations'];

					
		
		$resultats = $mResultats->getResultats($candidat_metier_id, $this->passage );
			
		foreach( $resultats as $resultat ){
			$outil = $mOutils->get( $resultat->outil_id );
			switch( $outil['outil_libelle'] ){
				case 'debutFormationBanche' :
					$resultatIdDebutFormationBranche = $resultat->resultat_outil_id;
					$date_debut_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
				case 'finFormationBanche' :
					$resultatIdFinFormationBranche = $resultat->resultat_outil_id;
					$ate_fin_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
				case 'entretienNoteBanche' :
					$resultatIdentretiensBranche = $resultat->resultat_outil_id;
					$date_entretien_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					$resultEnt = explode('@',$resultat->resultat_valeur);
					foreach($resultEnt as $idx => $entNote){
						$getNote = explode('_', $entNote);
						$ent['notes'][$idx] = $getNote[0];
					}
				case 'entretienCommentaireBanche' :
					$resultatIdentretiensCommentaireBranche = $resultat->resultat_outil_id;
					$resultEnt = explode('@£$@',$resultat->resultat_valeur);
					foreach($resultEnt as $idx => $entCommentaire){
						$ent['commentaire'][$idx] = $entCommentaire;
					}
		
					$entretienBranche = $ent;
		
					break;
			}
		}
		
;
			
		
		if($candidat_metier['etat_id'] =='4')
		{
			$etat = 'Admissible';
		}
		
		if($candidat_metier['etat_id'] =='2')
		{
			$etat = 'En formation';
		}
		if($candidat_metier['etat_id'] =='13')
		{
			$etat = 'En Entretien';
		}
		if($candidat_metier['etat_id'] =='6')
		{
			$etat = 'En Abandon';
		}
		if($candidat_metier['etat_id'] =='10')
		{
			$etat = 'Inscrit';
		}
		if($candidat_metier['etat_id'] =='5')
		{
			$etat = 'Certifié';
		}
		
		if($candidat_metier['etat_id'] =='14')
		{
			$etat = 'En cours d\'acquisition';
		}
		
			
		$commentaire = $candidat_metier['candidat_metier_formation_remarque'];
		$commentaire2 = $candidat_metier['candidat_metier_formation_remarque2'];
		if( $candidat_metier['tuteur_id'] > 0 ){
			$binome = $mBinomes->get( $candidat_metier['tuteur_id'] );
			$evaluateur = $mContacts->getPersonne( $binome['contact_id'] );
			$civ = $mCivilites->get( $evaluateur['civilite_id'] );
			if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
			$s .= $evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'];
			$evaluateur = ucwords($s);
		}else{
			$evaluateur = '';
		}
		if( $candidat_metier['expert_id'] > 0 ){
			$binome = $mBinomes->get( $candidat_metier['expert_id'] );
			$expert = $mContacts->getPersonne( $binome['contact_id'] );
			$civ = $mCivilites->get( $expert['civilite_id'] );
			if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
			$s .= $expert['personne_prenom'].' '.$expert['personne_nom'];
			$expert = ucwords($s);
		}else{
			$expert = '';
		}
			
		$expert = '';
		$contacts = $mMetiers->getExperts($metier['metier_id']);
			
		foreach( $contacts as $contact ){
			$expert.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$expert = $expert;
			
		$binomes = '';
		$contacts = $mMetiers->getEvaluateurs($metier['metier_id']);
			
		foreach( $contacts as $contact ){
			$binomes.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$evaluateur = $binomes;
			
			
		$poste = $candidat['personne_poste'];			
	
		//$html=ob_get_contents();
		ob_end_clean();
		$hauteur_mini = number_format((500/(count($capacites))),0);
		$hauteur_maxi  = ($hauteur_mini*(count($capacites)));
	
		$html = '<table border="1" style="border-collapse:collapse;width:747px;" id="header">
		<tr>
			<td rowspan="2" style="width: 323px; text-align: center;" >
				<label>'.$titre.'</label>
			</td>
			<td style="width: 350px;">
				<label>Candidat</label>
				'.$nom.'
			</td>
			
			<td rowspan="3" style="text-align: center;" >
				<img src="../public/img/cqp3.jpg" alt="PMQ" style="width:65px;height:65px;" >
			</td>
		</tr>

		<tr>
			<td>
				<label>Entreprise</label>
				'.$entreprise.'
			</td>
		</tr>

		<tr>
			<td>
				<label>Organisme référent</label><br />
				'.$organisme_referent.'
			</td>
			<td>
				<label>Branche</label>
				'.$branche.'
			</td>
		</tr>
	</table>
<br>';


$hauteur_mini = number_format((640/(count($capacites))),0);
 $hauteur_maxi  = ($hauteur_mini*(count($capacites)));

$html.= '<br>
	<table border="1" style="border-collapse:collapse;padding:0px;width:747px;height:25px" >
		<tr>
			<td style="width:747px;height: 25px;text-align: center;" valign="middle" >
				<span style="font-size: 12px;font-weight: bold;text-align:center">
					<big><big>Tableau de résultats</big></big>
				</span>

			</td>
		</tr>
	</table>
		<table border="0" style="border-collapse:collapse;padding:0px;width:748px;"" >
			<tr>
				<td style="padding:0px;width:100px;" valign="top">
					<table  style="width:100px;border-collapse:collapse;" border="1">
						<tr>
							<td style="background-color:#D3D3D3;height: 55px;width:100px;"></td>
						</tr>';
			 foreach( $capacites as $capacite ):
			$html.= '
						<tr>
							<td valign="middle" style="text-align: center;height:'.$hauteur_mini.'px;background-color:#D3D3D3;width:100px;" >
								<label>
									'.$capacite['nom'].'
								</label>
							</td>
						</tr>';
			 
			 endforeach;
			
		$html.= '</table>
				</td>
		
			<td style="padding:0px;width:320px;" valign="top" >
				<table  border="1" style="border-collapse:collapse;">
					<tr>
						<td style="background-color:#D3D3D3;height: 55px"  >
				<br>			
				<label><big><big>&nbsp;Capacités</big></big></label>
							<br><br><label>&nbsp;Date entretien&nbsp;:</label>
							'.$date_entretien_branche.'
						</td>
					</tr>';
		
		foreach( $capacites as $capacite ):
		
		$html.= '	<tr>
						<td style="text-align: center;height:'.$hauteur_mini.'px;width:320px"  >
							<label>
								'.$capacite['capacite'].'
							</label>
						</td>
					</tr>';
		endforeach;
			
		$html.= '</table>				
			
</td>
<td style="padding:0px;width:100px;" valign="top">
		<table style="width:100px;border-collapse:collapse;"  border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 55px; width:100px;">
				<br>
				&nbsp;&nbsp;&nbsp;<label>Appréciations</label><br />
			</td>
	</tr>	';	
				 foreach( $entretienBranche['notes'] as $entretienBranche2): 
	$html.= ' <tr>
	 		<td  style="text-align: center;left;height: '.$hauteur_mini.'px;width:100px;">';
			 
				if($entretienBranche2['notes'] == '1') $html.= '<p style="color:green;font-size:12px">Acquise</p>';
				if($entretienBranche2['notes'] == '0') $html.= '<p style="color:red;font-size:12px">En cours d\'acquisition</p>';
				
			$html.= ' 	</td>		
	</tr>';
							 endforeach; 
											
$html.= '</table>
</td>	


<td style="padding:0px;width:203px;" valign="top">
		<table style="width:203px;border-collapse:collapse;"  border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 55px;width:203px;">
		<br>		
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<label>Commentaires</label><br />
				
			</td>
	</tr>';		
 foreach( $entretienBranche['commentaire'] as $entretienBranche2): 
	$html.= ' <tr>
	 		<td  style="text-align: left;height:'.$hauteur_mini.'px;width:203px;" >
				'. nl2br(stripslashes($entretienBranche2)).'
				
			</td>		
	</tr>';
		 endforeach;	
											
	$html.= '</table>
</td>	





</tr>


	</table>
	
	<table border="1" width="100%" style="border-spacing: 0px;padding:0px;border-collapse:collapse;height:25px" >
		<tr>
			<td style="width:751px;border-width:1px;padding:0px;height:25px;text-align:center;" valign="middle">
				
					<span style="font-size: 12px;font-weight: bold">
						<big><big>Bilan : '.$etat.'</big></big>
					</span>
				
			</td>
		</tr>
	</table>

							
<br>
		<table border="1" style="border-collapse:collapse;padding:0px;width:747px;" >
		<tr>
			<td style="width:130px;background-color:#D3D3D3;" >
				<label>Commentaires en Formation</label>
			</td>
			<td style="text-align: justify;width:609px;" >'.$commentaire.'</td>
		</tr>
		<tr>
			<td style="width: 130px; background-color:#D3D3D3;" >
				<label>Commentaires Evaluation</label>
			</td>
			<td style="text-align: justify;width:609px;" >'.$commentaire2.'</td>
		</tr>
		<tr>
			<td style="width: 130px;background-color:#D3D3D3;">
				<label>Informations complémentaires</label>
			</td>
			<td style="background-color:#D3D3D3;width:609px;">
				
				Expert métier : '.$expert.'<br />
				Poste occupé par le candidat : '.$poste.'<br />
				'.$nb_heures.'
			</td>
		</tr>
	</table>';

		// convert in PDF
		require_once('../library/html2pdf_v4.03/html2pdf.class.php');
		try
		{
			$html2pdf = new HTML2PDF('P', 'A4', 'fr');
			//$html2pdf->setModeDebug();
			$html2pdf->setDefaultFont('Arial');
			$html2pdf->writeHTML($html);
			$html2pdf->Output();
		}
		catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
	
	}
	
	
	

	public function tableauresultatscqpacquisAction(){

			$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			
			$this->passage = $this->_request->getParam('passage');
			
			if( $this->passage == 1 ) $type = "Positionnement";
			if( $this->passage == 2 ) $type = "Evaluation";
			$title = "Tableau de synthèse des résultats";
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

			
			$this->view->demarche = $demarche['demarche_abrege'];

			
			$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					

					$nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";					


					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];
					$nom = ucwords( $s_nom );

					$entreprise = $mEntites->get($candidat['entite_id']);
					$nb = 45;
					if( strlen( $entreprise['entite_nom'] ) > $nb ) $entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
					else $entreprise = ucwords( $entreprise['entite_nom'] );

					$branche = $mEntites->get( $candidat['parent_id'] );
					$nb = 45;
					if( strlen( $branche['entite_nom'] ) > $nb ) $branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
					else $branche = ucwords( $branche['entite_nom'] );

					$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
					$org_ref = $mEntites->get( $corg_ref['entite_id'] );
					$nb = 45;
					if( strlen( $org_ref['entite_nom'] ) > $nb ) $organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
					else $organisme_referent = ucwords( $org_ref['entite_nom'] );

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
								$datelivret = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								break;
							case 'observation' :
								$dateobservation = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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
								$observations_base = $observations;

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
								$note_total_aquis = 0;
								$note_note_aquis = 0;
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
								$questionnaire_base = $questionnaire;
								$questionnaire_total_acquis = ($note_note_aquis/$note_total_aquis)*100;							
								break;
								
								//livret Acquis
								case 'livretacquis' :
								$notes = array();
								$totaux = array();
								$datequestionnaire = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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
									$note_maxi_finale = 0;
									$note_total_finale = 0;
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
								
						$i=0;								
									foreach ($questionnaire_base as $q)
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
									$livretacquis_total = ($note_total_finale/$note_maxi_finale)*100;	
								}else{
									$ivretacquis_total = 0;
								}	
								
								$livretacquis_base = $questionnaire;
								break;	
								
								
								
							case 'entretien' :
								$dateentretien = $fDates->formatDateMoisLettres( $resultat->resultat_date );
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
									$entretien_base = $notes_tempo;
								}
								break;
							case 'bilan' :
								$datebilan = $fDates->formatDateMoisLettres( $resultat->resultat_date );
								$notes = explode( '@', $resultat->resultat_valeur );
								$bilan = $notes;
								break;
						}

					}
					$commentaire = $candidat_metier['candidat_metier_formation_remarque'];
					$commentaire2 = $candidat_metier['candidat_metier_formation_remarque2'];
					if( $candidat_metier['tuteur_id'] > 0 ){
						$binome = $mBinomes->get( $candidat_metier['tuteur_id'] );
						$evaluateur = $mContacts->getPersonne( $binome['contact_id'] );
						$civ = $mCivilites->get( $evaluateur['civilite_id'] );
						if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
						$s .= $evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'];
						$evaluateur = ucwords($s);
					}else{
						$evaluateur = '';
					}
					if( $candidat_metier['expert_id'] > 0 ){
						$binome = $mBinomes->get( $candidat_metier['expert_id'] );
						$expert = $mContacts->getPersonne( $binome['contact_id'] );
						$civ = $mCivilites->get( $expert['civilite_id'] );
						if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
						$s .= $expert['personne_prenom'].' '.$expert['personne_nom'];
						$expert = ucwords($s);
					}else{
						$expert = '';
					}
					$poste = $candidat['personne_poste'];

		
	//$html=ob_get_contents();
 ob_end_clean();
 $hauteur_mini = number_format((590/(count($capacites))),0);
 $hauteur_maxi  = ($hauteur_mini*(count($capacites)));
 
 
	
				 $acq = 0;$total_aquis =0;
			
		

			$total_aquis = number_format($livretacquis_total,'2');


			$acq = 0;$total_obs =0;
			
			 foreach( $observations_base as $observation ):
			
			 if( isset( $observation['pourcent'] ) && $observation['total'] >0 ): 
			
			 endif; 
			
			$total_obs = $observation['pourcent']+$total_obs;
			$total_obs = number_format($total_obs,'2');	
			
		
			 
			if($observation['pourcent'] !=''){$acq++;}
			endforeach; 
			
			if($total_obs !='0')
			{
			$total_obs = $total_obs/4;
			}else{
				$total_obs = 0;
			}
			
			
			$total_obs = number_format($total_obs,'2');
			
			
			


				 $acq = 0;$total_ques =0;
		

			
			$total_ques = number_format($questionnaire_total_acquis,'2');
			$l=0;$total_note=0; $acq = 0;$total_ent =0;
				
			 $m = 0;

			 foreach( $entretien_base as $note): 
							
							$total_note+=$note;
							$l++;
							
							if($note =='100'){
								$note_affiche = '?';
							}else{
								$note_affiche = $note;
							}
							
							if($note !='100')
							{		
								$total_ent = $note_affiche+$total_ent;
								
								
							}
							$m++;
							 endforeach;
						 

							if($m !=0 && $total_ent != 0)
							{
			$total_ent = $total_ent/$m;

			
			}else{
				$total_ent = 0;
			}
			
$total_ent = $total_ent*100;


$total_ent = number_format($total_ent,'2');


if(($total_obs)<40){$total_obs_valid='0';}if(($total_obs)>69){$total_obs_valid = '1';} if(($total_obs)<=69 && ($total_obs)>=40  ){$total_obs_valid =  '?';}
if(($total_ques)<40){$total_ques_valid='0';}if(($total_ques)>69){$total_ques_valid = '1';} if(($total_ques)<=69 && ($total_ques)>=40  ){$total_ques_valid =  '?';}
if(($total_ent)<40){$total_ent_valid='0';}if(($total_ent)>69){$total_ent_valid = '1';} if(($total_ent)<=69 && ($total_ent)>=40  ){$total_ent_valid =  '?';}
if(($total_aquis)<40){$total_aquis_valid='0';}if(($total_aquis)>69){$total_aquis_valid = '1';} if(($total_aquis)<=69 && ($total_aquis)>=40  ){$total_aquis_valid =  '?';}
  
 
$html = '
<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;background-color:#D3D3D3;" cellspacing="0">

		<tr>
			<td rowspan="2" style="width: 200px; text-align: center;" >
				<label style="font-size: 11px;">'.$titre.'</label>
			</td>
			<td style="width:220px;">
				<label style="font-size: 11px;">Candidat :</label>
				<label style="font-size: 11px;">'.$nom.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:100px;" >
				<label style="font-size: 11px;">'.$type.'</label>
				<br />
				
			</td>
			<td rowspan="3" style="width: 130px; text-align: center;" >
				<label style="font-size: 11px;">'.$title.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:65px;" >
				<img src="../public/img/PMQ_logo.jpg" alt="PMQ" style="width:65px;height:65px;" >
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Entreprise :</label>
				<label style="font-size: 11px;">'.$entreprise.'</label>
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Organisme référent :</label><br />
				<label style="font-size: 11px;">'.$organisme_referent.'</label>
			</td>
			<td>
				<label style="font-size: 11px;">Branche :</label>
				<label style="font-size: 11px;">'.$branche.'</label>
			</td>
		</tr>
	</table>
<br />
<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>
		
<td style="width:747px;border-width:1px;padding:0px;height:20px;vertical-align:middle;text-align:center;">
		<span style="font-size: 11px;font-weight: bold;text-align: center;vertical-align:middle;text-align:center;"><big><big>TABLEAU DE RESULTATS ACQUIS DE BASE</big></big></span>
</td>
</tr>
</table>
		<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
		<tr>

<td style="width:30px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 40px;border-spacing: 0px;">
				
			</td>
			</tr>';
			foreach( $capacites as $capacite ):
			$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">'.$capacite['nom'].'
				</label>
			</td>
			</tr>';
			endforeach;
				$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">Total
				</label>
			</td>
			</tr>';
			
		$html.= '</table>
</td>		
		
		
<td style="width:291px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 40px;border-spacing: 0px;">
				<label style="font-size: 11px;">Savoirs</label>
				<br><label style="font-size: 11px;">Livret&nbsp;:
				'.$datelivret.'</label>
			</td>
			</tr>';
			foreach( $capacites as $capacite ):
			$html.= '<tr><td style="text-align: center;width:281px;height: '.$hauteur_mini.'px;vertical-align:middle;" >
				<label style="font-size: 10px;">'.$capacite['capacite'].'
				</label>
			</td>
			</tr>';
			endforeach;
		$html.= '<tr><td style="text-align: center;width:281px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
				<label style="font-size: 10px;">Total acquis de base
				</label>
			</td>
			</tr>';
		$html.= '</table>
</td>
	

<td style="width:70px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
			<tr>
			<td style="background-color:#D3D3D3;height:40px;border-spacing: 0px;">
				<label style="font-size: 11px;">Livret</label><br />
				<label style="font-size: 11px;">'.$datelivret.'</label>
			</td>
			</tr>';
			foreach( $livretacquis_base as $observation ):

			if( isset( $observation['pourcent'] ) && $observation['total'] >0 ){
				$html.= '<tr><td  style="text-align: center;height:'.$hauteur_mini.'px;" >';
				$html.= '<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['caractere'].'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['pourcent'].' %</label></td>
					</tr>
				</table>';
				$html.= '</td></tr>';
			}else{
				$html.= '<tr>
			<td  style="text-align: center;height: '.$hauteur_mini.'px;background-color:#D3D3D3;" >
				
			</td>
	</tr>';
			}

			 endforeach;
			$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_aquis_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_aquis.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';
			 
			
$html.= '</table>
</td>	






<td style="width:70px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 40px;border-spacing: 0px;">
				<label style="font-size: 11px;">Questionnaire<br/>technique</label><br />
				<label style="font-size: 11px;">'.$datequestionnaire.'</label>
			</td>
	</tr>';
			 foreach( $questionnaire_base as $question ):
	
	if($this->passage == 1)
	{	
		if( $question['total'] >0 )	 
		{
			$html.= '<tr>
					<td  style="text-align: center;height: '.$hauteur_mini.'px;" >
						<table style="width:100%; height:30px;" >
							<tr>
								<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$question['caractere'].'</label></td>
								<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
								<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$question['pourcent'].' %</label></td>
							</tr>
						</table>
					</td>
			</tr>';
		}else{
			$html.= '<tr>
			<td  style="text-align: center;height: '.$hauteur_mini.'px;background-color:#D3D3D3;" >
				
			</td>
	</tr>';
		}
	}else{
		$html.= '<tr>
			<td  style="text-align: center;height: '.$hauteur_mini.'px;background-color:#D3D3D3;" >
				
			</td>
	</tr>';
	}
			endforeach; 
			$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ques_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ques.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';			
$html.= '</table>
</td>	




<td style="width:70px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
			<tr>
			<td style="background-color:#D3D3D3;height:40px;border-spacing: 0px;">
				<label style="font-size: 11px;">Observation/<br />Projet pro</label><br />
				<label style="font-size: 11px;">'.$dateobservation.'</label>
			</td>
			</tr>';
			$k=0;
			foreach( $observations_base as $observation ):

			if( isset( $observation['pourcent'] )  && $observation['total'] >0 ){ 
				$html.= '<tr><td  style="text-align: center;height:'.$hauteur_mini.'px;" >';
				$html.= '<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['caractere'].'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$observation['pourcent'].' %</label></td>
					</tr>
				</table>';
			$html.= '</td></tr>';
			$k++;	
			}else{
				$html.= '<tr>
			<td  style="text-align: center;height: '.$hauteur_mini.'px;background-color:#D3D3D3;" >
				
			</td>
	</tr>';
			}

			 endforeach;
			$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_obs_valid.'</label></td>
						<td width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:35px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_obs.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';
$html.= '</table>
</td>	









<td style="width:70px;border-width:0px;padding:0px;" valign="top">
		<table style="border-spacing: 0px;border-collapse:collapse" border="1">
	<tr>
			<td style="background-color:#D3D3D3;height: 40px;border-spacing: 0px;">
				<label style="font-size: 11px;">Entretien<br/>technique</label><br />
				<label style="font-size: 11px;">'.$dateentretien;

$l=0;$total_note=0;

			$html.= '</label></td>
	</tr>';		
			foreach( $entretien_base as $note):
						 
							$total_note+=$note;
							$l++;
							
							if($note =='100'){
								$note_affiche = '?';
							}else{
								$note_affiche = $note;
							}
							
	 $html.= '<tr>
	 		<td  style="text-align: center;height: '.$hauteur_mini.'px;vertical-align:middle;width:70px;" ><label style="font-size: 10px;vertical-align:middle;">
				'.$note_affiche.'</label>
			</td>		
	</tr>';
							endforeach; 	
						$html.= '<tr><td style="text-align: center;width:30px;height: '.$hauteur_mini.'px;vertical-align:middle;background-color:#D3D3D3;" >
					<table style="width:70px; height:40px;"  >
					<tr>
						<td style="width:35px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ent_valid.'</label></td>
						<td width:25px; text-align: center; border:none; border-right-style: dotted;vertical-align:middle;height:40px;><img src="../public/img/dotte.png" alt="PMQ" style=";height:40px;" ></td>
						<td style="width:45px; text-align: center; border: none;vertical-align:middle;height:40px;" ><label style="font-size: 10px;vertical-align:middle;">'.$total_ent.' %</label></td>
					</tr>
				</table>
			</td>
			</tr>';						
$html.= '</table>
</td>	
</tr>';



							if($total_note == $l)
							{
								$tempo = "Admissible";
							}
							if($total_note < $l)
							{
								$tempo = "Non Admissible";
							}
							if($total_note > $l || $note_affiche=='')
							{
								$tempo = "A compl&eacute;ter";
							}


		


	 $html.= '</table>';

	
 // convert in PDF
    require_once('../library/html2pdf_v4.03/html2pdf.class.php');
    try
    {
    	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
      //$html2pdf->setModeDebug();
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($html);
        $html2pdf->Output();
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
	
		
	}
	
	

	public function tableauresultatsdiplomeAction(){

	$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			
			$this->passage = $this->_request->getParam('passage');
			
			if( $this->passage == 1 ) $type = "Positionnement";
			if( $this->passage == 2 ) $type = "Evaluation";
			$title = "Tableau de synthèse des résultats";
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

			
			$this->view->demarche = $demarche['demarche_abrege'];

			
			$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					
		
			
		$nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";
			
			
		$civ = $mCivilites->get( $candidat['civilite_id'] );
		if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_abrege;
		else $s_nom = "";
		$s_nom .= ' '.$candidat['personne_prenom'];
		$s_nom .= ' '.$candidat['personne_nom'];
		$nom = ucwords( $s_nom );
			
		$entreprise = $mEntites->get($candidat['entite_id']);
		$nb = 45;
		if( strlen( $entreprise['entite_nom'] ) > $nb ) $this->view->entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
		else $entreprise = ucwords( $entreprise['entite_nom'] );
			
		$branche = $mEntites->get( $candidat['parent_id'] );
		$nb = 45;
		if( strlen( $branche['entite_nom'] ) > $nb ) $this->view->branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
		else $branche = ucwords( $branche['entite_nom'] );
			
		$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
		$org_ref = $mEntites->get( $corg_ref['entite_id'] );
		$nb = 45;
		if( strlen( $org_ref['entite_nom'] ) > $nb ) $this->view->organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
		else $organisme_referent = ucwords( $org_ref['entite_nom'] );
		
		
		if( $candidat['candidat_anciennete'] != '' ) $anciennete = $fDates->getNbYears( $candidat['candidat_anciennete'] );
		$anciennete = $anciennete.' ans';
		$poste = $candidat['personne_poste'];
			
		$mMetier = new Model_Metier();
			
		$contacts = $mMetier->getReferents($metier['metier_id']);
			
		foreach( $contacts as $contact ){
			$referent_vae = ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$referentvae = $referent_vae;
		
		$expertDiplome = '';
		$contacts = $mMetier->getExperts($metier['metier_id']);
		
		foreach( $contacts as $contact ){
			$expertDiplome = ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$expertDiplome = $expertDiplome;
		
		
		$mEtat = new Model_Etat();
		$etat = $mEtat->get($candidat_metier->etat_id);
			
		$etat_libelle =  $etat->etat_libelle;

		if( $candidat_metier['expert_id'] > 0 ){
			$binome = $mBinomes->get( $candidat_metier['expert_id'] );
			$expert = $mContacts->getPersonne( $binome['contact_id'] );
			$civ = $mCivilites->get( $expert['civilite_id'] );
			if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
			$s .= $expert['personne_prenom'].' '.$expert['personne_nom'];
			$expert = ucwords($s);
		}else{
			$expert = '';
		}
		
		$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');
		
			
		$infoDem = $xmldemarche->getDemarche();
		$listeModule = $infoDem['liste_module'];
		$num_diplome = $infoDem['diplome_num'];
			
			
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
						
					$date_livret1diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
						
						
				case 'livret2Diplome' :
					$date_livret2diplome = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
				
				case 'questionDiplome' :
					$v = explode( '@', $resultat->resultat_valeur );
					$i=0;
					foreach( $v as $valeurs ){
						$temp = explode( '_', $valeurs );
							
						if($temp[1] ==''){
							$temp[1] = 0;
						}
						if($temp[0] ==''){
							$temp[0] = 0;
						}
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
		
		
		
		
					$resultatquestion = $m;
					$date_questiondiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
						
						
						
				case 'observationDiplome' :
					$v = explode( '@', $resultat->resultat_valeur );
					foreach( $v as $valeurs ){
						$temp = explode( '_', $valeurs );
						$n[] = $temp[0];
					}
					$resultatobservation = $n;
					$date_observationiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
						
				case 'entretienDiplome' :
					$v = explode( '@', $resultat->resultat_valeur );
					foreach( $v as $valeurs ){
						$temp = explode( '_', $valeurs );
						$o[] = $temp[0];
					}
					$resultatentretien = $o;
					$date_entretiendiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
		
		
					case 'bilanDiplome' :
						$v = explode( '@', $resultat->resultat_valeur );
						foreach( $v as $valeurs ){
							$temp = explode( '_', $valeurs );
							$q[] = $temp[0];
						}
						$resultatbilan = $q;
						$date_bilandiplome = Fonctions_Dates::formatDate($resultat->resultat_date);
						break;
						
						case 'commentaireDiplome' :
						
							$resultatcommentaire = nl2br($resultat->resultat_valeur);
						
						
						
						
			}
		}
		
		//debut html
		$html = '
		<table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;background-color:#D3D3D3;" cellspacing="0">

		<tr>
			<td rowspan="2" style="width: 200px; text-align: center;" >
				<label style="font-size: 11px;">'.$titre.'</label>
			</td>
			<td style="width:220px;">
				<label style="font-size: 11px;">Candidat :</label>
				<label style="font-size: 11px;">'.$nom.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:100px;" >
				<label style="font-size: 11px;">'.$etat_libelle.'</label>
				<br />
				
			</td>
			<td rowspan="3" style="width: 130px; text-align: center;" >
				<label style="font-size: 11px;">'.$title.'</label>
			</td>
			<td rowspan="3" style="text-align: center;width:65px;" >
				<img src="../public/img/PMQ_logo.jpg" alt="PMQ" style="width:65px;height:65px;" >
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Entreprise :</label>
				<label style="font-size: 11px;">'.$entreprise.'</label>
			</td>
		</tr>

		<tr>
			<td>
				<label style="font-size: 11px;">Organisme référent :</label><br />
				<label style="font-size: 11px;">'.$organisme_referent.'</label>
			</td>
			<td>
				<label style="font-size: 11px;">Branche :</label>
				<label style="font-size: 11px;">'.$branche.'</label>
			</td>
		</tr>
	</table>
<br />';
		
		$html .= '<table border=1 width="100%" style="border-collapse:collapse;width:715px;" id="header">
		
				<tr>
					<td style="width:200px;background-color:#D3D3D3;"><label style="font-size: 11px;">Livret 1</label></td>
					<td style="background-color:white;width:535px;"><label style="font-size: 11px;">Date : '.$date_livret1diplome.'</label></td>
				</tr>
					<tr>
					<td  style="width:200px;background-color:#D3D3D3;">	<label style="font-size: 11px;">Livret 2</label></td>
					<td style="background-color:white;;width:535px;"><label style="font-size: 11px;">Date : '.$date_livret2diplome.'</label></td> 
				</tr>
		</table>
		<br>
		
		
		
		<table border=1 width="100%" style="border-collapse:collapse;width:715px;" id="header">
			<tr>
				<td style="vertical-align:middle;text-align: center;background-color:#D3D3D3;" colspan="2"><label style="font-size: 10px;">
					Unit&eacute;s</label>
				</td>
				<td style="vertical-align:middle;text-align: center;background-color:#D3D3D3;"><label style="font-size: 10px;">
					Modules</label>
				</td>
				<td style="vertical-align:top;text-align: center;">
			<table border=1  style="border-collapse:collapse" >
				<tr><td style="height:48px;width:65px;background-color:#D3D3D3;vertical-align:middle;"><label style="font-size: 10px;">R&eacute;sultats<br>questionnaire<br>technique</label></td></tr>
				<tr><td style="height:10px;width:65px;vertical-align:middle;"><label style="font-size: 10px;">'.$date_questiondiplome.'</label></td></tr>	
			</table>
		</td>
		<td style="vertical-align:top;text-align: center;">
			<table border=1 style="border-collapse:collapse" >
				<tr><td style="height:48px;width:65px;background-color:#D3D3D3;vertical-align:middle;"><label style="font-size: 10px;">R&eacute;sultats<br>Observation en<br>situation de<br>travail</label></td></tr>
				<tr><td style="height:10px;width:65px;vertical-align:middle;"><label style="font-size: 10px;">'.$date_observationiplome.'</label></td></tr>	
			</table>
			
			
		</td>
		<td style="vertical-align:top;text-align: center;">
			<table border=1  style="border-collapse:collapse" >
				<tr><td style="height:48px;width:65px;background-color:#D3D3D3;vertical-align:middle;"><label style="font-size: 10px;">R&eacute;sultats<br>Entretien<br>technique<br>final</label></td></tr>
				<tr><td style="height:10px;width:65px;vertical-align:middle;"><label style="font-size: 10px;">'.$date_entretiendiplome.'</label></td></tr>	
			</table>

		</td>
		<td style="vertical-align:top;text-align: center;">
			<table border=1  style="border-collapse:collapse" >
				<tr><td style="height:48px;width:65px;background-color:#D3D3D3;vertical-align:middle;"><label style="font-size: 10px;">Bilan</label></td></tr>
				<tr><td style="height:10px;width:65px;vertical-align:middle;"><label style="font-size: 10px;">'.$date_bilandiplome.'</label></td></tr>	
			</table>
		</td>
			</tr>';
			
			
		
			
					
					$j = 1;
					$var='';
					$k = 0;
					$l = 0;
					$m = 0;
					$o = 0;
					$total_bian = 0;
					$nb_module = count($listeModule);
					
					$hauteur_mini = number_format((470/(count($listeModule))),0);
					
					
					foreach($listeModule as $index => $listeModule):
					$nom_unite = '';
					
					
		
					 if($var != $listeModule['numero_unite'])	{
						$tableauDeResultat = new Model_Resultat();
						$resultat = $tableauDeResultat->connaitreunite($listeModule['id_unite'],$num_diplome);
					
						$html .= '<tr>
				<td style="vertical-align:middle;text-align: center;width:40px;">
					'.$listeModule['numero_unite'].'
				</td>
				<td style="vertical-align:middle;text-align: center;width:155px;"><label style="font-size: 10px;">
					'.$listeModule['nom_unite'].'</label>
				</td>
				<td style="vertical-align:middle;text-align: left;">
					<table width="100%" style="border-collapse:collapse;border:0px;" border="1">
						';
						foreach($resultat as  $modules):
						
							$html .= '<tr><td style="background-color:white;height:'.$hauteur_mini.'px;width:205px;"><label style="font-size: 10px;"><b>'.$j.'</b> - '.$modules['nom_module'].'</label></td></tr>';
						
						$j++;
						 endforeach;
						
					
					$html .= '</table>
				</td>
				<td style="vertical-align:middle;text-align: center;">
				<table width="100%" style="border-collapse:collapse;border:0px;" border="1">';
				
						foreach($resultat as  $modules):
						if($modules['questionnaire'] == 'non')
						{
								
							$html.= '<tr><td style="background-color:lightgrey;height:'.$hauteur_mini.'px;width:70px;"><label style="font-size: 10px;"></label></td></tr>';
						}else{
							$html.= '<tr><td style="background-color:white;height:'.$hauteur_mini.'px;width:70px;"><label style="font-size: 10px;">'.number_format($resultatquestion[$o],0).'%</label></td></tr>';
							
						}	$o++;
						 endforeach;
						
					
					$html .= '</table>
				</td>
				<td style="vertical-align:middle;text-align: center;">
					<table width="100%" style="border-collapse:collapse;border:0px;" border="1">'; 
				
						
							foreach($resultat as  $modules):
							if($modules['observation'] == 'non')
							{
							
								$html.= '<tr><td style="background-color:lightgrey;height:'.$hauteur_mini.'px;width:70px;"><label style="font-size: 10px;"></label></td></tr>';
							}else{
							$html.= '<tr><td style="background-color:white;height:'.$hauteur_mini.'px;width:70px;">';
							
							
							if($resultatobservation[$k] >0)
							{
								$html.= '<img src="../public/img/yes6gi.png" width="20" border="0">';
							}else{
								$html.= '<img src="../public/img/no5fg.png" width="18" border="0">';
							}
							
							
							$html.= '</td></tr>';
							$k++;
							}
						 endforeach;
						
					
					$html .= '</table>
				</td>
				<td style="vertical-align:middle;text-align: center;">
					<table width="100%" style="border-collapse:collapse;border:0px;" border="1">';
					
						
						foreach($resultat as  $modules):
						if($modules['entretien'] == 'non')
						{
								
							$html.= '<tr><td style="background-color:lightgrey;height:'.$hauteur_mini.'px;width:70px;"><label style="font-size: 10px;"></label></td></tr>';
						}else{
							$html.= '<tr><td style="background-color:white;height:'.$hauteur_mini.'px;width:70px;">';
					
						if($resultatentretien[$l] > 0)
					 		{
								$html.= '<img src="../public/img/yes6gi.png" width="20" border="0">';
							}else{
								$html.= '<img src="../public/img/no5fg.png" width="18" border="0">';
							}
													
						$html.= '</td></tr>';
							$l++;
						}
						 endforeach;
						
					
					$html .= '</table>
				</td>
				<td style="vertical-align:middle;text-align: center;">
					<table width="100%" style="border-collapse:collapse;border:0px;" border="1">';
					
						foreach($resultat as  $modules):
						if($modules['bilan'] == 'non')
						{
								
							$html.= '<tr><td style="background-color:lightgrey;height:'.$hauteur_mini.'px;width:70px;"><label style="font-size: 10px;"></label></td></tr>';
						}else{
							$html.= '<tr><td style="background-color:white;height:'.$hauteur_mini.'px;width:70px;">';
						
						if($resultatbilan[$m] > 0)
					 		{
								$html.= '<img src="../public/img/yes6gi.png" width="20" border="0">';
							}else{
								$html.= '<img src="../public/img/no5fg.png" width="18" border="0">';
							}
							
						$html.= '</td></tr>';
						
						$total_bian = $total_bian+$resultatbilan[$m];
						
							$m++;
						}
						 endforeach;
						
					
						$html .= '</table>
				</td></tr>';
			
					
						
					}
					$var = $listeModule['numero_unite'];
					
					
					endforeach;

					if($total_bian/$m == 1)
					{
						$text_bilan = 'Valid&eacute;';
						$image_bilan ='<img src="../public/img/yes6gi.png" width="20" border="0">';
					}else{
						$text_bilan = 'Non Valid&eacute;';
						$image_bilan ='<img src="../public/img/no5fg.png" width="18" border="0">';
					}	
					
			
			$html.='
			<tr>
			<td  style="vertical-align:middle;text-align: center;" colspan="7"><b><label style="font-size:14px;">Bilan : '.$image_bilan.' '.$text_bilan.'</label></b>
			</td>
			</tr>
		</table>
				
		';
			
			
			$html.= '<br /><table border="1" cellpadding="0" style="border-width:1px;border-collapse:collapse;" cellspacing="0">
			<tr>
			<td style="width: 180px; background-color:#D3D3D3;height:35px;" >
			<label style="font-size: 10px;">Commentaires /Appr&eacute;ciation g&eacute;n&eacute;rale de syth&egrave;se</label>
			</td>
			<td style="text-align: justify;width: 555px" ><label style="font-size: 10px;">'.$resultatcommentaire.'</label></td>
			</tr>
			<tr>
			<td style="background-color:#D3D3D3;width: 180px;height:40px;">
			<label style="font-size: 10px;">Informations compl&eacute;mentaires</label>
			</td>
			<td style="background-color:#D3D3D3;width: 555px;height:40px;">
			<label style="font-size: 10px;">Expert m&eacute;tier: '.$expertDiplome.'<br />
			Poste occup&eacute; par le candidat :'.$poste.'<br />
			Accompagnateur Greta: '.$referentvae.'<br />
			Anciennet&eacute; dans le poste : '.$anciennete.'<br />
		</label>
			</td>
			</tr>
			</table>';
			
		
			///*
	    require_once('../library/html2pdf_v4.03/html2pdf.class.php');
    try
    {
    	$html2pdf = new HTML2PDF('P', 'A4', 'fr');
      //$html2pdf->setModeDebug();
        $html2pdf->setDefaultFont('Arial');
        $html2pdf->writeHTML($html);
        $html2pdf->Output();
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }
	//*/
		
		
	}

	public function tableaujuryscqpAction(){

		//parametres
		$candidat_metier_id = $this->_request->getParam('candidat_metier_id');
		$passage = $this->_request->getParam('passage');

		///////recup donnees////////
		$mCandidatMetiers = new Model_CandidatMetier();
		$mCandidats = new Model_Candidat();
		$mMetiers = new Model_Metier();
		$mOperations = new Model_Fiche();
		$mResultats = new Model_Resultat();
		$mEntites = new Model_Entite();
		$mBinomes = new Model_Binome();
		$mContacts = new Model_Contact();
		$mOutils = new Model_Outil();

		$fString = new Fonctions_Strings();
		$fDates = new Fonctions_Dates();

		$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);
		$candidat = $mCandidats->get($candidat_metier['candidat_id']);
		$metier = $mMetiers->get($candidat_metier['metier_id']);
		$titre = $mMetiers->getTitre($candidat_metier['metier_id']);
		$resultat = $mResultats->getLast($candidat_metier_id);
		$entreprise = $mEntites->get($candidat['entite_id']);
		$branche = $mEntites->get($entreprise['parent_id']);
		$t = $mOperations->getContactOrgRef($metier['fiche_id']);
		$org_ref = $mEntites->get($t['entite_id']);
		$b_tuteur = $mBinomes->get($candidat_metier['tuteur_id']);
		$tuteur = $mContacts->getPersonne($b_tuteur['contact_id']);
		$b_expert = $mBinomes->get($candidat_metier['expert_id']);
		$expert = $mContacts->getPersonne($b_expert['contact_id']);
		$resultats_outil = $mResultats->getResultatsCandidat($candidat_metier_id);

		$xml = new Fonctions_XmlDemarche($metier['demarche_id'], $metier['metier_xml'], $titre['bloc1_ab'], $titre['bloc2_id']);
		$D = $xml->getDemarche();

		$ventilation = $D['ventilations']->ventilation;

		/**************gestion resultats*******************/
		foreach( $resultats_outil as $ro ){
			$outil = $mOutils->get($ro->outil_id);
			switch( $outil['outil_libelle'] ){
				case 'entretien' :
					$s_resultats[$outil['outil_libelle']]['date'] = $ro->resultat_date;
					$s_resultats[$outil['outil_libelle']]['valeurs'] = $ro->resultat_valeur;
					break;
			}
		}
		/**************************************************/

		$s_titre = $titre['bloc1_lib'];
		$s_nom = ucwords( $candidat['personne_prenom'].' '.$candidat['personne_nom'] );
		$s_entreprise = ucwords($entreprise['entite_nom']);
		$s_branche = ucwords($branche['entite_nom']);
		$s_org_ref = ucwords($org_ref['entite_nom']);
		$s_poste = $candidat['personne_poste'];
		$s_tuteur = ucwords( $tuteur['personne_prenom'].' '.$tuteur['personne_nom'] );
		$s_expert = ucwords( $expert['personne_prenom'].' '.$expert['personne_nom'] );
		$s_commentaires = $resultat['resultat_commentaire_jury'];

		$nb_capacites = $D['capacites']['nb'];

		////////////////////////////

		////////////////////////creation du pdf/////////////////////////////
		$pdf = new Zend_Pdf();
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4_LANDSCAPE);
		$pdf->pages[] = $page;

		//tailles
		$page_height = $page->getHeight();
		$page_width = $page->getWidth();

		//tailles tableau
		$left_tab = 40;
		$right_tab = $page_width-40;
		$top_tab = $page_height-100;
		$bottom_tab = 161;

		//couleurs
		$c_white = new Zend_Pdf_Color_Rgb(255,255,255);
		$c_black = new Zend_Pdf_Color_Rgb(0,0,0);
		$c_gray = new Zend_Pdf_Color_Html('#d3d3d3');
		$c_blue = new Zend_Pdf_Color_Rgb(0,0,255);
		$c_red = new Zend_Pdf_Color_Rgb(255,0,0);
		$c_green = new Zend_Pdf_Color_Rgb(0,128,0);
		$c_purple = new Zend_Pdf_Color_Html('#ba55d3');

		//
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
		$page->setFillColor($c_black);

		///////////////////////////////////////////////////////////////////

		//tableau
		$page->setLineColor($c_purple);
		$page->drawRectangle($left_tab, $bottom_tab, $right_tab, $top_tab, Zend_Pdf_Page::SHAPE_DRAW_STROKE);

		////////header//////
		$bottom_header = $top_tab-64;
		$page->setFillColor($c_gray);
		$page->drawRectangle($left_tab, $bottom_header, $right_tab, $top_tab, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		//title
		$page->drawRectangle($right_tab-200, $bottom_header, $right_tab, $top_tab, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		//passage
		$page->drawRectangle($right_tab-300, $bottom_header, $right_tab-200, $top_tab, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		//titre
		$page->drawRectangle($left_tab, $bottom_header+25, $left_tab+200, $top_tab, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		//org referant
		$page->drawRectangle($left_tab, $bottom_header, $left_tab+200, $bottom_header+25, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		///////////////////

		////////image////////
		$image = Zend_Pdf_Image::imageWithPath('./img/forthac.jpg');
		//image width : 402
		//image height : 251
		$page->drawImage($image, $right_tab-80, $top_tab-50, $right_tab-1, $top_tab-1);
		////////////////////

		/////////header textes//////////
		$page->setFillColor($c_blue);
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
		//title
		$page->drawText('Tableau', $right_tab-170, $bottom_header+50, 'utf-8');
		$page->drawText('récapitulatif', $right_tab-180, $bottom_header+30, 'utf-8');
		$page->drawText('des résultats', $right_tab-180, $bottom_header+10, 'utf-8');
		//titre
		$long_string = 25;
		if(strlen($s_titre) > $long_string ){
			$mots = explode(' ', $s_titre);
			$ligne1=$ligne2='';
			foreach( $mots as $mot ){
				if( strlen( $ligne1.' '.$mot ) <= $long_string ){
					$ligne1.=' '.$mot;
				}else{
					$ligne2.=' '.$mot;
				}
			}
			$page->drawText($ligne1, $left_tab+10, $top_tab-15, "utf-8");
			$page->drawText($ligne2, $left_tab+10, $top_tab-30, "utf-8");
		}else{
			$page->drawText($s_titre, $left_tab+10, $top_tab-10, 'utf-8');
		}
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
		//passage
		if( $resultat['resultat_num_passage'] == 1 ){
			$page->drawText('Positionnement', $right_tab-290, $top_tab-40, 'utf-8');
		}elseif( $resultat['resultat_num_passage'] == 2 ){
			$page->drawText('Evaluation', $right_tab-290, $top_tab-40, 'utf-8');
		}
		//org referent
		$page->drawText('Org. référent :', $left_tab+10, $bottom_header+10, 'utf-8');
		//candidat
		$page->drawText('Candidat :', $left_tab+210, $top_tab-15, 'utf-8');
		//entreprise
		$page->drawText('Entreprise :', $left_tab+210, $top_tab-35, 'utf-8');
		//branche
		$page->drawText('Branche :', $left_tab+210, $top_tab-55, 'utf-8');
		///////////données//////////////
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
		$page->setFillColor($c_black);
		//nom
		$page->drawText($s_nom, $left_tab+280, $top_tab-15, 'utf-8');
		//entreprise
		$page->drawText($s_entreprise, $left_tab+280, $top_tab-35, 'utf-8');
		//branche
		$page->drawText($s_branche, $left_tab+280, $top_tab-55, 'utf-8');
		//org referent
		$page->drawText($fString->reduce($s_org_ref, 25).'...', $left_tab+80, $bottom_header+10, 'utf-8');
		////////////////////////////////

		/////////////////////////1ere colonne//////////////////////////////
		$right_column = $left_tab+150;
		$page->setFillColor($c_gray);
		$page->drawRectangle($left_tab, $bottom_header, $right_column, $bottom_tab, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
		///////////////////////////////////////////////////////////////////

		//////////////////////////////lignes//////////////////////////////
		$page->setFillColor($c_black);
		//bilan
		$page->drawRectangle($left_tab, $bottom_tab+100, $right_tab, $bottom_tab+50, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		//commentaires
		$page->drawRectangle($left_tab, $bottom_tab+50, $right_tab, $bottom_tab, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
		//////////////////////////////////////////////////////////////////

		/////////////////////////textes 1ere colonne//////////////////////////////
		$page->setFillColor($c_blue);
		//capacités
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
		$page->drawText('Capacités', $left_tab+30, $bottom_header-40, 'utf-8');
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
		//bilan
		$page->drawText('Bilan', $left_tab+10, $bottom_tab+80, 'utf-8');
		//commentaires
		$page->drawText('Commentaires', $left_tab+10, $bottom_tab+30, 'utf-8');
		///////////////////////////////////////////////////////////////////

		//////////////////////////données//////////////////////////////////
		$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_ITALIC), 10);
		$page->setFillColor($c_black);
		//commentaires
		$nb_caracteres = 110;
		$mots = explode(' ', $s_commentaires);
		$ligne1=$ligne2=$ligne3='';
		foreach( $mots as $mot ){
			if( strlen( $ligne1.' '.$mot ) <= $nb_caracteres ){
				$ligne1.=' '.$mot;
			}else{
				if( strlen( $ligne2.' '.$mot ) <= $nb_caracteres ){
					$ligne2.=' '.$mot;
				}else{
					$ligne3.=' '.$mot;
				}
			}
		}
		$page->drawText($ligne1, $right_column+10, $bottom_tab+35, "utf-8");
		$page->drawText($ligne2, $right_column+10, $bottom_tab+25, "utf-8");
		$page->drawText($ligne3, $right_column+10, $bottom_tab+15, "utf-8");
		///////////////////////colonnes/////////////////////////////
		$left = $right_column;
		$width = round( ($right_tab-$right_column)/$nb_capacites );
		$i=0;
		foreach( $D['capacites']->capacite as $cap ){
			$num = $cap['nom'];
			$lib = $cap['capacite'];
			$page->setFillColor($c_black);
			$page->drawRectangle($left, $bottom_header, $left+$width, $bottom_tab+50, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
			$page->setFillColor($c_purple);
			$page->drawRectangle($left, $bottom_header-150, $left+$width, $bottom_header-170, Zend_Pdf_Page::SHAPE_DRAW_FILL_AND_STROKE);
			$page->setFillColor($c_black);
			$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);
			$page->drawText($num, ( $left+($width/2) ) - 10, $bottom_header-165, "utf-8");
			$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
			//
			$page->saveGS();
			$page->rotate($left, $bottom_header-150, M_PI_2);
			$nb_caracteres = 25;
			if(strlen($lib) > $nb_caracteres ){
				$mots = explode(' ', $lib);
				$ligne1=$ligne2='';
				$test=0;
				foreach( $mots as $mot ){
					if( strlen( $ligne1.' '.$mot ) <= $nb_caracteres && $test == 0 ){
						$ligne1.=' '.$mot;
					}else{
						$ligne2.=' '.$mot;
						$test++;
					}
				}
				$page->drawText($ligne1, $left+10, $bottom_header-170, "utf-8");
				$page->drawText($ligne2, $left+10, $bottom_header-180, "utf-8");
			}else{
				$page->drawText($lib, $left+10, $bottom_header-180, "utf-8");			}
			$page->restoreGS();
			//
			$bilan = explode( '@', $s_resultats['entretien']['valeurs'] );
			if( $bilan[$i] == 0 ){
				$page->drawText('non', $left+($width/2)-5, $bottom_tab+80, "utf-8");
				$page->drawText('maitrisée', $left+($width/2)-20, $bottom_tab+70, "utf-8");
			}
			if( $bilan[$i] == 1 ){
				$page->drawText('maitrisée', $left+($width/2)-20, $bottom_tab+70, "utf-8");
			}
			if( $bilan[$i] == 100 ){
				$page->drawText('a', $left+($width/2)-5, $bottom_tab+80, "utf-8");
				$page->drawText('compléter', $left+($width/2)-20, $bottom_tab+70, "utf-8");
			}
			
			$left += $width;
			$i++;
		}
		///////////////////////////////////////////////////////////////////

		//////////////////////////rendu du pdf/////////////////////////////
		$pdfdata = $pdf->render();
		header("Content-Disposition: inline; filename=tableau_jurys.pdf");
		header("Content-type: application/pdf");
		echo $pdfdata;

	}
	
	
	public function tableauresultatsccspAction(){
		

		
		exec('C:\wkhtmltopdf\wkhtmltopdf.exe -H "http://localhost/pmqvision4/public/fichepedago/index/?candidat_metier_id=6355&passage=2&module=M3&username=forthac_siege&redirect=oui" "toto.pdf"');
		
		header("Content-Disposition: inline; filename=tableau_jurys.pdf");
		header("Content-type: application/pdf");
		readfile('toto.pdf');
		
		
		
		
		
		
		
		
		
		
	}
	
	
	
	

	public function diplomebrancheAction(){
	
	
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
		
		$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			
			$this->passage = '1';
			
			
			
			$livretacquis_base_aquis = array();
			$note_affiche = array();
			
			if( $this->passage == 1 ) $type = "Positionnement";
			if( $this->passage == 2 ) $type = "Evaluation";
			$title = "Tableau de synthèse des résultats";
			$front = Zend_Controller_Front::getInstance();

			$this->view->headLink()->appendStylesheet($front->getBaseUrl()."/css/tableau_resultats.css");
//			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.rotate.1-1.js','text/javascript');
			$this->view->headScript()->appendFile($this->_request->getBaseUrl().'/js/jquery.mb.flipText.js','text/javascript');

			$candidat_metier_id = $this->candidat_metier_id;

			$title = "Tableau de synthèse des résultats";

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
			$mCandidats = new Model_Candidat();
			$mCivilites = new Model_Civilite();
			$mEntites = new Model_Entite();
			$mOperations = new Model_Fiche();
			$mJurys = new Model_Jury();
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



			
			
			$s_titre = $mMetiers->getTitre($metier['metier_id']);
					$titre = $s_titre['bloc1_lib'];
					if( isset( $s_titre['bloc2_lib'] ) ){
						$titre .= ' - '.$s_titre['bloc2_lib'];
						$specialite = $s_titre['bloc2_id'];
					}else{
						$specialite = null;
					}
					
					
					$codeRNCP = mb_strtoupper($titre_tab['bloc1']['codeRNCP'], 'UTF-8');
					
					$resultat = $mResultats->getLast($candidat_metier_id);
					
					//jury
					$jury = $mJurys->get($resultat['jury_id']);
					//date
					//$date = $fDates->formatDate($jury['jury_date']);
					$date = $fDates->formatDateMoisLettres($jury['jury_date']);
					//lieu
					$lieu = ucwords( $jury['jury_ville'] );
					//branche
					$entite = $mEntites->get($candidat['entite_id']);
					$branche = $mEntites->get($entite['parent_id']);
					$nom_branche = $branche['entite_nom'];
					
					$NSF ='';
					
					if($nom_branche =="textile")
					{
						$NSF = $titre_tab['bloc2']['NSFTextile'];
					}
					
					if($nom_branche =="couture")
					{
						$NSF = $titre_tab['bloc2']['NSFgenerique'];
					}
					
					if($nom_branche =="maroquinerie")
					{
						$NSF = $titre_tab['bloc2']['NSFgenerique'];
					}
					
					if($nom_branche =="habillement")
					{
						$NSF = $titre_tab['bloc2']['NSFHabillement'];
					}
					
					if($nom_branche =="cuirs et peaux")
					{
						$NSF = $titre_tab['bloc2']['NSFCuirsetPeaux'];
					}
					if($nom_branche =="chaussure")
					{
						$NSF = $titre_tab['bloc2']['NSFChaussure'];
					}
					
					if($NSF =='')
					{
						$NSF = $titre_tab['bloc2']['NSFgenerique'];
					}
						

					$candidat = $mCandidats->get( $candidat_metier['candidat_id'] );
					

					$nb_heures = "Nombre d'heures r&eacute;alis&eacute;s&nbsp;:&nbsp;".$candidat_metier['candidat_metier_formation_duree_realisee']."h";					


					$civ = $mCivilites->get( $candidat['civilite_id'] );
					if( $civ->civilite_abrege != 'nc' ) $s_nom = $civ->civilite_libelle;
					else $s_nom = "";
					$s_nom .= ' '.$candidat['personne_prenom'];
					$s_nom .= ' '.$candidat['personne_nom'];
					$nom = ucwords( $s_nom );

					$entreprise = $mEntites->get($candidat['entite_id']);
					$nb = 45;
					if( strlen( $entreprise['entite_nom'] ) > $nb ) $entreprise = ucwords( $fStrings->reduce( $entreprise['entite_nom'], $nb ) ) . '...';
					else $entreprise = ucwords( $entreprise['entite_nom'] );

					$branche = $mEntites->get( $candidat['parent_id'] );
					$nb = 45;
					if( strlen( $branche['entite_nom'] ) > $nb ) $branche = ucwords( $fStrings->reduce( $branche['entite_nom'], $nb ) ) . '...';
					else $branche = ucwords( $branche['entite_nom'] );

					$corg_ref = $mOperations->getContactOrgRef($metier['fiche_id']);
					$org_ref = $mEntites->get( $corg_ref['entite_id'] );
					$nb = 45;
					if( strlen( $org_ref['entite_nom'] ) > $nb ) $organisme_referent = ucwords( $fStrings->reduce( $org_ref['entite_nom'], $nb ) ) . '...';
					else $organisme_referent = ucwords( $org_ref['entite_nom'] );

					$xmldemarche = new Fonctions_XmlDemarche($demarche['demarche_id'], $metier['metier_xml'], $s_titre['bloc1_ab'], $specialite,'normal');

					$demarch = $xmldemarche->getDemarche();
					$capacites = $demarch['capacites_base'];
					$nb_capacites = count($capacites);
					//$ventilations = $demarch['ventilations'];

					
		
		$resultats = $mResultats->getResultats($candidat_metier_id, $this->passage );
			
		foreach( $resultats as $resultat ){
			$outil = $mOutils->get( $resultat->outil_id );
			switch( $outil['outil_libelle'] ){
				case 'debutFormationBanche' :
					$resultatIdDebutFormationBranche = $resultat->resultat_outil_id;
					$date_debut_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
				case 'finFormationBanche' :
					$resultatIdFinFormationBranche = $resultat->resultat_outil_id;
					$ate_fin_formation_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					break;
				case 'entretienNoteBanche' :
					$resultatIdentretiensBranche = $resultat->resultat_outil_id;
					$date_entretien_branche = Fonctions_Dates::formatDate($resultat->resultat_date);
					$resultEnt = explode('@',$resultat->resultat_valeur);
					foreach($resultEnt as $idx => $entNote){
						$getNote = explode('_', $entNote);
						$ent['notes'][$idx] = $getNote[0];
					}
				case 'entretienCommentaireBanche' :
					$resultatIdentretiensCommentaireBranche = $resultat->resultat_outil_id;
					$resultEnt = explode('@£$@',$resultat->resultat_valeur);
					foreach($resultEnt as $idx => $entCommentaire){
						$ent['commentaire'][$idx] = $entCommentaire;
					}
		
					$entretienBranche = $ent;
		
					break;
			}
		}
		
;
			
		
		if($candidat_metier['etat_id'] =='4')
		{
			$etat = 'Admissible';
		}
		
		if($candidat_metier['etat_id'] =='2')
		{
			$etat = 'En formation';
		}
		if($candidat_metier['etat_id'] =='13')
		{
			$etat = 'En Entretien';
		}
		if($candidat_metier['etat_id'] =='6')
		{
			$etat = 'En Abandon';
		}
		if($candidat_metier['etat_id'] =='10')
		{
			$etat = 'Inscrit';
		}
		if($candidat_metier['etat_id'] =='5')
		{
			$etat = 'Certifié';
		}
		
		if($candidat_metier['etat_id'] =='14')
		{
			$etat = 'En cours d\'acquisition';
		}
		
			
		$commentaire = $candidat_metier['candidat_metier_formation_remarque'];
		$commentaire2 = $candidat_metier['candidat_metier_formation_remarque2'];
		if( $candidat_metier['tuteur_id'] > 0 ){
			$binome = $mBinomes->get( $candidat_metier['tuteur_id'] );
			$evaluateur = $mContacts->getPersonne( $binome['contact_id'] );
			$civ = $mCivilites->get( $evaluateur['civilite_id'] );
			if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
			$s .= $evaluateur['personne_prenom'].' '.$evaluateur['personne_nom'];
			$evaluateur = ucwords($s);
		}else{
			$evaluateur = '';
		}
		if( $candidat_metier['expert_id'] > 0 ){
			$binome = $mBinomes->get( $candidat_metier['expert_id'] );
			$expert = $mContacts->getPersonne( $binome['contact_id'] );
			$civ = $mCivilites->get( $expert['civilite_id'] );
			if( $civ->civilite_abrege == 'nc') $s = ""; else $s = $civ->civilite_abrege.' ';
			$s .= $expert['personne_prenom'].' '.$expert['personne_nom'];
			$expert = ucwords($s);
		}else{
			$expert = '';
		}
			
		$expert = '';
		$contacts = $mMetiers->getExperts($metier['metier_id']);
			
		foreach( $contacts as $contact ){
			$expert.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$expert = $expert;
			
		$binomes = '';
		$contacts = $mMetiers->getEvaluateurs($metier['metier_id']);
			
		foreach( $contacts as $contact ){
			$binomes.= ucwords( $contact['personne_prenom'].' '.$contact['personne_nom'].' ( '.$contact['entite_nom'] ).' ) ';
		}
			
		$evaluateur = $binomes;
			
			
		$poste = $candidat['personne_poste'];	
		//branche
		$entite = $mEntites->get($candidat['entite_id']);
		$branche = $mEntites->get($entite['parent_id']);
		$nom_branche = $branche['entite_nom'];
	
		//$html=ob_get_contents();
		ob_end_clean();
		$hauteur_mini = number_format((500/(count($capacites))),0);
		$hauteur_maxi  = ($hauteur_mini*(count($capacites)));
	
	$html='';
	$html2 = '';


$hauteur_mini = number_format((640/(count($capacites))),0);
 $hauteur_maxi  = ($hauteur_mini*(count($capacites)));

 $titre = str_replace('CQP Branche -','CQP ',$titre);
 $titre = str_replace('- Transverse','',$titre);
 
 
$html.= '<div style="background-image: url(../public/img/CQP-Recto2.jpg);width:1055px;height:735px;margin-left:10px;margin-top:8px" >
		
			<div style="margin-top:150px;margin-left:60px;text-align:center;font-size:25px">
				'.$titre.'
			</div>
			<div style="margin-top:68px;text-align:left;font-size:20px;margin-left:265px">
				'.$nom.'
			</div>
						<div style="margin-top:75px;text-align:left;font-size:16px;margin-left:480px">
				'.$date.'
			</div>
						

			<div style="margin-top:69px;text-align:left;font-size:16px;margin-left:230px;width:300px">

						<table border="0">
							<tr>
								<td style="width:290px">'.$lieu.'</td>
								<td style="width:200px">Le '.$date.'</td>
							</tr>
						</table>
						
			</div>		
			<div  style="margin-top:210px;text-align:left;font-size:14px;margin-left:22px;color:#ffffff;text-align:center">
										<table border="0">
										<tr>
										<td style="width:120px">
										'.strtoupper($nom_branche).'
										</td>
										</tr>
										</table>
			</div>		
		</div>

		';

$html.= '<div style="background-image: url(../public/img/CQP-verso2.jpg);width:1055px;height:735px;margin-left:10px;margin-top:8px" >

			<div style="margin-top:160px;text-align:center;font-size:25px">
				
			</div>



			<div style="margin-top:30px;text-align:left;font-size:16px;margin-left:100px;width:300px">

						<table border="0">';
						
						$i=0;
foreach( $capacites as $capacite ):

if($entretienBranche["notes"][$i] == 1) $note = 'Acquise';
if($entretienBranche["notes"][$i] == 0) $note = 'En cours d\'acquisition';
$html.= '<tr>
								<td style="width:25px;"><img src="../public/img/fleche.jpg" alt="PMQ" style="width:25px;height:25px;" ></td>
								<td style="width:830px;padding:5px">'.$capacite['nom'].' - '.$capacite['capacite'].' : <b>'.$note.'</b></td>
							</tr>';
	
$i++;
endforeach;
						
$html.= '							
						</table>

			</div>
		




		</div>
		';





		// convert in PDF
		require_once('../library/html2pdf_v4.03/html2pdf.class.php');
		try
		{
			$html2pdf = new HTML2PDF('L', 'A4', 'fr');
			//$html2pdf->setModeDebug();
			$html2pdf->setDefaultFont('Arial');
			$html2pdf->writeHTML($html);
			$html2pdf->Output();
		}
		catch(HTML2PDF_exception $e) {
			echo $e;
			exit;
		}
	
	
	
	
	
	
	}
	
	
	

}