<?php

	class Model_EntiteTypeEntite extends Zend_Db_Table_Abstract{
		
		protected $_name = 'entite_type_entite';
		protected $_referenceMap = array(
	        'entite'	=> array(
	            'columns'		=> 'entite_id',
	            'refTableClass' => 'Model_Entite',
				'refColumns'	=> 'entite_id'
	        ),
	        'type_entite'	=> array(
	            'columns'		=> 'type_entite_id',
	            'refTableClass' => 'Model_TypeEntite',
				'refColumns'	=> 'type_entite_id'
	        )
	    );
	    
	    public function set($entite_id, $type_entite_id){
	    	
	    	$sql = "
	    		INSERT INTO entite_type_entite(entite_id, type_entite_id)
	    		VALUES($entite_id, $type_entite_id);
	    	";
	    	
	    	$this->_db->query($sql);
	    }
	    
	    public function deleteByEntite($entite_id){
	    	
	    	echo $sql = "
	    		DELETE FROM entite_type_entite
	    		WHERE entite_id = $entite_id
	    	";
	    	
	    	$this->_db->query($sql);
	    	
	    }
	    
	}