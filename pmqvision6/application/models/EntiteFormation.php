<?php

	class Model_EntiteFormation extends Zend_Db_Table_Abstract{
		
		protected $_name = 'entite_formation';
		protected $_referenceMap = array(
	        'entite'	=> array(
	            'columns'		=> 'entite_id',
	            'refTableClass' => 'Model_Entite',
				'refColumns'	=> 'entite_id'
	        ),
	        'formation'	=> array(
	            'columns'		=> 'formation_id',
	            'refTableClass' => 'Model_Formation',
				'refColumns'	=> 'formation_id'
	        )
	    );
	    
	}