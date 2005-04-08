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


   class uiu_list_records
   {
	  var $public_functions = Array
	  (
		 'display'		=> True,
		 'browse_objects'		=> True
	  );

	  var $bo;
	  var $ui;
	  var $template;
	  var $filtermanager;

	  function uiu_list_records()
	  {
//_debug_array('uiu_list_records constructor called');
		 $this->bo = CreateObject('jinn.bouser');

		 $this->template = $GLOBALS['phpgw']->template;

		 $this->ui = CreateObject('jinn.uicommon',$this->bo);

		 $this->filtermanager = CreateObject('jinn.uiu_filter');
		 $this->filtermanager->init_bo(&$this->bo);

		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;
	  }

	  /**
	  @function display
	  @abstract wrapper function for listing one or more record
	  @note FIXME this function must be removed or at least renamed
	  */
	  function display()
	  {
		 unset($this->bo->session['mult_where_array']);

		 if($this->bo->site_object[max_records]==1)
		 {
			$columns=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
			if(!is_array($columns)) $columns=array();

			// walk through all table columns and fill different array 
			foreach($columns as $onecol)
			{
			   //create more simple col_list with only names //why
			   $all_col_names_list[]=$onecol[name];

			   // check for primaries and create array 
			   if (eregi("primary_key", $onecol[flags]) && $onecol[type]!='blob') // FIXME howto select long blobs
			   {						
				  $pkey_arr[]=$onecol[name];
			   }
			   elseif($onecol[type]!='blob') // FIXME howto select long blobs
			   {
				  $akey_arr[]=$onecol[name];
			   }
			}

			$records=$this->bo->get_records($this->bo->site_object[table_name],'','',0,1,'name',$orderby,'*',$where_condition);
			if(count($records)>0)
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
			   }

			   $this->bo->common->exit_and_open_screen('jinn.uiu_edit_record.view_record&where_string='.$where_string);
			}
			else
			{
			   $this->bo->session['message']['info']=lang('There are no records found for this object. You can now at a new record.');
			   $this->bo->sessionmanager->save();
			   $this->bo->common->exit_and_open_screen('jinn.uiu_edit_record.display_form');
			}
		 }
		 else
		 {
			$this->list_records();
		 }

	  }

	  /**
	  @function pager
	  @abstract create pager to browse through pages in listview
	  */
	  function pager($current_page,$total_records,$rec_per_page)
	  {

		 if($total_records==0)
		 {
			return lang('There are no pages');
		 }

		 if(!$current_page) $current_page=1;

		 $total_pages_tmp=$total_records/$rec_per_page;

		 if(is_float($total_pages_tmp))
		 {
			$total_pages = intval($total_pages_tmp)+1;
		 }
		 else
		 {
			$total_pages = intval($total_pages_tmp);
		 }

		 if($current_page>1)
		 {
			$pager='<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($current_page-1)).'" title="'.lang('One page backwards').'">&lt&lt;</a>&nbsp;';
		 }

		 if($total_pages > 10)
		 {
			if($current_page<7)
			{
			   // start
			   for($i=1; $i<=9;$i++)
			   {
				  if($current_page==$i)
				  {
					 $pager.= '<span style="font-weight:bold;">'.$i.'</span>&nbsp;';	
				  }
				  else
				  {
					 $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($i)).'" title="'.lang('Page %1',$i).'">'.$i.'</a>&nbsp;';	
				  }
			   }

			   $pager.= '...&nbsp;<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($total_pages)).'" title="'.lang('Page %1',$total_pages).'">'.$total_pages.'</a>&nbsp;';	

			   //einde start
			}
			elseif($current_page <= ($total_pages-6))
			{
			   //midden
			   $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.'1').'" title="'.lang('Page %1','1').'">'.'1'.'</a>&nbsp;...&nbsp;';	

			   for($i=($current_page-4); $i<=($current_page+4);$i++)
			   {
				  if($current_page==$i)
				  {
					 $pager.= '<span style="font-weight:bold;">'.$i.'</span>&nbsp;';	
				  }
				  else
				  {
					 $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($i)).'" title="'.lang('Page %1',$i).'">'.$i.'</a>&nbsp;';	
				  }
			   }

			   $pager.= '...&nbsp;<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($total_pages)).'" title="'.lang('Page %1',$total_pages).'">'.$total_pages.'</a>&nbsp;';	

			}
			else
			{
			   //eind
			   $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.'1').'" title="'.lang('Page %1','1').'">'.'1'.'</a>&nbsp;...&nbsp;';	

			   for($i=($total_pages-8); $i<=$total_pages;$i++)
			   {
				  if($current_page==$i)
				  {
					 $pager.= '<span style="font-weight:bold;">'.$i.'</span>&nbsp;';	
				  }
				  else
				  {
					 $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($i)).'" title="'.lang('Page %1',$i).'">'.$i.'</a>&nbsp;';	
				  }
			   }
			}
		 }
		 else
		 {
			for($i=1; $i<=$total_pages;$i++)
			{
			   if($current_page==$i)
			   {
				  $pager.= '<span style="font-weight:bold;">'.$i.'</span>&nbsp;';	
			   }
			   else
			   {
				  $pager.= '<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($i)).'" title="'.lang('Page %1',$i).'">'.$i.'</a>&nbsp;';	
			   }
			}

		 }

		 if($total_pages>1 && $current_page!=$total_pages)
		 {
			$pager.='<a href="'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display&current_page='.($current_page+1)).'" title="'.lang('One page forward').'">&gt;&gt;</a>';
		 }

		 return $pager;

	  }

	

	  /**
	  @function list_records
	  @abstract make recordlist for browsing and selecting records
	  @todo implement nextmatch-class, number of record, better navigation, 
	  @todo advanced filter, filters,positioning and ranges in session
	  @todo searching with fulltext
	  @fixme when not allowed to object give error msg
	  */
	  function list_records()
	  {
		 unset($this->bo->session['mult_where_array']);

		 // check if table exists
		 if(!$this->bo->so->test_JSO_table($this->bo->site_object))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->session['message']['error']=lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']);
			$this->bo->session['message']['error_code']=117;

			$this->bo->sessionmanager->save();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
		 }				

		 // check if there's permission to this object
		 if(!$this->bo->acl->has_object_access($this->bo->session['site_object_id']))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->session['message']['error']=lang('You have no access to this object');
			$this->bo->session['message']['error_code']=116;

			$this->bo->sessionmanager->save();
			$this->bo->common->exit_and_open_screen('jinn.uiuser.index');
		 }

		 $this->ui->header('browse through records');
		 $this->ui->msg_box($this->bo->session['message']);
		 unset($this->bo->session['message']);

		 $this->ui->main_menu();	

		 $this->template->set_file(array(
			'list_records' => 'list_records.tpl',
		 ));

		 $this->template->set_block('list_records','header','header');
		 $this->template->set_block('list_records','column_name','column_name');
		 $this->template->set_block('list_records','column_field','column_field');
		 $this->template->set_block('list_records','row','row');
		 $this->template->set_block('list_records','empty_row','empty_row');
		 $this->template->set_block('list_records','emptyfooter','emptyfooter');
		 $this->template->set_block('list_records','footer','footer');

		 $show_fields_str=$this->bo->read_preferences('show_fields'.$this->bo->site_object[unique_id]); 
		 $default_order=$this->bo->read_preferences('default_order'.$this->bo->site_object[unique_id]);
		 $default_col_num=$this->bo->read_preferences('default_col_num');

		 $current_page = 
		 ($_GET[current_page]?$_GET[current_page]:$this->bo->session['browse_settings']['current_page']);

		 $rec_per_page = $this->bo->records_per_page();

		 $offset = $this->bo->get_offset($current_page,$rec_per_page);

		 $filter = ($_GET[filter]?$_GET[filter]:$this->bo->session['browse_settings']['filter']);

		 $orderby = ($_GET[orderby]?$_GET[orderby]:$this->bo->session['browse_settings']['orderby']);
		 if(!$orderby && $default_order) $orderby=$default_order;

			//the filter class takes care of detecting the current filter, compiling a where statement and compiling the filter options for the listbox
		 $filter_where = $this->filtermanager->get_filter_where();
		 $this->template->set_var('filter_list',$this->filtermanager->format_filter_options($_POST[filtername]));
		 
		 $this->template->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.edit'));
		 $this->template->set_var('refresh_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->template->set_var('filter_text',lang('activate filter'));
		 $this->template->set_var('filter_edit',lang('edit filter'));

		 if( trim($_POST[quick_filter]) || $_POST[quick_filter_hidden] )
		 {
			$quick_filter = trim( $_POST[quick_filter] );
			$current_page=1;
		 }
		 else
		 {
			$quick_filter = trim( $this->bo->session['browse_settings']['quick_filter'] );
		 }

		 $this->bo->session['browse_settings'] = array
		 (
			'orderby'=>$orderby,
			'quick_filter'=>$quick_filter,
			'filter_arr'=>$filter_arr,
			'current_page'=>$current_page,
		 );

		 /* get one with many relations */
		 $relation1_array=$this->bo->extract_O2M_relations($this->bo->site_object['relations']);
		 if (count($relation1_array)>0)
		 {
			foreach($relation1_array as $relation1)
			{
			   $fields_with_relation1[]=$relation1[org_field];
			}
		 }

		 /* get prefered columnnames to show */
		 if ($show_fields_str)
		 {
			$all_prefs_show_hide=explode('|',$show_fields_str);
			foreach($all_prefs_show_hide as $pref_show_hide)
			{
			   $pref_show_hide_arr=explode(',',$pref_show_hide);
			   if($pref_show_hide_arr[0]==$this->bo->session['site_object_id'])
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

		 $columns=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);
		 if(!is_array($columns)) $columns=array();
		 /* walk through all table columns and fill different array */
		 $fields_show_default = array();
		 foreach($columns as $onecol)
		 {
			$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$onecol[name]);
			if($field_conf_arr[field_show_default])
			{
			   $fields_show_default[] = $onecol;
			}

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

			/* format quick_filter condition 
			fixme make general function in so
			*/
			if ($quick_filter)
			{
			   $quick_filter_arr=explode(' ',$quick_filter);

			   foreach($quick_filter_arr as $like_str)
			   {
				  if ($where_condition)
				  {
					 $where_condition.= " OR {$onecol[name]} LIKE '%$like_str%'";
				  }
				  else
				  {
					 $where_condition = " {$onecol[name]} LIKE '%$like_str%'";
				  }
			   }

			   $where_condition = '('.$where_condition.')';
			}

		 }

		 //fixme start of object filters
		 if($this->bo->site_object[extra_where_sql_filter])
		 {
			if ($where_condition) 
			{
			   $where_condition.= " AND ({$this->bo->site_object[extra_where_sql_filter]})"; 	
			}
			else
			{
			   $where_condition= " ({$this->bo->site_object[extra_where_sql_filter]})"; 	
			}
		 }

		 //start of new filtersystem
		 if($filter_where != '')
		 {
			if ($where_condition) 
			{
			   $where_condition.= " AND ($filter_where)"; 	
			}
			else
			{
			   $where_condition= " ($filter_where)"; 	
			}
		 }
		 
		 /* which/how many column to show: all, the preferred, the default fields set by the site admin, the default first X, or the default first 4 */
		 if ($show_all_cols=='True' || $default_col_num=='-1')
		 {
			$col_list=$columns;
		 }
		 elseif($pref_columns)
		 {
			$col_list=$valid_pref_columns;
		 }
		 elseif($fields_show_default)	//new: the Administrator has defined which fields to show default
		 {
			$col_list = $fields_show_default;
		 }
		 elseif($default_col_num)
		 {
			$col_list=array_slice($columns,0,$default_col_num);
		 }
		 else
		 {
			$col_list=array_slice($columns,0,4);
		 }

		 /*	check if orderbyfield exist else drop orderby it	*/
		 if(!in_array(trim(substr($orderby,0,(strlen($orderby)-4))),$all_col_names_list)) unset($orderby);
		 //	unset($all_col_names_list);


		 // make columnheaders
		 foreach ($col_list as $col)
		 {
			unset($testvalue);

			$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$col[name]);

			$display_colname=($field_conf_arr[field_alt_name]?$field_conf_arr[field_alt_name]:$col[name]);

			unset($tipmouseover);
			if(trim($field_conf_arr[field_help_info]))
			{
			   $tooltip=str_replace("'", "\'", $field_conf_arr[field_help_info]);
			   $tipmouseover='<img onMouseover="tooltip(\''.$tooltip.'\')" onMouseout="hidetooltip()" src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			}

			if(!$this->bo->field_is_enabled($this->bo->site_object[object_id], $col[name]))
			{
			   continue ;
			}

			$col_names_list[]=$col[name];
			unset($orderby_link);
			unset($orderby_image);

			$display_colname=($field_conf_arr[field_alt_name]?$field_conf_arr[field_alt_name]:$col[name]);

			if ($col[name] == trim(substr($orderby,0,(strlen($orderby)-4))))
			{
			   if (substr($orderby,-4)== 'DESC')
			   {
				  $orderby_link = $col[name].' ASC';
				  $orderby_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','desc.png').'" border="0">';
			   }
			   else 
			   {
				  $orderby_link = $col[name].' DESC';
				  $orderby_image = '<img src="'. $GLOBALS['phpgw']->common->image('jinn','asc.png').'" border="0">';
			   }
			}
			else
			{
			   $orderby_link = $col[name].' ASC';
			}
			
			$this->template->set_var('colhead_bg_color',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$this->template->set_var('colhead_order_link',$GLOBALS[phpgw]->link("/index.php","menuaction=jinn.uiu_list_records.display&orderby=$orderby_link"));
			$this->template->set_var('colhead_name',str_replace('_','&nbsp;',$display_colname));
			$this->template->set_var('colhead_order_by_img',$orderby_image);
			$this->template->set_var('tipmouseover',$tipmouseover);

			$this->template->parse('colnames','column_name',true);
		}
		
		$records = $this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object[table_name],'','',$offset,$rec_per_page,'name',$orderby,'*',$where_condition);

		$record_count = count($records);

		$num_rows=$this->bo->so->num_rows_table($this->bo->session['site_id'],$this->bo->site_object['table_name'],$where_condition);

		$lang_total_records= lang('%1 records',$num_rows);
		$lang_rec_per_page= lang('%1 records per page', $rec_per_page);

		// get pager code
		$pager=$this->pager($current_page,$num_rows,$rec_per_page);

		$this->template->set_var('list_form_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.bouser.multiple_actions'));
		$this->template->set_var('colfield_lang_confirm_delete_multiple',lang('Are you sure you want to delete these multiple records?'));
		$this->template->set_var('colfield_lang_confirm_edit_multiple',lang('Are you sure your want to edit these records?'));
		$this->template->set_var('orderby',$orderby);
		$this->template->set_var('menu_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		$this->template->set_var('row_off',$GLOBALS['phpgw_info']['theme']['row_off']);
		$this->template->set_var('start_at',lang('start at record'));
		$this->template->set_var('stop_at',lang('stop at record'));
		$this->template->set_var('search_for',lang('search for string'));
		$this->template->set_var('show',lang('show'));
		$this->template->set_var('search',lang('search'));
		$this->template->set_var('action_config_table',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.config_table'));
		$this->template->set_var('lang_config_this_tableview',lang('Configure this tableview'));
		$this->template->set_var('lang_select_checkboxes',lang('You must select one or more records for this function.'));
		$this->template->set_var('search_string',$quick_filter);
		$this->template->set_var('total_records',$lang_total_records);
		$this->template->set_var('rec_per_page',$lang_rec_per_page);
		$this->template->set_var('pager',$pager);
		$this->template->set_var('lang_Actions',lang('Actions'));
		$this->template->set_var('edit',lang('edit'));
		$this->template->set_var('delete',lang('delete'));
		$this->template->set_var('copy',lang('copy and edit the new record'));
		$this->template->set_var('show_all_cols',$show_all_cols);

		$this->template->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
		$this->template->set_var('table_title',$this->bo->site_object[name]);

		$popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		$this->template->set_var('popuplink',$popuplink);

		 if(!is_array($pkey_arr))
		 {
			$pkey_arr=$akey_arr;
			unset($akey_arr);
		 }

		 $this->template->parse('out','header');
		 $this->template->pparse('out','header');

		 if($record_count>0)
		 {
			foreach($records as $recordvalues)
			{
			   unset($where_string);
			   if(count($pkey_arr)>0)
			   {
				  foreach($pkey_arr as $pkey)
				  {
					 if($where_string) $where_string.=' AND ';
					 $where_string.= '('.$pkey.' = \''. addslashes($recordvalues[$pkey]).'\')';
				  }

				  $where_string=base64_encode($where_string);
			   }

			   if ($bgclr==$GLOBALS['phpgw_info']['theme']['row_off'])
			   {
				  $bgclr='#ffffff';
			   }
			   else
			   {
				  $bgclr=$GLOBALS['phpgw_info']['theme']['row_off'];
			   }

			   if(count($recordvalues)>0)
			   {
				  $this->template->set_var('colfield_check_name','SEL'.$where_string);
				  $this->template->set_var('colfield_check_val',$where_string);

				  // action_links
				  $this->template->set_var('colfield_bg_color',$bgclr);
				  $this->template->set_var('colfield_lang_edit',lang('edit'));
				  $this->template->set_var('colfield_edit_link',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.display_form&where_string='.$where_string));
				  $this->template->set_var('colfield_edit_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','edit'));

				  $this->template->set_var('colfield_lang_view',lang('view'));
				  $this->template->set_var('colfield_view_link',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiu_edit_record.view_record&where_string='.$where_string));
				  $this->template->set_var('colfield_view_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','view'));

				  $this->template->set_var('colfield_lang_delete',lang('delete'));
				  $this->template->set_var('colfield_delete_link',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.del_record&where_string='.$where_string));
				  $this->template->set_var('colfield_lang_confirm_delete_one',lang('Are you sure you want to delete this record?'));
				  $this->template->set_var('colfield_lang_confirm_copy_one',lang('Do you want to copy and then edit this record?'));
				  $this->template->set_var('colfield_delete_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','delete'));


				  $this->template->set_var('colfield_lang_copy',lang('copy and edit the new record'));
				  $this->template->set_var('colfield_copy_link',$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.bouser.copy_record&where_string='.$where_string));
				  $this->template->set_var('colfield_copy_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','copy'));


				  $this->template->set_var('colfields','');

				  foreach($col_names_list  as $onecolname)
				  {
					 $field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$onecolname);
					 $recordvalue=$recordvalues[$onecolname];
					 if ($recordvalue && is_array($fields_with_relation1) && in_array($onecolname,$fields_with_relation1))
					 {
						$related_value=$this->bo->get_related_value($relation1_array[$onecolname],$recordvalue);
						$recordvalue= '<i>'.$related_value.'</i> ('.$recordvalue.')';
					 }
					 else
					 {	
						if($this->bo->site_object[plugins])
						{
						   $recordvalue=$this->bo->get_plugin_bv($onecolname,$recordvalue,$where_string,$onecolname);
						}
						else
						{
						   $recordvalue=$this->bo->plug->call_plugin_bv($onecolname,$recordvalue,$where_string,$field_conf_arr);
						}
					 }

					 if (empty($recordvalue))
					 {
						$recordvalue="&nbsp;";

					 }

					 $this->template->set_var('colfield_bg_color',$bgclr);
					 $this->template->set_var('colfield_value',$recordvalue);

					 $this->template->parse('colfields','column_field',true);
				  }

				  $this->template->parse('rows','row',true);
				  $this->template->pparse('out','row');



			   }// end if table has fields


			}//end foreach row

		 }
		 else
		 {
			$this->template->set_var('lang_no_records',lang('No records found'));
			$this->template->set_var('colspan',(count($col_names_list)+3));
			$this->template->pparse('out','empty_row');
		 }

		 $this->template->set_var('colfield_lang_check_all',lang('toggle all above checkboxes'));
		 $this->template->set_var('lang_actions_to_apply_on_selected',lang('Actions to apply on all selected record'));
		 $this->template->set_var('colfield_lang_view_sel',lang('view all selected records'));
		 $this->template->set_var('colfield_view_link_sel','');
		 $this->template->set_var('colfield_lang_edit_sel',lang('edit all selected records'));
		 $this->template->set_var('colfield_edit_link_sel','');
		 $this->template->set_var('colfield_lang_delete_sel',lang('delete all selected records'));
		 $this->template->set_var('colfield_delete_link_sel','');
		 $this->template->set_var('colfield_lang_export_sel',lang('export all selected records'));
		 $this->template->set_var('colfield_export_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','filesave'));

		 if($record_count>0)
		 {
			$this->template->parse('out','footer');
			$this->template->pparse('out','footer');
		 }
		 else 
		 {
			$this->template->parse('out','emptyfooter');
			$this->template->pparse('out','emptyfooter');
		 }

		$this->bo->sessionmanager->save();
	  }


   }












?>
