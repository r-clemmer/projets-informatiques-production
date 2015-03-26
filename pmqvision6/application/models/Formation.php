<?php

	class Model_Formation extends Zend_Db_Table_Abstract{
		
		protected $_name = 'formation';
		protected $_primary = 'formation_id';
		protected $_dependentTables = array(
			'Model_ContactFormation'
		);
		protected $_referenceMap = array(
	        'resultat'	=> array(
	            'columns'		=> 'resultat_id',
	            'refTableClass' => 'Model_Resultat',
				'refColumns'	=> 'resultat_id'
	        )
	    );
	    
	    public function get( $formation_id ){
	    	
	    	
	    	return $this->find( $formation_id )->current();
	    }
	    
	    public function addEntites( $formation_id, $entite_ids = array() ){
	    	
	    	foreach( $entite_ids as $entite_id ){
	    		$sql = "
	    			INSERT INTO `entite_formation`
	    				( entite_id, formation_id )
	    			VALUES
	    				( $entite_id, $formation_id );
	    		";
	    		$this->_db->query( $sql );
	    	}
	    	
	    }
	    
	    public function getEntites( $formation_id ){
	    	
	    	$sql = "
	    		SELECT
	    			*
	    		FROM
	    			`entite_formation`
	    		WHERE
	    			formation_id = $formation_id
	    	";
	    	$stmt = $this->_db->query( $sql );
	    	$efs = $stmt->fetchAll();
	    	
	    	$mEntites = new Model_Entite();
	    	foreach( $efs as $ef ){
	    		$entites[] = $mEntites->get( $ef['entite_id'] );
	    	}
	    	return $entites;
	    }
	    
	    public function getListe( $sidx, $sord, $start, $limit ){
	    	
	    	$sql = "
	    		SELECT
	    			*
	    		FROM
	    			`formation`
	    		ORDER BY $sidx $sord
	    		LIMIT $start, $limit;
	    	";
	    	$stmt = $this->_db->query( $sql );
	    	$formations = $stmt->fetchAll();
	    	return $formations;
	    }
	    
	    
	    

		public function getCandidats( $formation_id = 0, $sidx = '', $sord = 'ASC', $start = 0, $limit = 0 ){

			if( $formation_id > 0 ){

				$sql = "
					SELECT
						p.*,
						c.*,
						cm.*
					FROM
						`candidat_metier` AS cm,
						`candidat` AS c,
						`personne` AS p
					WHERE
						cm.candidat_id = c.candidat_id
						AND c.personne_id = p.personne_id
						AND cm.formation_id = $formation_id
				";
				if( $sidx != '' ) $sql .= " ORDER BY $sidx $sord ";
				if( $limit > 0 ){
					$sql .= " LIMIT $limit ";
					if( $start > 0 ) $sql .= " OFFSET $start ";
				};
				$stmt = $this->_db->query($sql);
				return $candidats = $stmt->fetchAll();
			}

		}
	    
	}