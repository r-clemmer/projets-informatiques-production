<?php

	class Model_Objectif extends Zend_Db_Table_Abstract{
		
		protected $_name = 'objectif';
		protected $_primary = 'objectif_id';
		protected $_dependentTables = array('Model_Fiche');
		
		public function get( $objectif_id ){
			
			$objectif = $this->find($objectif_id)->current();
			return $objectif;
		}
	    
	}
	