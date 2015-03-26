<?php

	class Model_TypeEntite extends Zend_Db_Table_Abstract{
		
		protected $_name = 'type_entite';
		protected $_primary = 'type_entite_id';
		protected $_dependentTables = array(
			'Model_EntiteTypeEntite',
			'Model_TypeEntiteFonction'
		);
		
		public function getListe(){
			
			$sql = "
				SELECT *
				FROM type_entite
				ORDER BY type_entite_libelle
			";
			$stmt = $this->_db->query($sql);
	    	$types = $stmt->fetchAll();
	    	return $types;
		}
	    
	}