<?php

   class japie 
   {
	  var $bo;
	  var $site_id=false;
	  var $site_object_id=false;
	  var $default_view='list'; //possible: create,list
	  var $japie_functions=array();
	  var $baselink;

	  function japie()
	  {
		 $this->set_default_functions();
	  }

	  function set_default_functions()
	  {
		 $this->japie_functions['list']=true;
		 $this->japie_functions['read']=true;
		 $this->japie_functions['edit']=true;
		 $this->japie_functions['copy']=true;
		 $this->japie_functions['delete']=true;

		 $this->japie_functions['reports']=false;
		 $this->japie_functions['filter']=false;
	  }

	  function display()
	  {
		 if(!$this->site_object_id)
		 {
			die(lang('Error calling Japie function: no object id')); 
		 }

		 if($_GET['jma']=='jinn.uiu_edit_record.read_record')
		 {
			$this->read_record();	
		 }
		 elseif($_GET['jma']=='jinn.uiu_edit_record.new_record')
		 {
			$this->new_record();	
		 }
		 elseif($_GET['jma']=='jinn.uiu_edit_record.edit_record')
		 {
			$this->edit_record();	
		 }
		 elseif($_GET['jma']=='jinn.bouser.del_record')
		 {
			$this->del_record();	
		 }
		 elseif($_GET['jma']=='jinn.bouser.copy_record')
		 {
			$this->copy_record();	
		 }
		 else
		 {
			$this->list_records();
		 }
	  }

	  function make_japie_link()
	  {
		 return $this->baselink.'&jma=';
	  }

	  function setSession()
	  {
		 $tmpso = CreateObject('jinn.sojinn');
		 $this->site_id = $tmpso->get_site_id_by_object_id($this->site_object_id);
		 unset($tmpso);

		 //fixme destroy current session??
		 
		 $sessionmanager = CreateObject('jinn.sojinnsession');
		 $sessionmanager->sessionarray['site_object_id']=$this->site_object_id;
		 $sessionmanager->sessionarray['site_id']=$this->site_id;
		 //_debug_array($sessionmanager->sessionarray);
		 $sessionmanager->save();
		 unset($sessionmanager);
	  }

	  function doClassStuff()
	  {
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();
		 $this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(false,'jinn'));

	  }

	  function list_records()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.uiu_list_records');
		 $this->doClassStuff();

		 $this->uijapie->template->set_root($GLOBALS['egw']->common->get_tpl_dir('jinn'));
		 $this->uijapie->display();
	  }

	  function read_record()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->read_record();		 
	  }

	  function edit_record()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->edit_record();		 
	  }
  
	  function new_record()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->new_record();		 
	  }

	  function del_record()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();
		 //$this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(false,'jinn'));


		 $this->uijapie->del_record();		 
	  }
	  function copy_record()
	  {
		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();
		 //$this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(false,'jinn'));


		 $this->uijapie->copy_record();		 
	  }
   }



