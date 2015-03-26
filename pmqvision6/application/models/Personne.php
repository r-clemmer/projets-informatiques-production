<?php

	class Model_Personne extends Zend_Db_Table_Abstract{
		
		protected $_name = 'personne';
		protected $_primary = 'personne_id';
		protected $_dependentTables = array('Model_Contact', 'Model_Candidat');
		protected $_referenceMap = array(
	        'civilite'	=> array(
	            'columns'		=> 'civilite_id',
	            'refTableClass' => 'Model_Civilite',
				'refColumns'	=> 'civilite_id'
	        ),
	        'entite'	=> array(
	            'columns'		=> 'entite_id',
	            'refTableClass' => 'Model_Entite',
				'refColumns'	=> 'entite_id'
	        )
	    );
	    
	    public function get($personne_id){
	    	
	    	return $this->find($personne_id)->current();
	    }
	    
	    public function getListeByEntite($entite_id){
	    	
	    	$sql = "
	    		SELECT *
	    		FROM personne
	    		WHERE entite_id = $entite_id
	    	";
	    	
	    	$stmt = $this->_db->query($sql);
	    	$personnes = $stmt->fetchAll();
	    	
	    	return $personnes;
	    }
	    
		public function deleteEntite($entite_id){
	    	
	    	echo $sql = "
	    		UPDATE personne
	    		SET entite_id = 9999
	    		WHERE entite_id = $entite_id
	    	";
	    	$this->_db->query($sql);
	    	
	    }
	    
	    public function getListePersonne($string){
	    	
	    	$sql = "
		    	SELECT p.personne_id, civ.civilite_abrege, p.personne_nom, p.personne_prenom
				FROM personne AS p, contact AS c, civilite AS civ
				WHERE p.personne_id = c.personne_id
				AND civ.civilite_id = p.civilite_id
				AND (
					p.personne_nom LIKE '%$string%'
					OR p.personne_prenom LIKE '%$string%'
				)
				ORDER BY p.personne_nom, p.personne_prenom
				LIMIT 0,10
	    	";
	    	$stmt = $this->_db->query($sql);
	    	$personnes = $stmt->fetchAll();
	    	return $personnes;
	    }
	    
        public function updateContact(
	    	$personne_id,
	    	$civilite_id = 4,
	    	$personne_nom,
	    	$personne_prenom,
	    	$personne_date_naissance = "0000-00-00",
	    	$personne_tel,
	    	$personne_port,
	    	$personne_mail,
	    	$personne_poste,
	    	$entite_id,
	    	$contact_forme,
	    	$contact_date_formation,
	    	$fonctions
	    ){
	    	
	    	$sql = "
	    		UPDATE personne
	    		SET civilite_id = $civilite_id,
	    		personne_nom = '$personne_nom',
	    		personne_prenom = '$personne_prenom',
	    		personne_date_naissance = '$personne_date_naissance',
	    		personne_tel = '$personne_tel',
	    		personne_port = '$personne_port',
	    		personne_mail = '$personne_mail',
	    		personne_poste = '$personne_poste',
	    		entite_id = $entite_id
	    		WHERE personne_id = $personne_id
	    	";
	    	$this->_db->query($sql);
	    	
	    	$sql = "
	    		UPDATE contact
	    		SET contact_forme = $contact_forme,
	    		contact_date_formation = '$contact_date_formation'
	    		WHERE personne_id = $personne_id
	    	";
	    	$this->_db->query($sql);
	    	
	    	$sql = "
	    		SELECT contact_id
	    		FROM contact
	    		WHERE personne_id = $personne_id
	    	";
	    	$stmt = $this->_db->query($sql);
	    	$contact = $stmt->fetchObject();
	    	$contact_id = $contact->contact_id;
	    	
	    	$sql = "
		    		DELETE FROM fonction_contact
		    		WHERE contact_id = $contact_id
		    	";
	    	$this->_db->query($sql);
	    	
	    	foreach($fonctions as $fonction){
		    	$sql = "
		    		INSERT INTO fonction_contact
		    		(fonction_id, contact_id)
		    		VALUES($fonction, $contact_id)
		    	";
		    	$this->_db->query($sql);
	    	}
	    	
	    }

		public function getListeRechercheNom( $string, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			$strings = explode(' ', $string);
			$where = "";
			foreach( $strings as $string ){
				$where .= " AND ";
				$where .= " (p.personne_prenom LIKE '$string%' ";
				$where .= " OR p.personne_nom LIKE '$string%') ";
			}

			$role = Zend_Auth::getInstance()->getIdentity()->role;
			$entite_id = Zend_Auth::getInstance()->getIdentity()->entite_id;

			if( $role == 'branche' ) $where .= ' AND e.parent_id = '.$entite_id;

			$sql = "
				SELECT
				  p.*,
				  (
					SELECT
					  c.candidat_id
					FROM
					  `candidat` AS c
					WHERE
					  c.personne_id = p.personne_id
				  ) AS candidat_id,
				  (
					SELECT
					  c.contact_id
					FROM
					  `contact` AS c
					WHERE
					  c.personne_id = p.personne_id
				  ) AS contact_id
				FROM
				  `personne` AS p,
				  `entite` AS e
				WHERE p.entite_id = e.entite_id
				$where
			";
//			echo $sql;
			if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
			if( $limit > 0 ){
				$sql .= " LIMIT $limit ";
				if( $start > 0 ) $sql .= " OFFSET $start ";
			};
			$stmt = $this->_db->query( $sql );
			return $personnes = $stmt->fetchAll();

		}

		public function getContact( $personne_id = 0 ){

			if( $personne_id > 0 ){

				$mContacts = new Model_Contact();
				return $contact = $mContacts->fetchRow(" personne_id = $personne_id ");

			}

		}

		public function getCandidat( $personne_id = 0 ){

			if( $personne_id > 0 ){

				$mCandidats = new Model_Candidat();
				return $candidat = $mCandidats->fetchRow(" personne_id = $personne_id ");

			}

		}

		public function checkPersonneExist( $nom = "", $prenom = "" ){

			if( $nom != "" && $prenom != "" ){

				$sql = "
					SELECT
						*,
						(
							SELECT
								c.candidat_id
							FROM
								`candidat` as c
							WHERE
								c.personne_id = p.personne_id
						) AS candidat_id,
						(
							SELECT
								c.contact_id
							FROM
								`contact` as c
							WHERE
								c.personne_id = p.personne_id
						) AS contact_id
					FROM
						`personne` AS p
					WHERE
						p.personne_nom LIKE '$nom%'
						AND p.personne_prenom LIKE '$prenom%'
					ORDER BY
						p.personne_nom, p.personne_prenom
				";
				return $personnes = $this->_db->query( $sql )->fetchAll();
			}

		}

		public function add(
			$civilite_id,
			$nom,
			$prenom,
			$date_naissance,
			$tel,
			$port,
			$mail,
			$poste,
			$date_creation = '0000-00-00',
			$entite_id
		){

			$fDates = new Fonctions_Dates();

			//civilite
			if( $civilite_id > 0 ) $civilite_id = $civilite_id;
			else $civilite_id = 4;
			//nom
			if( $nom == '' ) return false;
			//prenom
			if( $prenom == '' ) return false;
			//date naissance
			 $date_naissance = $fDates->unformatDate($date_naissance);
			
			//date creation

			 $date_creation = date('Y-m-d');
			
			//entite
			if( $entite_id > 0 ) $entite_id = $entite_id;
			else return false;

			$data = array(
				'civilite_id'				=>	$civilite_id,
				'personne_nom'				=>	$nom,
				'personne_prenom'			=>	$prenom,
				'personne_date_naissance'	=>	$date_naissance,
				'personne_tel'				=>	$tel,
				'personne_port'				=>	$port,
				'personne_mail'				=>	$mail,
				'personne_poste'			=>	$poste,
				'personne_date_creation'	=>	$date_creation,
				'entite_id'					=>	$entite_id
			);

			return $personne_id = $this->insert($data);
		}
		
		
	}
	
	