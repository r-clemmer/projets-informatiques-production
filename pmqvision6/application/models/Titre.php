<?php

	class Model_Titre extends Zend_Db_Table_Abstract{

		public function get( $demarche_id = 0, $bloc1_id = 0, $bloc2_id = 0 ){

			if( $demarche_id > 0 && $bloc1_id > 0 ){
			
				$mDemarches = new Model_Demarche();

				$demarche = $mDemarches->get($demarche_id);

				$file_name = $this->getLastXML($demarche_id);

				$resultat = array();

				if (file_exists( "./xml/$file_name" )) {
					$xml = simplexml_load_file("./xml/$file_name")->$demarche['demarche_abrege'];
					$resultat['demarche'] = $demarche;
					foreach( $xml as $bloc1 ){
						if( $bloc1_id == $bloc1['num'] ){
							$resultat['bloc1']['libelle'] = $bloc1['nom'];
							$resultat['bloc1']['num'] = $bloc1['num'];
							$resultat['bloc1']['abrege'] = $bloc1['abrege'];
							$resultat['bloc1']['codeRNCP'] = $bloc1['codeRNCP'];
							$specialites = $bloc1->specialites;
							foreach( $specialites->specialite as $specialite ){
								if( $bloc2_id == $specialite['num'] ){
									$resultat['bloc2']['libelle'] = $specialite['nom'];
									$resultat['bloc2']['NSFgenerique'] = $specialite['NSFgenerique'];
									$resultat['bloc2']['NSFTextile'] = $specialite['NSFTextile'];
									$resultat['bloc2']['NSFHabillement'] = $specialite['NSFHabillement'];
									$resultat['bloc2']['NSFChaussure'] = $specialite['NSFChaussure'];
									$resultat['bloc2']['num'] = $bloc1['num'];
									$resultat['bloc2']['NSFCuirsetPeaux'] = $specialite['NSFCuirsetPeaux'];
								}
							}
						}
					}
				}else{
					exit("Echec lors de l'ouverture du fichier $file_name.");
				}
				return $resultat;

			}

		}
		
		public function getListe( $demarche_id = 0 ){
			
			if( $demarche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`demarche` AS d
					WHERE
						d.demarche_id = $demarche_id
				";
				$resultat = $this->_db->query( $sql )->fetch();

				$file_name = $this->getLastXML($demarche_id);

				if (file_exists( "./xml/$file_name" )) {
					$xml = simplexml_load_file("./xml/$file_name")->$resultat['demarche_abrege'];
					$i=0;
					foreach( $xml as $bloc1 ){
						$blocs[$i]['id'] = $bloc1['num'];
						$blocs[$i]['nom'] = $bloc1['nom'];
						$i++;
					}

				} else {
					exit("Echec lors de l'ouverture du fichier $file_name.");
				}
				return $blocs;
			}
			
		}
	public function getListeAndSpec( $demarche_id = 0 ){
			
			if( $demarche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`demarche` AS d
					WHERE
						d.demarche_id = $demarche_id
				";

				$resultat = $this->_db->query( $sql )->fetch();

				$file_name = $this->getLastXML($demarche_id);

				if (file_exists( "./xml/$file_name" )) {
					$xml = simplexml_load_file("./xml/$file_name")->$resultat['demarche_abrege'];
					$i=0;

					foreach( $xml as $bloc1 ){
						
						$blocs[$i]['id'] = $bloc1['num'];
						$blocs[$i]['nom'] = $bloc1['abrege'];
						$listSpec = $bloc1->specialites;
				
						if(count($listSpec->specialite) != NULL){
							$blocs[$i]['spec'] = array();
							$j = 0;
							foreach($listSpec->specialite as $spec){
								
								$blocs[$i]['spec'][$spec['num'].''] = $spec['nom'].''; 
								$j++;
							}
						}
						
						$i++;
					}

				} else {
					exit("Echec lors de l'ouverture du fichier $file_name.");
				}

				return $blocs;
			}
			
		}

		public function getListeBloc2( $demarche_id = 0, $bloc1 = 0 ){

			if( $demarche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`demarche` AS d
					WHERE
						d.demarche_id = $demarche_id
				";
				$resultat = $this->_db->query( $sql )->fetch();

				$file_name = $this->getLastXML($demarche_id);

				$blocs = "";

				if (file_exists( "./xml/$file_name" )) {
					$xml = simplexml_load_file("./xml/$file_name")->$resultat['demarche_abrege'];
					foreach( $xml as $bloc_1 ){
						if( $bloc_1['num'] == $bloc1 ){
							$i = 0;
							$specialites = $bloc_1->specialites[0];
							foreach( $specialites as $bloc_2 ){
								$blocs[$i]['num'] = $bloc_2['num'];
								$blocs[$i]['nom'] = $bloc_2['nom'];
								$i++;
							}
						}
					}

				} else {
					exit("Echec lors de l'ouverture du fichier $file_name.");
				}
				return $blocs;
			}

		}

		public function getLastXML( $demarche_id = 0 ){

			if( $demarche_id > 0 ){

				$sql = "
					SELECT
						*
					FROM
						`demarche` AS d
					WHERE
						d.demarche_id = $demarche_id
				";
				$resultat = $this->_db->query( $sql )->fetch();

				$demarche = $resultat['demarche_abrege'];

				$string = "";

				$i = "00";
				while( file_exists( './xml/'.strtoupper( $demarche ).'_'.$i.'.xml' ) ){

					$i++;
					if( strlen( $i ) < 2 ) $i = '0'.$i;
				}

				$i--;

				if( strlen( $i ) < 2 ) $i = '0'.$i;

				$string = strtoupper( $demarche ).'_'.$i.'.xml';

				return  $string;
			}

		}

		public function getNumXML( $file = '' ){

			if( $file != '' ){

				$underscore_position = strpos($file, '_')+1;
				return $num = substr($file, $underscore_position, 2);
			}

		}
	    
	}