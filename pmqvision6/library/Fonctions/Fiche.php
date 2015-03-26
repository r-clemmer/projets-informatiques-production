<?php
class Fonctions_Fiche{
	
	function getObjectif($id_objectif){
		switch($id_objectif){
			case 1 :
				$label = 'montée en qualification';
				break;
			case 2 :
				$label = 'reconversion';
				break;
			case 3 :
				$label = 'approfondissement des connaissances';
				break;
			case 4 :
				$label = 'validation des acquis';
				break;
			case 5 :
				$label = 'autre';
				break;
			default :
				$label = 'non renseigné';
				break;
		}
		return $label;
	}
	
}