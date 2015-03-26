<?php
class Model_Droits_Acl extends Zend_Acl
{
	public function __construct(){
		
		$droits = new Model_TypeEntite();
		$listeDroits = $droits->fetchAll();
		
		foreach($listeDroits as $droit){
			$this->addRole(new Zend_Acl_Role($droit->type_entite_libelle));
		}

		//resources
		$this->add( new Zend_Acl_Resource('operations') );
		$this->add( new Zend_Acl_Resource('candidat') );
		$this->add( new Zend_Acl_Resource('saisie') );
		$this->add( new Zend_Acl_Resource('intervenants') );
		$this->add( new Zend_Acl_Resource('entites') );
		$this->add( new Zend_Acl_Resource('contacts') );
		$this->add( new Zend_Acl_Resource('formations') );
		$this->add( new Zend_Acl_Resource('jurys') );

		//droits forthac
		$this->allow('forthac');

		//droits organisme référent
		//		organisme de formation
		//		greta
		$this->allow('organisme référent', 'operations');
		$this->allow('organisme référent', 'candidat');
		$this->allow('organisme référent', 'saisie');
		$this->allow('organisme référent', 'intervenants');
		$this->allow('organisme référent', 'entites');
		$this->allow('organisme référent', 'contacts');
		$this->allow('organisme référent', 'formations');
		$this->allow('organisme référent', 'jurys');

		$this->allow('organisme de formation', 'operations');
		$this->allow('organisme de formation', 'candidat');
		$this->allow('organisme de formation', 'saisie');
		$this->allow('organisme de formation', 'intervenants');
		$this->allow('organisme de formation', 'entites');
		$this->allow('organisme de formation', 'contacts');
		$this->deny('organisme de formation', 'formations');
		$this->deny('organisme de formation', 'jurys');

		$this->allow('greta', 'operations');
		$this->allow('greta', 'candidat');
		$this->allow('greta', 'saisie');
		$this->allow('greta', 'intervenants');
		$this->allow('greta', 'entites');
		$this->allow('greta', 'contacts');
		$this->deny('greta', 'formations');
		$this->deny('greta', 'jurys');

		//droits délégation
		//		branche
		$this->allow('délégation', 'operations');
		$this->deny('délégation', 'operations', 'new');
		$this->allow('délégation', 'intervenants', 'index');
		$this->allow('délégation', 'entites', 'index');
		$this->allow('délégation', 'contacts', 'index');
		$this->allow('délégation', 'formations','index');
		$this->allow('délégation', 'jurys','index');
		$this->allow('délégation', null, 'details');

		$this->allow('branche', 'operations');
		$this->deny('branche', 'operations', 'new');
		$this->allow('branche', 'intervenants', 'index');
		$this->allow('branche', 'entites', 'index');
		$this->allow('branche', 'contacts', 'index');
		$this->allow('branche', null, 'details');

		//droits entreprise
		//		organisation professionelle
		//		organisation syndicale
		$this->deny('entreprise');
		$this->deny('organisation professionnelle');
		$this->deny('organisation syndicale');

		Zend_View_Helper_Navigation_HelperAbstract::setDefaultAcl($this);
		
	}	
}