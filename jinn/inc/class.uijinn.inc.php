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

	class uijinn
	{
		var $public_functions = Array
		(
			'index'					=> True,
			'add_edit_object'		=> True,
			'object_update'			=> True,
			'object_insert'			=> True,
			'del_object'			=> True,
			'browse_objects'		=> True,
			'config_objects'		=> True,
			'save_object_config'	=> True,
			'copy_object'			=> True
		);
		
		var $app_title='JiNN';
		var $bo;// = CreateObject('jinn.bojinn');
		var $template;
		var $debug=False;
		var $add_edit;
		var $message;

		function uijinn()
		{
			$this->bo = CreateObject('jinn.bojinn');
			$this->message = $this->bo->message;
			$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->template = $GLOBALS['phpgw']->template;
		}

		function save_sessiondata()
		{
			$data = array(
				'message' => $this->message,
				'site_id' => $this->bo->site_id,
				'site_object_id' => $this->bo->site_object_id
			);
			$this->bo->save_sessiondata($data);
		}

		/****************************************************************************\
		* debug function, can be included in de ui pagefunctions                     *
		\****************************************************************************/

		function debug_info()
		{				
			if ($this->debug)
			{
				echo '<P><hr><P>';
				echo '<P>debug informatie';
				echo '<br>site_id='.$this->bo->site_id;
				echo '<br>site_object_id='.$this->bo->site_object_id;
				echo '<br>site_db_name='.$this->bo->site[site_db_name];
				echo '<br>site_db_host='.$this->bo->site[site_db_host];
				echo '<br>site_db_user='.$this->bo->site[site_db_user];
				echo '<br>site_db_passwd='.$this->bo->site[site_db_password];
				echo '<P>object_table_name='.$this->bo->site_object[table_name];
				echo '<P>object_id='.$this->bo->site_object_id;
				echo '<P>message='.$this->message;

			}
		}

		/****************************************************************************\
		* create index page                                                          *
		\****************************************************************************/

		function index()
		{

			if (!empty($this->bo->site_object_id) && $this->bo->site_object['parent_site_id']==$this->bo->site_id )
			{

				$this->save_sessiondata();
				Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.browse_objects'));			// all the same so remove 2
			//$this->template->set_var('buttons_action',$GLOBALS['phpgw']->link('/index.php?menuaction=jinn.uijinn.browse_objects'));
			//$this->template->set_var('number_action',$GLOBALS['phpgw']->link('/index.php?menuaction=jinn.uijinn.browse_objects'));
				$GLOBALS['phpgw']->common->phpgw_exit();


			}
			else
			{
				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				unset($GLOBALS['phpgw_info']['flags']['noappheader']);
				unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

				$GLOBALS['phpgw']->common->phpgw_header();
				$this->template->set_file(array('header' => 'header.tpl'));

                                if (!$this->bo->site_id)
                                {
                                        $this->message=lang('Select site to moderate');
                                }
                                else
                                {
                                        $this->message=lang('Select site-object to moderate');
                                }

                                $action=lang('Start');
				$this->template->set_var('title',$this->app_title);
                                $this->template->set_var('action',$action);
				$this->template->pparse('out','header');
                //unset($this->site_object_id);
				$this->debug_info();
                                $this->message_box();
                                $this->main_menu();
				$this->save_sessiondata();
			}
		}



		
		function header()
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			unset($GLOBALS['phpgw_info']['flags']['noappheader']);
			unset($GLOBALS['phpgw_info']['flags']['noappfooter']);


			$GLOBALS['phpgw']->common->phpgw_header();
			$this->template->set_file(array(
				'header' => 'header.tpl'
			));

				$action=lang('add object');

         	$this->template->set_var('title',$this->app_title);
            $this->template->set_var('action',$action);
			$this->template->pparse('out','header');
		}
		

		
		/****************************************************************************\
		* 	create form to new objectrecord                                          *
		\****************************************************************************/
		function add_edit_object()
		{
			$this->add_edit= CreateObject('jinn.uiuseraddedit',$this->bo);

			$this->debug_info();
			$this->header();
			$this->main_menu();	

			$this->add_edit->render_form();
			$this->save_sessiondata();
		}

		
		
		/****************************************************************************\
		* 	Browse through site_objects                                              *
		\****************************************************************************/
		
		function browse_objects()
		{
			
			$this->browse= CreateObject('jinn.uiuserbrowse',$this->bo);

			$this->debug_info();
			$this->header();
			$this->main_menu();	

			$this->browse->render_list();
			$this->save_sessiondata();


		}

		/****************************************************************************\
		* 	Config site_objects                                              *
		\****************************************************************************/
		
		function config_objects()
		{
			$config = CreateObject('jinn.uiconfig');

			$this->debug_info();
			$this->header();
			$this->main_menu();	

			$config->show_fields();
			
			$this->save_sessiondata();
		}
		/****************************************************************************\
		* 	Config site_objects                                              *
		\****************************************************************************/
		
		function save_object_config()
		{
			$config = CreateObject('jinn.uiconfig');

			$this->debug_info();
			$this->header();
			$this->main_menu();	

			//$data=$this->bo->make_http_vars_pairs($GLOBALS[HTTP_POST_VARS],'');
			while(list($key, $x) = each($GLOBALS[HTTP_POST_VARS]))
			{
				//echo $key;
				$columns[]=$key;
			}
			
			foreach($columns as $col)
			{


			}
			
			
			var_dump($columns);
			//			$config->show_fields();
			
			$this->save_sessiondata();
		}
		/****************************************************************************\
		* create main menu                                                           *
		\****************************************************************************/

		function main_menu()
		{
			$this->template->set_file(array(
				'main_menu' => 'main_menu.tpl'));

			// get sites for user and group and make options
			$sites=$this->bo->get_sites($this->bo->uid);
			$site_options=$this->bo->site_options($sites);

			// get object options for site and user
			if ($this->bo->site_id)
			{
				$objects=$this->bo->get_objects($this->bo->site_id, $this->bo->uid);
				$object_options=$this->bo->object_options($objects);
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

			$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.index" name="jinn'));

			$this->template->set_var('select_site',lang('select site'));
			$this->template->set_var('select_object',lang('select_object'));
			$this->template->set_var('go',lang('go'));

			$this->template->pparse('out','main_menu');

		}

		/****************************************************************************\
		* insert routine after submission                                            *
		\****************************************************************************/

		function object_insert()
		{
			$status=$this->bo->insert_object_data($this->bo->site_object[table_name],$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES]);
			if ($status==1)
			{
			        $this->message='Record met succes toegevoegd';
			}

			$this->save_sessiondata();
			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		/****************************************************************************\
		* update routine after submission                                            *
		\****************************************************************************/

		function object_update()
		{
			$where_condition = $GLOBALS[where_condition];
			if($GLOBALS[HTTP_POST_VARS][delete])
			{
			        $this->del_object();
			}

			$status = $this->bo->update_object_data($this->bo->site_object[table_name],$GLOBALS[HTTP_POST_VARS],$GLOBALS[HTTP_POST_FILES],$where_condition);
			if ($status==1)
			{
			        $this->message='Record met succes gewijzigd';
			}

			$this->save_sessiondata();
			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
			$GLOBALS['phpgw']->common->phpgw_exit();

		}

		/****************************************************************************\
		* delete routine after submission                                            *
		\****************************************************************************/

		function message_box()
		{
		        echo '<table align=center width="80%"><tr><td>'.$this->message.'</td></tr></table>';
			unset($this->message);
		}


		function del_object()
		{
			
			
			$status = $this->bo->delete_object_data($this->bo->site_object[table_name],$GLOBALS[where_condition]);
			if ($status==1)
			{
			        $this->message=lang('Record succesfully deleted');
			}

			$this->save_sessiondata();
			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		}

		function copy_object()
		{
			
			
			$status = $this->bo->copy_object_data($this->bo->site_object[table_name],$GLOBALS[where_condition]);
			if ($status==1)
			{
			        $this->message=lang('Record succesfully copied');
			}

			$this->save_sessiondata();
			Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
			$GLOBALS['phpgw']->common->phpgw_exit();
		}


	}
?>
