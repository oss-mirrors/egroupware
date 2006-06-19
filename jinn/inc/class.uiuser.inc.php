<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2005 Pim Snel <pim@lingewoud.nl>

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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   class uiuser extends uijinn
   {
	  var $public_functions = Array
	  (
		 'index'				=> True,
		 'list_all_sites'		=> True,
		 'list_all_objects'		=> True,
		 'add_edit_object'		=> True,
		 'file_download'		=> True,
		 'config_objects'		=> True,
		 'img_popup'			=> True,
		 'popwalkevent'			=> True,
		 'do_loop_walk_events'	=> True,
		 'runonrecord'			=> True
	  );


	  /**
	  * uiuser 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiuser()
	  {	
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();
	  }

	  /**
	  * index: create the default index page which is listview                                                         
	  * 
	  * @access public
	  * @return void
	  */
	  function index()
	  {
		 unset($GLOBALS['phpgw_info']['flags']['noheader']);
		 unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
		 unset($GLOBALS['phpgw_info']['flags']['noappheader']);
		 unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

		 if (($this->bo->session['site_id']==0 || $this->bo->session['site_id']) && $this->bo->session['site_object_id'] && $this->bo->site_object['parent_site_id']==$this->bo->session['site_id'] )
		 {
			if(trim($this->bo->site_object[plugins]))
			{
			   $boplugin = CreateObject('jinn.boadmin');
			   $boplugin->upgrade_plugins($this->bo->site_object[object_id],true);
			   unset($this->bo->site_object[plugins]);
			}

			$this->bo->exit_and_open_screen('jinn.uiu_list_records.display');
		 }
		 elseif($this->bo->session['site_id'])
		 {
			unset($this->bo->session['site_object_id']);
			unset($_GET['site_object_id']);
			unset($_POST['site_object_id']);

			$this->bo->addHelp(lang('Select site-object to moderate'));
			$this->list_all_objects();
		 }
		 else
		 {
			$this->bo->addHelp(lang('Select site to moderate'));
			unset($this->bo->session['site_object_id']);
			$this->list_all_sites();
		 }
	  }

	  /**
	  * list_all_sites 
	  * 
	  * @access public
	  * @return void
	  */
	  function list_all_sites()
	  {
		 $this->header('Index');
		 $this->msg_box();

		 $this->tplsav2->assign('icon_new',$GLOBALS[phpgw]->common->image('phpgwapi','new'));
		 $this->tplsav2->assign('icon_browse',$GLOBALS[phpgw]->common->image('phpgwapi','browse'));

		 $sites=$this->bo->get_sites_allowed($GLOBALS['phpgw_info']['user']['account_id']);

		 if(is_array($sites))
		 {
			foreach($sites as $site_id)
			{

			   unset($object_arr);
			   $objects=$this->bo->get_objects_allowed($site_id, $GLOBALS['phpgw_info']['user']['account_id']);

			   if (is_array($objects))
			   {
				  foreach ( $objects as $object_id) 
				  {
					 $object_arr[]=array(
						'value'=>$object_id,
						'name'=>$this->bo->so->get_object_name($object_id),
						'link_list'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display&site_id='.$site_id.'&site_object_id='.$object_id)	   ,
						'link_new'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.edit_record&site_id='.$site_id.'&site_object_id='.$object_id)	   
					 );
				  }
			   }

			   $site_arr[]=array(
				  'value'=>$site_id,
				  'name'=>$this->bo->so->get_site_name($site_id),
				  'link'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index&site_id='.$site_id),
				  'object_arr'=>$object_arr
			   );
			}
			$this->tplsav2->assign('site_arr',$site_arr);
			$this->tplsav2->display('list_all_sites.tpl.php');

		 }
	  }

	  /**
	  * list_all_objects 
	  * 
	  * @access public
	  * @return void
	  */
	  function list_all_objects()
	  {
		 $this->header('Index');
		 $this->msg_box();

		 $objects=$this->bo->get_objects_allowed($this->bo->session['site_id'], $GLOBALS['phpgw_info']['user']['account_id']);

		 if (is_array($objects))
		 {
			foreach ( $objects as $object_id) 
			{
			   $obj_val=$this->bo->so->get_object_values($object_id);
			   $object_arr[]=array(
				  'value'=>$object_id,
				  'name'=>$this->bo->so->get_object_name($object_id),
				  'help_information'=>$obj_val[help_information],
				  'link_list'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display&site_id='.$this->bo->session['site_id'].'&site_object_id='.$object_id)	   ,
				  'link_new'=>$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.edit_record&site_id='.$this->bo->session['site_id'].'&site_object_id='.$object_id)	   
			   );
			}
			$this->tplsav2->assign('object_arr',$object_arr);
			$this->tplsav2->display('list_all_objects.tpl.php');
		 }
	  }

	  //FIXME implement icons
	  //FIXME make sure it only runs the choosen event
	  //FIXME render buttons per record in edit record
	  //FIXME add GET Arg with return link
	  //FIXME don't show button on new records
	  //implement multiple
	  function runonrecord()
	  {
		 //run event on current record
		 $this->bo->addInfo(lang('Run On Record event ...'));
			 
		 if($_GET['base64_where_string'])
		 {
			$where_string=base64_decode($_GET['base64_where_string']);
		 }
		 else
		 {
			$where_string=$_GET[where_string];
		 }

		 $rows=$this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name'],'','','','','name','','*',$where_string);

		 foreach($rows as $recvals)
		 {
			while(list($key, $val) = each($recvals))
			{
			   $_row['FLDXXX'.$key]=$val;
			}
		 }
		 $status[eventstatus] = $this->bo->run_event_plugins('run_on_record', $_row);

		 //redirect to correct screen
		 //TODO test on apache 1.3
		 if(strstr ($_SERVER['HTTP_REFERER'], 'jinn.uiu_edit_record.edit_record'))
		 {
			$this->bo->exit_and_open_screen($this->japielink.'jinn.uiu_edit_record.edit_record');
		 }
		 else
		 {
			$this->bo->exit_and_open_screen($this->japielink.'jinn.uiu_list_records.display');
		 }
	  }

	  function popwalkevent()
	  {
		 #_debug_array($_GET['selvalues']);
		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('theme_css',$theme_css);
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;

		 $this->tplsav2->assign('selval',$_GET[selvalues]);
		 $this->tplsav2->display('pop_walk_event.tpl.php');
	  }

	  function do_loop_walk_events()
	  {
		 $start_time = time();
		 //TODO get it from the configuration
		 if($this->bo->so->config[loop_numbers] != '' or !empty( $this->bo->so->config[loop_numbers]))
		 {
			$items=$this->bo->so->config[loop_numbers];
		 }
		 else
		 {
			 $items=20;
		 }
		 $theme_css = $GLOBALS['phpgw_info']['server']['webserver_url'] . 
		 '/phpgwapi/templates/idots/css/'.$GLOBALS['phpgw_info']['user']['preferences']['common']['theme'].'.css';

		 $this->tplsav2->assign('theme_css',$theme_css);
		 $GLOBALS['phpgw_info']['flags']['noheader']=True;
		 $GLOBALS['phpgw_info']['flags']['nonavbar']=True;
		 $GLOBALS['phpgw_info']['flags']['noappheader']=True;
		 $GLOBALS['phpgw_info']['flags']['noappfooter']=True;
		 $GLOBALS['phpgw_info']['flags']['nofooter']=True;
		 if(!$_GET['where'])
		 {
			$this->filter = CreateObject('jinn.uiu_filter');
			$this->filter->init_bo(&$this->bo);

			#_debug_array($_POST);
			#die($_POST);
			switch($_POST[data_source])
			{
			   case 'filtered':
			   $filter_where = $this->filter->get_filter_where();
			   break;
			   case 'unfiltered':
			   $filter_where = 'all';
			   break;
			   case 'selected':
			   $arr = explode(',',$_POST[selvalues]);
			   if(is_array($arr))
			   {
				  $filter_where = '(';
				  foreach($arr as $filter)
				  {
					 if($filter_where!='(') $filter_where .= ' OR ';
					 $filter_where .= base64_decode($filter);
				  }
				  $filter_where .= ')';
			   }
			   else
			   {
				  $filter_where = 'all';
			   }
			   break;
			   default:
			   $filter_where = 'all';
			   break;
			}
			$limit = " LIMIT 0, $items" ;
			#_debug_array($filter_where);
			#die();
		 }
		 else
		 {
			$start = intval($_GET[start]);
			$end = $start + $items;
			$filter_where = unserialize(base64_decode($_GET['where']));
			$limit = " LIMIT $start, $end";
		 }
		 
		 $columns = $this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
		 if(is_array($columns))
		 {
			$columns_arr = array();
			foreach($columns as $column)
			{
			   $columns_arr[] = $column[name];
			}
		 }
		 if($_GET[amount] != '')
		 {
			$count = $_GET[amount];
		 }
		 else
		 {
			$site_id = $this->bo->session['site_id'];
			$table_name = $this->bo->site_object['table_name'];

			#$data = $this->bo->get_data($columns_arr, $filter_where);
			$count = $this->bo->so->num_rows_table($site_id, $table_name, $filter_where);
			#$count = count($data);
		 }
		 $data = $this->bo->get_data($columns_arr, $filter_where, $limit);

		 foreach($data as $row)
		 {
			//EVENT ON WALK LIST
			while(list($key, $val) = each($row))
			{
			   $_row['FLDXXX'.$key]=$val;
			}
			$status[eventstatus] = $this->bo->run_event_plugins('on_walk_list_button', $_row);

		 }
		 if($_GET[start]+$items < $count)
		 {
			$this->tplsav2->assign('items',$items);
			$this->tplsav2->assign('amount',$count);
			$this->tplsav2->assign('where',base64_encode(serialize($filter_where)));
			$this->tplsav2->assign('number',$_GET[start]+$items);
			$spend= time() - $start_time;
			$this->tplsav2->assign('time_spend',$spend);
			$this->tplsav2->display('pop_walk.tpl.php');
		 }
		 else
		 {
			$this->tplsav2->assign('amount',$count);
			$this->tplsav2->display('pop_walk_succes.tpl.php');
		 }
		 return "false";
	  }

	  /**
	  * config_objects 
	  * 
	  * @access public
	  * @return void
	  */
	  function config_objects()
	  {
		 $this->header(lang('configure browse view'));

		 if(!$this->bo->session['site_object_id'])
			{
			   $this->bo->addError(lang('No object selected. No able to configure this view'));
			   $this->msg_box();
			   $this->main_menu();	
			}
			else
			{
			   $this->msg_box();
			   $this->main_menu();	

			   $this->template->set_file(array(
				  'config' => 'config_browse_view.tpl'
			   ));

			   $columns_data=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);

			   if(is_array($columns_data));
			   {
				  foreach($columns_data as $col_data)
				  {
					 $columns[]=$col_data[name];

				  }

			   }

			   if (count($columns)>0)
			   {
				  // get the prefered columns, if they exist
				  $prefs_show_hide=$this->bo->read_preferences('show_fields'.$this->bo->site_object[unique_id]); 
				  $default_order=$this->bo->read_preferences('default_order'.$this->bo->site_object[unique_id]);

				  $prefs_show_hide=explode('|',$prefs_show_hide);
				  if(is_array($prefs_show_hide))
				  {
					 foreach($prefs_show_hide as $pref_s_h)
					 {
						$pref_array=explode(',',$pref_s_h);
						if($pref_array[0]==$this->bo->session['site_object_id'])
						{
						   $pref_columns=array_slice($pref_array,1);
						}
					 }
				  }

				  // which/how many column to show, all, the prefered, or the default thirst 4
				  if($pref_columns)
				  {
					 $show_cols=$pref_columns;
				  }
				  else
				  {
					 $show_cols=array_slice($columns,0,4);
				  }

				  foreach ($columns as $col)
				  {
					 unset($checked);
					 unset($checked2);
					 unset($checked3);

					 $field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$col);
					 $display_colname=($field_conf_arr[field_alt_name]?$field_conf_arr[field_alt_name]:$col);

					 if($default_order=="$col ASC") $checked2='CHECKED';
					 if($default_order=="$col DESC") $checked3='CHECKED';
					 if(in_array($col,$show_cols)) $checked='CHECKED';
					 if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
					 {
						$bgclr=$GLOBALS['phpgw_info']['theme']['row_on'];
					 }
					 else
					 {
						$bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
					 }

					 //FIXME move to template (gabriel help??)
					 $rows.='<tr>';				
						$rows.='<td bgcolor='.$bgclr.' align="left">'.$display_colname.'</td>';
						$rows.='<td bgcolor='.$bgclr.' align="left"><input name="SHOW'.$col.'" type=checkbox '.$checked.'></td>';
						$rows.='<td bgcolor='.$bgclr.' align="left"><input name="ORDER" type=radio value="'.$col.' ASC" '.$checked2.'>'.lang('ascending').'</td>';
						$rows.='<td bgcolor='.$bgclr.' align="left"><input name="ORDER" type=radio value="'.$col.' DESC" '.$checked3.'>'.lang('descending'). '</td>';
						$rows.='</tr>';
				  }

				  $form_action=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.save_object_config');
				  $button_save='<td><input class="egwbutton"  type="submit" name="action" value="'.lang('save').'"></td>';

				  $button_cancel='<td><input class="egwbutton"  type="button" onClick="location=\''.
					 $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_list_records.display') .
					 '\'" name="action" value="'.lang('cancel').'"></td>';

				  $this->template->set_var('form_action',$form_action);
				  $this->template->set_var('button_save',$button_save);
				  $this->template->set_var('button_cancel',$button_cancel);
				  $this->template->set_var('lang_config_table',lang('Configure view of').' '.$this->bo->site_object[name]);
				  $this->template->set_var('rows',$rows);
				  $this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
				  $this->template->set_var('lang_column_name',lang('column name'));
				  $this->template->set_var('lang_show_column',lang('show colomn'));
				  $this->template->set_var('lang_default_order',lang('default order'));

				  $this->template->pparse('out','config');
			   }

			}

		 }

		 /**
		 * file_download 
		 * 
		 * @access public
		 * @return void
		 */
		 function file_download()
		 {
			/* check current site  and object*/
			if(!$this->bo->session['site_id'] || !$this->bo->session['site_object_id'])
			{
			   $this->bo->addError(lang('You have no access to this file.'));

			   $this->bo->exit_and_open_screen('jinn.uiuser.index');
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
			   $this->bo->addError(lang('You have no access to this file.'));
			   $this->bo->exit_and_open_screen('jinn.uiuser.index');
			}

			$file_name=$_GET['file'];

			if(file_exists($file_name))
			{
			   $browser = CreateObject('phpgwapi.browser'); 

			   $browser->content_header(basename($file_name));

			   $handle = fopen ($file_name, "r");
			   $contents = fread ($handle, filesize ($file_name));
			   fclose ($handle);

			   echo $contents;
			}
			else
			{
			   $this->bo->addError(lang('ERROR: the file %1 doesn\'t exists, please contact the webmaster',$file_name));
			   $this->bo->exit_and_open_screen('jinn.uiuser.index');
			}

			$GLOBALS['phpgw']->common->phpgw_exit();
		 }

		 /**
		 * img_popup 
		 * 
		 * @access public
		 * @return void
		 */
		 function img_popup()
		 {
			$attributes=base64_decode($_GET[attr]);
			$new_path=base64_decode($_GET[path]);
			
			/*
			$this->template->set_file(array(
			   'imgpopup' => 'imgpopup.tpl'
			));
			*/

			$this->tplsav2->set_var('img',$new_path);
			//$this->tplsav2->set_var('ctw',);
			$this->tplsav2->display('imgpopup.tpl.php');
		 }

	  }
   ?>
