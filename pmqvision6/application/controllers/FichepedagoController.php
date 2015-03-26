<?php
	class FichepedagoController extends Zend_Controller_Action {

		function init(){

			Zend_Layout::getMvcInstance()->setLayout('empty');

			$this->candidat_metier_id = $this->_request->getParam('candidat_metier_id');
			$this->capacite_id = $this->_request->getParam('capacite_id');
			$this->module_id = $this->_request->getParam('module_id');
			$this->passage = $this->_request->getParam('passage');
			$this->view->passage = $this->_request->getParam('passage');

		}

		public function indexAction(){

			$front = Zend_Controller_Front::getInstance();
			$this->view->headLink()->appendStylesheet($front->getBaseUrl()."/css/fichepedago.css");

			$this->view->returnlink = "/tableauresultats/index/?id=".$this->candidat_metier_id."&passage=".$this->passage;
			$this->view->returnlib = "Retour au tab. de r&eacute;s.";

			$candidat_metier_id = $this->candidat_metier_id;
			$this->view->candidat_metier_id = $candidat_metier_id;
			$capacite_id = $this->capacite_id;
			$module_id = $this->module_id;

			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
			$mDemarches = new Model_Demarche();
			$mResultats = new Model_Resultat();
			$mOutils = new Model_Outil();

			$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);

			$metier = $mMetiers->get($candidat_metier['metier_id']);
			$titre = $mMetiers->getTitre($candidat_metier['metier_id']);

			$demarche = $mDemarches->get( $metier['demarche_id'] );
			$this->view->demarche = $demarche['demarche_abrege'];

			$XML = new Fonctions_XmlDemarche($metier['demarche_id'], $metier['metier_xml'], $titre['bloc1_ab'], $metier['bloc2_id']);
			$DEM = $XML->getDemarche();

			switch( $demarche['demarche_abrege'] ){

				case 'cqp' :
					$capacites = $DEM['capacites_base'];

					
				$capacites_base = array();	
			 $query_capacite_debut = "SELECT id_titres FROM pmqvision5.capacites WHERE `id_capacite` ='".$capacite_id ."'";
			$result_capacite_debut = mysql_query($query_capacite_debut);
			while($row_capacite_debut = mysql_fetch_array($result_capacite_debut))
					{	
					$id_titres= $row_capacite_debut["id_titres"];
					
					
							
						$query_capacite_num = "SELECT * FROM pmqvision5.capacites LEFT JOIN pmqvision5.classement_capacite 
						ON capacites.nom_capacite =classement_capacite.nom_capacite  WHERE  (`id_titres` ='".$id_titres."' AND capacites.nom_capacite NOT LIKE 'C999')
						GROUP BY ordre_general 
						ORDER BY  ordre_general ";
						$result_capacite_test = mysql_query($query_capacite_num);				
						$num_total =   @mysql_num_rows($result_capacite_test);
										
					
					
					$query_capacite1 = "SELECT * FROM pmqvision5.capacites 
					LEFT JOIN pmqvision5.classement_capacite 
					ON capacites.nom_capacite =classement_capacite.nom_capacite 
					WHERE 
					`id_titres` ='".$id_titres."' 
					ORDER BY  ordre_general "; 
					$result_capacite1 = mysql_query($query_capacite1);
					$ns = "";
					while($row_capacite1 = mysql_fetch_array($result_capacite1))
								{

						$nb_capacites = count($capacites);

						if( $row_capacite1['id_capacite'] == $capacite_id ){
							
							$capacites_base_nom = $row_capacite1["nom_capacite"];
							$capacites_base_titres_id = $row_capacite1["id_titres"];
							$capacites_base_id = $row_capacite1["id_capacite"];
							$capacites_base_texte = utf8_encode($row_capacite1["intitule_capacite"]);
							$capacite = $capacites;				
							$num_capacite = $row_capacite1["ordre_general"] ;
						
						
							$query_capacite3 = "SELECT * FROM pmqvision5.capacites 
							WHERE `id_titres` ='".$capacites_base_titres_id."' 
							AND `nom_capacite` ='".$capacites_base_nom."' 
							AND (`outils` LIKE 'mesurable' OR `outils` LIKE 'observable') 
							ORDER BY  position  ";
							$result_capacite3 = mysql_query($query_capacite3);
							$compte_capacite3=0;	
							while($row_capacite3 = mysql_fetch_array($result_capacite3))
												{
													$capacites_base[$compte_capacite3]['nom'] = $row_capacite3["nom_capacite"];
													$capacites_base[$compte_capacite3]['num'] = $row_capacite3["id_capacite"];
													$capacites_base[$compte_capacite3]['outils'] = $row_capacite3["outils"];
													$capacites_base[$compte_capacite3]['capacite'] = $row_capacite3["intitule_capacite"];
													$capacites_base[$compte_capacite3]['intitule_question'] = $row_capacite3["intitule_question"];
													$capacites_base[$compte_capacite3]['texte'] = nl2br(utf8_encode($row_capacite3["intitule_question"]));
													$compte_capacite3++;
												}
				
		
									$nb_items = $compte_capacite3;
									$i=0;
									while( $i != $nb_items ){
										$ns .= '0_';
										$i++;
									}
								} $ns.= '@';
							}
					}
							$this->view->capacite_active = $nb_items;
							$this->view->capacite = $capacites_base;
					
					
							$query_capacite2 = "SELECT * FROM pmqvision5.capacites 
							WHERE `id_titres` ='".$capacites_base_titres_id."' 
							AND `nom_capacite` ='".$capacites_base_nom."' 
							AND (`outils` LIKE 'observable-mesurable') 
							ORDER BY  position  ";
							$result_capacite2 = mysql_query($query_capacite2);
							$compte_capacite2=0;	
							$capacites_base_text = array();
							while($row_capacite2 = mysql_fetch_array($result_capacite2))
												{
													$capacites_base_text[$compte_capacite2]['nom'] = $row_capacite2["nom_capacite"];
													$capacites_base_text[$compte_capacite2]['num'] = $row_capacite2["id_capacite"];
													$capacites_base_text[$compte_capacite2]['outils'] = $row_capacite2["outils"];
													$capacites_base_text[$compte_capacite2]['capacite'] = $row_capacite2["intitule_capacite"];
													$capacites_base_text[$compte_capacite2]['intitule_question'] = $row_capacite2["intitule_question"];
													$capacites_base_text[$compte_capacite2]['texte'] = nl2br(utf8_encode($row_capacite2["intitule_question"]));
													$compte_capacite2++;
												}
							
							$this->view->capacite_definition = $capacites_base_text;
							$this->view->id = $capacites_base_id;
		
							$this->view->title = 'Capacit&eacute; '.$capacites_base_nom.' : '.$capacites_base_texte;
		
							$this->view->resultat_outil_id = 0;
		
							$resultats = $mResultats->getResultats($candidat_metier_id,$this->passage);
							$r = $mResultats->getLast($candidat_metier_id);
							$this->view->resultat_id = $r['resultat_id'];
							foreach( $resultats as $resultat ){
								$outil = $mOutils->get($resultat->outil_id);
								if( $outil['outil_libelle'] == 'capacite' ){
									$ns = $resultat->resultat_valeur;
									$this->view->resultat_outil_id = $resultat->resultat_outil_id;
								}
							}

							$temp = explode( '@', $ns );
							if($num_capacite >0)
							{
							$this->view->notes = explode( '_', $temp[$num_capacite-1]);
							}
		
							break;

					
					
					
					
				case 'ccsp' :
			$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);
			$demarche = $mCandidatMetiers->getDemarche($candidat_metier_id);

			$metier = $mMetiers->get( $candidat_metier['metier_id'] );
			
			
		$resultat = $mResultats->getLast($candidat_metier_id);
		$this->view->resultat_id = $resultat['resultat_id'];
		
			$resultats = $mResultats->getResultatsCandidat($candidat_metier_id);
			//si resultats existent
			
				
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
							}
						
							
										
			 	$query_module = "SELECT * FROM pmqvision5.modules WHERE notation ='oui'";
			$result_module = mysql_query($query_module);
			$compte_savoir=0;	
					while($row_module = mysql_fetch_array($result_module))
						{
						$query_savoir = "SELECT * FROM pmqvision5.savoirs WHERE (code_module LIKE '".$row_module["code"]."' AND support_eval = 'observation' ) OR (code_module LIKE '".$row_module["code"]."' AND support_eval = 'question') ORDER BY code";
							$result_savoir = mysql_query($query_savoir);
								while($row_savoirs = mysql_fetch_array($result_savoir))
								{
									$listeQuestions[$compte_savoir]['name'] = $row_savoirs['code'].utf8_encode($row_savoirs['code_module']).' - '.utf8_encode($row_savoirs['libelle_savoir']);
									$listeQuestions[$compte_savoir]['value'] = $row_savoirs['note'];
									$listeQuestions[$compte_savoir]['module'] = $row_savoirs['code_module'];
									$listeQuestions[$compte_savoir]['nom_module'] = $row_module['nom'];
									$listeQuestions[$compte_savoir]['support_eval'] = $row_savoirs['support_eval'];
									$listeQuestions[$compte_savoir]['libelle_module'] = utf8_encode($row_module['libelle_module']);
									$compte_savoir++;
								}
						}
	        $query_module = "SELECT * FROM pmqvision5.modules WHERE notation ='oui'";
			$result_module = mysql_query($query_module);
			$compte_savoir=0;	
					while($row_module = mysql_fetch_array($result_module))
						{
							$query_savoir = "SELECT * FROM pmqvision5.savoirs WHERE (code_module LIKE '".$row_module["code"]."' AND support_eval = 'observation' ) OR (code_module LIKE '".$row_module["code"]."' AND support_eval = 'question') ORDER BY code";
							$result_savoir = mysql_query($query_savoir);
								while($row_savoirs = mysql_fetch_array($result_savoir))
								{
									$listeObservations[$compte_savoir]['name'] = $row_savoirs['code'].utf8_encode($row_savoirs['code_module']).' - '.utf8_encode($row_savoirs['libelle_savoir']);
									$listeObservations[$compte_savoir]['value'] = $row_savoirs['note'];
									$listeObservations[$compte_savoir]['module'] = $row_savoirs['code_module'];
									$listeObservations[$compte_savoir]['nom_module'] = $row_module['nom'];
									$listeObservations[$compte_savoir]['support_eval'] = $row_savoirs['support_eval'];
									$listeObservations[$compte_savoir]['libelle_module'] = utf8_encode($row_module['libelle_module']);
									$compte_savoir++;
								}
						}
		
						
	        $query_module = "SELECT * FROM pmqvision5.modules WHERE notation ='oui'";
			$result_module = mysql_query($query_module);
			$compte_savoir=0;	
					while($row_module = mysql_fetch_array($result_module))
						{
							$query_savoir = "SELECT * FROM pmqvision5.savoirs WHERE (code_module LIKE '".$row_module["code"]."' AND support_eval = 'observation' ) OR (code_module LIKE '".$row_module["code"]."' AND support_eval = 'question') ORDER BY code";
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
						
		/*
		foreach( $temp as $index => $t ){
			$question = explode( ':', $t );
			$listeQuestions[$index]['name'] = 'test';
			$listeQuestions[$index]['value'] = $question[1];
		}
		*/

							
			$this->view->listeQuestionaire = $listeQuestions;
			$this->view->listeObservation = $listeObservations;	
			$this->view->listeEntretien = $listeEntretiens;	
				
			}

					break;
				case 'diplome' :

					break;
			}

		}

		public function updateAction(){

			$id = $_POST['id'];
			$resultat_outil_id = $_POST['resultat_outil_id'];
			$resultat_id = $_POST['resultat_id'];
			$nombre = $_POST['nombre'];
			$candidat_metier_id = $_POST['candidat_metier_id'];
			$passage = $_POST['passage'];

			$mResultatOutils = new Model_ResultatOutil();
			$mCandidatMetiers = new Model_CandidatMetier();
			$mMetiers = new Model_Metier();
			$mOutils = new Model_Outil();

				$query_capacite1 = "SELECT nom_capacite FROM pmqvision5.capacites WHERE LEFT JOIN pmqvision5.classement_capacite 
						ON capacites.nom_capacite =classement_capacite.nom_capacite  `id_capacite` ='".$id ."'"; 
				$result_capacite1 = mysql_query($query_capacite1);
				while($row_capacite1 = mysql_fetch_array($result_capacite1))
								{

									$num_capacite = $row_capacite1["ordre_general"];
								}
			
			if( isset( $_POST['capacite'] ) ){

				$new_valeurs = $_POST['capacite'];
				
				$i=0;
				while( $i < $nombre ){
					if( !isset( $new_valeurs[$i] ) ) $news[$i] = 0; else $news[$i] = 1;
					$i++;
				}

				$new = implode('_', $news);

				if( $resultat_outil_id > 0 ){
					//modif
					$resultat = $mResultatOutils->get($resultat_outil_id);
					$valeurs = $resultat['resultat_valeur'];

					$valeurs_tab = explode( '@', $valeurs );

					$i=1;
					foreach( $valeurs_tab as &$valeur ){
						if( $i == $num_capacite ){
							$valeur = $new;
						}
						$i++;
					}


					$nouveau = implode( '@', $valeurs_tab );

					$mResultatOutils->modif($resultat_outil_id, $nouveau, '0000-00-00');
				}else{
					//creation

					$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);

					$metier = $mMetiers->get($candidat_metier['metier_id']);
					$titre = $mMetiers->getTitre($candidat_metier['metier_id']);

					$XML = new Fonctions_XmlDemarche($metier['demarche_id'], $metier['metier_xml'], $titre['bloc1_ab'], $metier['bloc2_id']);
					$DEM = $XML->getDemarche();
					$capacites = $DEM['capacites_base'];
										
					$nb_capacites = count($capacites);

					$i=0;
					$valeurs = "";
					while( $i != $nb_capacites ){
						$valeurs .= '@';
						$i++;
					}

					$valeurs_tab = explode( '@', $valeurs );

					$i=1;
					foreach( $valeurs_tab as &$valeur ){
						if( $i == $num_capacite ){
							$valeur = $new;
						}
						$i++;
					}

					$nouveau = implode( '@', $valeurs_tab );

					$outil = $mOutils->fetchRow(" outil_libelle LIKE 'capacite' AND demarche_id = ".$metier['demarche_id']." ");

					$mResultatOutils->add($outil['outil_id'], $resultat_id, $nouveau, '0000-00-00');

				}

				$this->_redirect( '/fichepedago/index/?candidat_metier_id='.$candidat_metier_id.'&capacite_id='.$id.'&passage='.$passage );

			}elseif( isset( $_POST['savoirs'] ) ){

				$new_valeurs = $_POST['savoirs'];

				$i=0;
				while( $i < $nombre ){
					if( !isset( $new_valeurs[$i] ) ) $news[$i] = 0; else $news[$i] = 1;
					$i++;
				}

				$new = implode('_', $news);

				if( $resultat_outil_id > 0 ){

					$resultat = $mResultatOutils->get($resultat_outil_id);
					$valeurs = $resultat['resultat_valeur'];

					$valeurs_tab = explode( '@', $valeurs );

					$i=1;
					foreach( $valeurs_tab as &$valeur ){
						if( $i == $id ){
							$valeur = $new;
						}
						$i++;
					}

					$nouveau = implode( '@', $valeurs_tab );

					$mResultatOutils->modif($resultat_outil_id, $nouveau, '0000-00-00');

				}else{

					$candidat_metier = $mCandidatMetiers->get($candidat_metier_id);

					$metier = $mMetiers->get($candidat_metier['metier_id']);
					$titre = $mMetiers->getTitre($candidat_metier['metier_id']);

					$XML = new Fonctions_XmlDemarche($metier['demarche_id'], $metier['metier_xml'], $titre['bloc1_ab'], $metier['bloc2_id']);
					$DEM = $XML->getDemarche();
					$capacites = $DEM['capacites_base'];
										
					$nb_capacites = count($capacites);

					$i=0;
					$valeurs = "";
					while( $i != $nb_modules ){
						$valeurs .= '@';
						$i++;
					}

					$valeurs_tab = explode( '@', $valeurs );

					$i=1;
					foreach( $valeurs_tab as &$valeur ){
						if( $i == $id ){
							$valeur = $new;
						}
						$i++;
					}

					$nouveau = implode( '@', $valeurs_tab );

					$outil = $mOutils->fetchRow(" outil_libelle LIKE 'module' AND demarche_id = ".$metier['demarche_id']." ");

					$mResultatOutils->add($outil['outil_id'], $resultat_id, $nouveau, '0000-00-00');

				}

				$this->_redirect( '/fichepedago/index/?candidat_metier_id='.$candidat_metier_id.'&module_id='.$id.'&passage='.$passage );

			}

		}

	}
