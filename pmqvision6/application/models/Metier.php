<?php

	class Model_Metier extends Zend_Db_Table_Abstract{
		
		protected $_name = 'metier';
		protected $_primary = 'metier_id';
		protected $_dependentTables = array(
			'Model_Binome',
			'Model_CandidatMetier'
		);
		protected $_referenceMap = array(
	        'fiche'	=> array(
	            'columns'		=> 'fiche_id',
	            'refTableClass' => 'Model_Fiche',
				'refColumns'	=> 'fiche_id'
	        ),
	        'demarche'	=> array(
	            'columns'		=> 'demarche_id',
	            'refTableClass' => 'Model_Demarche',
				'refColumns'	=> 'demarche_id'
	        )
	    );
		
		public function get( $metier_id = 0 ){
			
			if( $metier_id > 0 ){
				return $this->find($metier_id)->current();	
			}
			
		}

		public function getFicheMetier($metier_id){
			
			$reqFiche = $this->select()->setIntegrityCheck(false);
			$reqFiche->from('metier', 'fiche_id');
			$reqFiche->where('metier_id = ?', $metier_id);
			
			return $reqFiche->query()->fetch(Zend_Db::FETCH_OBJ);
		}

		public function getTitre( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`metier` AS m,
						`demarche` AS d
					WHERE
						m.demarche_id = d.demarche_id
						AND m.metier_id = $metier_id
				";
				$stmt = $this->_db->query( $sql );
				$resultat = $stmt->fetch();

				if( strlen($resultat['metier_xml']) < 2 ) $resultat['metier_xml'] = '0'.$resultat['metier_xml'];

				$file_name = strtoupper( $resultat['demarche_abrege'] ).'_'.$resultat['metier_xml'].'.xml';
				if (file_exists( "./xml/$file_name" )) {
					$xml = simplexml_load_file("./xml/$file_name")->$resultat['demarche_abrege'];
					foreach( $xml as $bloc1 ){
						if( $resultat['bloc1_id'] == $bloc1['num'] ){
							$resultat['bloc1_lib'] = $bloc1['nom'];
							$resultat['codeRNCP'] = $bloc1['codeRNCP'];
							$resultat['bloc1_ab'] = $bloc1['abrege'];
							$resultat['titre'] = $bloc1['titre'];
							$resultat['niveaux'] = $bloc1['niveaux'];
							$specialites = $bloc1->specialites;
							if( isset( $specialites->specialite ) ){
								foreach( $specialites->specialite as $specialite ){
									if( $specialite['num'] == $resultat['bloc2_id'] ){
										$resultat['bloc2_lib'] = $specialite['nom'];
										$resultat['NSF'] = $bloc1['NSF'];
									}
								}
							}
						}
					}

				} else {
					exit("Echec lors de l'ouverture du fichier $file_name.");
				}
				return $resultat;
			}

		}

		public function getBinomes( $metier_id = 0 ){

			if( $metier_id > 0 ){

				echo $sql = "
					SELECT
						*
					FROM
						`binome` AS b
					WHERE
						b.metier_id = $metier_id
				";
				return $binomes = $this->_db->query($sql)->fetchAll();
			}

		}

		public function getExperts( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
					  *
					FROM
					  `binome` AS b,
					  `contact` AS c,
					  `fonction_contact` AS fc,
					  `fonction` AS f,
					  `personne` AS p,
					  `entite` AS e
					WHERE
					  b.contact_id = c.contact_id
					  AND fc.contact_id = c.contact_id
					  AND f.fonction_id = fc.fonction_id
					  AND c.personne_id = p.personne_id
					  AND p.entite_id = e.entite_id
					  AND b.metier_id = $metier_id
					  AND f.fonction_libelle = 'expert métier'
				";
				return $experts = $this->_db->query($sql)->fetchAll();
			}

		}
		public function getExperts2( $metier_id = 0 ){

			if( $metier_id > 0 ){
				$req = $this->select()->setIntegrityCheck(false);
				$req->from('binome AS b0',array());	
				
				$req->joinLeft('contact AS c0','b0.contact_id=c0.contact_id',array());
				$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id');
				$req->joinLeft('metier AS m0','m0.metier_id=b0.metier_id',array());
				$req->joinLeft('fonction_contact AS fc0','fc0.contact_id = c0.contact_id',array());
				$req->joinLeft('fonction AS f0','f0.fonction_id = fc0.fonction_id',array());
				
				$req->group('p0.personne_id');
				$req->where('b0.metier_id = ?', $metier_id);
				$req->where('f0.fonction_libelle = ?', 'expert métier');
				
				
				$query = $req->query();

				return $query->fetchAll();
			}

		}
		public function getEvaluateurs2( $metier_id = 0 ){

			if( $metier_id > 0 ){
				$req = $this->select()->setIntegrityCheck(false);
				$req->from('binome AS b0',array());	
				
				$req->joinLeft('contact AS c0','b0.contact_id=c0.contact_id',array());
				$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id');
				$req->joinLeft('metier AS m0','m0.metier_id=b0.metier_id',array());
				$req->joinLeft('fonction_contact AS fc0','fc0.contact_id = c0.contact_id',array());
				$req->joinLeft('fonction AS f0','f0.fonction_id = fc0.fonction_id',array());
				
				$req->group('p0.personne_id');
				$req->where('b0.metier_id = ?', $metier_id);
				$req->where('f0.fonction_libelle = ?', 'référent évaluateur');
				
				
				$query = $req->query();

				return $query->fetchAll();
			}

		}
		
	    public function getReferentEntreprise( $metier_id = 0 ){

			if( $metier_id > 0 ){
				$req = $this->select()->setIntegrityCheck(false);
				$req->from('binome AS b0',array());	
				
				$req->joinLeft('contact AS c0','b0.contact_id=c0.contact_id',array());
				$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id');
				$req->joinLeft('metier AS m0','m0.metier_id=b0.metier_id',array());
				$req->joinLeft('fonction_contact AS fc0','fc0.contact_id = c0.contact_id',array());
				$req->joinLeft('fonction AS f0','f0.fonction_id = fc0.fonction_id',array());
				
				$req->group('p0.personne_id');
				$req->where('b0.metier_id = ?', $metier_id);
				$req->where('f0.fonction_libelle = ?', 'référent acompagnateur');
				
				
				$query = $req->query();

				return $query->fetchAll();
			}

		}
		

		public function getEvaluateurs( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
					  *
					FROM
					  `binome` AS b,
					  `contact` AS c,
					  `fonction_contact` AS fc,
					  `fonction` AS f,
					  `personne` AS p,
					  `entite` AS e
					WHERE
					  b.contact_id = c.contact_id
					  AND fc.contact_id = c.contact_id
					  AND f.fonction_id = fc.fonction_id
					  AND c.personne_id = p.personne_id
					  AND p.entite_id = e.entite_id
					  AND b.metier_id = $metier_id
					  AND (f.fonction_libelle = 'référent évaluateur' OR f.fonction_libelle = 'référent acompagnateur' )
				";
				return $evaluateurs = $this->_db->query($sql)->fetchAll();
			}

		}

		public function getContactsSameExpertise( $metier_id = 0, $fonction = '' ){

			if( $metier_id > 0 ){

				$demarche = $this->getDemarche($metier_id);
				$demarche_id = $demarche['demarche_id'];

				$bloc1_id = $this->getBloc1ID($metier_id);
				if( $bloc1_id != null ) $w_bloc1 = " AND ept.bloc1_id = $bloc1_id ";
				else $w_bloc1 = " AND ept.bloc1_id is null ";

				/*$bloc2_id = $this->getBloc2ID($metier_id);
				if( $bloc2_id != null && $bloc2_id != 0 ) $w_bloc2 = " AND ept.bloc2_id = $bloc2_id ";
				else $w_bloc2 = " AND ept.bloc2_id is null ";*/

				if( $fonction != '' ) $w_fonction = " AND f.fonction_libelle = '$fonction' ";
				else $w_fonction = "";

				$sql = "
					SELECT
					  *
					FROM
					  `expertise` AS ept,
					  `contact` AS c,
					  `personne` AS p,
					  `entite` AS ett,
					  `fonction_contact` AS fc,
					  `fonction` AS f
					WHERE
					  ept.contact_id = c.contact_id
					  AND c.personne_id = p.personne_id
					  AND p.entite_id = ett.entite_id
					  AND fc.contact_id = c.contact_id
					  AND p.visible = 'oui'
					  AND fc.fonction_id = f.fonction_id
					  $w_bloc1
					  $w_fonction
					ORDER BY
						ett.entite_nom,
						p.personne_nom
				";
				return $contacts = $this->_db->query( $sql )->fetchAll();
			}

		}
		
		public function getOrganisme($metier_id = 0 ){

			if( $metier_id > 0 ){
				$sql = "
						SELECT
					  *
					FROM
					  `orga_spe` AS b,
					  `contact` AS c,
					  `fonction_contact` AS fc,
					  `fonction` AS f,
					  `personne` AS p,
					  `entite` AS e
					WHERE
					  b.contact_id = c.contact_id
					  AND fc.contact_id = c.contact_id
					  AND f.fonction_id = fc.fonction_id
					  AND c.personne_id = p.personne_id
					  AND p.entite_id = e.entite_id
					  AND b.metier_id = $metier_id
					  AND f.fonction_id = '16'				
				";
				return $contacts = $this->_db->query( $sql )->fetchAll();
			}

		}
		
		
		public function getReferents($metier_id = 0 ){
		
			if( $metier_id > 0 ){
				$sql = "
				SELECT
				*
				FROM
				`referentsvae` AS b,
				`contact` AS c,
				`fonction_contact` AS fc,
				`fonction` AS f,
				`personne` AS p,
				`entite` AS e
				WHERE
				b.contact_id = c.contact_id
				AND fc.contact_id = c.contact_id
				AND f.fonction_id = fc.fonction_id
				AND c.personne_id = p.personne_id
				AND p.entite_id = e.entite_id
				AND b.metier_id = $metier_id
				AND f.fonction_id = '18'
				";
				return $contacts = $this->_db->query( $sql )->fetchAll();
			}
		
			}
		
		
		public function getContactsSameOrganisme( $metier_id = 0, $fonction = '' ){

			if( $metier_id > 0 ){

				$demarche = $this->getDemarche($metier_id);
				$demarche_id = $demarche['demarche_id'];

				$bloc1_id = $this->getBloc1ID($metier_id);
				if( $bloc1_id != null ) $w_bloc1 = " AND ept.bloc1_id = $bloc1_id ";
				else $w_bloc1 = " AND ept.bloc1_id is null ";

				/*$bloc2_id = $this->getBloc2ID($metier_id);
				if( $bloc2_id != null && $bloc2_id != 0 ) $w_bloc2 = " AND ept.bloc2_id = $bloc2_id ";
				else $w_bloc2 = " AND ept.bloc2_id is null ";*/

				if( $fonction != '' ) $w_fonction = " AND f.fonction_libelle = '$fonction' ";
				else $w_fonction = "";

				$sql = "
					SELECT
					  *
					FROM
					
					  `contact` AS c,
					  `personne` AS p,
					  `entite` AS ett,
					  `fonction_contact` AS fc,
					  `fonction` AS f
					WHERE
					
					  c.personne_id = p.personne_id
					  AND p.entite_id = ett.entite_id
					  AND fc.contact_id = c.contact_id
					  AND fc.fonction_id = f.fonction_id
					  $w_fonction
					ORDER BY
						ett.entite_nom,
						p.personne_nom
				";
				return $contacts = $this->_db->query( $sql )->fetchAll();
			}

		}	
		

		
		public function getReferentsVAE( $metier_id = 0 ){
		
			
		
				$sql = "
				SELECT
				*
				FROM
				
				`contact` AS c,
				`fonction_contact` AS fc,
				`fonction` AS f,
				`personne` AS p,
				`entite` AS e
				WHERE
				
				 fc.contact_id = c.contact_id
				AND f.fonction_id = fc.fonction_id
				AND c.personne_id = p.personne_id
				AND p.entite_id = e.entite_id
				
				AND f.fonction_libelle = 'Référent VAE'
				";
				return $experts = $this->_db->query($sql)->fetchAll();
			
			}
		
		
		public function getDemarche( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
						d.*
					FROM
						`metier` AS m,
						`demarche` AS d
					WHERE
						m.demarche_id = d.demarche_id
						AND m.metier_id = $metier_id
				";
				return $demarche = $this->_db->query( $sql )->fetch();
			}

		}

		public function getBloc1ID( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
						m.bloc1_id
					FROM
						`metier` AS m
					WHERE
						m.metier_id = $metier_id
				";
				return $bloc1 = $this->_db->query( $sql )->fetchColumn(0);
			}

		}

		public function getBloc2ID( $metier_id = 0 ){

			if( $metier_id > 0 ){

				$sql = "
					SELECT
						m.bloc2_id
					FROM
						`metier` AS m
					WHERE
						m.metier_id = $metier_id
				";
				return $bloc2 = $this->_db->query( $sql )->fetchColumn(0);
			}

		}

		public function getExpertIDDefault( $metier_id = 0 ){

			if( $metier_id > 0 ){
				$sql = "
					SELECT
					  b.binome_id
					FROM
					  `binome` AS b,
					  `candidat_metier` AS cm
					WHERE
					  cm.expert_id = b.binome_id
					  AND b.metier_id = $metier_id
					  AND b.binome_defaut = 1;
				";
				$binome = $this->_db->query( $sql )->fetch();
				return $expert_id = $binome['binome_id'];
			}

		}

		public function getEvaluateurIDDefault( $metier_id = 0 ){

			if( $metier_id > 0 ){
				$sql = "
					SELECT
					  b.binome_id
					FROM
					  `binome` AS b,
					  `candidat_metier` AS cm
					WHERE
					  cm.tuteur_id = b.binome_id
					  AND b.metier_id = $metier_id
					  AND b.binome_defaut = 1;
				";
				$binome = $this->_db->query( $sql )->fetch();
				return $expert_id = $binome['binome_id'];
			}

		}
		
	public function getInfosForStats($candidatsM){
			$req = $this->select()->setIntegrityCheck(false);
			$req->from('candidat_metier AS cm0',array());
		
			$req->joinLeft('candidat AS c0','c0.candidat_id=cm0.candidat_id',array());
			$req->joinLeft('personne AS p0','p0.personne_id=c0.personne_id',array());
		
			$req->joinLeft('metier AS m0','m0.metier_id=cm0.metier_id',array('m0.metier_nb_dossiers_candidats AS nbCandidats','m0.metier_nb_dossiers_tuteurs AS referent','m0.metier_date_envoi_dossiers AS envoi'));
			
			
			$req->where('cm0.candidat_metier_id IN(?)', $candidatsM);
			$req->order('p0.personne_nom ASC');
			$req->group('cm0.candidat_metier_id');
			
			$query = $req->query();
			//Zend_Debug::dump($req->assemble());
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
		}
	    
	}