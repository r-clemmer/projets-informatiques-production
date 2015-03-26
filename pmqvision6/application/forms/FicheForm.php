<?php
class Form_FicheForm extends Zend_Form{
	
	public function init(){

		//$baseUrl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$id = new Zend_Form_Element_Hidden('fiche_id');
		
		$remarque = $this->createElement('text', 'fiche_remarque');
		$remarque
				->setLabel('Remarque : ');
		
		$acces_candidats = $this->createElement('checkbox', 'fiche_acces_candidats');
		$acces_candidats
				->setLabel('Accès candidats : ');
				
		$objectif = $this->createElement('select', 'fiche_objectif');
		$objectif
				->setLabel('Objectif : ');
		$objectifOptions = array ();
		for($i = 0; $i <= 5; $i ++) {
			switch($i){
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
			$objectifOptions [$i] = array ('key' => $i, 'value' => $label );
		}
		$objectif->addMultiOptions ( $objectifOptions );
		
		$projet = $this->createElement('checkbox', 'fiche_projet');
		$projet->setLabel('Projet : ');
		
		//$submit = new Zend_Form_Element_Submit('submit');
		$submit = $this->createElement('submit', 'fiche_submit');
		$submit->setLabel('Valider');
				
		// Ajout des éléments au formulaire
		$this->addElement($remarque)
		     ->addElement($acces_candidats)
		     ->addElement($objectif)
		     ->addElement($projet)
		     ->addElement($submit)
		     ;
		     //->addElement('submit', 'login', array('label' => 'Valider'));
	
	}
}