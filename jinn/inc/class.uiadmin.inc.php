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



	class uiadmin
	{

		var $public_functions = Array(
			'index' => True,
			'import_phpgw_jinn_site' => True,
			'add_edit_phpgw_jinn_sites' => True,
			'add_edit_phpgw_jinn_site_objects' => True,
			'browse_phpgw_jinn_sites' => True,
			'browse_phpgw_jinn_site_objects' => True,
			'del_phpgw_jinn_sites'=> True,
			'del_phpgw_jinn_site_objects' => True,
			'insert_phpgw_jinn_sites'=> True,
			'insert_phpgw_jinn_site_objects'=> True,
			'update_phpgw_jinn_sites'=> True,
			'update_phpgw_jinn_site_objects' => True,
			'access_rights'=> True,
			'set_access_rights_site_objects'=> True,
			'set_access_rights_sites'=> True,
			'save_access_rights_object'=> True,
			'save_access_rights_site'=> True,
			'export_site'=> True,
			'FIP_config'=> True,
			'SFP_config'=> True
		);

		var $app_title='jinn';
		var $bo;
		var $template;
		var $debug=False;
		var $browse;
		var $plugins;

		function uiadmin()
		{

			if(!$GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uijinn.index'));
				$GLOBALS['phpgw']->common->phpgw_exit();
			}

			//$GLOBALS['phpgw']->nextmatchs = CreateObject('phpgwapi.nextmatchs');
			$this->bo = CreateObject('jinn.bojinn');
			$this->template = $GLOBALS['phpgw']->template;
		}

		function save_sessiondata()
		{
			$data = array(
				'message' => $this->bo->message,
			);
			$this->bo->save_sessiondata($data);
		}

		/****************************************************************************\
		* public routines                                                            *
		\****************************************************************************/

		function add_edit_phpgw_jinn_site_objects()
		{
			$this->add_edit_record('phpgw_jinn_site_objects');
			$this->save_sessiondata();
		}

		function add_edit_phpgw_jinn_sites()
		{

			$this->add_edit_record('phpgw_jinn_sites');

			if ($GLOBALS[where_condition])
			{

				$new_where='parent_'.$GLOBALS[where_condition];

				$this->browse_record('phpgw_jinn_site_objects',$new_where);
			}

			$this->save_sessiondata();
		}
		function import_phpgw_jinn_site()
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			unset($GLOBALS['phpgw_info']['flags']['noappheader']);
			unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

			$GLOBALS['phpgw']->common->phpgw_header();

			$this->template->set_file(array(
				'header' => 'header.tpl',
				'import_form' => 'import.tpl',
			));

			$action=lang('Import JiNN-Site'.$table);
			$this->template->set_var('title',$this->app_title);
			$this->template->set_var('action',$action);
			$this->template->pparse('out','header');

			$this->admin_menu();

			// plan
			/*
			export_file uploaden als temp file
			controleren of er ongeldige instructie in bestand staan
			controleren of het bestand ok is en zonder problemen include
			bestand includen
			temp file unlinken
			arrays controleren
			nieuwe site aanmaken met array data
			status teruggeven en naar link naar site aanbieden

			*/
			//					var_dump($GLOBALS[HTTP_POST_FILES]);

			if (is_array($GLOBALS[HTTP_POST_FILES][importfile]))
			{


				$num_objects=0;
				$import=$GLOBALS[HTTP_POST_FILES][importfile];
				//	var_dump($GLOBALS[HTTP_POST_FILES][importfile]);



				@include($import[tmp_name]);
				if ($import_site && $checkbit)
				{
					//var_dump($import_site_objects);	


					while(list($key, $val) = each($import_site)) 
					{
						$data[] = array
						(
							'name' => $key,
							'value' => addslashes($val) // removed nl2br, now using plugins
						);

					}

					/* insert site */
					if ($new_site_id=$this->bo->so->insert_phpgw_data('phpgw_jinn_sites',$data))
					{
						if (is_array($import_site_objects))
						{
							foreach($import_site_objects as $object)
							{
								unset($data_objects);
								while(list($key2, $val2) = each($object)) 
								{
									if ($key2=='parent_site_id') $val2=$new_site_id;

									$data_objects[] = array
									(
										'name' => $key2,
										'value' => addslashes($val2) // removed nl2br, now using plugins
									);
										

								}
									if ($object_id[]=$this->bo->so->insert_phpgw_data('phpgw_jinn_site_objects',$data_objects))
									{
										$num_objects=count($object_id);
									} 

								//die(var_dump($data_objects));
								/* insert objects */

							}

						}



						echo lang('import was succesfull<br>Imported one site with '.$num_objects.' object(s)');
					}
					else
					{
						echo lang('import failed');
					}

					//var_dump($data);
				}






			}
			else
			{
				$this->template->set_var('form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiadmin.import_phpgw_jinn_site'));
				$this->template->set_var('lang_Select_JiNN_site_file',lang('Select JiNN site file'));
				$this->template->set_var('lang_submit_and_import',lang('submit and import'));
				$this->template->pparse('out','import_form');


			}





			//include('./Bolder_en_Plante.JiNN.php');	
			//var_dump($import_site_objects);

			$this->save_sessiondata();


		}

		function browse_phpgw_jinn_sites()
		{

			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			unset($GLOBALS['phpgw_info']['flags']['noappheader']);
			unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

			$GLOBALS['phpgw']->common->phpgw_header();

			$this->template->set_file(array('header' => 'header.tpl'));

			$action=lang('Browse '.$table);
			$this->template->set_var('title',$this->app_title);
			$this->template->set_var('action',$action);
			$this->template->pparse('out','header');


			$this->debug_info();
			$this->admin_menu();

			$this->browse_record('phpgw_jinn_sites','');
			$this->save_sessiondata();

		}

		function export_site()
		{
			global $where_condition;

			$GLOBALS['phpgw_info']['flags']['noheader']=True;
			$GLOBALS['phpgw_info']['flags']['nonavbar']=True;
			$GLOBALS['phpgw_info']['flags']['noappheader']=True;
			$GLOBALS['phpgw_info']['flags']['noappfooter']=True;
			$GLOBALS['phpgw_info']['flags']['nofooter']=True;

			/* plan:

			** get all site data en all site objects
			** present this data and render a cancel button and a save exportfile button
			** if cancel go back to site admin page else
			** open a saveas button and render a txtfile with an array

			*/

			$site_data=$this->bo->get_phpgw_records('phpgw_jinn_sites',$where_condition,'','','name');

			$filename=ereg_replace(' ','_',$site_data[0][site_name]).'.JiNN';
			$date=date("d-m-Y",time());

			header("Content-type: text");
			header("Content-Disposition:attachment; filename=$filename");

			$out="<?php\n\n";
				$out.='	/***************************************************************************'."\n";
				$out.='	**                                                                        **'."\n";
				$out.="	** JiNN Site Export:  ".$filename."\n";
				$out.="	** Date: ".$date."\n";
				$out.='	** ---------------------------------------------------------------------- **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	** JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare **'."\n";
				$out.='	** Copyright (C)2002, 2003 Pim Snel <pim.jinn@lingewoud.nl>               **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	** JiNN - http://linuxstart.nl/jinn                                       **'."\n";
				$out.='	** phpGroupWare - http://www.phpgroupware.org                             **'."\n";
				$out.='	** This file is part of JiNN                                              **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	** JiNN is free software; you can redistribute it and/or modify it under  **'."\n";
				$out.='	** the terms of the GNU General Public License as published by the Free   **'."\n";
				$out.='	** Software Foundation; either version 2 of the License, or (at your      **'."\n";
				$out.='	** option) any later version.                                             **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	** JiNN is distributed in the hope that it will be useful,but WITHOUT ANY **'."\n";
				$out.='	** WARRANTY; without even the implied warranty of MERCHANTABILITY or      **'."\n";
				$out.='	** FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License  **'."\n";
				$out.='	** for more details.                                                      **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	** You should have received a copy of the GNU General Public License      **'."\n";
				$out.='	** along with JiNN; if not, write to the Free Software Foundation, Inc.,  **'."\n";
				$out.='	** 59 Temple Place, Suite 330, Boston, MA 02111-1307  USA                 **'."\n";
				$out.='	**                                                                        **'."\n";
				$out.='	***************************************************************************/'."\n";
				$out.="\n";

				$out.= "/* SITE ARRAY */\n";

				$out.= '$import_site=array('."\n";

				while (list ($key, $val) = each($site_data[0])) 
				{
					if($key!='site_id') $out.= "	'$key '=> '$val',\n";
				}
				$out.=");\n\n";

				$site_object_data=$this->bo->get_phpgw_records('phpgw_jinn_site_objects','parent_'.$where_condition,'','','name');

				$out.= "\n/* SITE_OBJECT ARRAY */\n";

				foreach($site_object_data as $object)
				{

					$out.= '$import_site_objects[]=array('."\n";

					while (list ($key, $val) = each ($object)) 
					{ 
						if ($key != 'object_id') 
						{
							$out .= "	'$key' => '".ereg_replace("'","\'",$val)."',\n"; 

						}

					}
					$out.=");\n\n";

				}

				$out.='$checkbit=true;'."\n";
				$out.='?>';
				echo $out;
			}

			function browse_phpgw_jinn_site_objects()
			{

				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				unset($GLOBALS['phpgw_info']['flags']['noappheader']);
				unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

				$GLOBALS['phpgw']->common->phpgw_header();

				$this->template->set_file(array('header' => 'header.tpl'));

				$action=lang('Browse '.$table);

				$this->template->set_var('title',$this->app_title);
				$this->template->set_var('action',$action);
				$this->template->pparse('out','header');

				$this->message_box();
				$this->debug_info();
				$this->admin_menu();


				$this->browse_record('phpgw_jinn_site_objects','');
				$this->save_sessiondata();
			}

			function del_phpgw_jinn_sites()
			{
				$this->del_record('phpgw_jinn_sites');
			}

			function del_phpgw_jinn_site_objects()
			{
				$this->del_record('phpgw_jinn_site_objects');
			}

			function insert_phpgw_jinn_sites()
			{
				$this->insert_record('phpgw_jinn_sites');
			}

			function insert_phpgw_jinn_site_objects()
			{
				$this->insert_record('phpgw_jinn_site_objects');
			}


			function update_phpgw_jinn_sites()
			{
				$this->update_record('phpgw_jinn_sites');
			}

			function update_phpgw_jinn_site_objects()
			{
				$this->update_record('phpgw_jinn_site_objects');
			}

			function header()
			{
				unset($GLOBALS['phpgw_info']['flags']['noheader']);
				unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
				unset($GLOBALS['phpgw_info']['flags']['noappheader']);
				unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

				$GLOBALS['phpgw']->common->phpgw_header();
				$this->template->set_file(array
				(
					'header' => 'header.tpl',
				));

				$action=lang('Access rights site-objects');
				$this->template->set_var('title',$this->app_title);
				$this->template->set_var('action',$action);
				$this->template->pparse('out','header');
			}



			function index()
			{
				$this->header();
				$this->message_box();
				$this->admin_menu();
				$this->debug_info();
				$this->save_sessiondata();
			}

			function access_rights()
			{
				$this->header();
				$this->debug_info();
				$this->admin_menu();
				$access_rights = CreateObject('jinn.uiadminacl');
				$access_rights->main_screen();

				$this->save_sessiondata();

			}

			function set_access_rights_site_objects()
			{
				$this->header();
				$this->debug_info();
				$this->admin_menu();
				$access_rights = CreateObject('jinn.uiadminacl');
				$access_rights->set_site_objects();

				$this->save_sessiondata();

			}

			function set_access_rights_sites()
			{
				$this->header();
				$this->debug_info();
				$this->admin_menu();
				$access_rights = CreateObject('jinn.uiadminacl');
				$access_rights->set_sites();

				$this->save_sessiondata();

			}

			/****************************************************************************\
			* create form for new record                                                 *
			\****************************************************************************/
			function add_edit_record($table)
			{
				$this->header();
				$this->debug_info();
				$this->admin_menu();
				$add_edit = CreateObject('jinn.uiadminaddedit');
				$add_edit->render_form($table);

				$this->save_sessiondata();

			}


			/****************************************************************************\
			*                                                                            *
			\****************************************************************************/

			function browse_record($table,$where_condition)
			{
				//$this->header();
				//$this->debug_info();
				//$this->admin_menu();
				$browse = CreateObject('jinn.uiadminbrowse');
				$browse->render_list($table,$where_condition);

				$this->save_sessiondata();
			}

			function del_record($table)
			{
				$status = $this->bo->delete_phpgw_data($table,$GLOBALS[where_condition]);
				if ($status==1)
				{
					$this->bo->message=lang('Record deleted succesfully');
				}

				Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.index"));
				$GLOBALS['phpgw']->common->phpgw_exit();
				$this->save_sessiondata();

			}

			function insert_record($table)
			{

				$where_condition = $GLOBALS[where_condition];
				$status=$this->bo->insert_phpgw_data($table,$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES]);
				if ($status==1)
				{
					$this->bo->message=lang('Record added succesfully');
				}

				if ($table=='phpgw_jinn_sites')
				{
					Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.browse_phpgw_jinn_sites"));
					$GLOBALS['phpgw']->common->phpgw_exit();
				}
				else
				{
					Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites&where_condition=site_id=".$GLOBALS[HTTP_POST_VARS][FLDparent_site_id]));
					$GLOBALS['phpgw']->common->phpgw_exit();
				}


				//			Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.index"));
				//			$GLOBALS['phpgw']->common->phpgw_exit();

				$this->save_sessiondata();


			}

			function update_record($table)
			{
				$where_condition = $GLOBALS[where_condition];

				$status = $this->bo->update_phpgw_data($table,$GLOBALS[HTTP_POST_VARS],$GLOBAL[HTTP_POST_FILES],$where_condition);
				if ($status==1)
				{
					$this->bo->message=lang('Record succesfully editted');
				}
				$this->save_sessiondata();

				if ($table=='phpgw_jinn_sites')
				{
					Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.browse_phpgw_jinn_sites"));
					$GLOBALS['phpgw']->common->phpgw_exit();
				}
				else
				{
					Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites&where_condition=site_id=".$GLOBALS[HTTP_POST_VARS][FLDparent_site_id]));
					$GLOBALS['phpgw']->common->phpgw_exit();
				}


			}

			function save_access_rights_object()
			{

				$status = $this->bo->update_access_rights_object($GLOBALS[HTTP_POST_VARS]);
				if ($status==1)
				{
					$this->bo->message=lang('Access rights for site-object succesfully editted');
				}

				Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.access_rights"));
				$GLOBALS['phpgw']->common->phpgw_exit();

				$this->save_sessiondata();

			}

			function save_access_rights_site()
			{
				$status = $this->bo->update_access_rights_site($GLOBALS[HTTP_POST_VARS]);
				if ($status==1)
				{
					$this->bo->message=lang('Access rights for site succesfully editted');
				}

				Header('Location: '.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiadmin.access_rights"));
				$GLOBALS['phpgw']->common->phpgw_exit();
				$this->save_sessiondata();
			}


			function FIP_config()
			{
				$GLOBALS['phpgw_info']['flags']['noheader']=True;
				$GLOBALS['phpgw_info']['flags']['nonavbar']=True;
				$GLOBALS['phpgw_info']['flags']['noappheader']=True;
				$GLOBALS['phpgw_info']['flags']['noappfooter']=True;
				$GLOBALS['phpgw_info']['flags']['nofooter']=True;

				//var $plugins;
				$this->plugins =  CreateObject('jinn.boplugins');
				$use_records_cfg=False;

				$plugin_name=$this->plugins->plugins['fip'][$GLOBALS['FIP_name']]['title'];
				$plugin_version=$this->plugins->plugins['fip'][$GLOBALS['FIP_name']]['version'];

				echo '<h1>'.$plugin_name.'</h1> '.lang('version').' '. $plugin_version.'<P>';
				echo '<b>'.lang('field plugin configuration').'</b><p>';	

				if ($GLOBALS[hidden_val]) 
				{
					$orig_conf=explode(";",$GLOBALS[hidden_val]);
					if ($GLOBALS[FIP_name]==$GLOBALS[FIP_orig]) $use_records_cfg=True;
				}

				if (is_array($orig_conf))
				{
					foreach($orig_conf as $orig_conf_entry)
					{
						unset($cnf_pair);
						$cnf_pair[]=explode("=",$orig_conf_entry);
						//var_dump($cnf_pair);die();
						$def_orig_conf[$cnf_pair[0][0]]=$cnf_pair[0][1];
						//var_dump($def_orig_conf);
					}
				}
				// get config fields for this plugin
				// if hidden value is empty get defaults vals for this plugin


				$cfg=$this->plugins->plugins['fip'][$GLOBALS['FIP_name']]['config'];
				if(is_array($cfg))
				{
					echo '<form name=popfrm><table>';
					foreach($cfg as $cfg_key => $cfg_val)
					{
						echo '<tr>';
						echo '<td>'.$cfg_key.'</td>';
						if ($use_records_cfg)
						{
							$val=$def_orig_conf[$cfg_key];
						}
						else
						{
							$val=$cfg_val;
						}
						echo '<td><input name="'.$cfg_key.'" type=text value="'.$val.'"></td>';
						echo '</tr>';

						if($newconfig) $newconfig.='+";"+';
						$newconfig.='"'.$cfg_key.'="+document.popfrm.'.$cfg_key.'.value';

					}
					echo '</table></form>';
				}
				echo '<script>
				function fake_submit()
				{
					var newconfig;
					newconfig='.$newconfig.';

					window.opener.document.frm.'.$GLOBALS[hidden_name].'.value=newconfig;

					//alert(window.opener.document.frm.'.$GLOBALS[hidden_name].'.value);
					self.close();
				}

				</script>';
				echo '<P><input type=button value='.lang('save').' onClick="fake_submit()">';
				echo '<input type=button value='.lang('cancel').' onClick="self.close()">';

				$this->save_sessiondata();

			}

			function SFP_config()
			{
				$GLOBALS['phpgw_info']['flags']['noheader']=True;
				$GLOBALS['phpgw_info']['flags']['nonavbar']=True;
				$GLOBALS['phpgw_info']['flags']['noappheader']=True;
				$GLOBALS['phpgw_info']['flags']['noappfooter']=True;
				$GLOBALS['phpgw_info']['flags']['nofooter']=True;

				//var $plugins;
				$this->plugins =  CreateObject('jinn.boplugins');
				$use_records_cfg=False;

				$plugin_name=$this->plugins->plugins['sfp'][$GLOBALS['SFP_name']]['title'];
				$plugin_version=$this->plugins->plugins['sfp'][$GLOBALS['SFP_name']]['version'];

				echo '<h1>'.$plugin_name.'</h1> '.lang('version').' '. $plugin_version.'<P>';
				echo '<b>'.lang('field plugin configuration').'</b><p>';	

				if ($GLOBALS[hidden_val]) 
				{
					$orig_conf=explode(";",$GLOBALS[hidden_val]);
					if ($GLOBALS[SFP_name]==$GLOBALS[SFP_orig]) $use_records_cfg=True;
				}

				if (is_array($orig_conf))
				{
					foreach($orig_conf as $orig_conf_entry)
					{
						unset($cnf_pair);
						$cnf_pair[]=explode("=",$orig_conf_entry);
						//var_dump($cnf_pair);die();
						$def_orig_conf[$cnf_pair[0][0]]=$cnf_pair[0][1];
						//var_dump($def_orig_conf);
					}
				}
				// get config fields for this plugin
				// if hidden value is empty get defaults vals for this plugin


				$cfg=$this->plugins->plugins['sfp'][$GLOBALS['SFP_name']]['config'];
				if(is_array($cfg))
				{
					echo '<form name=popfrm><table>';
					foreach($cfg as $cfg_key => $cfg_val)
					{
						echo '<tr>';
						echo '<td>'.$cfg_key.'</td>';
						if ($use_records_cfg)
						{
							$val=$def_orig_conf[$cfg_key];
						}
						else
						{
							$val=$cfg_val;
						}
						echo '<td><input name="'.$cfg_key.'" type=text value="'.$val.'"></td>';
						echo '</tr>';

						if($newconfig) $newconfig.='+";"+';
						$newconfig.='"'.$cfg_key.'="+document.popfrm.'.$cfg_key.'.value';

					}
					echo '</table></form>';
				}
				echo '<script>
				function fake_submit()
				{
					var newconfig;
					newconfig='.$newconfig.';

					window.opener.document.frm.'.$GLOBALS[hidden_name].'.value=newconfig;

					//alert(window.opener.document.frm.'.$GLOBALS[hidden_name].'.value);
					self.close();
				}

				</script>';
				echo '<P><input type=button value='.lang('save').' onClick="fake_submit()">';
				echo '<input type=button value='.lang("cancel").' onClick="self.close()">';

				$this->save_sessiondata();

			}


			function admin_menu()
			{
				$this->template->set_file(array
				(
					'admin_menu' => 'admin_menu.tpl'
				));
				$this->template->set_var('global_settings_link',
				$GLOBALS['phpgw']->link('/index.php','menuaction=admin.uiconfig.index&appname=jinn'));
				$this->template->set_var('global_configuration',lang('Global Configuration'));
				$this->template->set_var('add_site',lang('add site'));
				$this->template->set_var('import_site',lang('import site'));
				$this->template->set_var('browse_sites',lang('browse sites'));
				$this->template->set_var('access_rights',lang('access_rights'));
				$this->template->set_var('add_site_link',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_edit_phpgw_jinn_sites'));
				$this->template->set_var('import_site_link',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.import_phpgw_jinn_site'));
				$this->template->set_var('browse_sites_link',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.browse_phpgw_jinn_sites'));
				$this->template->set_var('access_rights_link',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.access_rights'));
				$this->template->pparse('out','admin_menu');
			}

			function message_box()
			{
				echo $this->bo->message;
				unset($this->bo->message);
			}


			function debug_info()
			{

				if ($this->debug)
				{
					echo '<P><hr><P>';
					echo '<P>debug information';
					echo '<P>message='.$this->bo->message;

				}
			}

		}



	?>
