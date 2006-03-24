<?php
	/***************************************************************************\
	* eGroupWare - Files Center                                                 *
	* http://www.egroupware.org                                                 *
	* Written by:                                                               *
	*  - Vinicius Cubas Brand <vinicius@think-e.com.br>                         *
	*  sponsored by Think.e - http://www.think-e.com.br                         *
	* ------------------------------------------------------------------------- *
	* Description: UI Class for file center                                     *
	* ------------------------------------------------------------------------- *
	*  This program is free software; you can redistribute it and/or modify it  *
	*  under the terms of the GNU General Public License as published by the    *
	*  Free Software Foundation; either version 2 of the License, or (at your   *
	*  option) any later version.                                               *
	\***************************************************************************/

	# viniciuscb: This file is very based in the file manager UI class, but I've
	# made a lot of modifications, due to the following reasons:
	# - template system is not fully used (here we should have no html)
	# - access to the vfs from here (this should be at least in BO class
	# - functions with hundreds of lines
	# 
	# Another intent was to port the Intermedia GroupOffice filemanager design to
	# here. As they don't use templates, I just got their html
	# page and templarizated it.
	#
	# Future modifications: integration with tyapi and bo/so layer

#error_reporting(E_ALL & !E_NOTICE);
#error_reporting(E_ALL);

	class ui_fm2
	{
		var $xml_functions = array();

		var $soap_functions = array(
			'get_dir_info' => array(
				'in'  => array(),
				'out' => array('string')
			),
			'refresh' => array(
				'in'  => array(),
				'out' => array('string')
			)
		);
		
		var $public_functions = array(
			'index'          => True,
			'upload'         => True,
			'properties'     => True,
			'css'            => True,
			'prop_commit'    => True,   #Properties form commit treatment
			'upl_commit'     => True,   #Upload form commit treatment
			'view'           => True,
			'history'        => True,
			'custom_manager' => True,
			'custom_edit'    => True,
			'custom_add'     => True,
			'sharing'        => True,
			'search'         => True,
			'compress'       => True,
			'comp_commit'    => True,
			'decompress'     => True,
			'extract'        => True,
			'mime_manager'   => True,
			'mime_edit'      => True,
			'prefix_manager' => True,
			'delete'         => True,
			'print_files_table' => True
		);

		var $bo;

		# (array) Describe the buttons on the main menu 
		var $menu_buttons;

		# Relative Application Root, as viewed from browser 
		var $appl_rel_root;

		# Relative Template Root, as viewed from browser 
		var $tpl_root;

		# Application Name 
		var $appname;

		# Current directory; Up level directory
		var $path;
		var $lesspath;

		# Current directory, as it must be displayed. And separator.
		var $disppath;
		var $dispsep;

		# Template object 
		var $t;

		# Session object
		var $s;

		# User preferences for this application 
		var $prefs;

		# This is set if user can write in the current directory 
		var $can_add;

		# File property fields to be displayed 
		var $disp_file_attributes;

		# Files (and directories) in $path 
		var $path_content;

		# Sort order when displaying files (default by name)
		var $fc_sortby = 'name';
		
		# Task that object will do 
		var $ftask;

		# Allowed vars in get/post , array ('varname' => 'normal'/'encoded')
		var $allowed_vars;

		# A storage copy of the var $menuaction, passed though get or post
		var $menuaction;

		# array Copied files
		var $copied;

		# array Cut files
		var $cutted;

		#the list with the possible file attributes
		var $valid_file_attributes;

		# Array with vars received from form 
		var $formvar;
		var $ok;
		var $apply;
		var $return_to_path; # Path of return, to forms
		var $keyword;

		# custom field vars
		var $delete;
		var $custom_id;

		#FIXME this should not be here, but in a class ui_custom or the like.
		#this have the information for custom field attributes
		var $customfields_attributes; 
			
		# $GLOBALS['egw_info']['user'] (itself, not a copy)
		var $user_info;

		var $location;

		#used when ui_fm2 is just being used as file handler for another application
		var $client_app = 'filescenter';

		/*
		 * Availability of base dir
		 */
		var $base_dir_available = false;
	
		/**
		  * Constructor: sets object properties
		  *
		  * @author   Vinicius Cubas Brand
		 */
		function ui_fm2 ()
		{
			//viniciuscb: this is only present in egroupware, not in concisus...
			$GLOBALS['egw_info']['flags']['include_jsbackend'] = true;
			$GLOBALS['egw_info']['flags']['nojsapi'] = false;
		
			$this->user_info =& $GLOBALS['egw_info']['user'];

			$this->bo =& CreateObject('filescenter.bo_fm2');

			//$this->appname =& $this->bo->appname;
			$this->appname = 'filescenter';

			$this->t =& $GLOBALS['egw']->template;

			$this->cutted = $this->bo->get_cutted();
			
			$this->copied = $this->bo->get_copied();

			//workaround to allow a ExecMethod call some method of this class, being the
			//caller another application, without interfering with caller's global flags
/*			if ($GLOBALS['egw_info']['flags']['currentapp'])
			{
				$GLOBALS['egw_info']['flags']['currentapp'] = 'filescenter';
				$GLOBALS['egw_info']['flags']['noheader'] = False;
				$GLOBALS['egw_info']['flags']['nonavbar'] = False;
				$GLOBALS['egw_info']['flags']['nofooter'] = False;
				$GLOBALS['egw_info']['flags']['noappheader'] = False;
				$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;
			}
*/
			#turns get/post vars into object properties (normal/encoded)
			# TODO encoded support / solve bugs
			$this->allowed_vars = array(
				'ftask'          => 'normal', 
				'path'           => 'normal', //must be set to encoded 
				'fc_sortby'         => 'normal',
				'menuaction'     => 'normal',
				'formvar'        => 'normal',
				'files'          => 'normal',
				'ok'             => 'normal',
				'apply'          => 'normal',
				'return_to_path' => 'normal',
				'delete'         => 'normal',  //must be set to encoded
				'custom_id'      => 'normal',
				'keyword'        => 'normal',
				'location'       => 'normal'
			);

			$this->handle_get_post_vars();

	
			//Checks if user is using vfs2. If not, throws a error message and
			//exists. This is this way because filescenter (currently) have no
			//support to old vfs.
			$this->check_if_using_vfs2();

			if ($this->formvar['import'] == 'Y')
			{
				$this->import_vfs2();
			}

			//Checks if there are records in database. If not, will ask user
			//for import, depending on whom is the user.
			$this->check_if_upgrade_needed();


			#set $this->path to the correct path, based in the path received
			#in get/post
						if (!$GLOBALS['egw_info']['user']['apps']['admin'] && ($this->path == '/' || $this->path == $this->bo->fakebase || !$this->bo->allowed_access($this->path)))
						{
								$this->path = $this->bo->homedir;
						}
			elseif ($this->ftask == 'images')
			{
				$this->path = $this->bo->create_images_dir();
			}

			$this->set_current_path();


			$this->menu_buttons = array(
				'uplevel' => array (
					'icon_link'          => $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->lesspath)),
					'icon_other'         => '',
					'icon_imgsrc'        => 'uplvl.png',
					'lang_icon_imgalias' => lang('Level Up')
				),
				'gohome' => array (
					'icon_link'          => $GLOBALS['egw']->link('/index.php','menuaction='.$this->appname.'.ui_fm2.index&'.$this->eprintf('path='.$this->bo->homedir)),
					'icon_other'         => '',
					'icon_imgsrc'        => 'home.png',
					'lang_icon_imgalias' => lang('Home')
				),
				'refresh' => array (
					'icon_link'          => 'javascript:fcFolderView.refresh();',
					'icon_other'         => '',
					'icon_imgsrc'        => 'fsrefresh.png',
					'lang_icon_imgalias' => lang('Refresh')
				),
				'properties' => array (
					'icon_link'          => 'javascript:fcFolderView.properties()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'properties.png',
					'lang_icon_imgalias' => lang('Properties')
				),
				'new_folder' => array (
					'icon_link'          => 'javascript:fcFolderView.new_folder()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'newfolder.png',
					'lang_icon_imgalias' => lang('New folder')
				),
				'upload' => array (
					'icon_link'          => 'javascript:fcFolderView.open_upload_window()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'upload.png',
					'lang_icon_imgalias' => lang('Upload')
				),
				'delete' => array (
					'icon_link'          => 'javascript:fcFolderView.delete_items()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'delete.png',
					'lang_icon_imgalias' => lang('Delete')
				),
				'cut' => array (
					'icon_link'          => 'javascript:fcFolderView.copy(\'cut\')',
					'icon_other'         => '',
					'icon_imgsrc'        => 'cut.png',
					'lang_icon_imgalias' => lang('Cut')
				),
				'copy' => array (
					'icon_link'          => 'javascript:fcFolderView.copy()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'copy.png',
					'lang_icon_imgalias' => lang('Copy')
				),
				'paste' => array (
					'icon_link'          => 'javascript:fcFolderView.paste()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'paste.png',
					'lang_icon_imgalias' => lang('Paste')
				),
/*				'email' => array (
					'icon_link'          => 'javascript:mail_files("'.urlencode(lang('You didn\'t select an item. Click on an icon next to the name to select an item.')).'")',
					'icon_other'         => '',
					'icon_imgsrc'        => 'email.png',
					'lang_icon_imgalias' => lang('E-mail')
				), */
				'share' => array (
					'icon_link'          => $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.sharing'),
					'icon_other'         => '',
					'icon_imgsrc'        => 'sharing.png',
					'lang_icon_imgalias' => lang('Sharing')
				),
				'search' => array (
					'icon_link'          => $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.search'),
					'icon_other'         => '',
					'icon_imgsrc'        => 'search.png',
					'lang_icon_imgalias' => lang('Search')
				),
				/** Uncomment this when the so_db_highlevel be migrated to egroupware
				'subscribe' => array (
					'icon_link'          => 'javascript:fcFolderView.subscribe()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'subs.png',
					'lang_icon_imgalias' => lang('Subscribe')
				),
				'unsubscribe' => array (
					'icon_link'          => 'javascript:fcFolderView.subscribe(\'unsubscribe\')',
					'icon_other'         => '',
					'icon_imgsrc'        => 'unsubs.png',
					'lang_icon_imgalias' => lang('Unsubscribe')
				),*/
				'compress' => array (
					'icon_link'          => 'javascript:fcFolderView.compress()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'compress.png',
					'lang_icon_imgalias' => lang('Compress')
				),
				'extract' => array (
					'icon_link'          => 'javascript:fcFolderView.extract()',
					'icon_other'         => '',
					'icon_imgsrc'        => 'decompress.png',
					'lang_icon_imgalias' => lang('Decompress')
				)
			);



			$this->appl_rel_root = $GLOBALS['egw_info']['server']['webserver_url'].'/'.$this->appname;

			$this->tpl_root = str_replace('/images','',EGW_IMAGES);

			#I've just copied all this stuff from old filemanager. 

			/* Preferences */
			$pref =& CreateObject('phpgwapi.preferences', $this->bo->userinfo['username']);

			$pref->read_repository();
			//			$GLOBALS['egw']->hooks->single('add_def_pref', $GLOBALS['appname']);
			$pref->save_repository(True);
			$pref_array = $pref->read_repository();
			$this->prefs = $pref_array['filescenter']; //FIXME check appname var in _debug_array

			//always show name
			$this->prefs[name] =1;

			# Set file attributes to be displayed
			$this->valid_file_attributes = array_merge($this->bo->file_attributes,$this->bo->vfs->vfs_customfields->get_attributes());

			$blah = $this->array_diff_key($this->prefs,$this->valid_file_attributes);
			$attributes = array_keys($this->array_diff_key($this->prefs,$blah),true);

			foreach ($this->valid_file_attributes as $key => $val)
			{
				if (in_array($key,$attributes))
				{
					$this->disp_file_attributes[] = $key;
				}
			}

	/*		if ($GLOBALS['egw_info']['flags']['currentapp'] != 'filescenter')
			{
				$this->disp_file_attributes = array('name','size','version');
			}*/

			$this->check_set_default_prefs();


			/*
				Check for essential directories
				admin must be able to disable these tests
			*/
			$this->base_dir_available = $this->bo->check_base_dir();

			#FIXME create a ui_custom and purge this from here. (as well as
			# all or mostly of custom field handling functions)
			$this->customfields_attributes = array(
				'customfield_id'          => lang('ID'),
				'customfield_name'        => lang('Field Name (small caps only, no spaces)'),
				'customfield_description' => lang('Description'),
				'customfield_type'        => lang('Type'),
				'customfield_precision'   => lang('Precision'),
				'customfield_active'      => lang('Active')
			); 


			$js_arr =& $GLOBALS['egw_info']['flags']['java_script_globals']['messages']['filescenter'];
		
			$js_arr['newFolder'] = lang('New folder name');
			$js_arr['noItemsSelected'] = lang('You selected no items. Click in the checkbox near the file name to select an item.');
			$js_arr['deleteConfirmation'] = lang('Are you sure you want to delete _filename_?');
			$js_arr['deleteItemsConfirmation'] = lang('Are you sure you want to delete all these _count_ items?');
			$js_arr['justOneItem'] = lang('You can\\\'t select more than one item with this option');
			
		}

		function list_methods($_type='xmlrpc')
		{
			/*
				This handles introspection or discovery by the logged in client,
				in which case the input might be an array.  The server always calls
				this function to fill the server dispatch map using a string.
			*/
			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}
			switch($_type)
			{
				case 'xmlrpc':
					$xml_functions = array(
						'get_dir_info' => array(
							'function'  => 'get_dir_info', 
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Returns data about a dir')
						),
						'refresh' => array(
							'function'  => 'get_dir_info', 
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Returns data about a dir (alias of refresh_dir_info)')
						),
						'new_folder' => array(
							'function'  => 'rpc_new_folder', 
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Creates a directory.')
						),
						'delete' => array(
							'function'  => 'rpc_delete', 
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Deletes a file or set of files.')
						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Read this list of methods.')
						),
						'clean_tmp_folder' => array(
							'function' => 'rpc_clean_tmp_folder',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Clean the Temporary Folder for application')
						),
						'copy' => array(
							'function' => 'rpc_copy',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Copies(or cuts) a file (or a given set of files) into clipboard')
						),
						'paste' => array(
							'function' => 'rpc_paste',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Pastes a file (or a given set of files) from clipboard')
						),
/*						'compress' => array(
							'function' => 'rpc_compress',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Compresses a set of files in an archive')
						),*/
						'extract' => array(
							'function' => 'rpc_extract',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Extract the contents of an archive in a folder')
						),
						'subscribe' => array(
							'function' => 'rpc_subscribe',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('Subscribe some files')
						),
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}

		/**
		  * This function deletes files and reload the page.
		  *
							  * posts
		  * @author   Vinicius Cubas Brand
		 */
		function delete()
		{
			$this->bo->delete($this->files);
			header('Location: '.$this->return_to_path);
			$GLOBALS['egw']->common->egw_exit();
		}

		/**
		  * This function builds the main template and handles most form
		  *
							  * posts
		  * @author   Vinicius Cubas Brand
		 */
		function index ()
		{
			$GLOBALS['egw_info']['flags']['currentapp'] = 'filescenter';

			$clean = get_var('clean',array('GET','POST'));

			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;
			
			if ($clean)
			{
				$GLOBALS['egw_info']['flags']['noappiconbar'] = true;
				$GLOBALS['egw_info']['flags']['nostatusbar'] = true;
				$GLOBALS['egw_info']['flags']['nosidebox'] = true;
				$GLOBALS['egw_info']['flags']['nologo'] = true;

				$GLOBALS['egw_info']['extra_get_vars']['clean'] = '1';
			}

			if ($ret_name = get_var('ret_name',array('GET')))
			{
				$GLOBALS['egw_info']['extra_get_vars']['ret_name'] = $ret_name;
			}

			if (!$this->base_dir_available)
			{
				echo lang("There was a problem while trying to access/create the base infrastructure of FilesCenter directories.<br>".
									"Please, consult admin or check configuration");
				return;
			}

			$disppath = $this->bo->get_disppath($this->path);
			$this->disppath = $disppath['caption'];
			$this->linkpath = $disppath['link'];

			switch ($this->ftask)
			{
				case "new_folder":
					$this->bo->createdir($this->path,urldecode($this->formvar));
					break;
				case "delete":
					$this->bo->delete($this->files);
					break;
				case "attach_picture":
					$GLOBALS['egw_info']['extra_get_vars']['ftask'] = 'attach_picture';
					$GLOBALS['egw']->common->egw_header();
					echo $this->get_files_table($this->path,'filescenter',array());
					echo '<br><div style="text-align:center;"><input type="button" onclick="fcFolderView.select_picture();" value="'.lang('Use this file').'"></div>';
					$GLOBALS['egw']->common->egw_footer();
					$GLOBALS['egw']->common->egw_exit();
					break;
				//These methods are not used anymore.
/*				case "cut":
					$this->bo->cut($this->files);
					$this->cutted = $this->files;
					unset($this->copied); #only for displaying reasons
					break;
				case "copy":
					$this->bo->copy($this->files);
					$this->copied = $this->files;
					unset($this->cutted); #only for displaying reasons
					break;
				case "paste":
					$this->bo->paste($this->path);
					unset($this->copied);
					unset($this->cutted);
					break;*/ 
			}


			/* =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= *
						 * Template Variable assigning and parsing                     *
			 * =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= */
			

			# Get the groups for the current user
			# $groups = $GLOBALS['egw']->accounts->membership();


			$this->t->set_file(array('index' => 'index.tpl'));
			$this->t->set_block('index','dhtml_externals','dhtml_externals');
			$this->t->set_var('img_dir',EGW_IMAGES);
			$this->t->set_var('css_dir',$this->tpl_root.'/css');

			$this->t->set_var('path',($this->location)?$this->location:$this->path);
			
			if ($this->linkpath)
			{
				$disppath = '<a href="'.$this->linkpath.'">'.$this->disppath.'</a>';
			}
			else
			{
				$disppath = $this->disppath;
			}

			$this->t->set_var('disppath',$disppath);
			$this->t->set_var('lang_path',lang('Path'));
						
						/* <trees> */
						$bo_all_tree =& $this->bo->get_all_trees();
			//_debug_array($bo_all_tree);
					$this->t->set_var('tree_path',$this->parsed_tree_path($bo_all_tree,'filescenter'));
						/* </trees> */


			// Button under the page for extra action
	
			if ($ret_name = get_var('ret_name',array('GET')))
			{
				$this->t->set_var('opt_action_button','<br><div style="text-align:center;"><input type="button" onclick="fcFolderView.select_file_for_upload(\'path\',\''.$ret_name.'\');" value="'.lang('Use this file').'"></div>');
			}
			else
			{
				$this->t->set_var('opt_action_button','');
			}


			/* Template parsing and printing */
			$this->display_app_header();
		
			$this->t->set_var('folder_contents',$this->get_files_table($this->path,'filescenter',array(),$options));

			$this->t->pparse('out','index');

			$this->display_app_footer();


		}

		/**
		  * (public) gets all the ui view of a folder. 
		  *
							  * (with options, etc, from the templates)
		  * @author   Vinicius Cubas Brand
		 */
		function get_files_table($id,$app='filescenter',$hide_buttons=array(),$extra_options=array())
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			#commented: future
			$external_allowed_buttons = array(
				#'uplevel',
				#'new_folder',
				'upload',
				'delete',
				#'cut',
				#'copy',
				#'paste',
				'refresh',
				#'properties',
				#'search',
				'compress',
				'extract'
			);

			$buttons = $this->menu_buttons;

			if ($app != 'filescenter') //can not show all buttons;
			{
				foreach ($buttons as $btn_name => $btn_desc)
				{
					if (!in_array($btn_name,$external_allowed_buttons))
					{
						unset($buttons[$btn_name]);
					}
				}
			}

			if (is_array($hide_buttons))
			{
				foreach($hide_buttons as $btn_name)
				{
					unset($buttons[$btn_name]);
				}
			}
			elseif (strtoupper($hide_buttons) == 'ALL') //case where no button appear
			{
				$buttons = array();
			}
			
			if (!$app) $app = 'filescenter';

			$tpl_dir = ExecMethod('phpgwapi.phpgw.common.get_tpl_dir','filescenter');

			//Saving some vars
			$norm_client = $this->client_app;
			$norm_path = $this->path;
			$norm_can_add = $this->can_add;
			
			$this->client_app = $app;

			#gets the correct path for applications
			$path = $this->bo->get_app_path($id,$app);
			$this->path = $path;

			# Check available permissions for $this->path, so we can disable
			# unusable operations in user interface
			if ($this->bo->allowed_access($this->path,EGW_ACL_ADD))
			{
				$this->can_add = True;
			}
			else
			{
				$this->can_add = False;
			}

			//javascript vars
			$jsobject =& CreateObject('phpgwapi.javascript');
			
			$jsobject->validate_file('jscode','common','filescenter');
			$jsobject->validate_file('jscode','filesystem','filescenter');
			$jsobject->validate_file('jscode','fcFolderView-plugin','filescenter');
			$jsobject->validate_file('tablesort','tablesort');

			$jscode = $jsobject->get_script_links();
			
			$jsvars = array(
				'fcFolderViewDefaultPath' => $path,
				'options' => array(
					'upload_other_opts' => ($app=='filescenter') ? '':'external=1'
					),
				'urlCompress' => $GLOBALS['egw']->link('/index.php','menuaction='.$this->appname.'.ui_fm2.compress&'.$this->eprintf('path='.$path))
				);
			
			$jscode .= '<script language="javascript">';
			$jscode .= $jsobject->convert_phparray_jsarray("GLOBALS",$jsvars,false);
			$jscode .= '</script>';

			$tmpl =& CreateObject('phpgwapi.Template',$tpl_dir);

			$tmpl->set_file(array('folder_contents' => 'folder_contents.tpl'));
			$tmpl->set_block('folder_contents','main','main');
			$tmpl->set_var('css_dir',$GLOBALS['egw_info']['server']['webserver_url'].'/filescenter/templates/default/css');
			$tmpl->set_var('javascripts',$jscode);

			if ($app == 'filescenter')
			{
				$tmpl->set_var('display_location','');
			}
			else
			{
				$tmpl->set_var('display_location',''.$this->disppath.'');
			}
			
			//deprecated
			$tmpl->set_var('lang_no_items_selected',lang('You selected no items. Click in the checkbox near the file name to select an item.'));
			
			$tmpl->set_var('lang_delete_confirmation',lang('Are you sure you want to delete \'"+filename+"\'?'));
			$tmpl->set_var('lang_delete_items_confirmation',lang('Are you sure you want to delete all these "+count+" items?'));
			$tmpl->set_var('this_url',$_SERVER["REQUEST_URI"]);

			$tmpl->set_var('path',$path);//($this->location)?$this->location:$this->disppath);
			$tmpl->set_var('form_action',$GLOBALS['egw']->link('/index.php'));


			
			/* Parse main toolbar */
						$iconssize = ($app=='filescenter')?'22':'16';
			$this->toolbar($path,$tmpl,$buttons,$iconssize);

			/* Parse directory content */
			$this->parse_directory_content($path,$tmpl);

			//Restoration of vars
			$this->client_app = $norm_client;
			$this->path = $norm_path;
			$this->can_add = $norm_can_add;

			$tmpl->parse('out','main');
			return $tmpl->get_var('out');
		}

		function print_files_table($id,$app='filescenter',$hide_buttons=array())
		{
			if (!$id)
			{
				$id = $this->path;
			}
			$GLOBALS['egw']->common->egw_header();
			echo $this->get_files_table($id,$app,$hide_buttons);
			echo '<br><div style="text-align:center;"><input type="button" onclick="fcFolderView.select_picture();" value="'.lang('Use this file').'"></div>';
			$GLOBALS['egw']->common->egw_footer();
			$GLOBALS['egw']->common->egw_exit();
		}
		

		/**
		  * (private) Analyzes situation, then builds the file menu
		  *
							  * (with options, etc, from the templates)
		  * @author   Vinicius Cubas Brand
		 */
		function toolbar($path,&$tmpl,&$buttons,$toolbar_img_size)
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
						$image_root = $GLOBALS['egw_info']['server']['webserver_url'].'/filescenter/templates/default/images';

			$navbar_format = $GLOBALS['egw_info']['user']['preferences']['common']['navbar_format'];

			# button uplevel 
						# When user is not admin, he cannot dive into root or fakebase
			if ($path == '/' || (!$GLOBALS['egw_info']['user']['apps']['admin'] &&($path == $this->bo->fakebase || $path == $this->bo->homedir || !$this->bo->allowed_access(dirname($path))) ))
			{
				unset($buttons['uplevel']);
			}
			
			# button gohome 
			if($path == $this->bo->homedir)
			{
				unset($buttons['gohome']);
			}

			# buttons upload, new_folder 
			if(!($path != '/' && $path != $this->bo->fakebase && $this->can_add))
			{
				unset($buttons['upload']);
				unset($buttons['new_folder']);
				unset($buttons['paste']);
				unset($buttons['cut']);
				unset($buttons['delete']);
				unset($buttons['compress']);
				unset($buttons['extract']);
			}
			
			#button paste
			if (empty($this->cutted) && empty($this->copied))
			{
				unset($buttons['paste']);
			}

			
			/* Template parsing */			

			switch ($navbar_format)
			{
				case 'icons':
					$tmpl->set_var('navbar_element','<img src="{icon_imgsrc}" border="0" height="'.$toolbar_img_size.'" width="'.$toolbar_img_size.'" alt="{lang_icon_imgalias}" title="{lang_icon_imgalias}">');
					//$td_width="3%";
					break;
				case 'text':
					$tmpl->set_var('navbar_element','{lang_icon_imgalias}');
					//$td_width="3%";
					break;
				case 'icons_and_text':
				default:
					$tmpl->set_var('navbar_element','<nobr><img src="{icon_imgsrc}" border="0" height="'.$toolbar_img_size.'" width="'.$toolbar_img_size.'" alt="{lang_icon_imgalias}" title="{lang_icon_imgalias}" style="vertical-align: middle;">{lang_icon_imgalias}&nbsp;&nbsp;</nobr>');
					//$td_width="7%";
					$use_second_row = true;
					break;

			}

			//gets extra get vars in a string
			foreach ($GLOBALS['egw_info']['extra_get_vars'] as $key => $val)
			{
				$ev[] = "$key=$val";
			}
			$ev = implode('&',$ev);


			$tmpl->set_var('td_width',$toolbar_img_size+4);

			$tmpl->set_block('folder_contents','header_menu','header_menu');
			foreach($buttons as $icon_name=>$icon_prop)
			{
				$link = $icon_prop['icon_link'];

				if (!ereg('javascript',$icon_prop['icon_link']))
				{
					$icon_prop['icon_link'] .= "&".$ev;
				}

				$icon_prop['icon_imgsrc'] = $image_root.'/'.$toolbar_img_size.'x'.$toolbar_img_size.'/'.$icon_prop['icon_imgsrc'];
				$tmpl->set_var($icon_prop);
				$tmpl->parse('header_menu_contents','header_menu',true);

				$second_row .= '<td align="center" valign="middle" class="appTitles" style="text-align: center;"><a href="'.$icon_prop['icon_link'].'">'.$icon_prop['lang_icon_imgalias'].'</a></td>';
			}

			if ($use_second_row)
			{
				$tmpl->set_var('navbar_second_row','<tr>'.$second_row.'</tr>');
			}
			else
			{
				$tmpl->set_var('navbar_second_row','');
			}


		}

		/**
		  * Shows files in dir $dir
		  *
		  * @author   Vinicius Cubas Brand
		 */
		function parse_directory_content($path='',&$tmpl)
		{

			if (!$this->base_dir_available)
			{
				return '';
			}
			
			if (!$path)
			{	
				$path = $this->path;
			}

			$files_data = $this->get_dir_info(array('path' => $path));

			# Table Header 

			$tmpl->set_block('folder_contents','files_header_tbl_field','files_header_tbl_field');
			
			$fieldnumber = 1;
			foreach ($this->disp_file_attributes as $field)
			{

				$onclick = 'onclick="sortTable('.$fieldnumber.',\'fcTfolders\');sortTable('.$fieldnumber.',\'fcTfiles\');"';

				$tmpl->set_var('tdhoptions','align="left"'.$onclick);
				if ($field === 'size' || $field === 'version')
				{

					$tmpl->set_var('tdhoptions','align="right" '.$onclick);
					$tmpl->set_var('lang_fieldname',$this->valid_file_attributes[$field].'&nbsp;&nbsp;');
				}
				else
				{
					$tmpl->set_var('lang_fieldname',$this->valid_file_attributes[$field]);
				}
				$tmpl->set_var('lang_invert_selection',lang('Invert Selection'));

				$tmpl->parse('files_header_contents','files_header_tbl_field',true);
				$fieldnumber++;
			}

			
			# Table body 
			$tmpl->set_block('folder_contents','dirs_tbl_row','dirs_tbl_row');
			$tmpl->set_block('folder_contents','dirs_tbl_field','dirs_tbl_field');

			$tmpl->set_block('folder_contents','files_tbl_row','files_tbl_row');
			$tmpl->set_block('folder_contents','files_tbl_field','files_tbl_field');


			#Files and folders are very different, so they are handled separately.
			#Also, folders ever come before of files in the html table.
	
			#begin folders processing
			foreach($files_data['folders'] as $key => $file)
			{
				$numfiles++;

				$append = false;
				foreach ($this->disp_file_attributes as $field)
				{
					if ($file['fields'][$field]['tdoptions'])
					{
						$arr_opts = array_merge($file['def_tdoptions'],$file['fields'][$field]['tdoptions']);
					}
					else
					{
						$arr_opts = $file['def_tdoptions'];
					}
					
					$options = '';
					foreach($arr_opts as $key => $val)
					{
						$options .= " $key=\"$val\" ";
					}
					
				
					$tmpl->set_var('tdoptions',$options);
					$tmpl->set_var('field_content',$file['fields'][$field]['content']);

					#parse another table cell
					$tmpl->parse('dirs_tbl_field_contents','dirs_tbl_field',$append);
					$append = true;
				}

				#set some other vars
				$tmpl->set_var('filename',$file['filename']);

				#parse another row
				$tmpl->parse('dirs_tbl_row_contents','dirs_tbl_row',true);
			}

			if (!$files_data['folders'])
			{
				$tmpl->set_var('dirs_tbl_row_contents','');
			}
			#end folders processing

			#begin files processing
			foreach($files_data['files'] as $key => $file)
			{
				$append = false;
				foreach ($this->disp_file_attributes as $field)
				{
					if ($file['fields'][$field]['tdoptions'])
					{
						$arr_opts = array_merge($file['def_tdoptions'],$file['fields'][$field]['tdoptions']);
					}
					else
					{
						$arr_opts = $file['def_tdoptions'];
					}
					
					$options = '';
					foreach($arr_opts as $key => $val)
					{
						$options .= " $key=\"$val\" ";
					}
					

					$tmpl->set_var('tdoptions',$options);
					$tmpl->set_var('field_content',$file['fields'][$field]['content']);

					#parse another table cell
					$tmpl->parse('files_tbl_field_contents','files_tbl_field',$append);
					$append=true;
				}

				$tmpl->set_var('filename',$file['filename']);

				#parse another row
				$tmpl->parse('files_tbl_row_contents','files_tbl_row',true);
			}
			
			if (!$files_data['files'])
			{
				$tmpl->set_var('files_tbl_row_contents','');
			}
			#end files processing
			

			#final table line
			$tmpl->set_var('folder_information',$files_data['dir_info']);

		}

		/**
		  * returns the information about the files in some directory
		  *
		  * @author Vinicius Cubas Brand
		 */
		function get_dir_info($params)
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			//TODO rethink this, and put this code in a generic and transparent place...
			if ($params['extra_get_vars'])
			{
				$GLOBALS['egw_info']['extra_get_vars'] = $params['extra_get_vars'];
			}
			
//			print_r($params);
			$path = $params['path'];

			if (!$path)
			{
				if ($params['appid'] && $params['appname'])
				{
					$path = $this->bo->get_app_path($params['appid'],$params['appname']);
				}
				else
				{
					if ($this->xmlrpc)
					{
						return array(
							'status' => 'fail',
							'msg' => lang('Could not retrieve files for this directory.')
							);
					}
					else
					{
						return false;
					}
				}
			}

			/* Gets all the content of $this->path from BO */

			$this->path_content =& $this->bo->get_path_content($path,$this->fc_sortby);

			#separate files from folders
			$folders = array();
			$files = array();
			foreach($this->path_content as $key => $file)
			{
				if ($file['mime_type'] === 'Directory')
				{
					if (!empty($file['name']))
						$folders[] = $this->path_content[$key];
				}
				else
				{
					$files[] = $this->path_content[$key];
				}
			}
			
	
			//Current folder information, for the end of html table
			$numfiles=0;
			$sizefiles=0;

			$tdotheropts = '';

	//		echo $GLOBALS['egw_info']['flags']['currentapp'] ;
			#begin folders processing
			foreach($folders as $key => $file)
			{
				$numfiles++;

				$ret_folders = array();
				foreach ($this->disp_file_attributes as $field)
				{
					switch ($field)
					{
						case 'name':
							
							$extension = array_pop(explode('.',$field['name']));
							$link = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$file['directory'].'/'.$file['name']));
							//echo "GL:".print_r($GLOBALS['egw_info']['extra_get_vars'],true);
							$link_icon = $GLOBALS['egw']->link('/filescenter/icons/folder.gif');

							$ret_folders[$field]['content'] = '<a href="'.$link.'" alt="'.$file['name'].'" title="'.$file['name'].'"><img width="16" height="16" border="0" src="'.$link_icon.'" align="absmiddle" /> '.$file['name'].'</a>';
							//echo "GL:<xmp>".$ret_folders[$field]['content']."</xmp><br>\n";
							break;
						case 'size':
							$ret_folders[$field]['content']   = '-&nbsp;&nbsp;';
							$ret_folders[$field]['tdoptions']['align'] = 'right';
							break;
						case 'modified':
						case 'created':
							if ($file[$field])
							{
								$ret_folders[$field]['content'] = $GLOBALS['egw']->common->show_date($file[$field]);
							}
							else
							{
								$ret_folders[$field]['content'] = '';
							}
							break;
						case 'modifiedby_id':
						case 'createdby_id':
							if ($file[$field])
							{
								$GLOBALS['egw']->accounts->get_account_name($file[$field],$lid,$fname,$lname);
								$ret_folders[$field]['content'] = $fname.' '.$lname;

								/* Uncomment this when appears the so_db_highlevel 
								unset($acc);
								$acc =& $GLOBALS['egw']->db_hl->get_entity(array(
									'entity_type' => 'account',
									'info_data_id' => $file[$field]
									));
								$ret_folders[$field]['content'] = $acc->get('name');
								*/
							}
							else
							{
								$ret_folders[$field]['content'] = '';
							}
							break;
						case 'version':
							$ret_folders[$field]['content']   = '-&nbsp;&nbsp;';
							$ret_folders[$field]['tdoptions']['align'] = 'right';
							break;
							
						default:
							$ret_folders[$field]['content'] = $file[$field].'&nbsp;';
					}
				}

				$rf[$key]['fields'] = $ret_folders;
				$rf[$key]['filename'] = $path.'/'.$file['name'];

				#default td options
				$rf[$key]['def_tdoptions'] = array(); 			
			}

			if (!$folders)
			{
				$rf = array();
			}
			#end folders processing

			#begin files processing
			foreach($files as $key => $file)
			{
				$numfiles++;
				$sizefiles += $file['size'];
				
				$tdotheropts = '';

				$ret_files = array();
				foreach ($this->disp_file_attributes as $field)
				{
					switch ($field)
					{
						case 'name':

							$extension = array_pop(explode('.',$file['name']));

							$link_icon = $GLOBALS['egw']->link('/filescenter/icon.php','extension='.$extension);

							$link = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.view&'.$this->eprintf('path='.$file['directory'].'/'.$file['name']));
							$ret_files[$field]['content'] = '<a href="'.$link.'"><img width="16" height="16" border="0" src="'.$link_icon.'" align="absmiddle" />'.$file['name'].'</a>';
							break;
						case 'size':
							$ret_files[$field]['content']   = $this->bo->borkb($file[$field]).'&nbsp;&nbsp;';
							$ret_files[$field]['tdoptions']['align'] = 'right'; 
							break;
						case 'mime_type':
							if ($file['mime_friendly'])
							{
								$ret_files[$field]['content'] = lang($file['mime_friendly']);
							}
							elseif (empty($file[$field]))
							{
								$ret_files[$field]['content'] = lang('Unknown');
							}
							else
							{
								$ret_files[$field]['content'] = lang($file[$field]);
							}
							break;
						case 'modified':
						case 'created':
							if ($file[$field])
							{
								$ret_files[$field]['content'] = $GLOBALS['egw']->common->show_date($file[$field]);
							}
							else
							{
								$ret_files[$field]['content'] = '';
							}
							break;
						case 'modifiedby_id':
						case 'createdby_id':
							if ($file[$field])
							{
								$GLOBALS['egw']->accounts->get_account_name($file[$field],$lid,$fname,$lname);
								$ret_files[$field]['content'] = $fname.' '.$lname;

								/* Uncomment this when appear the so_db_highlevel
								unset($acc);
								$acc =& $GLOBALS['egw']->db_hl->get_entity(array(
									'entity_type' => 'account',
									'info_data_id' => $file[$field]
									));
								$ret_files[$field]['content'] = $acc->get('name');*/
							}
							else
							{
								$ret_files[$field]['content'] = '';
							}
							break;
						case 'version':
							$ret_files[$field]['tdoptions']['align'] = 'right';
							$link = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.history&'.$this->eprintf('path='.$file['directory'].'/'.$file['name']));
							$ret_files[$field]['content'] = '<A HREF="'.$link.'">'.$file['version'].'</A>&nbsp;&nbsp;';
							break;
						default:
							$ret_files[$field]['content'] = $file[$field].'&nbsp;';
					}
				}

				$rd[$key]['fields'] = $ret_files;
				$rd[$key]['filename'] = $path.'/'.$file['name'];

				#default td options
				$rd[$key]['def_tdoptions'] = array(); 			
			}
			
			if (!$files)
			{
				$rd = array();
			}
			#end files processing

			$ret_array['folders'] =& $rf;
			$ret_array['files'] =& $rd;

			#final table line
			$ret_array['dir_info'] = '&nbsp;'.$numfiles.' '.lang('item(s)').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.lang('Size of folder').': '.$this->bo->borkb($sizefiles);

			if (!$this->disppath)
			{
				$disppath = $this->bo->get_disppath($path);
				$this->disppath = $disppath['caption'];
				$this->linkpath = $disppath['link'];
			}
			
			$ret_array['display_location'] = ($params['appname'] == 'filescenter')?'':$this->disppath;

			return $ret_array;
		}

		/**
		  * Set the object roperties with values received by get/post
		  *
		  * @author Vinicius Cubas Brand
		 */
		function handle_get_post_vars()
		{	

			// here local vars are created from the HTTP vars
			@reset($GLOBALS['_POST']);
			while(list($name,) = @each($GLOBALS['_POST']))
			{
				if ($this->allowed_vars[$name] && isset($GLOBALS['_POST'][$name]))
				{
					$this->$name = $GLOBALS['_POST'][$name];
				}
			}

			@reset($GLOBALS['_GET']);
			while(list($name,) = @each($GLOBALS['_GET']))
			{
				if ($this->allowed_vars[$name] && isset($GLOBALS['_GET'][$name]))
				{
					$this->$name = $GLOBALS['_GET'][$name];
				}
			}
			
			#decodification of codified properties
			foreach ($this->allowed_vars as $varname => $varopt)
			{
				if ($varopt === 'encoded')
				{
					$this->$varname = $this->bo->decode($this->$varname);
				}
			}
		}


		/**
		  * Analyzes situation and changes current path if necessary
		  *
		 */
		function set_current_path()
		{
			if ($this->menuaction == 'filescenter.ui_fm2.index')
			{
				$must_be_a_dir = true;
			}

			$this->bo->set_current_path($this->path,$must_be_a_dir);

			/* This just prevents // in some cases */
			if($this->path == '/')
			{
				$this->dispsep = '';
			}
			else
			{
				$this->dispsep = '/';
			}

			if(!($this->lesspath = dirname(ereg_replace('/$','',$this->path))))
			{
				$this->lesspath = '/';
			}
		}
		
		/**
		  * Encodes $var if necessary
		  *
		  * @param array $var   array ('varname' => 'varval') returns array w/
														  * correct vals encoded
			  * OR
		  * @param string $var  a string with the form 'varname=varval'
														  * returns a string with the form 'varname=encvarval'
		 */
		function eprintf($var)
		{
		
			$str = false;
			if (is_string($var))
			{
				$str = true;
				$varx = explode('=',$var);
				unset($var);
				$var[$varx[0]] = $varx[1];
			}

			if (is_array($var))
			{
				foreach ($var as $key => $val)
				{
					//trick
					if ($key == 'path')
					{
						$val = ereg_replace('^//','/',$val);
					}
			
					if ($this->allowed_vars[$key] == 'encoded')
					{
						$return[$key] = $this->bo->encode($val);
					}
					else
					{
						$return[$key] = $val;
					}
				}
			}
			
			if ($str)
			{
				$ret = each($return);
				return $ret['key'].'='.$ret['value'];
			}

			return $return;
		}
		
		function display_app_header()
		{
			if(!@is_object($GLOBALS['egw']->js))
			{
				$GLOBALS['egw']->js =& CreateObject('phpgwapi.javascript');
			}


			switch($this->menuaction)
			{
				case 'filescenter.ui_fm2.index':
					$GLOBALS['egw']->js->validate_file('foldertree','foldertree');
					break;
				
				case 'filescenter.ui_fm2.upload':

					$GLOBALS['egw']->js->validate_file('tabs','tabs');
					$GLOBALS['egw']->js->validate_file('jscode','upload','filescenter');

					$GLOBALS['egw']->js->set_onload('javascript:initAll();');
					break;

				case 'filescenter.ui_fm2.properties':
				case 'filescenter.ui_fm2.prop_commit':
					
					$GLOBALS['egw']->js->validate_file('tabs','tabs');
					$GLOBALS['egw']->js->validate_file('jscode','properties','filescenter');
					$GLOBALS['egw']->js->set_onload('javascript:initAll();');
					break;

			}
			$GLOBALS['egw']->common->egw_header();
		}


		/**
		  * Treats the footer section
		  *
		  * @author Vinicius Cubas Brand
		 */
		function display_app_footer()
		{
			$GLOBALS['egw']->common->egw_footer();
			$GLOBALS['egw']->common->egw_exit();
		}

		/**
		  * upload
		  * creates the upload screen, asking for file uploads from user
		  *
		  * @author Vinicius Cubas Brand
		 */
		function upload()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$external = get_var('external',array('GET'));

			$phpgw_flags = Array(
//				'currentapp'    =>      'filescenter',
				'noheader'      =>      True,
				'nonavbar'      =>      True,
				'noappheader'   =>      True,
				'noappfooter'   =>      True,
				'nofooter'      =>      True
			);

			$GLOBALS['egw_info']['flags'] = array_merge($GLOBALS['egw_info']['flags'],$phpgw_flags);

			$this->get_prefix_selects($select_prefix0,$select_type0);

			$js_arr =& $GLOBALS['egw_info']['flags']['java_script_globals']['messages']['filescenter'];
		
			$js_arr['remove'] = lang('remove');
			$js_arr['from_fc'] = lang('from FilesCenter...');

			//must know if the prefix and type will or not be showed
			$options['enable_prefix'] = (int) $this->prefs['upl_prefix'];
			$options['enable_type'] = (int) $this->prefs['upl_type'];

			$this->display_app_header();

			$this->t->set_file(array('upload' => 'upload.tpl'));
			$this->t->set_unknowns('remove');
			$this->t->set_block('upload','main');
			$this->t->set_block('upload','row_title_block');
			$this->t->set_block('upload','col_title_block');


			if ($options['enable_prefix'])
			{
				$this->t->set_var('lang_caption',lang('Prefix'));
				$this->t->parse('extra_cols','col_title_block',true);
				$added = true;
			}
			if ($options['enable_type'])
			{
				$this->t->set_var('lang_caption',lang('Type'));
				$this->t->parse('extra_cols','col_title_block',true);
				$added = true;
			}
			if (!$added)
			{
				$this->t->set_var('extra_cols','');
			}
			$this->t->set_var('lang_filename',lang('Files to Upload'));

			$this->t->parse('row_title','row_title_block');

			$this->t->set_var('path',$this->path);

			$this->t->set_var('lang_upload_files',lang('File(s) to upload:'));
			$this->t->set_var('lang_upload_max_filesize',lang('Maximum filesize upload: %1',ini_get('upload_max_filesize')));
			$this->t->set_var('lang_upload_anotherfile',lang('New').'...');
			$this->t->set_var('lang_upload',lang('Upload'));
			$this->t->set_var('lang_uploadb',lang('Save'));
			$this->t->set_var('lang_cancelb',lang('Cancel'));
			$this->t->set_var('upload_options0',$this->arr2js($options));
			$this->t->set_var('upload_options1',$this->arr2js(array_merge($options,array('type'=>'from_fc'))));

			$disppath = $this->bo->get_disppath($this->path);
			$this->t->set_var('path_information',lang('Upload files in folder').': '.$disppath['caption']);

			$this->t->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.upl_commit'));
			$this->t->set_var('return_to_path',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path)));
			$this->t->set_var('fc_progress_path',$GLOBALS['egw']->common->find_image('filescenter','progress'));
			$this->t->set_var('file_prefix0',$select_prefix0);
			$this->t->set_var('file_type0',$select_type0);
				
			$usual_javascript = 'addNewUpload('.$this->arr2js($options).');';
			$fc_javascript = 'addNewUpload('.$this->arr2js(array_merge($options,array('type'=>'from_fc'))).');';

		
			if (!$external)
			{
				$this->t->set_var('extra_javascript',$usual_javascript);
				$this->t->set_block('main','browse_fc','browse_fc_stored');
				$this->t->set_var('browse_fc_stored','');
			}
			else
			{
				$this->t->set_var('extra_javascript',$usual_javascript.' '.$fc_javascript);
			}


			$this->get_prefix_selects($select_prefix0,$select_type0,'fc');
			$this->t->set_var('file_fcprefix0',$select_prefix0);
			$this->t->set_var('file_fctype0',$select_type0);


			$this->t->pparse("out","main");

			$this->display_app_footer();
		}

		/**
		  * creates the selects for file id prefix and file id type
		  *
		  * @author Vinicius Cubas Brand
		 */
		function get_prefix_selects(&$sel_prefixes,&$sel_ptypes,$pprefix='')
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$vfs_prefixes =& CreateObject('phpgwapi.vfs_prefixes');

			$prefixes = $vfs_prefixes->get_prefixes();

			
			$sel_prefixes = '<select name="'.$pprefix.'prefix0" style="width: 120px;" id="base_prefix">';

			$sel_prefixes .= '<option value="'.$this->user_info['account_lid'].'">'.$this->user_info['account_lid']."</option>\n";

			foreach($prefixes as $prefix)
			{
				$sel_prefixes .= '<option value="'.$prefix['prefix'].'">'.$prefix['prefix'].' ('.$prefix['prefix_description'].")</option>\n";
			}

			//get prefixes from projects

			$boprojects =& CreateObject('projects.boprojects',True,'mains');
			$projs = $boprojects->list_projects(array('action' => 'mains'));

			foreach($projs as $proj)
			{
				$sel_prefixes .= '<option value="'.$proj['number'].'">'.$proj['number'].' ('.$proj['title'].")</option>\n";
			}


			$sel_prefixes .= '</select>';




			$ptypes = $vfs_prefixes->get_prefixes('view',false,'t');
			
			$sel_ptypes = '<select name="'.$pprefix.'type0" style="width: 120px;" id="base_type">';

			$sel_ptypes .= '<option value="">('.lang('None').")</option>\n";

			$sel_ptypes .= '<option value="'.$this->user_info['account_lid'].'">'.$this->user_info['account_lid']."</option>\n";

			foreach($ptypes as $prefix)
			{
				$sel_ptypes .= '<option value="'.$prefix['prefix'].'">'.$prefix['prefix'].' ('.$prefix['prefix_description'].")</option>\n";
			}

			$sel_ptypes .= '</select>';

		}


		/**
		  * creates the file properties screen
		  *
		  * @author Vinicius Cubas Brand
		 */
		function properties()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$phpgw_flags = Array(
//				'currentapp'    =>      'filescenter',
				'noheader'      =>      True,
				'nonavbar'      =>      True,
				'noappheader'   =>      True,
				'noappfooter'   =>      True,
				'nofooter'      =>      True
			);

			$GLOBALS['egw_info']['flags'] = array_merge($GLOBALS['egw_info']['flags'],$phpgw_flags);

			//number of properties to do the colspan in template
			$numofcols = 4;

			#retrieves information about the file
			$this->path_content =& $this->bo->get_path_content($this->path,$this->fc_sortby,true);

			$file_history = $this->bo->get_file_history($this->path);

			$this->display_app_header();

			$this->t->set_file(array('properties' => 'properties.tpl'));
			$this->t->set_block('properties','main');

			//If current file is not a directory, delete sharing section of
			//the form
			if (/*$this->path_content[0]['mime_type'] != 'Directory' ||*/
				$this->path_content[0]['owner_id'] != $this->user_info['account_id'])
			{
				#separing the blocks from the main body
				$this->t->set_block('main','permissions','p1');
				$this->t->set_block('main','sharing','p2');
				$this->t->set_block('main','perm_tabs','p3');

				#destroying theirs content
				$this->t->set_var('p1','');
				$this->t->set_var('p2','');
				$this->t->set_var('p3','');
				$numofcols -= 2;
			}
			else
			{
				$this->t->set_var('select_r_auth_groups',$this->prop_draw_perms_select('gr'));
				$this->t->set_var('select_r_auth_users',$this->prop_draw_perms_select('ur'));
				$this->t->set_var('select_w_auth_groups',$this->prop_draw_perms_select('gw'));
				$this->t->set_var('select_w_auth_users',$this->prop_draw_perms_select('uw'));
			}

			$custom =& CreateObject('phpgwapi.vfs_customfields');

			$customtypes = $custom->get_customfields();

			//Custom fields
			if (!$customtypes)
			{
				#separing the blocks from the main body
				$this->t->set_block('main','custom','p4');
				$this->t->set_block('main','custom_tabs','p5');

				#destroying theirs content
				$this->t->set_var('p4','');
				$this->t->set_var('p5','');

				$numofcols -= 1;
			}
			else
			{
				$file_id = $this->path_content[0]['file_id'];
				$custom_file_fields = $custom->get_fields_by_fileid($file_id);

				//Parses the table body
				$this->t->set_block('main','custom_row');
				$this->t->set_block('custom_row','custom_data');

				$custom_data = $this->t->get_var('custom_data');
				foreach ($customtypes as $custom_name => $val)
				{

					$tdopts = ($i++ % 2) ? ' class="row_off"' : ' class="row_on"';
					$tdopts .= ' width="50%"';

					//begin: parses a line

					$this->t->set_var('tdopts',$tdopts);

					$this->t->set_var('tdcontent',$val['customfield_description'].':');
					$this->t->parse('tmp_custom_data','custom_data',true);

					$this->t->set_var('tdcontent','<input type="text" name="formvar[custom]['.$custom_name.']" value="'.addslashes($custom_file_fields[$custom_name]).'">');

					$this->t->parse('tmp_custom_data','custom_data',true);
					//end

					$this->t->set_var('custom_data',$this->t->get_var('tmp_custom_data'));
					$this->t->parse('tmp_tblbody','custom_row',true);

					$this->t->set_var('custom_data',$custom_data);
					$this->t->set_var('tmp_custom_data','');
					
				}			

				$this->t->set_var('custom_row',$this->t->get_var('tmp_tblbody'));



				$this->t->set_var('lang_custom',lang('Other Properties'));

			}

			#Lang-related
			$this->t->set_var('lang_general',lang('General'));
			$this->t->set_var('lang_general_properties',lang('General File Properties'));
			$this->t->set_var('lang_filename',lang('File Name'));
			$this->t->set_var('lang_filelocation',lang('Location'));
			$this->t->set_var('lang_filetype',$this->valid_file_attributes['mime_type']);
			$this->t->set_var('lang_history',lang('History'));
			$this->t->set_var('lang_datetime_created',lang('Created'));
			$this->t->set_var('lang_datetime_modified',lang('Modified'));
			$this->t->set_var('lang_filehistory',lang('File History'));
			$this->t->set_var('lang_view_history',lang('View History'));
			$this->t->set_var('lang_sharing',lang('Sharing'));
			$this->t->set_var('lang_activate_sharing',lang('Activate Sharing'));

			$this->t->set_var('lang_read_permissions',lang('Read Permissions'));
			$this->t->set_var('lang_write_permissions',lang('Write Permissions'));
			$this->t->set_var('lang_auth_groups',lang('Authorized Groups'));
			$this->t->set_var('lang_auth_users',lang('Authorized Users'));
			$this->t->set_var('lang_ok',lang('OK'));
			$this->t->set_var('lang_apply',lang('Apply'));
			$this->t->set_var('lang_close',lang('Close'));
			$this->t->set_var('lang_owner',lang('Owner'));
			$this->t->set_var('lang_proper_id',lang('File ID'));
			$this->t->set_var('lang_comment',lang('Comment'));


			#Values

			if ($this->path_content[0]['mime_friendly'])
			{	
				$showed_mime = $this->path_content[0]['mime_friendly'];
			}
			else if ($this->path_content[0]['mime_type'])
			{
				$showed_mime = $this->path_content[0]['mime_type'];
			}
			else
			{
				$showed_mime = 'Unknown';
			}

			//Account_name:
			$GLOBALS['egw']->accounts->get_account_name($this->path_content[0]['owner_id'],$owner_lid,$owner_fname,$owner_lname);
			

			$last = count($file_history) - 1;

			$this->t->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prop_commit&'.$this->eprintf('path='.$this->path)));
			$this->t->set_var('return_to_path',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path_content[0]['directory'])));
			$this->t->set_var('full_filename',$this->path);

			if ($this->bo->is_application_dir($this->path_content[0]['directory'].'/'.$this->path_content[0]['name']))
			{
				$dirname = $this->bo->external_directory_name($this->path_content[0]['directory'].'/'.$this->path_content[0]['name']);
				$this->t->set_var('input_filename','['.$dirname['appname'].']'.$dirname['dirname']);
			}
			elseif (in_array($this->path,array($this->bo->publicdir,$this->bo->fakebase,$this->bo->homedir,$this->bo->rootdir)))
			{
				$this->t->set_var('input_filename','{value_filename}');
			}
			else
			{
				$this->t->set_var('input_filename','<input type="text" size="30" name="formvar[filename]" value="{value_filename}">');
			}
			
			$this->t->set_var('value_filename',$this->path_content[0]['name']);			
			
			$this->t->set_var('value_filelocation',$this->path_content[0]['directory']);
			$this->t->set_var('value_filetype',$showed_mime);

			$this->t->set_var('value_datetime_created',$this->path_content[0]['created']);
			$this->t->set_var('value_datetime_modified',$file_history[$last]['modified']);
			$this->t->set_var('value_filehistory',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.history&'.$this->eprintf('path='.$this->path)));
			$this->t->set_var('file_id',$this->path_content[0]['file_id']);

			$this->t->set_var('numofcols',$numofcols);
			$this->t->set_var('value_owner',$owner_fname.' '.$owner_lname.' ('.$owner_lid.')');
			$this->t->set_var('value_proper_id','<input type="text" maxlength="15" name="formvar[proper_id]" value="'.$this->path_content[0]['proper_id'].'">');
			$this->t->set_var('value_comment',$this->path_content[0]['comment']);

			if ($this->path_content[0]['shared'] == 'Y')
			{
				$this->t->set_var('sharing_checked'," CHECKED");
				$this->t->set_var('disabled',"");
			}
			else
			{
				$this->t->set_var('sharing_checked',"");
				$this->t->set_var('disabled',"DISABLED");
			}


			$this->t->pparse("out","main");

			$this->display_app_footer();
		}

		/**
		  *  Creates the html selects for permissions in file properties
		  *
										Intimately bound to the $this->properties method
		  * @param $select_type string can be one of the {ur,uw,gr,gw}
		  * @author Vinicius Cubas Brand
		 */
		function prop_draw_perms_select($select_type)
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			static $passed = false;
			static $groups;
			static $users;
			static $my_groups;
			static $shares_for_this_file;


			if (!$passed)
			{
				//all groups in the system
				$groups = $GLOBALS['egw']->accounts->get_list('groups');

				$users = $GLOBALS['egw']->accounts->get_list('accounts');
				//the groups which I am member
				$my_groups = $GLOBALS['egw']->accounts->membership($this->user_info['account_id']);
			
				#fixme do not talk directly with vfs_sharing. Instead would be
				# more correct to this talk with bo, that talk with vfs that 
				# talk with vfs_sharing

				$vfs_sharing =& CreateObject('phpgwapi.vfs_sharing');

				$shares_for_this_file = $vfs_sharing->get_permissions($this->path_content[0]['file_id']);

				$passed = true;
			}

			
			if ($select_type == 'ur' || $select_type == 'uw')
			{
				$source_rep =& $users;
			}
			elseif ($select_type == 'gr' || $select_type == 'gw')
			{
				$source_rep =& $groups;
			}

			if ($select_type == 'ur' || $select_type == 'gr')
			{
				$source_mask = EGW_ACL_READ;
			}
			elseif ($select_type == 'uw' || $select_type == 'gw')
			{
				$source_mask = EGW_ACL_ADD | EGW_ACL_EDIT;
			}

			$select = "<SELECT name=\"formvar[select_$select_type][]\" SIZE=6 WIDTH=\"60\" id=\"$select_type\" {disabled} MULTIPLE>\n";

			$select .= "<OPTION value=\"-1\">".lang('None')."</OPTION>";
			
			reset($source_rep);
			while(list($num,$accountinfo) = each($source_rep))
			{
				if ($accountinfo['account_id'] != $this->user_info['account_id'])
				{
					$selected = "";
					if (array_key_exists($accountinfo['account_id'],$shares_for_this_file) && ($shares_for_this_file[$accountinfo['account_id']] & $source_mask))
					{
						$selected = " SELECTED";
					}
					
					$select .= "<OPTION value=\"".$accountinfo['account_id']."\"$selected>".$accountinfo['account_firstname']." ".$accountinfo['account_lastname']."</OPTION>";
				}
			}
			
			$select .= "</SELECT>";

			return $select;
		
		}

		/**
		  *  Treats the Properties form commit
		  *
										Intimately bound to the $this->properties method
		  * @author Vinicius Cubas Brand
		 */
		function prop_commit()
		{
			$oldpath = $this->formvar['full_filename'];
			if ($this->formvar['filename'])
			{
				$newpath = dirname($this->formvar['full_filename']).'/'.$this->formvar['filename'];
			}
			else
			{
				$newpath = $oldpath;	
			}

//            echo "oldpath=$oldpath ";
	//          echo "newpath=$newpath ";

			#changing custom properties
			if ($this->formvar['custom'])
			{
				$this->bo->update_custom($oldpath,$this->formvar['custom']);
			}

			#changing permissions
			#todo a condition here, only the owner of the file can set this
			foreach(array('gr','gw','ur','uw') as $perms_operation)
			{
				if ($perms_operation == 'gr' || $perms_operation == 'ur')
				{
					$mask = EGW_ACL_READ;
				}
				else
				{
					$mask = EGW_ACL_ADD | EGW_ACL_EDIT;
				}
			
				if (array_key_exists('select_'.$perms_operation,$this->formvar))
				{
					while (list($key,$val) = each($this->formvar['select_'.$perms_operation]))
					{
						if ($val != -1)
						{
							$set_perms[$val] = $set_perms[$val] | $mask;
						}
					}
				}
			}

			//Sharing is on, and nobody has no permission on this dir
			if (!$set_perms && $this->formvar['shared'])
			{
				$set_perms[0] = 0;
			}

			$vfs_sharing =& CreateObject('phpgwapi.vfs_sharing');
			$vfs_sharing->set_permissions(array($this->formvar['file_id'] => $set_perms));
			
			$this->bo->vfs->set_attributes(array(
				'string' => $oldpath,
				'attributes' => array(
					'shared' => ($this->formvar['shared'])?'Y':'N',
					'comment' => $this->formvar['comment'],
					'proper_id' => $this->formvar['proper_id']),
				'relatives' => array(RELATIVE_ROOT)
			));


			#renaming
			if ($this->bo->move($oldpath,$newpath)) 
			{

				# Destination redirection
				if ($this->apply)
				{
					unset($this->files[0]);
					$this->files[0] = dirname($this->formvar['full_filename']).'/'.$this->formvar['filename'];
					$this->menuaction='filescenter.ui_fm2.properties';
					$this->properties();
				}
				elseif($this->ok)
				{
							echo '<html><body onLoad="window.opener.location.reload();window.close();"></body></html>';
//					header('Location: '.$this->return_to_path);
					$GLOBALS['egw']->common->egw_exit();
				}
			}
			else
			{
				echo "error"; //FIXME ERROR
			}
		}

		/**
		  *  Treats the Upload form commit
		  *
										Intimately bound to the $this->upload method
		  * @author Vinicius Cubas Brand
		 */
		function upl_commit()
		{
			$this->bo->fileUpload($this->path,$GLOBALS['_FILES'],$GLOBALS['_POST']);
			echo '<html><body onLoad="window.opener.fcFolderView.refresh({ \'window\':window});">'.lang('Wait. Uploading...').'</body></html>';
//			header('Location: '.$this->return_to_path);
			$GLOBALS['egw']->common->egw_exit();

		}

		function view()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$this->bo->view($this->path);
		}

		/**
		  *  Parses the path tree, returning it
		  *
		  * @author Vinicius Cubas Brand
		 */
		function parsed_tree_path($bo_tree,$tree_name='d')
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			/* Catalogue Tree */

			$mainFolderImageDir = substr($GLOBALS['egw']->common->image('phpgwapi','foldertree_line.gif'),0,-19);
			$parsed_tree = '<script type="text/javascript">'."\n".$tree_name.' = new dTree(\''.$tree_name.'\',\''.$mainFolderImageDir.'\');'."\n".$tree_name.'.config.inOrder=true;'."\n".$tree_name.'.config.closeSameLevel=true; '."\n".$tree_name.'.config.useCookies=false;  '."\n";
			$parsed_tree .= $this->convert_tree($bo_tree, $mainFolderImageDir, $tree_name,'0',$tree_state);
			$parsed_tree .= 'document.write('.$tree_name.');'."\n".$tree_name.'.openTo(\'0\',\'true\');'."\n".'</script>';

			return $parsed_tree;
		}

		/**
		  *  Recursive Helper for parsed_tree_path
		  *
		  *    Raphael Derosso Pereira, Vinicius Cubas Brand
		 */
		function convert_tree($tree, &$iconDir, $tree_name, $parent='0',&$tree_state)
		{

			#javascript syntax:
			#Node(id, pid, name, url, urlClick, urlOut, title, target, icon, iconOpen, open)

			$new = null;
			$icon = 'null';
			$iconOpen = 'null';			

			#will do at first time
			if ($parent === '0')
			{
				if ($tree['root']->icon)
				
				{
					$icon = "'".$tree['root']->icon."'";
					$iconOpen = "'".$tree['root']->icon."'";
				}					

				$link = ($tree['root']->path) ? "'".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$tree['root']->path))."'" : 'null';
				$new .= $tree_name.'.add(\''.$parent.'\',\'-1\',\''.$tree['root']->name.'\','.$link.',null,null,null,null,'.$icon.','.$iconOpen.');'."\n";
								$tree =& $tree['root']->contents;
			}
			
			foreach ($tree as $id => $value)
			{
								$path = $value->path;

				if ($path)
				{
					$location = ($value->location)?'&location='.$value->location:'';
					$link = "'".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$path)).$location."'";
				}
				else
				{
					$link = 'null';
				}


				$icon = 'null';
				$iconOpen = 'null';
				if ($value->icon)
				{
					$icon = "'".$value->icon."'";
					$iconOpen = "'".$value->icon."'";
				}					

				$open_node_string = $tree_name.'.add(\''.$parent.'_'.$id.'\',\''.$parent.'\',\''.str_replace('\'','\\\'',$value->name).'\','.$link.',null,null,null,null,'.$icon.','.$iconOpen.',true);'."\n";
				$closed_node_string = $tree_name.'.add(\''.$parent.'_'.$id.'\',\''.$parent.'\',\''.str_replace('\'','\\\'',$value->name).'\','.$link.',null,null,null,null,'.$icon.','.$iconOpen.');'."\n";

				if (strpos($this->path,$path) === 0 || ($value->act_as && (strpos($this->path,$value->act_as) === 0))) //force opened
				{
					$node_string = $open_node_string;
					$tree_state=true;
				}
				else
				{
					$node_string = $closed_node_string;
				}

				if (is_array($value->contents))
				{
					$r_open = false;
					$ret = $this->convert_tree($value->contents,$iconDir,$tree_name,$parent.'_'.$id,$r_open);
					if ($r_open)
					{
						$tree_state = true;
						$new .= $open_node_string . $ret;
					}
					else
					{
						$new .= $node_string . $ret;
					}
					continue;
				}
				
				$new .= $node_string;
			}

			return $new;
		}

		function history()
		{
			$GLOBALS['egw_info']['flags']['currentapp'] = 'filescenter';
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			$fields = array(
				'operation'      => lang('Operation'),
				'version'        => lang('Version'), 
				'modified'       => lang('Modified'),
				'modifiedby_id'  => lang('Modificator'),
				'comment'        => lang('Comment'),
				'backup_file_id' => lang('Backup')
			);

			$file_journal = $this->bo->vfs->get_journal($this->path);

			$this->t->set_file(array('history' => 'history.tpl'));
			$this->t->set_block('history','main');
			$this->t->set_block('main','h_line'); //header
			$this->t->set_block('main','tbl_body');
			$this->t->set_block('tbl_body','b_line');

			//Parses the table header
			foreach ($fields as $key => $val)
			{
				$this->t->set_var('tdopts','');
				$this->t->set_var('tdcontent',$val);

				$this->t->parse('tmp_hline','h_line',true);
			}

			$this->t->set_var('h_line',$this->t->get_var('tmp_hline'));

			$b_line = $this->t->get_var('b_line');

			//Parses the table body
			foreach ($file_journal as $jnum => $journal)
			{

				$tdopts = ($i++ % 2) ? ' class="row_off"' : ' class="row_on"';

				//parses a line
				foreach ($fields as $key => $val)
				{
					switch($key)
					{
						case 'operation':
							switch ($journal[$key])
							{
								case VFS_OPERATION_CREATED:
									$display = lang('Created');
									break;
								case VFS_OPERATION_EDITED:
									$display = lang('Edited');
									break;
								case VFS_OPERATION_EDITED_COMMENT:
									$display = lang('Edited Comment');
									break;
								case VFS_OPERATION_COPIED:
									$display = lang('Copied');
									break;
								case VFS_OPERATION_MOVED:
									$display = lang('Moved');
									break;
								case VFS_OPERATION_DELETED:
									$display = lang('Deleted');
									break;
								default:
									$display = lang('Other');
							}
							break;
						case 'modifiedby_id':
							$GLOBALS['egw']->accounts->get_account_name($journal[$key],$lid,$fname,$lname);
							$display = $fname." ".$lname;
							break;
						case 'backup_file_id':
							if (is_numeric($journal[$key]) && $journal[$key] != 0)
							{
								$link = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.view&'.$this->eprintf('path='.$this->bo->vfs->get_path_from_id($journal[$key])));
								$display = "<A HREF=\"$link\">Download</A>";
							}
							else $display = '';
							break;
					

						//case 'comment':     
						default:
							$display = $journal[$key];
					}

					$this->t->set_var('tdopts',$tdopts);
					$this->t->set_var('tdcontent',$display);
					
					$this->t->parse('tmp_b_line','b_line',true);
				}

				$this->t->set_var('b_line',$this->t->get_var('tmp_b_line'));
				$this->t->parse('tmp_tblbody','tbl_body',true);

				$this->t->set_var('b_line',$b_line);
				$this->t->set_var('tmp_b_line','');
				
			}			

			$this->t->set_var('tbl_body',$this->t->get_var('tmp_tblbody'));

			$currdir = dirname($this->path);

			//other vars
			$this->t->set_var('path',$this->path);
			$this->t->set_var('lang_path',lang('Location'));
			$this->t->set_var('lang_file_history',lang('File History'));

			$this->t->set_var('lang_backb',lang('Back'));
			$this->t->set_var('link_back',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$currdir)));

			$this->t->parse('main','main');


			$this->display_app_header();
			
			$this->t->pparse('out','history');

			$this->display_app_footer();

		}

		/**
		  *  Creates the file sharing screen
		  *
		  * @author    Vinicius Cubas Brand 
				  * #FIXME redo this function to transfer vfs_sharing access to bo
		  */
		function sharing()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$GLOBALS['egw_info']['flags']['currentapp'] = 'filescenter';
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			$this->t->set_file(array('sharing' => 'sharing.tpl'));
			$this->t->set_block('sharing','shared_folder','shared_folder');
			$this->t->set_block('sharing','other_shared_folder','other_shared_folder');

			$vfs_sharing =& CreateObject('phpgwapi.vfs_sharing');


			$my_shares = $vfs_sharing->get_shares($this->user_info['account_id'],true);
			
			if ($my_shares)
			{
				foreach ($my_shares as $share)
				{
					$this->t->set_var('foldername','<A HREF="'.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$share['directory'].'/'.$share['name'])).'">'.$share['directory'].'/'.$share['name'].'</A>');

					$this->t->parse('tmp_sharing','shared_folder',true);
				}
				$this->t->set_var('shared_folder',$this->t->get_var('tmp_sharing'));
			}
			else
			{
				$this->t->set_var('foldername',lang('No shared folders.'));

			}

			$other_shares = $vfs_sharing->get_shares($this->user_info['account_id'],false);

			if ($other_shares)
			{
				foreach ($other_shares as $share)
				{
					$this->t->set_var('foldername2','<A HREF="'.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$share['directory'].'/'.$share['name'])).'">'.$share['directory'].'/'.$share['name'].'</A>');

					$this->t->parse('tmp_sharing2','other_shared_folder',true);
				}
				$this->t->set_var('other_shared_folder',$this->t->get_var('tmp_sharing2'));
			}
			else
			{
				$this->t->set_var('foldername2',lang('No shared folders.'));

			}
			

			$this->t->set_var('css_dir',$this->tpl_root.'/css');
			$this->t->set_var('lang_shared_folders',lang('My Shared Folders'));
			$this->t->set_var('lang_other_shared_folders',lang('External Shared Folders'));

			$this->display_app_header();

			$this->t->pparse('out','sharing');

			$this->display_app_footer();
			
		}

		/**
		  *  Creates the file search screen
		  *
		  * @author    Vinicius Cubas Brand 
		  */
		function search()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$GLOBALS['egw_info']['flags']['currentapp'] = 'filescenter';
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			$this->t->set_file(array('search' => 'search.tpl'));
			$this->t->set_block('search','search_results','search_results');
			$this->t->set_block('search_results','file','file');

			if ($this->keyword)
			{
				$files = $this->bo->search($this->keyword,$this->user_info['account_id']);
				foreach ($files as $file_id => $file)
				{
					$this->t->set_var('filename','<A HREF="'.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.view&'.$this->eprintf('path='.$file['directory'].'/'.$file['name'])).'">'.$file['directory'].'/'.$file['name'].'</A>');
					$this->t->parse('tmp_fname','file',true);
				}
				$this->t->set_var('file',$this->t->get_var('tmp_fname'));
			}
			else //somente monta a tela
			{
				$this->t->set_var('search_results','');
			}

			$this->t->set_var('location_cancel',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index'));
			$this->t->set_var('form_action',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.search'));
			$this->t->set_var('lang_submit',lang('Search'));
			$this->t->set_var('lang_search',lang('Search'));
			$this->t->set_var('lang_search_results',lang('Search Results'));
			$this->t->set_var('lang_search_description',lang('Search for a keyword in files:'));
			$this->t->set_var('lang_cancel',lang('Cancel'));

			$this->display_app_header();

			$this->t->pparse('out','search');
			
			$this->display_app_footer();
		}

		/**
		  *  Creates the compress screen
		  *
		  * @author    Vinicius Cubas Brand 
		  */
		function compress()
		{

			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$phpgw_flags = Array(
//				'currentapp'    =>      'filescenter',
				'noheader'      =>      True,
				'nonavbar'      =>      True,
				'noappheader'   =>      True,
				'noappfooter'   =>      True,
				'nofooter'      =>      True
			);

			$GLOBALS['egw_info']['flags'] = array_merge($GLOBALS['egw_info']['flags'],$phpgw_flags);

			$files = get_var('files',array('GET','POST'));

			$this->get_prefix_selects($select_prefix0,$select_ptype0);
	
			if (!count($files))
			{
				header('Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path)));
				$GLOBALS['egw']->common->egw_exit();
			}
		
			
			$this->t->set_file(array('compress' => 'compress.tpl'));
			$this->t->set_block('compress','main');


			$this->t->set_var('path',$this->path);

			$this->t->set_var('lang_select_type',lang('Compression type'));
			$this->t->set_var('lang_select_name',lang('Archive name'));
			$this->t->set_var('lang_select_prefix',lang('File ID Prefix'));
			$this->t->set_var('lang_select_type',lang('File Type'));
			$this->t->set_var('lang_upload_anotherfile',lang('Add another file'));
			$this->t->set_var('lang_strremove',lang('remove'));
			$this->t->set_var('lang_operation',lang('Compress'));
			$this->t->set_var('lang_okb',lang('Compress'));
			$this->t->set_var('lang_cancelb',lang('Cancel'));

			$this->t->set_var('action_url',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.comp_commit'));
			$this->t->set_var('return_to_path',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path)));
			$this->t->set_var('files_list',urlencode(serialize($files)));
			$this->t->set_var('select_prefix',$select_prefix0);
			$this->t->set_var('select_ptype',$select_ptype0);


			$this->display_app_header();
			$this->t->pparse("out","main");
			$this->display_app_footer();

		}

		/**
		  *  Receives the compress screen results, placing result in BO
		  *
		  * @author    Vinicius Cubas Brand 
		  */
		function comp_commit()
		{
			$files = unserialize(urldecode($this->formvar['files_list']));
			$compression_type = $this->formvar['type'];
			$archname = $this->formvar['archname'];
			$prefix = get_var('prefix0',array('GET','POST'));
			$type = get_var('type0',array('GET','POST')); 
		
			$this->bo->fileCompress($files,$archname,$compression_type,$this->path,$prefix,$type);
			ECHO '<HTML><BODY onLoad="window.opener.location.reload();window.close();"></body></html>';
//			header('Location: '.$this->return_to_path);
			$GLOBALS['egw']->common->egw_exit();
		}

		/**
		  *  Decompresses a file in current dir
		  *
		  * @author    Vinicius Cubas Brand 

		  * will show file contents. User can check/uncheck file contents,
					  * and assign prefixes to them.
		  */
		function decompress()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			$source_filename = $this->path.'/'.basename($this->files[0]);
			$dest_path = $this->path;

			$this->bo->fileDecompress($source_filename,$dest_path);
			header('Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path)));
			$GLOBALS['egw']->common->egw_exit();
//			echo 'Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path));
		}


		/**
		  *  Manager for the custom fields
		  *
		  * @author    Vinicius Cubas Brand

		  * This function is in the wrong place. As I don't have yet
						a parent UI class with the features of this, I cannot
						create (yet) in little time a ui_custom with these
						features. You are encouraged to FIXME, if I(vcb) am not
						doing it right now.
		 */
		function custom_manager()
		{
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;


			if (!$this->base_dir_available)
			{
				return '';
			}
			
			//just administrator can have access to this
						if (!$GLOBALS['egw_info']['user']['apps']['admin'])
						{
								$GLOBALS['egw']->common->egw_exit();
						}

			//displayed fields
			$fields = array(
//				'customfield_id'          => lang('ID'),
				'customfield_name'        => lang('Name'),
				'customfield_description' => lang('Description'), 
				'customfield_type'        => lang('Type'),
				'customfield_precision'   => lang('Precision'),
				'customfield_active'      => lang('Active'),
				'operations'              => lang('Operations')
//				'backup_file_id'   => lang('Backup')
//				'src'            => lang('Source'),
//				'dest'           => lang('Destination')
			);

			$custom =& CreateObject('phpgwapi.vfs_customfields');

			//Preventing when user want to delete custom field 0
			if (is_numeric($this->delete)) 
			{
				$custom->remove_customfield($this->delete);
			}

			//if returning from an add or edit page
			switch ($this->formvar['operation'])
			{
				case 'custom_add':
					$custom->add_customfield(
						$this->formvar['data']['customfield_name'],
						$this->formvar['data']['customfield_description'],
						$this->formvar['data']['customfield_type'],
						$this->formvar['data']['customfield_precision'],
						$this->formvar['data']['customfield_active']);
					break;
				case 'custom_edit':
					$customfield_id = $this->formvar['data']['customfield_id'];
					unset($this->formvar['data']['customfield_id']);
					$custom->update_customfield($customfield_id,$this->formvar['data']);
					break;
			}

			$custom_fields = $custom->get_customfields('customfield_name',false);

			$this->t->set_file(array('page' => 'custom_manager.tpl'));
			$this->t->set_block('page','main');
			$this->t->set_block('main','h_line'); //header
			$this->t->set_block('main','tbl_body');
			$this->t->set_block('tbl_body','b_line');

			//Parses the table header
			foreach ($fields as $key => $val)
			{
				switch($key)
				{
					case 'operations':
						$this->t->set_var('tdopts',' colspan="2" ');
						$this->t->set_var('tdcontent',$val);
						break;
					default:
						$this->t->set_var('tdopts','');
						$this->t->set_var('tdcontent',$val);
				}

				$this->t->parse('tmp_hline','h_line',true);
			}

			$this->t->set_var('h_line',$this->t->get_var('tmp_hline'));

			$b_line = $this->t->get_var('b_line');
			//Parses the table body
			foreach ($custom_fields as $custom_name => $custom_info)
			{

				$tdopts = ($i++ % 2) ? ' class="row_off"' : ' class="row_on"';

				//parses a line
				foreach ($fields as $key => $val)
				{
					switch($key)
					{
						case 'customfield_active':
							$display = ($custom_info[$key]=='Y')?'Yes':'No';
							break;
						case 'operations':
							$link_edit= $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_edit&custom_id='.$custom_info['customfield_id']);

							$link_delete = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager&'.$this->eprintf('delete='.$custom_info['customfield_id']));

							$display = '<span class="lk3" onClick="location=\''.$link_edit.'\'">Edit</span></td>';
							$display .= '<td '.$tdopts.'><span class="lk3" onClick="custom_delete(\''.$link_delete.'\',\''.$custom_info['customfield_name'].'\')">Delete</span>';
							break;
						default:
							$display = $custom_info[$key];
					}

					$this->t->set_var('tdopts',$tdopts);
					$this->t->set_var('tdcontent',$display);
					
					$this->t->parse('tmp_b_line','b_line',true);
				}

				$this->t->set_var('b_line',$this->t->get_var('tmp_b_line'));
				$this->t->parse('tmp_tblbody','tbl_body',true);

				$this->t->set_var('b_line',$b_line);
				$this->t->set_var('tmp_b_line','');
				
			}			

			$this->t->set_var('tbl_body',$this->t->get_var('tmp_tblbody'));

			
			//other vars

			//javascript code

			$message1 = lang('Do you really want to delete the custom field');

			$message2 = lang('Are you sure? You can alternatively inactivate this field, so you can recover its information later. Really delete? (you cannot reverse this)');


			$javascript_code =	'			
				function custom_delete(link,name)
				{
					var res2;
					var res = confirm("'.$message1.' \"" + name + "\"?");

					if (res)
					{
						res2 = confirm("'.$message2.'");

						if (res2)
						{
							location = link;
						}
					}
				}
				';
			
			$this->t->set_var('lang_custom_fields',lang('Custom File Properties'));
			$this->t->set_var('lang_page_description',lang('Here is the management of custom file properties.'));


			$this->t->set_var('custom_add_link',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_add'));
			$this->t->set_var('lang_custom_add_text','Add new custom file property');
			$this->t->set_var('javascript_code',$javascript_code);


			$this->t->parse('main','main');

			$this->display_app_header();
			
			$this->t->pparse('out','page');

			$this->display_app_footer();

		}

		/**
		  *  Handler of the operation edit of custom fields
		  *
		  * @author    Vinicius Cubas Brand

		  * In the wrong place too...
		 */
		function custom_edit()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			//just administrator can have access to this
						if (!$GLOBALS['egw_info']['user']['apps']['admin'])
						{
								$GLOBALS['egw']->common->egw_exit();
						}


			$custom =& CreateObject('phpgwapi.vfs_customfields');

			$custom_fields = $custom->get_customfields('customfield_id');

			$custom_info = $custom_fields[$this->custom_id];

			$this->t->set_file(array('page' => 'custom_edit.tpl'));
			$this->t->set_block('page','main');
			$this->t->set_block('main','h_line'); //header
			$this->t->set_block('main','tbl_body');
			$this->t->set_block('tbl_body','b_line');

			//Parses the table header
			$this->t->set_var('tdopts',' colspan="2" ');
			$this->t->set_var('tdcontent',lang('Edit File Property'));
			$this->t->parse('tmp_hline','h_line',true);

			$this->t->set_var('h_line',$this->t->get_var('tmp_hline'));

			$b_line = $this->t->get_var('b_line');
			//Parses the table body
			foreach ($this->customfields_attributes as $custom_name => $custom_value)
			{

				$tdopts = ($i++ % 2) ? ' class="row_off"' : ' class="row_on"';

				//begin: parses a line

				$this->t->set_var('tdopts',$tdopts);

				$this->t->set_var('tdcontent',$custom_value);
				$this->t->parse('tmp_b_line','b_line',true);

				switch($custom_name)
				{
					case 'customfield_name':
					case 'customfield_description':
					case 'customfield_precision':
						$this->t->set_var('tdcontent','<input type="text" name="formvar[data]['.$custom_name.']" value="'.addslashes($custom_info[$custom_name]).'">');
					
						break;
					case 'customfield_active':
						$this->t->set_var('tdcontent',"<select name=\"formvar[data][customfield_active]\">\n<option value=\"Y\">Yes</option>\n<option value=\"N\">No</option>\n</select>");
						break;
					default:
						$this->t->set_var('tdcontent',$custom_info[$custom_name]."<input type=\"hidden\" name=\"formvar[data][".$custom_name."]\" value=\"".addslashes($custom_info[$custom_name])."\">");
				}

				$this->t->parse('tmp_b_line','b_line',true);
				//end

				$this->t->set_var('b_line',$this->t->get_var('tmp_b_line'));
				$this->t->parse('tmp_tblbody','tbl_body',true);

				$this->t->set_var('b_line',$b_line);
				$this->t->set_var('tmp_b_line','');
				
			}			

			$this->t->set_var('tbl_body',$this->t->get_var('tmp_tblbody'));

			
			//other vars
			$form_name = 'form_edit';
			$commit_action = "document.$form_name.action='".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager')."';document.$form_name.submit();";
			$cancel_action = "location='".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager')."'";
			
			
			$this->t->set_var('lang_custom_fields',lang('Edit Custom File Property'));
			$this->t->set_var('lang_page_description',lang('Here you can edit this property.'));

			$this->t->set_var('javascript_code','');

			$this->t->set_var('lang_commit',lang('Save'));
			$this->t->set_var('lang_cancel',lang('Cancel'));

			$this->t->set_var('commit_action',$commit_action);
			$this->t->set_var('cancel_action',$cancel_action);

			$this->t->set_var('form_name',$form_name);

			$this->t->set_var('val_operation','custom_edit');

			$this->t->parse('main','main');

			$this->display_app_header();
			
			$this->t->pparse('out','page');

			$this->display_app_footer();

		
		}

		/**
		  *  Handler of the operation add of custom fields
		  *
		  * @author    Vinicius Cubas Brand

		  * In the wrong place too...
		 */
		function custom_add()
		{
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			if (!$this->base_dir_available)
			{
				return '';
			}
			
			//just administrator can have access to this
						if (!$GLOBALS['egw_info']['user']['apps']['admin'])
						{
								$GLOBALS['egw']->common->egw_exit();
						}


			$custom =& CreateObject('phpgwapi.vfs_customfields');

			$custom_fields = $custom->get_customfields('customfield_id');

			$custom_info = $custom_fields[$this->custom_id];

			$this->t->set_file(array('page' => 'custom_edit.tpl'));
			$this->t->set_block('page','main');
			$this->t->set_block('main','h_line'); //header
			$this->t->set_block('main','tbl_body');
			$this->t->set_block('tbl_body','b_line');

			//Parses the table header
			$this->t->set_var('tdopts',' colspan="2" ');
			$this->t->set_var('tdcontent',lang('Add File Property'));
			$this->t->parse('tmp_hline','h_line',true);

			$this->t->set_var('h_line',$this->t->get_var('tmp_hline'));

			$b_line = $this->t->get_var('b_line');

			$possible_types = array(
				'varchar',
				'number',
//				'timestamp',
				'text',
//				'longtext'
			);

			$sel = "<select name=\"formvar[data][customfield_type]\">\n";
			foreach($possible_types as $key => $val)
			{
				$sel.="<option value=\"$val\">$val</option>\n";
			}
			$sel .="</select>";
			
			//Parses the table body
			foreach ($this->customfields_attributes as $custom_name => $custom_value)
			{
				if ($custom_name == 'customfield_id')
				{
					continue;
				}

				$tdopts = ($i++ % 2) ? ' class="row_off"' : ' class="row_on"';

				//begin: parses a line

				$this->t->set_var('tdopts',$tdopts);

				$this->t->set_var('tdcontent',$custom_value);
				$this->t->parse('tmp_b_line','b_line',true);

				switch($custom_name)
				{
					case 'customfield_type':
						$this->t->set_var('tdcontent',$sel);
						break;
					case 'customfield_active':
						$this->t->set_var('tdcontent',"<select name=\"formvar[data][customfield_active]\">\n<option value=\"Y\">Yes</option>\n<option value=\"N\">No</option>\n</select>");
						break;
					default:
						$this->t->set_var('tdcontent','<input type="text" name="formvar[data]['.$custom_name.']" value="">');
				}

				$this->t->parse('tmp_b_line','b_line',true);
				//end

				$this->t->set_var('b_line',$this->t->get_var('tmp_b_line'));
				$this->t->parse('tmp_tblbody','tbl_body',true);

				$this->t->set_var('b_line',$b_line);
				$this->t->set_var('tmp_b_line','');
				
			}			

			$this->t->set_var('tbl_body',$this->t->get_var('tmp_tblbody'));

			
			//other vars
			$form_name = 'form_add';
			$commit_action = "document.$form_name.action='".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager')."';document.$form_name.submit();";
			$cancel_action = "location='".$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.custom_manager')."'";
			
			
			$this->t->set_var('lang_custom_fields',lang('Add Custom File Property'));
			$this->t->set_var('lang_page_description',lang('Here you can edit this property.'));

			$this->t->set_var('javascript_code','');

			$this->t->set_var('lang_commit',lang('Save'));
			$this->t->set_var('lang_cancel',lang('Cancel'));

			$this->t->set_var('commit_action',$commit_action);
			$this->t->set_var('cancel_action',$cancel_action);

			$this->t->set_var('form_name',$form_name);

			$this->t->set_var('val_operation','custom_add');

			$this->t->parse('main','main');

			$this->display_app_header();
			
			$this->t->pparse('out','page');

			$this->display_app_footer();
		}

		/**
		  * Creates the mime management screen
		  *
		  * @author   Vinicius Cubas Brand
		 */
		function mime_manager()
		{
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;


			$fieldnames = array(
				'mime_id'    => lang('ID'),
				'extension'  => lang('Extension'),
				'mime'       => lang('Mime Type'),
//				'mime_magic' => lang('Mime Magic'),
				'friendly'   => lang('Friendly Description'),
//				'image'      => lang('Icon'),
//				'proper_id'  => lang('Type Identifier for File IDs')
				);
				
			$fieldalign = array(
				'mime_id'    => 'right',
				'extension'  => 'center',
				'mime'       => 'center',
				'mime_magic' => 'center',
				'friendly'   => 'center',
				'image'      => 'center',
				'proper_id'  => 'center'
				);

			$vfs_mimetypes =& CreateObject('phpgwapi.vfs_mimetypes');

			if ($this->formvar['mime_id']) //Edit
			{
				if ($image_file = $GLOBALS['_FILES']['formvar']['tmp_name']['image'])
				{
					$this->formvar['image'] = file_get_contents($image_file);
				}

				$vfs_mimetypes->edit_filetype($this->formvar);
			}
			elseif ($this->formvar['extension']) //Add
			{
				if ($image_file = $GLOBALS['_FILES']['formvar']['tmp_name']['image'])
				{
					$this->formvar['image'] = file_get_contents($image_file);
				}

				$vfs_mimetypes->add_filetype($this->formvar,false,true);
			}
			elseif($this->formvar['action'] == 'delete')
			{
				$vfs_mimetypes->delete_filetype($this->formvar['type']);
			}
		
			$this->t->set_unknowns('remove');

			$this->t->set_file(array(
				'page' => 'mime_manager.tpl'
			));

			$this->t->set_block('page','mime_row');
			$this->t->set_block('page','mime_table');
			$this->t->set_block('page','mime_data');

			
			$types = $vfs_mimetypes->get_filetypes();


			//Parse header
			foreach ($fieldnames as $fieldname => $fieldtitle)
			{
				$this->t->set_var('tropts','');
				$this->t->set_var('tdopts','align="center"');
				$this->t->set_var('tdcontent',"<b>".$fieldtitle."</b>");
				
				$this->t->parse('tabledatas','mime_data',true);
			}
			$this->t->parse('tablerows','mime_row','true');


			$link_onclick = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_edit');
			
			$i=0;
			foreach ($types as $type)
			{
				$row_class = ($i%2)?'row_on':'row_off';


				$this->t->set_var('tropts','class="'.$row_class.'" onclick="window.location=\''.$link_onclick.'&'.urlencode('formvar[id]').'=\' + this.id" onmouseover="this.className=\'mouse_over_me\';" onmouseout="this.className=\''.$row_class.'\'"');
				$this->t->set_var('tabledatas','');
				$this->t->set_var('tr_id',$type['mime_id']);

				foreach ($fieldnames as $fieldname => $fieldtitle)
				{
					$this->t->set_var('tdopts','align="'.$fieldalign[$fieldname].'"');
					switch($fieldname)
					{
						case 'image':
							$this->t->set_var('tdcontent','<img src="'.$GLOBALS['egw']->link('/filescenter/icon.php','extension='.$type['extension']).'">');
							break;
						default:
							$this->t->set_var('tdcontent',$type[$fieldname]);
					}
					
					$this->t->parse('tabledatas','mime_data',true);
				}
				$i++;

				$this->t->parse('tablerows','mime_row','true');
			}

			$this->t->set_var('tableopts','width="90%"');
			$this->t->set_var('lang_page_description',lang('File Types Management'));
			$this->t->set_var('lang_page_instructions',lang('Click in the row to edit a file type.'));

			$this->t->set_var('link_add_file_type','');
			$this->t->set_var('lang_add_instructions',lang('Add a new file type'));
			$this->t->set_var('lang_value_addb',lang('Add a new file type'));

			$this->t->set_var('link_add_file_type',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_edit'));
			$this->t->set_var('hidden_fields','');
			


			$this->display_app_header();
			
			$this->t->pparse('out','mime_table');

			$this->display_app_footer();

		}

		function mime_edit()
		{
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			$fieldnames = array(
				'mime_id'    => lang('ID'),
				'extension'  => lang('Extension'),
				'mime'       => lang('Mime Type'),
//				'mime_magic' => lang('Mime Magic'),
				'friendly'   => lang('Friendly Description'),
				'image'      => lang('Icon'),
//				'proper_id'  => lang('Type Identifier for File IDs')
				);

			$fieldprecision = array(
				'extension'  =>  10,
				'mime'       =>  50,
				'mime_magic' => 255,
				'friendly'   =>  50,
				'proper_id'  =>   4
				);
				
			$this->t->set_unknowns('remove');

			$this->t->set_file(array(
				'page' => 'mime_edit.tpl'
			));

			$this->t->set_block('page','mime_row');
			$this->t->set_block('page','mime_table');
			$this->t->set_block('page','mime_data');

			$vfs_mimetypes =& CreateObject('phpgwapi.vfs_mimetypes');
			
			$type = $vfs_mimetypes->get_type(array(
				'mime_id' => $this->formvar['id']
				));

			if (!$this->formvar['id']) //Operation Add
			{
				unset($fieldnames['mime_id']);

				$this->t->set_var('lang_page_instructions',lang('Register a new file type:'));
				$this->t->set_var('button_delete','');
			}
			else //Operation Edit
			{
				$this->t->set_var('lang_page_instructions',lang('Edit file type:'));
				$this->t->set_var('button_delete','<input type="button" value="'.lang('Delete this file type').'" onclick="window.location=\''.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_manager&'.urlencode('formvar[action]').'=delete&'.urlencode('formvar[type]').'='.$type['mime_id']).'\'">');
			}

			$i=0;
			foreach ($fieldnames as $fieldname => $fieldtitle)
			{
				$row_class = ($i%2)?'row_on':'row_off';

				$this->t->set_var('tropts','class="'.$row_class.'"');

				//parse 1st col
				$this->t->set_var('tdopts','align="right" width="40%"');
				$this->t->set_var('tdcontent','<b>'.$fieldtitle.':</b>&nbsp;&nbsp;');
				$this->t->parse('tabledatas','mime_data',false);
					
				//parse 2nd col
				$this->t->set_var('tdopts','align="left"');
				
				switch($fieldname)
				{
					case 'image':
						$this->t->set_var('tdcontent','<img src="'.$GLOBALS['egw']->link('/filescenter/icon.php','extension='.$type['extension']).'"><br>Change:<input type="file" name="formvar[image]">');
						break;
					case 'mime_id':
						$this->t->set_var('tdcontent',$type[$fieldname]);
						break;
					default:
						$this->t->set_var('tdcontent','<input name="formvar['.$fieldname.']" value="'.$type[$fieldname].'" maxlength="'.$fieldprecision[$fieldname].'">');
				}
				
				$this->t->parse('tabledatas','mime_data',true);
				$i++;

				$this->t->parse('tablerows','mime_row','true');
			}

			$this->t->set_var('tableopts','width="90%"');
			$this->t->set_var('lang_page_description',lang('File Types Management'));

			$this->t->set_var('lang_but_cancel',lang('Cancel'));
			$this->t->set_var('lang_but_submit',lang('Save'));

			$this->t->set_var('cancel_url',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_manager'));
			$this->t->set_var('hidden_fields','<input type="hidden" name="formvar[mime_id]" value="'.$type['mime_id'].'">');
			$this->t->set_var('form_action',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.mime_manager'));


			$this->display_app_header();
			
			$this->t->pparse('out','mime_table');

			$this->display_app_footer();

		}

		function prefix_manager()
		{
			$GLOBALS['egw_info']['flags']['noheader'] = False;
			$GLOBALS['egw_info']['flags']['nonavbar'] = False;
			$GLOBALS['egw_info']['flags']['nofooter'] = False;
			$GLOBALS['egw_info']['flags']['noappheader'] = False;
			$GLOBALS['egw_info']['flags']['enable_browser_class'] = True;

			if (!$this->base_dir_available)
			{
				return '';
			}
			

			$vfs_prefixes =& CreateObject('phpgwapi.vfs_prefixes');

			if ($this->formvar['id'] && ($this->ftask == 'edit'))
			{
				$prefix = $vfs_prefixes->get(array('prefix_id'=>$this->formvar['id']));
			}

			$this->t->set_unknowns('remove');

			switch ($this->ftask)
			{
			/* ********************************************************** /
			/  HERE BEGINS THE CREATE/EDIT SECTION                        /
			/ ********************************************************** */


			case 'create': //Creates a new File ID Prefix
			case 'edit':   //Edits an existing File ID Prefix

				if ($this->formvar['type'] != 't')
				{
					$this->formvar['type'] = 'p';
					$main_msg  = 'prefix for file ID';
					$row_1_msg = lang('File ID Prefix');
					$row_2_msg = lang('Prefix Description');
				}
				else
				{
					$main_msg = 'file type';
					$row_1_msg = lang('File Type');
					$row_2_msg = lang('Type Description');
				}
			
				$this->t->set_file(array(
					'page' => 'prefix_create.tpl'
				));

				$this->t->set_block('page','gen_row');
				$this->t->set_block('page','gen_table');
				$this->t->set_block('page','gen_data');

				//Creates the file prefix row

				$this->t->set_var('tdopts','align="right" width="40%"');
				$this->t->set_var('tdcontent','<b>'.$row_1_msg.':</b>&nbsp;&nbsp;');
				$this->t->parse('tabledatas','gen_data',false);

				$this->t->set_var('tdopts','align="left"');
				$this->t->set_var('tdcontent','<input name="formvar[prefix]" value="'.$prefix['prefix'].'" maxlength="8">');
				$this->t->parse('tabledatas','gen_data',true);

				$this->t->set_var('tropts','class="row_on"');
				$this->t->parse('tablerows','gen_row','true');

				//Creates the file description row

				$this->t->set_var('tdopts','align="right" width="40%"');
				$this->t->set_var('tdcontent','<b>'.$row_2_msg.':</b>&nbsp;&nbsp;');
				$this->t->parse('tabledatas','gen_data',false);

				$this->t->set_var('tdopts','align="left"');
				$this->t->set_var('tdcontent','<input name="formvar[prefix_description]" value="'.$prefix['prefix_description'].'" maxlength="30">');
				$this->t->parse('tabledatas','gen_data',true);

				$this->t->set_var('tropts','class="row_off"');
				$this->t->parse('tablerows','gen_row','true');

				//Now the permission selection row 

				$prefix_permissions = $vfs_prefixes->get_permissions(array('prefix_id'=>$prefix['prefix_id']));

				$groups = $GLOBALS['egw']->accounts->get_list('groups');

				$users = $GLOBALS['egw']->accounts->get_list('accounts');


				$this->t->set_var('tdopts','align="right" width="40%"');
				$this->t->set_var('tdcontent','<b>'.lang('View Permissions - Users').':</b>&nbsp;&nbsp;');
				$this->t->parse('tabledatas','gen_data',false);

				$this->t->set_var('tdopts','align="left"');
				$this->t->set_var('tdcontent',$this->prefix_perms_select('u',$prefix_permissions));
				$this->t->parse('tabledatas','gen_data',true);

				$this->t->set_var('tropts','class="row_on"');
				$this->t->parse('tablerows','gen_row','true');

				/* ---- */

				$this->t->set_var('tdopts','align="right" width="40%"');
				$this->t->set_var('tdcontent','<b>'.lang('View Permissions - Groups').':</b>&nbsp;&nbsp;');
				$this->t->parse('tabledatas','gen_data',false);

				$this->t->set_var('tdopts','align="left"');
				$this->t->set_var('tdcontent',$this->prefix_perms_select('g',$prefix_permissions));
				$this->t->parse('tabledatas','gen_data',true);

				$this->t->set_var('tropts','class="row_off"');
				$this->t->parse('tablerows','gen_row','true');


				/* --- */

				if ($this->ftask == 'edit')
				{
					$this->t->set_var('lang_but_submit',lang('Edit'));
					$this->t->set_var('lang_page_description',lang('Edit '.$main_msg));
					$this->t->set_var('lang_page_instructions',lang('Change the values here to redefine this '.$main_msg.'.'));
					$this->t->set_var('hidden_fields','<input type="hidden" name="formvar[prefix_id]" value="'.$prefix['prefix_id'].'">');
				}
				else
				{
					$this->t->set_var('lang_but_submit',lang('Create'));
					$this->t->set_var('lang_page_description',lang('Create '.$main_msg));
					$this->t->set_var('lang_page_instructions',lang('Fill the fields here to define a new '.$main_msg.'.'));

					$this->t->set_var('hidden_fields','<input type="hidden" name="formvar[prefix_type]" value="'.$this->formvar['type'].'">');
				}

				$link_a = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager&ftask=manage');

				$this->t->set_var('cancel_url',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager'));

				$this->t->set_var('form_action',$link_a);

				break;

			/* ********************************************************** /
			/  HERE ENDS THE CREATE/EDIT SECTION                        /
			/ ********************************************************** */

			case 'manage':
			default:

			/* ********************************************************** /
			/  HERE BEGINS THE MANAGER SECTION                            /
			/ ********************************************************** */

			$fieldnames = array(
//				'prefix_id'          => lang('ID'),
				'prefix'             => lang('Prefix'),
				'prefix_description' => lang('Description'),
				);
				
			$fieldalign = array(
				'prefix_id'          => 'right',
				'prefix'             => 'center',
				'prefix_description' => 'center'
				);

			$fieldsize = array(
				'prefix'             => '25%',
				'prefix_description' => '75%'
				);

			$vfs_prefixes =& CreateObject('phpgwapi.vfs_prefixes');

			if (!$this->formvar['select_u']) $this->formvar['select_u'] = array();
			if (!$this->formvar['select_g']) $this->formvar['select_g'] = array();

			$perms = array_merge($this->formvar['select_u'],$this->formvar['select_g']);

			$perms = array_diff(array_unique($perms),array(-1));

			if ($this->formvar['prefix_id']) //Edit
			{
				$vfs_prefixes->edit($this->formvar);

				$vfs_prefixes->update_permissions($this->formvar['prefix_id'],$perms);

				//TODO treat permissions
			}
			elseif ($this->formvar['prefix']) //Add
			{
				$prefix_id = $vfs_prefixes->add($this->formvar);

				if ($prefix_id)
				{
					$vfs_prefixes->update_permissions($prefix_id,$perms);
				}
			}
			elseif($this->formvar['action'] == 'delete')
			{
				$vfs_prefixes->remove($this->formvar['prefix_id']);

				$vfs_prefixes->update_permissions($prefix_id,array());
			}

			$this->t->set_unknowns('remove');

			$this->t->set_file(array(
				'page' => 'prefix_manager.tpl'
			));

			$this->t->set_block('page','gen_row');
			$this->t->set_block('page','gen_table');
			$this->t->set_block('page','gen_data');
			$this->t->set_block('page','table_section');

			//Gets all prefixes current user owns, and are file id prefixes (not types)
			$prefixes = $vfs_prefixes->get_prefixes('owns');
			//$types = $vfs_mimetypes->get_filetypes();

			//Parse header
			foreach ($fieldnames as $fieldname => $fieldtitle)
			{
				$this->t->set_var('tropts','');
				$this->t->set_var('tdopts','align="center" width="'.$fieldsize[$fieldname].'"');
				$this->t->set_var('tdcontent',"<b>".$fieldtitle."</b>");
				
				$this->t->parse('tabledatas','gen_data',true);
			}
			$this->t->parse('tablerows','gen_row','true');


			$link_onclick = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager&ftask=edit');
			
			$i=0;
			foreach ($prefixes as $pref)
			{
				$row_class = ($i%2)?'row_on':'row_off';

				$this->t->set_var('tropts','class="'.$row_class.'" onclick="window.location=\''.$link_onclick.'&'.urlencode('formvar[id]').'=\' + this.id" onmouseover="this.className=\'mouse_over_me\';" onmouseout="this.className=\''.$row_class.'\'"');
				$this->t->set_var('tabledatas','');
				$this->t->set_var('tr_id',$pref['prefix_id']);

				foreach ($fieldnames as $fieldname => $fieldtitle)
				{
					$this->t->set_var('tdopts','align="'.$fieldalign[$fieldname].'" width="'.$fieldsize[$fieldname].'"');
					$this->t->set_var('tdcontent',$pref[$fieldname]);
					
					$this->t->parse('tabledatas','gen_data',true);
				}
				$i++;

				$this->t->parse('tablerows','gen_row','true');
			}

			$this->t->set_var('tableopts','width="90%"');
			$this->t->set_var('link_add_file_type',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager&ftask=create'));
			$this->t->set_var('lang_add_instructions',lang('Add a new prefix'));

			$this->t->parse('tbl_section','table_section',false);
	
			# ----------------------------------------------------------------------- #
			# Now the file prefixes section was parsed. Now is time of the file type. #
			# ----------------------------------------------------------------------- #

			$fieldnames['prefix'] = 'Type';

			//Gets all prefixes current user owns, and are file id prefixes (not types)
			$ftypes = $vfs_prefixes->get_prefixes('owns',false,'t');
			//$types = $vfs_mimetypes->get_filetypes();

			$this->t->set_var('tabledatas','');
			$this->t->set_var('tablerows','');

			//Parse header
			foreach ($fieldnames as $fieldname => $fieldtitle)
			{
				$this->t->set_var('tropts','');
				$this->t->set_var('tdopts','align="center" width="'.$fieldsize[$fieldname].'"');
				$this->t->set_var('tdcontent',"<b>".$fieldtitle."</b>");
				
				$this->t->parse('tabledatas','gen_data',true);
			}
			$this->t->parse('tablerows','gen_row','true');


			$link_onclick = $GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager&ftask=edit');
			
			$i=0;
			foreach ($ftypes as $pref)
			{
				$row_class = ($i%2)?'row_on':'row_off';

				$this->t->set_var('tropts','class="'.$row_class.'" onclick="window.location=\''.$link_onclick.'&'.urlencode('formvar[id]').'=\' + this.id" onmouseover="this.className=\'mouse_over_me\';" onmouseout="this.className=\''.$row_class.'\'"');
				$this->t->set_var('tabledatas','');
				$this->t->set_var('tr_id',$pref['prefix_id']);

				foreach ($fieldnames as $fieldname => $fieldtitle)
				{

					$this->t->set_var('tdopts','align="'.$fieldalign[$fieldname].'" width="'.$fieldsize[$fieldname].'"');
					$this->t->set_var('tdcontent',$pref[$fieldname]);
					
					$this->t->parse('tabledatas','gen_data',true);
				}
				$i++;

				$this->t->parse('tablerows','gen_row','true');
			}

			$this->t->set_var('tableopts','width="90%"');
			$this->t->set_var('link_add_file_type',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.prefix_manager&ftask=create&'.urlencode('formvar[type]').'=t'));
			$this->t->set_var('lang_add_instructions',lang('Add a new type descriptor for File IDs'));

			$this->t->parse('tbl_section','table_section',true);
			

			
			
			$this->t->set_var('lang_page_description',lang('File ID Management'));
			$this->t->set_var('lang_page_instructions',lang('Click in the row to edit an item.'));

			$this->t->set_var('lang_value_addb',lang('Add a new File ID prefix'));

			$this->t->set_var('hidden_fields','');

			/* ********************************************************** /
			/  HERE ENDS THE MANAGER SECTION                            /
			/ ********************************************************** */

			}

			$this->t->set_var('lang_but_cancel',lang('Cancel'));

			$this->display_app_header();
			
			$this->t->pparse('out','gen_table');

			$this->display_app_footer();

		}

		/**
		  *  Creates the html selects for permissions in prefixes
		  *
										Intimately bound to the $this->properties method
		  * @param $select_type string can be one of the {u,g}
		  * @author Vinicius Cubas Brand
		 */
		function prefix_perms_select($select_type,$user_list)
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			static $passed = false;
			static $groups;
			static $users;


			if (!$passed)
			{
				//all groups in the system
				$groups = $GLOBALS['egw']->accounts->get_list('groups');

				$users = $GLOBALS['egw']->accounts->get_list('accounts');
			
				$passed = true;
			}

			
			if ($select_type == 'u')
			{
				$source_rep =& $users;
			}
			elseif ($select_type == 'g')
			{
				$source_rep =& $groups;
			}

			$select = "<SELECT name=\"formvar[select_$select_type][]\" SIZE=6 WIDTH=\"60\" id=\"$select_type\" {disabled} MULTIPLE>\n";

			$select .= "<OPTION value=\"-1\">".lang('None')."</OPTION>";
			
			reset($source_rep);
			while(list($num,$accountinfo) = each($source_rep))
			{
				if ($accountinfo['account_id'] != $this->user_info['account_id'])
				{
					$selected = "";
					if (in_array($accountinfo['account_id'],$user_list))
					{
						$selected = " SELECTED";
					}
					
					$select .= "<OPTION value=\"".$accountinfo['account_id']."\"$selected>".$accountinfo['account_firstname']." ".$accountinfo['account_lastname']."</OPTION>";
				}
			}
			
			$select .= "</SELECT>";

			return $select;
		
		}


		function css()
		{

			switch ($this->menuaction)
			{
				case 'filescenter.ui_fm2.upload':
				case 'filescenter.ui_fm2.properties':
				case 'filescenter.ui_fm2.prop_commit':
				case 'filescenter.ui_fm2.mime_manager':
				case 'filescenter.ui_fm2.prefix_manager':
				
				$appCSS = 
			'th.activetab
			{
				color:#000000;
				background-color:#D3DCE3;
			}
			
			th.inactivetab
			{
				color:#000000;
				background-color:#E8F0F0;


			}

			.mouse_over_me
			{
				color:#000000;
				background-color:#B8C0FF;
				cursor: pointer;
				cursor: hand;
			}
			
			.td_left { } 
			.td_right { } 
			
			div.activetab{ 
				border: 0px solid rgb(153, 153, 153);
								left: 0px; 
								top: 25px; 
								width: 100%; 

				display:inline; 
						}
			
						div.inactivetab{ 

				border: 0px solid rgb(153, 153, 153);
								left: 0px; 
								top: 25px; 
								width: 100%; 

				display:none; 
			}

			.lk {
				color: #0000CC;
				text-decoration: underline;
				cursor: pointer;
				cursor: hand;
				white-space: nowrap;
			}
';

				break;

				case 'filescenter.ui_fm2.index':
					$appCSS = 

'			.lk2 {
				padding: 2px;
				font-weight: bold;
				font-size: 11px;
				color: #FFFFFF;
				text-decoration: none;
				cursor: pointer;
				cursor: hand;
				white-space: nowrap;
			}

			.lk2:hover {
					text-decoration: none;
					font-size: 11px;
				color: #000000;
			}
	
';

				case 'filescenter.ui_fm2.custom_manager':
					$appCSS = 

'			
			.lk3 {
				color: #0000CC;
				text-decoration: none;
				cursor: pointer;
				cursor: hand;
				white-space: nowrap;
			}

			.lk3:hover {
					text-decoration: underline;
			}
	
';


			
				default:
			}

			return $appCSS;
		}


		/**
		 * sets the default prefs, if they are not already set (on a per pref. basis)
		 *
		 * It sets a flag in the app-session-data to be called only once per session
		 */
		function check_set_default_prefs()
		{
			if (!$this->base_dir_available)
			{
				return '';
			}
			
			if (($set = $GLOBALS['egw']->session->appsession('default_prefs_set2','filescenter')))
			{
				return;
			}
			$GLOBALS['egw']->session->appsession('default_prefs_set2','filescenter','set');

			$default_prefs = $GLOBALS['egw']->preferences->default['filescenter'];

			$defaults = array(
				'vfs_backups'     => 5,
				//the fields that will be displayed
				'name'            => true,
				'mime_type'       => true,
				'size'            => true,
				'created'         => false,
				'modified'        => false,
				'owner'           => false,
				'createdby_id'    => false,
				'modifiedby_id'   => false,
				'app'             => false,
				'comment'         => false,
				'version'         => true,
				'proper_id'       => false,
				'upl_prefix'      => false,
				'upl_type'        => false
			);
			foreach($defaults as $var => $default)
			{
				if (!isset($default_prefs[$var]) || $default_prefs[$var] == '')
				{
					$GLOBALS['egw']->preferences->add('filescenter',$var,$default,'default');
					$need_save = True;
				}
			}
			if ($need_save)
			{
				$prefs = $GLOBALS['egw']->preferences->save_repository(False,'default');
				$this->prefs['filescenter'] = $prefs['filescenter'];
			}
			if ($this->prefs['filescenter']['send_updates'] && !isset($this->prefs['filescenter']['receive_updates']))
			{
				$this->prefs['filescenter']['receive_updates'] = $this->prefs['filescenter']['send_updates'];
				$GLOBALS['egw']->preferences->add('filescenter','receive_updates',$this->prefs['filescenter']['send_updates']);
				$GLOBALS['egw']->preferences->delete('filescenter','send_updates');
				$prefs = $GLOBALS['egw']->preferences->save_repository();
			}
		}


		//php will have this function in future versions. See php manual.
		function array_diff_key($array1,$array2)
		{
			foreach ($array2 as $key => $val)
			{
				unset($array1[$key]);
			}
			return $array1;
		}

		#Checks if using vfs2.
		function check_if_using_vfs2()
		{
			if ($GLOBALS['egw_info']['server']['file_repository'] != 'sql2')
			{
				$this->display_app_header();
				
				echo "Repository for files information is configured for some value other than vfs2. Unfortunately FilesCenter is now just compatible with vfs2, so please ask your administrator to reconfigure in setup/configuration.";

				$this->display_app_footer();
			}
			return false;
		}

		#Checks if there are files in vfs2. If not, checks if this is a new
		#install or must import data from vfs. If must import data, will see if
		#user has privileges
		function check_if_upgrade_needed()
		{
			if (!$this->bo->exist_records_vfs2())
			{
				if (!$this->bo->exist_records_vfs())
				{
					return false;
				}
				else
				{
					if ($GLOBALS['egw_info']['user']['apps']['admin'])
					{
						#Asks administrator if he wants to change from fm to fm2
						$this->display_app_header();

						$this->t->set_unknowns('remove');

						$this->t->set_file(array(
							'page' => 'import_wizard.tpl'
						));

						$this->t->set_block('page','wizard');

						$this->t->set_var('text_explaining_import_process',lang('This seems to be the first time you are using FilesCenter. The FilesCenter is the new file management tool for eGroupWare.<br><br>FilesCenter uses a new set of tables to store data about files. This allows a better management of files and some functionalities, like file versioning, mime types customization, different sharing methods and more. So, to use FilesCenter you will have to pass through an import process, where data about files in your system will be imported to this new set of tables. <b>After this import proccess, the old Filemanager must be disabled system-wide to preserve consistency about files</b>.<br><br>You are about to do this import procedure. You must know that this is <b> an one way procedure</b> and is made once, just in the first time the admin uses the FilesCenter. <b>BACKUP</b> of your data (database, as well the file repository folder in your system) is <b>VERY RECOMMENDED</b>. Remember that eGroupWare is a free software and offers <b>ABSOLUTELY NO WARRANTY</b> to any damage that could happen with your files. If you have doubts, just test this FilesCenter in a non-production instance of eGroupWare before installing it in this system.'));

						$this->t->set_var('lang_import_yes',lang('Yes, I am aware of the risks and I want to make this import procedure.'));

						$this->t->set_var('lang_import_no',lang('I do not want to do this import procedure now.'));

						$this->t->set_var('form_action',$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index'));
						$this->t->set_var('value_button_submit',lang('Proceed'));
						$this->t->pparse('out','wizard');

						$this->display_app_footer();

					}
					else
					{
						$this->display_app_header();
						
						echo "FILESCENTER NOT CONFIGURED. AN IMPORT MUST BE MADE. ASK ADMINISTRATOR TO MAKE IT BEFORE YOU CAN USE.";

						$this->display_app_footer();

					}
				}
			}
		}
		
		function import_vfs2()
		{
			if ($GLOBALS['egw_info']['user']['apps']['admin'])
			{
				$this->bo->import_vfs2();
			}
		}

		//returns a html table to infolog responding to a etemplate call
		function get_etemplate_links_api($content=array(),$sel_options=array(),$cell=array())
		{
			$app = $content['link_to']['to_app'];
			$id = $content['link_to']['to_id'];

			if (!$id)
			{
				$id = 'new';
			}

			return $this->get_files_table($id,$app);
		}

		//---------- Auxiliar functions --------------//
		function arr2js($options)
		{
			$first = true;
			foreach ($options as $key => $val)
			{
				if (!$first)
				{
					$options_str .= ',';
				}
				if (is_numeric($val))
				{
					$options_str .= "'$key': $val";
				}
				else
				{
					$options_str .= "'$key': '$val'";
				}
				$first = false;
			}
			return "{ $options_str }";
		}

		# -------------------------- RPC Functions -------------------------- #

		function rpc_new_folder($params)
		{
			if ($params['path'])
			{
								$params['path'] = ereg_replace('\/*$','',$params['path']);
								$exp_path = explode('/',$params['path']);
								$filename = array_pop($exp_path);
								$dirname = implode('/',$exp_path);

				if (($resp = $this->bo->createdir($dirname,urldecode($filename))) === true)
				{
					return array(
						'status' => 'ok'
						);
				}
				else
				{
					return array(
						'status' => 'fail',
						'msg' => $resp
						);
				}
			}
			else
			{
				return array(
					'status' => 'fail',
					'msg' => lang('Error in creating folder. Incorrect parameters.')
					);
			}
		}
		
		function rpc_delete($params)
		{
			$resp = $this->bo->delete($params['files']);

			if ($resp === false)
			{
				return array(
					'status' => 'fail',
					'msg' => lang('Delete unsuccessful. Selected files could not be deleted.')
					);
			}
			elseif ($resp === true)
			{
				return array(
					'status' => 'ok'
					);
			}
			else
			{
				return array(
					'status' => 'ok',
					'msg' => $resp
					);
			}
		}

		function rpc_clean_tmp_folder($params)
		{
			$application = $params['app'];
			if ($this->bo->clean_tmp_folder($application))
			{
				return array(
					'status' => 'ok'
					);
			}
			else
			{
				return array(
					'status' => 'fail',
					'msg' => lang('The temporary directory could not be deleted.')
					);
			}
		}

		/**
		 * Function: copy
		 *
		 *		Copies(or cuts) a set of files
		 */
		function rpc_copy($params)
		{
			switch($params['operation'])
			{
				case 'cut':
					if ($this->bo->cut($params['files']))
					{
						$ret = array(
							'status' => 'ok',
							'msg' => lang('%1 files were sucessfully cutted onto clipboard.',count($params['files']))
							);
					}
					break;
				case 'copy':
					if ($this->bo->copy($params['files']))
					{
						$ret = array(
							'status' => 'ok',
							'msg' => lang('%1 files were sucessfully copied onto clipboard.',count($params['files']))
							);
					}
					break;
			}
			return $ret;
		}

		/**
		 * Function: paste
		 *
		 *		Pastes a set of files
		 */
		function rpc_paste($params)
		{
			if (!$this->bo->paste($params['path']))
			{
				return array(
					'status' => 'fail',
					'msg' => lang('Could not paste files here.')
					);
			}
			else
			{
				return array(
						'status' => 'ok'
//						'msg' => lang('File(s) written sucessfully.')
					);
			}
		}

		/**
		 * Method: rpc_extract
		 *
		 *	Decompresses a file in current dir
		 *
		 * Proposal of enhancement: 
		 *
		 *	Shows a dialog with archive contents. User can check/uncheck file
		 *	contents, and assign prefixes to them.
		 */
		function rpc_extract($params)
		{
			$source_filename = $this->path.'/'.basename($params['archive']);
			$dest_path = $params['path'];

			$res = $this->bo->fileDecompress($source_filename,$dest_path);

			if ($res)
			{
				return array(
					'status' => 'ok'
					);
			}
			else
			{
				return array(
					'status' => 'fail',
					'msg' => lang('Could not extract files from archive.')
					);
			}
			
//			header('Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path)));
//			$GLOBALS['egw']->common->egw_exit();
//			echo 'Location: '.$GLOBALS['egw']->link('/index.php','menuaction=filescenter.ui_fm2.index&'.$this->eprintf('path='.$this->path));
		}

		function rpc_subscribe($params)
		{
			$default_values = array(
				'files' => array(),
				'operation' => 'subscribe'
				);
			$params = array_merge($default_values,$params);

			if ($this->bo->subscribe($params))
			{
				return array(
					'status' => 'ok',
					'msg' => ($params['operation'] == 'subscribe') ? lang('Subscribed successfully') :  lang('Unsubscribed successfully')
					);
			}
			else
			{
				return array(
					'status' => 'fail',
					'msg' => lang('Could not '.$params['operation'].' to files')
					);
			}
		}
	}


?>
