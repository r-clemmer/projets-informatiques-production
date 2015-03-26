<?php
class Form_EntiteForm extends Zend_Form{
	
	public function init(){
/*
		$baseUrl=Zend_Controller_Front::getInstance()->getBaseUrl();
		
		$this->setAction($baseUrl.'#')
		     ->setMethod('post');*/
		
		$id = new Zend_Form_Element_Hidden('entite_id');
		
		$nom = $this->createElement('text', 'entite_nom');
		$nom->setLabel('Nom : ');
		
		$code = $this->createElement('text', 'entite_code');
		$code->setLabel('Code : ');
				
		$adresse = $this->createElement('text', 'entite_adresse');
		$adresse->setLabel('Adresse : ');
		
		$ville = $this->createElement('text', 'entite_ville');
		$ville->setLabel('Ville : ');
				
		$cp = $this->createElement('text', 'entite_cp');
		$cp->setLabel('Code postal : ');
		
		$activite = $this->createElement('text', 'entite_activite');
		$activite->setLabel('Activité : ');
				
		$tel = $this->createElement('text', 'entite_tel');
		$tel->setLabel('Téléphone : ');
		
		$listeTypes = $this->createElement('select', 'type_entite_id');
		$listeTypes->setLabel('Type d\'entité : ');
		$types = new Model_TypeEntite();
		$liste = $types->fetchAll();
		$listeOptions = array ();
		foreach($liste as $type){
			$listeOptions[$type->type_entite_id] = array ('key' => $type->type_entite_id, 'value' => $type->type_entite_libelle );
		}
		$listeTypes->addMultiOptions($listeOptions);
		
		$listeParents = $this->createElement('select', 'parent_id');
		$listeParents->setLabel('Entité parente : ');
		$parents = new Model_Entite();
		$select = $parents->select()
			->from('entite', array('entite_id as parent_id', 'entite_nom'))
			->where('type_entite_id=?',5);
		$liste = $select->query()->fetchAll();
		$listeOptions = array();
		$listeOptions[0] = array('key' => 0, 'value' => 'aucune');
		foreach($liste as $parent){
			$listeOptions[$parent['parent_id']] = array('key' => $parent['parent_id'], 'value' => $parent['entite_nom']);
		}
		$listeParents->addMultiOptions($listeOptions);
		
		//$submit = new Zend_Form_Element_Submit('submit');
        //$submit->setAttrib('id', 'submitbutton');
        $submit = $this->createElement('submit', 'entite_submit');
		$submit->setLabel('Valider');
				
		// Ajout des éléments au formulaire
		$this->addElement($id)
		->addElement($nom)
		->addElement($code)
		->addElement($adresse)
		->addElement($ville)
		->addElement($cp)
		->addElement($activite)
		->addElement($tel)
		//->addElement($type_entite_id)
		->addElement($listeTypes)
		//->addElement($parent_id)
		->addElement($listeParents)
		->addElement($submit);
	
	}
}