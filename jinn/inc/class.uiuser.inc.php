<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for phpGroupWare
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

		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');
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

			$this->template->set_var('main_form_action',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.index'));
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

		 /*******************************\
		 * 	Browse through site_objects  *
		 \*******************************/

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

			$this->template->set_file(array(
			   'browse_menu' => 'browse_menu.tpl',
			   'browse' => 'browse.tpl'
			));

			$pref_columns_str=$this->bo->read_preferences('show_fields'); 
			$default_order=$this->bo->read_preferences('default_order');

			list($offset,$asc,$order,$filter,$navdir,$limit_start,$limit_stop,$direction,$show_all_cols,$search)=$this->bo->common->get_global_vars(array('offset','asc','order','filter','navdir','limit_start','limit_stop','direction','show_all_cols','search'));

			if(!$offset) $offset= $this->bo->browse_settings['offset'];
			if(!$asc)    $asc=    $this->bo->browse_settings['asc']; // FIXME remove?
			if(!$filter) $filter= $this->bo->browse_settings['filter'];
			if(!$order)  $order=  $this->bo->browse_settings['order'];
			$this->bo->browse_settings = array
			(
			   'offset'=>$offset,
			   'range'=>$range,
			   'navdir'=>$navdir, // FIXME test
			   'order'=>$order,
			   'filter'=>$filter
			);

			if(!$order && $default_order) $order=$default_order;

			
			$num_rows=$this->bo->so->num_rows_table($this->bo->site_id,$this->bo->site_object['table_name']);

			$limit=$this->bo->set_limits($limit_start,$limit_stop,$direction,$num_rows);

			$this->template->set_var('limit_start',$limit['start']);
			$this->template->set_var('limit_stop',$limit['stop']);
			$this->template->set_var('order',$order);
			$this->template->set_var('menu_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.browse_objects'));
			$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
			$this->template->set_var('start_at',lang('start at record'));
			$this->template->set_var('stop_at',lang('stop at record'));
			$this->template->set_var('search_for',lang('search for string'));
			$this->template->set_var('show',lang('show'));
			$this->template->set_var('search',lang('search'));
			$this->template->set_var('action_config_table',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.config_table'));
			$this->template->set_var('lang_config_this_tableview',lang('Configure this tableview'));
			$this->template->set_var('search_string',$search);
			$this->template->set_var('show_all_cols',$show_all_cols);
			$this->template->set_var('edit',lang('edit'));
			$this->template->set_var('delete',lang('delete'));
			$this->template->set_var('copy',lang('copy'));
			$this->template->set_var('show_all_cols',$show_all_cols);
			$this->template->pparse('out','browse_menu');

			$LIMIT="LIMIT $limit[start],$limit[stop]";

			/* get one with many relations */
			$relation1_array=$this->bo->extract_1w1_relations($this->bo->site_object['relations']);
			if (count($relation1_array)>0)
			{
			   foreach($relation1_array as $relation1)
			   {
				  $fields_with_relation1[]=$relation1[field_org];
			   }
			}

			/* get prefered columnnames to show */
			if ($pref_columns_str)
			{
			   $all_prefs_show_hide=explode('|',$pref_columns_str);
			   foreach($all_prefs_show_hide as $pref_show_hide)
			   {
				  $pref_show_hide_arr=explode(',',$pref_show_hide);
				  if($pref_show_hide_arr[0]==$this->bo->site_object_id)
				  {
					 $pref_columns=array_slice($pref_show_hide_arr,1);

					 //is this necessary?	
					 foreach($pref_columns as $pref_col)
					 {
						$valid_pref_columns[]=array('name'=>$pref_col);
					 }

				  }
			   }
			}


			$columns=$this->bo->so->site_table_metadata($this->bo->site_id, $this->bo->site_object['table_name']);
			if(!is_array($columns)) $columns=array();

			/* walk through all table columns and fill different array */
			foreach($columns as $onecol)
			{
			   //create more simple col_list with only names //why
			   $all_col_names_list[]=$onecol[name];

			   /* check for primaries and create array */
			   if (eregi("primary_key", $onecol[flags]) && $onecol[type]!='blob') // FIXME howto select long blobs
			   {						
				  $pkey_arr[]=$onecol[name];
			   }
			   elseif($onecol[type]!='blob') // FIXME howto select long blobs
			   {
				  $akey_arr[]=$onecol[name];
			   }

			   /* format search condition */
			   if ($search)
			   {
				  if ($where_condition)
				  {
					 $where_condition.= " OR {$onecol[name]} LIKE '%$search%'";
					 $limit="";
				  }
				  else
				  {
					 $where_condition = " {$onecol[name]} LIKE '%$search%'";
				  }
			   }


			   /* which/how many column to show, all, the prefered, or the default thirst 4 */
			   if ($show_all_cols=='True')
			   {
				  $col_list=$columns;
			   }
			   elseif($pref_columns)
			   {
				  $col_list=$valid_pref_columns;
			   }
			   else
			   {
				  $col_list=array_slice($columns,0,4);
			   }
			}

			/*	check if orderfield exist else drop order it	*/
			if(!in_array(trim(substr($order,0,(strlen($order)-4))),$all_col_names_list)) unset($order);
			//	unset($all_col_names_list);


			// make columnheaders
			foreach ($col_list as $col)
			{
			   $col_names_list[]=$col[name];
			   unset($order_link);
			   unset($order_image);
			   if ($col[name] == trim(substr($order,0,(strlen($order)-4))))
			   {
				  if (substr($order,-4)== 'DESC')
				  {
					 $order_link = $col[name].' ASC';
					 $order_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','desc.png').'" border="0">';
				  }
				  else 
				  {
					 $order_link = $col[name].' DESC';
					 $order_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','asc.png').'" border="0">';
				  }
			   }
			   else
			   {
				  $order_link = $col[name].' ASC';
			   }

			   // FIXME replace by template block
			   $col_headers_t.='<td bgcolor="'.$GLOBALS['phpgw_info']['theme']['th_bg'].'" ';
				  $col_headers_t.=' style="font-weight:bold;padding:3px;"  align=\"center\">';
				  $col_headers_t.='<a href="'.$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiuser.browse_objects&order=$order_link&search=$search&limit_start=$limit_start&limit_stop=$limit_stop&show_all_cols=$show_all_cols").'">'.str_replace('_','&nbsp;',$col[name]).'&nbsp;'.$order_image.'</a></td>';
			}

			if(!is_array($pkey_arr))
			{
			   $pkey_arr=$akey_arr;
			   unset($akey_arr);
			}
			
			$records=$this->bo->get_records($this->bo->site_object[table_name],'','',$limit[start],$limit[stop],'name',$order,'*',$where_condition);

			if (count($records)>0)
			{

			   foreach($records as $recordvalues)
			   {
				  unset($where_string);
				  if(count($pkey_arr)>0)
				  {
					 foreach($pkey_arr as $pkey)
					 {
						if($where_string) $where_string.=' AND ';
						$where_string.= '('.$pkey.' = \''. $recordvalues[$pkey].'\')';
					 }
				
					 $where_string=base64_encode($where_string);
				  }

				  if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
				  {
					 $bgclr=$GLOBALS['phpgw_info']['theme']['row_on'];
				  }
				  else
				  {
					 $bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
				  }


				  if(count($recordvalues)>0)
				  {
					 $table_rows.='<tr valign="top">';
						$table_rows.='<td bgcolor="'.$bgclr.'" align="left"><a title="'.lang('edit').'" href="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.display_form&where_string='.$where_string).'"><img src="'.$GLOBALS[phpgw]->common->image('phpgwapi','edit').'" alt="'.lang('delete').'" /></a></td><td bgcolor="'.$bgclr.'" align="left"><a title="'.lang('delete').'" href="'.$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.del_object&where_string='.$where_string).'" onClick="return window.confirm(\''.lang('Are you sure?').'\');"><img src="'.$GLOBALS[phpgw]->common->image('phpgwapi','delete').'" alt="'.lang('delete').'" /></a></td>';

						foreach($col_names_list  as $onecolname)
						{
						   $recordvalue=$recordvalues[$onecolname];
						   if (empty($recordvalue))
						   {
							  $table_rows.="<td bgcolor=\"$bgclr\">&nbsp;</td>";
						   }
						   else
						   {
							  if (is_array($fields_with_relation1) && in_array($onecolname,$fields_with_relation1))
							  {
								 $related_value=$this->bo->get_related_value($relation1_array[$onecolname],$recordvalue);
								 $recordvalue= '<i>'.$related_value.'</i> ('.$recordvalue.')';

							  }
							  else
							  {	
								 $recordvalue=$this->bo->get_plugin_bv($onecolname,$recordvalue);
							  }

							  $display_value=$recordvalue;
							  $table_rows.="<td bgcolor=\"$bgclr\" valign=\"top\">".$display_value."</td>";
						   }

						}

						$table_rows.='</tr>';

				  }


			   }
			}
			else
			{
			
			   $table_rows.='<tr><td colspan="'.(count($col_names_list)+3).'">'.lang('No records found').'</td></tr>';		   
			   
			}


			$button_config='<td><form name=form2 action="'.
				  $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects') .
				  '" method="post"><input type="submit" name="action" value="'.lang('Configure this View').'"></form></td>';

			$this->template->set_var('button_add',$button_add);
			$this->template->set_var('button_browse',$button_browse);
			$this->template->set_var('button_show_all_cols',$button_show_all_cols);
			$this->template->set_var('button_config',$button_config);
			$this->template->set_var('table_title',$this->bo->site_object[name]);
			$this->template->set_var('record_info',lang('record').' '.$limit[start].' '.lang('t/m').' '.$limit[stop]);
			$this->template->set_var('fieldnames',$col_headers_t);
			$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->template->set_var('fieldnames',$col_headers_t);
			$this->template->set_var('table_row',$table_rows);

			$this->template->pparse('out','browse');

			unset($this->message);

			unset($this->bo->message);
			$this->bo->save_sessiondata();
		 }

		 /****************************************************************************\
		 * 	Config site_objects                                              *
		 \****************************************************************************/

		 function config_objects()
		 {
			$this->ui->header(lang('configure browse view'));
			$this->ui->msg_box($this->bo->message);
			$this->main_menu();	

			$main = CreateObject('jinn.uiconfig',$this->bo);
			$main->show_fields();

			$this->bo->save_sessiondata();
		 }





	  }
   ?>
