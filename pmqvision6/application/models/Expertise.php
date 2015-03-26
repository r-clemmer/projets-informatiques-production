<?php

	class Model_Expertise extends Zend_Db_Table_Abstract{
		
		protected $_name = 'expertise';
		protected $_primary = 'expertise_id';
		protected $_referenceMap = array(
	        'contact'	=> array(
	            'columns'		=> 'contact_id',
	            'refTableClass' => 'Model_Contact',
				'refColumns'	=> 'contact_id'
	        ),
	        'demarche'	=> array(
	            'columns'		=> 'demarche_id',
	            'refTableClass' => 'Model_Demarche',
				'refColumns'	=> 'demarche_id'
	        )
	    );

		public function getContacts( $demarche_id = 0, $bloc1_id = 0, $bloc2_id = 0 ){

			if( $demarche_id > 0 ){

				$bloc1 = "";
				$bloc2 = "";

				if( $bloc1_id > 0 ){
					$bloc1 = " AND e.bloc1_id = $bloc1_id ";
					if( $bloc2_id > 0 ){
						$bloc2 = " AND e.bloc2_id = $bloc2_id ";
					}
				}

				$sql = "
					SELECT
					  p.*, c.*
					FROM
					  `expertise` AS e,
					  `contact` AS c,
					  `personne` AS p
					WHERE
					  e.contact_id = c.contact_id
					  AND c.personne_id = p.personne_id
					  AND e.demarche_id = $demarche_id
					  $bloc1
					  $bloc2
				";
				$contacts = $this->_db->query( $sql )->fetchAll();

				return $contacts;

			}

		}

		public function add( $contact_id = 0, $demarche_id = 0, $bloc1_id = 0, $bloc2_id = 0 ){

			if( $contact_id > 0 && $demarche_id > 0 && $bloc1_id > 0 ){

				if( !($bloc2_id > 0) ) $bloc2_id = 0;

				$data = array(
					'contact_id'	=>	$contact_id,
					'demarche_id'	=>	$demarche_id,
					'bloc1_id'		=>	$bloc1_id,
					'bloc2_id'		=>	$bloc2_id
				);
				$this->insert($data);
			}

		}
	    
	}