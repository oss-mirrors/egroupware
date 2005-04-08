<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; version 2 of the License.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   /* $Id$ */

   class uiuser 
   {
	  var $public_functions = Array
	  (
		 'index'				=> True,
		 'add_edit_object'		=> True,
		 'file_download'		=> True,
		 'config_objects'		=> True,
		 'img_popup'			=> True,
	  );

	  var $bo;
	  var $ui;
	  var $template;

	  function uiuser()
	  {
		 $this->bo = CreateObject('jinn.bouser');

		 $this->template = $GLOBALS['phpgw']->template;

		 $this->ui = CreateObject('jinn.uicommon',$this->bo);

		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;
	  }

	  /*!
	  @function index
	  @abstract create the default index page which is listview                                                         
	  */
	  function index()
	  {
		 if (($this->bo->session['site_id']==0 || $this->bo->session['site_id']) && $this->bo->site_object_id && $this->bo->site_object['parent_site_id']==$this->bo->session['site_id'] )
		 {
			$this->bo->save_sessiondata();
			//			$this->bo->common->exit_and_open_screen('jinn.uiu_list_records.display');
			$this->bo->common->exit_and_open_screen('jinn.uiu_list_records.display');
		 }
		 else
		 {
			if (!$this->bo->session['site_id'])
			{
			   $this->bo->session['message']['info'].=lang('Select site to moderate');
			}
			else 
			{
			   $this->bo->session['message']['info'].=lang('Select site-object to moderate');
			}

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			unset($GLOBALS['phpgw_info']['flags']['noappheader']);
			unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

			$this->ui->header('Index');
			$this->ui->msg_box($this->bo->session['message']);
			unset($this->bo->session['message']);

			$this->ui->main_menu();
			$this->bo->save_sessiondata();
		 }
	  }


	  /****************************************************************************\
	  * 	Config site_objects                                              *
	  \****************************************************************************/

	  function config_objects()
	  {
		 $this->ui->header(lang('configure browse view'));

		 if(!$this->bo->site_object_id)
		 {
			$this->bo->session['message']['error']=lang('No object selected. No able to configure this view');
			$this->ui->msg_box($this->bo->session['message']);
			unset($this->bo->session['message']);

			$this->ui->main_menu();	

		 }
		 else
		 {
			$this->ui->msg_box($this->bo->session['message']);
			unset($this->bo->session['message']);
			$this->ui->main_menu();	
			$main = CreateObject('jinn.uiconfig',$this->bo);
			$main->show_fields();
		 }

		 $this->bo->save_sessiondata();
	  }

	  function file_download()
	  {
		 /* check current site  and object*/
		 if(!$this->bo->session['site_id'] || !$this->bo->site_object_id)
		 {
			$this->bo->session['message'][error]=lang('You have no access to this file.');
			$this->bo->session['message'][error_code]=118;

			$this->bo->save_sessiondata();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
		 }

		 /* get available allowed paths from current site  and object*/
		 if($this->bo->site[cur_upload_path])
		 {
			$legal_paths[]=$this->bo->site[cur_upload_path];
		 }
		 if($this->bo->site_object[cur_upload_path])
		 {
			$legal_paths[]=$this->bo->site_object[cur_upload_path];
		 }

		 /* check if file is in one of the above paths */
		 foreach($legal_paths as $lpath)
		 {
			/* don't allow ../ in download string */
			if (preg_match("/%2F/i", $_GET['file']) || preg_match("/\.\./i", $_GET['file'])) 
			{
			   continue;	
			} 

			if(substr($_GET['file'],0,strlen($lpath))==$lpath)
			{
			   $allowed_action=true;	 
			}
		 }

		 if(!$allowed_action)
		 {
			$this->bo->session['message'][error]=lang('You have no access to this file.');
			$this->bo->session['message'][error_code]=118;

			$this->bo->save_sessiondata();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');

		 }

		 $file_name=$_GET['file'];

		 if(file_exists($file_name))
		 {

			$browser = CreateObject('phpgwapi.browser'); 

			$browser->content_header($file_name);

			$handle = fopen ($file_name, "r");
			$contents = fread ($handle, filesize ($file_name));
			fclose ($handle);

			echo $contents;
		 }
		 else
		 {
			die(lang('ERROR: the file %1 doesn\'t exists, please contact the webmaster',$file_name));
		 }

		 $GLOBALS['phpgw']->common->phpgw_exit();
	  }

	  function img_popup()
	  {
		 $attributes=base64_decode($_GET[attr]);
		 $new_path=base64_decode($_GET[path]);
		 $this->template->set_file(array(
			'imgpopup' => 'imgpopup.tpl'
		 ));

		 $this->template->set_var('img',$new_path);
		 $this->template->set_var('ctw',lang('close this window'));
		 $this->template->pparse('out','imgpopup');
	  }

   }
?>
