<?php

	class Model_TypeEntiteFonction extends Zend_Db_Table_Abstract{
		
		protected $_name = 'type_entite_fonction';
		protected $_primary = 'type_entite_fonction_id';
		protected $_referenceMap = array(
	        'type_entite'	=> array(
	            'columns'		=> 'type_entite_id',
	            'refTableClass' => 'Model_TypeEntite',
				'refColumns'	=> 'type_entite_id'
	        ),
	        'fonction'	=> array(
	            'columns'		=> 'fonction_id',
	            'refTableClass' => 'Model_Fonction',
				'refColumns'	=> 'fonction_id'
	        )
	    );
	    
	}