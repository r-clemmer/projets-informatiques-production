<?php

class Form_MetierForm extends Zend_Form{
	
	public function init(){
		
		$validInt = array(
			new Zend_Validate_Int(),
			new Zend_Validate_Between(0,999)
		);
		
		$metier_id = new Zend_Form_Element_Hidden('metier_id');
		
		$fiche_id = $this->createElement('text', 'fiche_id');
		$fiche_id->setLabel('Fiche id : ');
		$fiche_id->setRequired(true);
		
		$history = $this->createElement('select', 'history_id');
		$history->setLabel('History : ');
		$mHistory = new Model_History();
		$histories = $mHistory->getListeHistory();
		$historyOptions = array ();
		foreach($histories as $h){
			$historyOptions[$h['history_id']] = array('key' => $h['history_id'], 'value' => $h['history_id']);
		}
		$history->addMultiOptions($historyOptions);
		
		$date_envoi_dossiers = $this->createElement('text', 'date_envoi_dossiers');
		$date_envoi_dossiers->setLabel('Date d\'envoi des dossiers : ');
		$date_envoi_dossiers->setAttrib('class','datepicker');
		$date_envoi_dossiers->setRequired(true);
		$date_envoi_dossiers->addValidators(array(
			new Zend_Validate_Date('DD/MM/YYYY', 'fr_FR'),
			new Zend_Validate_StringLength(10,10)
		));
		$date_envoi_dossiers->addErrorMessage('"%value%" n\'est pas une date valide !');
		
		
		$effectif = $this->createElement('text', 'metier_effectif');
		$effectif->setLabel('Effectif prévu : ');
		$effectif->setRequired(true);
		$effectif->addValidators($validInt);
		//$effectif->setErrors(array('La valeur saisie n\'est pas valide !'));
		
		$dossiers_candidats = $this->createElement('text', 'metier_nb_dossiers_candidats');
		$dossiers_candidats->setLabel('Dossiers candidats : ');
		$dossiers_candidats->setRequired(true);
		$dossiers_candidats->addValidators($validInt);
		//$dossiers_candidats->setErrors(array('La valeur saisie n\'est pas valide !'));
		
		$dossiers_tuteurs = $this->createElement('text', 'metier_nb_dossiers_tuteurs');
		$dossiers_tuteurs->setLabel('Dossiers référents évaluateurs : ');
		$dossiers_tuteurs->setRequired(true);
		$dossiers_tuteurs->addValidators($validInt);
		//$dossiers_tuteurs->setErrors(array('La valeur saisie n\'est pas valide !'));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Ajouter');
		$submit->setAttrib('id', 'submit');
		
		// Ajout des éléments au formulaire
		$this
			->addElement($metier_id)
			->addElement($fiche_id)
		    ->addElement($history)
		    ->addElement($date_envoi_dossiers)
		    ->addElement($effectif)
		    ->addElement($dossiers_candidats)
		    ->addElement($dossiers_tuteurs)
		    ->addElement($submit);
	
	}
}