<?php

	class Model_Fonction extends Zend_Db_Table_Abstract{
		
		protected $_name = 'fonction';
		protected $_primary = 'fonction_id';
		protected $_dependentTables = array('Model_TypeEntiteFonction', 'Model_FonctionContact');
		
		public function get($fonction_id){
			
			return $this->find($fonction_id)->current();
		}
		
		public function getListeByTypeEntite($type_entite_id){
			
			$sql = "
				SELECT f.*
				FROM type_entite_fonction AS tef, fonction AS f
				WHERE tef.fonction_id = f.fonction_id
				AND tef.type_entite_id = $type_entite_id
			";
			
			$stmt = $this->_db->query($sql);
	    	$fonctions = $stmt->fetchAll();
	    	
	    	return $fonctions;
			
		}
	    
	}