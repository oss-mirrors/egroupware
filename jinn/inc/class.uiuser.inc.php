<?php
	/*
	JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
	Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

	phpGroupWare - http://www.phpgroupware.org

	This file is part of JiNN

	JiNN is free software; you can redistribute it and/or modify it under
	the terms of the GNU General Public License as published by the Free
	Software Foundation; either version 2 of the License, or (at your 
	option) any later version.

	JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
	WARRANTY; without even the implied warranty of MERCHANTABILITY or 
	FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
	for more details.

	You should have received a copy of the GNU General Public License 
	along with JiNN; if not, write to the Free Software Foundation, Inc.,
	59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
	*/

	class uiuser 
	{
		var $public_functions = Array
		(
			'index'				=> True,
			'add_edit_object'		=> True,
			'browse_objects'		=> True,
			'config_objects'		=> True,
			'save_object_config'	=> True
		);

		var $bo;
		var $ui;
		var $template;

		function uiuser()
		{
			$this->bo = CreateObject('jinn.bouser');

			$this->template = $GLOBALS['phpgw']->template;

			$this->ui = CreateObject('jinn.uicommon');
			$this->ui->app_title=lang('Moderator Mode');

		}

		/********************************
		*  create the default index page                                                          
		*/
		function index()
		{
			//var_dump($this->bo);

		
			if ($this->bo->site_object_id && $this->bo->site_object['parent_site_id']==$this->bo->site_id )
			{
				$this->bo->save_sessiondata();
				$this->bo->common->exit_and_open_screen('jinn.uiuser.browse_objects');
			}
			else
			{
		
				if (!$this->bo->site_id)
				{
					$this->bo->message['info']=lang('Select site to moderate');
				}
				else //if(!$this->bo->site_object_id)
				{
					$this->bo->message['info']=lang('Select site-object to moderate');
				}
					
				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				unset($GLOBALS['phpgw_info']['flags']['noappheader']);
				unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

				$this->ui->header('Index');
				$this->ui->msg_box($this->bo->message);

				$this->main_menu();
				$this->bo->save_sessiondata();
			}
		}

		/****************************************************************************\
		* create main menu                                                           *
		\****************************************************************************/

		function main_menu()
		{
			$this->template->set_file(array(
				'main_menu' => 'main_menu.tpl'));

				// get sites for user and group and make options
				$sites=$this->bo->common->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);

				if(is_array($sites))
				{
					foreach($sites as $site_id)
					{
						$site_arr[]=array(
							'value'=>$site_id,
							'name'=>$this->bo->so->get_site_name($site_id)
						);
					}
				}

				$site_options=$this->ui->select_options($site_arr,$this->bo->site_id,true);


				if ($this->bo->site_id)
				{
					$objects=$this->bo->common->get_objects_allowed($this->bo->site_id, $GLOBALS['phpgw_info']['user']['account_id']);

					if (is_array($objects))
					{
						foreach ( $objects as $object_id) 
						{
							$objects_arr[]=array(
								'value'=>$object_id,
								'name'=>$this->bo->so->get_object_name($object_id)
							);
						}
					}

					$object_options=$this->ui->select_options($objects_arr,$this->bo->site_object_id,true);

				}
				else
				{
					unset($this->bo->site_object_id);
				}

				// set theme_colors
				$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				$this->template->set_var('th_text',$GLOBALS['phpgw_info']['theme']['th_text']);
				$this->template->set_var('row_on',$GLOBALS['phpgw_info']['theme']['row_on']);
				$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);

				// set menu
				$this->template->set_var('site_objects',$object_options);
				$this->template->set_var('site_options',$site_options);

				$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index" name="jinn'));
				$this->template->set_var('select_site',lang('select site'));
				$this->template->set_var('select_object',lang('select_object'));
				$this->template->set_var('go',lang('go'));

				/* set admin shortcuts */
				// if site if site admin
				if($this->bo->site_id && $userisadmin)
				{
					$admin_site_link='<br><a href="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadminaddedit.').'">'.
					lang('admin:: edit site').'</a>';
				}
				$this->template->set_var('admin_site_link',$admin_site_link);
				$this->template->set_var('admin_object_link',$admin_object_link);

				$this->template->pparse('out','main_menu');

			}



			/**********************************
			* 	create form to new objectrecord                                          
			*/ 
			function add_edit_object()
			{

				$this->ui->header('add or edit objects');
				$this->ui->msg_box($this->bo->message);
				$this->main_menu();	

				$this->main = CreateObject('jinn.uiuseraddedit',$this->bo);
				$this->main->render_form();

				$this->bo->save_sessiondata();
			}



			/****************************************************************************\
			* 	Browse through site_objects                                              *
			\****************************************************************************/

			function browse_objects()
			{
				
				if(!$this->bo->so->test_JSO_table($this->bo->site_object))
				{
					unset($this->bo->site_object_id);
					$this->bo->message['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);

					$this->bo->save_sessiondata();
					$this->bo->common->exit_and_open_screen('jinn.uiuser.index');

				}
				
				$this->ui->header('browse through objects');
				$this->ui->msg_box($this->bo->message);
				$this->main_menu();	

				$this->main = CreateObject('jinn.uiuserbrowse',$this->bo);
				$this->main->render_list();

				unset($this->bo->message);
				$this->bo->save_sessiondata();
			}

			/****************************************************************************\
			* 	Config site_objects                                              *
			\****************************************************************************/

			function config_objects()
			{
				$this->ui->header('config_objects??');
				$this->ui->msg_box($this->bo->message);
				$this->main_menu();	

				$main = CreateObject('jinn.uiconfig',$this->bo);
				$main->show_fields();

				$this->bo->save_sessiondata();
			}


			/****************************************************************************\
			* 	Config site_objects                                              *
			\****************************************************************************/
			/*
			function save_object_config()
			{

				$this->header();
				$this->main_menu();	

				while(list($key, $x) = each($GLOBALS[HTTP_POST_VARS]))
				{
					$columns[]=$key;
				}

				foreach($columns as $col)
				{


					}

					$this->main = CreateObject('jinn.uiconfig');

					var_dump($columns);

					$this->bo->save_sessiondata();
				}
				*/


			}
			?>
