<?php

	class Model_Outil extends Zend_Db_Table_Abstract{
		
		protected $_name = 'outil';
		protected $_primary = 'outil_id';
		protected $_dependentTables = array('Model_ResultatOutil');
		protected $_referenceMap = array(
	        'demarche'	=> array(
	            'columns'		=> 'demarche_id',
	            'refTableClass' => 'Model_Demarche',
				'refColumns'	=> 'demarche_id'
	        )
	    );

		public function get( $outil_id = 0 ){

			if( $outil_id > 0 ) return $outil = $this->find( $outil_id )->current();

		}
	    
	 	public function getAllOutils($dem){
	 		
	 		$req = $this->select()->setIntegrityCheck(false);
			$req->from('outil AS o1',array('outil_libelle','outil_id'));
			$req->joinLeft('demarche as d0','d0.demarche_id=o1.demarche_id',array());
			$req->where('o1.outil_libelle !=?','capacite');
			$req->where('d0.demarche_abrege=?',$dem);
			$req->order('o1.outil_libelle ASC');
			$query = $req->query();
		
			return $query->fetchAll(Zend_Db::FETCH_OBJ);
	 	}
	    
	}