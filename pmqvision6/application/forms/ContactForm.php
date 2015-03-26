<?php
class Form_ContactForm extends Zend_Form{
	
	public function init(){
		
		$id = new Zend_Form_Element_Hidden('contact_id');
		
		$civilite = $this->createElement('select', 'contact_civilite');
		$civilite->setLabel('Civilité : ');
		$civiliteOptions = array ();
		for($i = -1; $i < 3; $i ++) {
			switch($i){
				case 0 :
					$label = 'Madame';
					break;
				case 1 :
					$label = 'Monsieur';
					break;
				case 2 :
					$label = 'Mademoiselle';
					break;
				default :
					$label = 'non renseignée';
					break;
			}
			$civiliteOptions [$i] = array ('key' => $i, 'value' => $label );
		}
		$civilite->addMultiOptions ( $civiliteOptions );
		
		$nom = $this->createElement('text', 'contact_nom');
		$nom->setLabel('Nom : ');
		$nom->setRequired();
		//$nom->addFilter('StringToLower');
		$nom->addFilter('StringTrim');
		$nom->addDecorator('Errors');
		
		$prenom = $this->createElement('text', 'contact_prenom');
		$prenom->setLabel('Prénom : ');
		//$prenom->addFilter('StringToLower');
		$prenom->addFilter('StringTrim');
		
		$tel = $this->createElement('text', 'contact_tel');
		$tel->setLabel('Téléphone : ');
		$tel->addFilter('StringTrim');
		
		$tel_port = $this->createElement('text', 'contact_tel_port');
		$tel_port->setLabel('Portable : ');
		$tel_port->addFilter('StringTrim');
		
		$mail = $this->createElement('text', 'contact_mail');
		$mail->setLabel('E-Mail : ');
		$mail->addValidator('EmailAddress');
		$mail->addFilter('StringTrim');
		
		/*$entite_id = $this->createElement('text', 'entite_id');
		$entite_id->setLabel('Entité id : ');
		$entite_id->setRequired();*/
		$listeEntites = $this->createElement('select', 'entite_id');
		$listeEntites->setLabel('Entité : ');
		$entites = new Model_Entite();
		$select = $entites->select()
			->from('entite', array('entite_id', 'entite_nom'))
			->order('entite_nom ASC');
		$liste = $select->query()->fetchAll();
		$listeOptions = array();
		foreach($liste as $entite){
			$listeOptions[$entite['entite_id']] = array('key' => $entite['entite_id'], 'value' => $entite['entite_nom']);
		}
		$listeEntites->addMultiOptions($listeOptions);
		
				
		$submit = new Zend_Form_Element_Submit('submit');
        $submit->setAttrib('id', 'submitbutton');
				
		// Ajout des éléments au formulaire
		$this->addElement($id)
			->addElement($civilite)
			->addElement($nom)
			->addElement($prenom)
			->addElement($tel)
			->addElement($tel_port)
			->addElement($mail)
			//->addElement($entite_id)
			->addElement($listeEntites)
			->addElement($submit);
	
	}
}