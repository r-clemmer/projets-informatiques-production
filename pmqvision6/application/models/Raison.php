<?php

	class Model_Raison extends Zend_Db_Table_Abstract{
		
		protected $_name = 'raison';
		protected $_primary = 'raison_id';
		protected $_dependentTables = array('Model_CandidatMetier');

		public function get( $raison_id = 0 ){

			return $raison = $this->find( $raison_id )->current();
		}
	    
	}