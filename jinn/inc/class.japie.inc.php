<?php

   class japie 
   {
	  var $bo;
	  var $site_id=false;
	  var $site_arr;
	  var $site_object_id=false;
	  var $default_view='list'; //possible: create,list
	  var $baselink;
	  var $calling_app;
	  var $xmlarray;

	  function japie($object_id)
	  {
		 $this->calling_app = $GLOBALS['egw_info']['flags']['currentapp'];

		 $this->site_object_id=$object_id;
		 
		 $this->setSession();

		 
		 //_debug_array($this->site_id);
		 //_debug_array($this->site_object_id);

		 $this->check_or_upgrade();
	  }

	  function check_or_upgrade()
	  {
		 //read app jsxl / jaxl
		 if(!$this->check_site_version_ok($this->calling_app))
		 {
			$this->set_app_jsxml_to_array($this->calling_app);
			$this->do_upgrade();
		 }
	  }

	  function do_upgrade()
	  {
		 $this->uiimport = CreateObject('jinn.ui_importsite');
		 $upgrade_ok = $this->uiimport->load_site_from_xml($this->xmlarray,true);
		 unset($this->uiimport);

//		 $this->setSession();
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

	  function check_site_version_ok($appname)
	  {
		 if(@include(PHPGW_SERVER_ROOT.'/'.$appname.'/setup/japie.info.php'))
		 {
			//if( intval($this->xmlarray['jinn']['site'][0]['site_version']) > intval($this->site_arr['site_version']) )
			if( $japie_info['site_version'] > intval($this->site_arr['site_version']) )
			{
//			   die('hallo');
			   return false;	
			}
			else
			{
			   return true;
			}
		 }
		 else 
		 {
			return false;
		 }
	  }

	  /**
	  * addExtraWhere: merge the extra_where filter SQL with an additional extra where
	  * 
	  * @param mixed $extra_where 
	  * @access public
	  * @return void
	  */
	  function addExtraWhere($extra_where)
	  {
		 //fixme start of object filters
		 if($this->bo->site_object[extra_where_sql_filter])
		 {
			if ($extra_where) 
			{
			   $extra_where.= " AND ({$this->bo->site_object[extra_where_sql_filter]})"; 	
			}
			else
			{
			   $extra_where= " ({$this->bo->site_object[extra_where_sql_filter]})"; 	
			}
		 }

		 $this->extra_where=$extra_where; 
//		 _debug_array($this->extra_where);

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
		 elseif($_GET['jma']=='jinn.uiuser.runonrecord')
		 {
			$this->run_on_record();	
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

		 //FIXME Workaround
		 $site_with_this_name_arr=$tmpso->get_sites_by_name($this->calling_app);
		 $this->site_id=$site_with_this_name_arr[0];

		 /*
		 if(!$this->site_id)
		 {
			die(lang('Error calling Japie function: no site id')); 
		 }
		 */

		 $this->site_arr = $tmpso->get_site_values($this->site_id);
		 unset($tmpso);

		 //fixme destroy current session??
		 
		 $sessionmanager = CreateObject('jinn.sojinnsession');
		 $sessionmanager->sessionarray['site_object_id']=$this->site_object_id;
		 $sessionmanager->sessionarray['site_id']=$this->site_id;
		 $sessionmanager->save();
		 unset($sessionmanager);
	  }

	  function doClassStuff()
	  {
		 $this->uijapie->no_header=true;

		 if($this->extra_where)
		 {
			$this->uijapie->bo->site_object['extra_where_sql_filter']=$this->extra_where;
		 }
		 if($this->upload_url)
		 {
			$this->uijapie->bo->site_object['cur_upload_url'] = $this->upload_url;
			$this->uijapie->bo->plug->local_bo->site_object['cur_upload_url'] = $this->upload_url;

			$sessdata =  $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi');
			$sessdata['UploadImageBaseURL'] = $this->upload_url;
			if(count($sessdata) > 0) //this catches the bug in the phpgwapi crypto class..
			{
			   $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi',$sessdata);
			}


		 }
		 if($this->upload_path)
		 {
			$this->uijapie->bo->site_object['cur_upload_path'] = $this->upload_path;
			$this->uijapie->bo->plug->local_bo->site_object['cur_upload_path'] = $this->upload_path;

			$sessdata =  $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi');
			$sessdata['UploadImageBaseDir'] = $this->upload_path;
			if(count($sessdata) > 0) //this catches the bug in the phpgwapi crypto class..
			{
			   $GLOBALS['phpgw']->session->appsession('UploadImage','phpgwapi',$sessdata);
			}
		 }

		 $this->uijapie->japielink=$this->make_japie_link();
		 $this->uijapie->tplsav2->japie=true;
		 $this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(false,'jinn'));
		 $this->uijapie->tplsav2->set_tpl_path($this->uijapie->tplsav2->get_tpl_dir(true,'jinn'));
		 
	  }

	  function list_records()
	  {
		 $this->uijapie = CreateObject('jinn.uiu_list_records');

		 $this->doClassStuff();

		 //		 $this->uijapie->template->set_root($GLOBALS['egw']->common->get_tpl_dir('jinn'));
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

		 $this->uijapie = CreateObject('jinn.uiu_edit_record');
		 $this->doClassStuff();

		 $this->uijapie->new_record();		 
	  }

	  function del_record()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->del_record();		 
	  }
	  function copy_record()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->copy_record();		 
	  }

	  function multiple_actions()
	  {
		 $this->uijapie = CreateObject('jinn.bouser');
		 
		 $this->uijapie->no_header=true;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->multiple_actions();		 
	  }

	  function run_on_record()
	  {
		 $this->uijapie = CreateObject('jinn.uiuser');

		 $this->uijapie->no_header=true;
		 $this->uijapie->japielink=$this->make_japie_link();

		 $this->uijapie->runonrecord();		 
	  }


   }



