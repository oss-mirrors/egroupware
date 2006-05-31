<?php

   class japie 
   {
	  var $bo;
	  var $site_id=false;
	  var $site_arr;
	  var $site_object_id=false;
	  var $default_view='list'; //possible: create,list
	  var $japie_functions=array();
	  var $baselink;
	  var $calling_app;
	  var $xmlarray;

	  function japie($object_id)
	  {
		 $this->calling_app = $GLOBALS['egw_info']['flags']['currentapp'];

		 $this->site_object_id=$object_id;
		 
		 $this->setSession();

		 $this->set_default_functions();
		 
		 //_debug_array($this->site_id);
		 //_debug_array($this->site_object_id);

		 $this->check_or_upgrade();
	  }

	  function check_or_upgrade()
	  {
		 //read app jsxl / jaxl
		 if($this->set_app_jsxml_to_array($this->calling_app))
		 {
			if(!$this->check_site_version_ok())
			{
			   $this->do_upgrade();
			}
		 }
	  }

	  function do_upgrade()
	  {
		 $this->uiimport = CreateObject('jinn.ui_importsite');
		 $upgrade_ok = $this->uiimport->load_site_from_xml($this->xmlarray,true);
		 unset($this->uiimport);
		 //_debug_array($this->site_id);
		 //_debug_array($this->site_object_id);
		 //$this->setSession();
	  }

	  function set_app_jsxml_to_array($appname)
	  {
		 $filename=PHPGW_SERVER_ROOT.'/'.$appname.'/setup/'.$appname.'.jsxl';
		 if(file_exists($filename))
		 {
			$dataFile = fopen( $filename, "r" ) ;
			if($dataFile)
			{
			   $buffer = fgets($dataFile, 4096);
			   while (!feof($dataFile)) 
			   {
				  $buffer .= fgets($dataFile, 4096);
			   }
			   fclose($dataFile);
			}

			$xmlObj   = CreateObject('jinn.xmltoarray',$buffer);
			$this->xmlarray = $xmlObj->createArray();

			return true;
		 }
	  }

	  function check_site_version_ok()
	  {
		 if( intval($this->xmlarray['jinn']['site'][0]['site_version']) > intval($this->site_arr['site_version']) )
		 {
			return false;	
		 }
		 else 
		 {
			return true;
		 }
		 
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
		 elseif($_GET['jma']=='jinn.bouser.multiple_actions')
		 {
			$this->multiple_actions();	
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
		 $this->site_id  = $tmpso->get_site_id_by_object_id($this->site_object_id);
		 $this->site_arr = $tmpso->get_site_values($this->site_id);
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
		 $this->uijapie->tplsav2->japie=true;
		 $this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(false,'jinn'));
	  }

	  function list_records()
	  {
		 $this->uijapie = CreateObject('jinn.uiu_list_records');

		 $this->doClassStuff();

		 $this->uijapie->template->set_root($GLOBALS['egw']->common->get_tpl_dir('jinn'));
		 $this->uijapie->display();
	  }

	  function read_record()
	  {
		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->read_record();		 
	  }

	  function edit_record()
	  {
		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->edit_record();		 
	  }
  
	  function new_record()
	  {
//		 $this->setSession();

		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->new_record();		 
	  }

	  function del_record()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->del_record();		 
	  }
	  function copy_record()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->copy_record();		 
	  }

	  function multiple_actions()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japie_functions=$this->japie_functions;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->multiple_actions();		 
	  }

	  
   }



