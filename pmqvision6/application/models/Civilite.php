<?php

	class Model_Civilite extends Zend_Db_Table_Abstract{
		
		protected $_name = 'civilite';
		protected $_primary = 'civilite_id';
		protected $_dependentTables = array('Model_Personne');
		
		public function get($civilite_id){
			
			$sql = "
				SELECT *
				FROM civilite
				WHERE civilite_id = $civilite_id
			";
			
			$stmt = $this->_db->query($sql);
	    	$row = $stmt->fetchObject();
	    	return $row;
		}
		
		public function getAll(){
			
			$sql = "
				SELECT *
				FROM civilite
				ORDER BY civilite_libelle DESC
			";
			
			$stmt = $this->_db->query($sql);
	    	$civilites = $stmt->fetchAll();
	    	return $civilites;
		}
	    
	}