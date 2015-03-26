<?php
class Fonctions_Contact{
	
	function getCivilite($id_civ){
		switch($id_civ){
			case 0 : $civ='Madame';
				break;
			case 1 : $civ='Monsieur';
				break;
			case 2 : $civ='Mademoiselle';
				break;
			default : $civ='';
				break;
		}
		return $civ;
	}
	
}