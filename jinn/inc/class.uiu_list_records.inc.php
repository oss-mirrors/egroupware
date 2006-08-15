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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * uiu_list_records 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiu_list_records extends uijinn
   {
	  var $public_functions = Array
	  (
		 'display'						=> True,
		 'display_dev'					=> True,
		 'browse_objects'				=> True,
		 'display_last_records_page'	=> True
	  );

	  var $filtermanager;
	  var $db_ftypes;

	  var $japielink;
	  /**
	  * uiu_list_records 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiu_list_records()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();

		 $this->boreport = CreateObject('jinn.boreport');

		 $this->filtermanager = CreateObject('jinn.uiu_filter');

		 $tmpbo=&$this->bo;

		 $this->filtermanager->init_bo($tmpbo);

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 // prevent ugly errors
		 if(!$this->bo->site_object[object_id])
		 {
			$this->bo->exit_and_open_screen('jinn.uiuser.index');
		 }
		 
		 $this->set_activated_list_elements();
		 
	  }

	  function display_dev()
	  {
		 $this->tplsav2->devtoolbar=$this->get_developer_object_toolbar();
		 $this->display();
	  }
	  
	  /**
	  * display: wrapper function for listing one or more record
	  * 
	  * @note FIXME this function must be removed or at least renamed
	  * @access public
	  * @return void
	  */
	  function display()
	  {
		 unset($this->bo->session['mult_where_array']);

		 if($this->bo->site_object[max_records]==1)
		 {
			$columns=$this->bo->so->site_table_metadata($this->bo->site_object['parent_site_id'], $this->bo->site_object['table_name']);

			if(!is_array($columns)) $columns=array();

			// walk through all table columns and fill different array 
			foreach($columns as $onecol)
			{
			   //create more simple col_list with only names //why
			   $all_col_names_list[]=$onecol[name];

			   // check for primaries and create array 
			   if ($onecol[primary_key] && $onecol[type]!='blob') // FIXME howto select long blobs
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

			   $this->bo->exit_and_open_screen($this->japielink.'jinn.uiu_edit_record.read_record&site_id='.$this->bo->site_object['parent_site_id'].'&site_object_id='.$this->bo->site_object['object_id'].'&where_string='.$where_string);
			   //die('jinn.uiu_edit_record.read_record&object_id='.$this->bo->site_object['object_id'].'&where_string='.$where_string);
			}
			else
			{
			   $this->bo->addInfo(lang('There are no records found for this object. You can now at a new record.'));
			   $this->bo->exit_and_open_screen($this->japielink.'jinn.uiu_edit_record.edit_record&site_object_id='.$this->bo->site_object['object_id']);
			}
		 }
		 else
		 {
			$this->list_records();
		 }
	  }

	  /**
	  * pager: create pager to browse through pages in listview
	  * 
	  * @param mixed $current_page if last current page is last
	  * @param mixed $total_records 
	  * @param mixed $rec_per_page 
	  * @access public
	  * @return void
	  */
	  function pager($current_page,$total_records,$rec_per_page)
	  {
		 if($total_records==0)
		 {
			return lang('There are no pages');
		 }

		 $total_pages_tmp=$total_records/$rec_per_page;

		 if(is_float($total_pages_tmp))
		 {
			$total_pages = intval($total_pages_tmp)+1;
		 }
		 else
		 {
			$total_pages = intval($total_pages_tmp);
		 }

		 if($current_page=='last')
		 {
			$current_page=$total_pages;
		 }

		 // prevent silly situations
		 if($current_page > $total_pages || !$current_page)
		 {
			$current_page=1;	
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

		 //extra info for other funcs
		 $this->total_pages=$total_pages;
		 $this->last_page=$total_pages;

		 return $pager;

	  }

	  /**
	  * list_records: make recordlist for browsing and selecting records 
	  * 
	  * @todo implement nextmatch-class, number of record, better navigation, 
	  * @todo advanced filter, filters,positioning and ranges in session
	  * @todo searching with fulltext
	  * @fixme when not allowed to object give error msg
	  * @access public
	  * @return void
	  */
	  function list_records()
	  {

		 
		 unset($this->bo->session['mult_where_array']);

		 // check if table exists
		 if(!$this->bo->so->test_site_object_table($this->bo->site_object))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->addError(lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']));

			$this->bo->exit_and_open_screen('jinn.uiuser.index');
		 }				

		 // check if there's permission to this object
		 if(!$this->bo->acl->has_object_access($this->bo->session['site_object_id']))
		 {
			unset($this->bo->session['site_object_id']);
			$this->bo->addError(lang('You have no access to this object'));

			$this->bo->exit_and_open_screen('jinn.uiuser.index');
		 }

		 $this->header('browse through records');
		 $this->msg_box();

		 $this->setRunOnRecordEvents();

		 $show_fields_str=$this->bo->read_preferences('show_fields'.$this->bo->site_object['unique_id']); 
		 $default_order=$this->bo->read_preferences('default_order'.$this->bo->site_object['unique_id']);
		 $default_col_num=$this->bo->read_preferences('default_col_num');
		 $rec_per_page = $this->bo->records_per_page();

		 $current_page_arr=$this->bo->session['browse_settings']['current_page'];
		 if($_GET[current_page])
		 {
			$current_page_arr[$this->bo->site_object[object_id]] = $_GET[current_page];
		 }

		 $order_by_arr=$this->bo->session['browse_settings']['orderby'];
		 if($_GET[orderby])
		 {
			$order_by_arr[$this->bo->site_object[object_id]] = $_GET[orderby];
		 }
		 elseif($default_order)
		 {
			$order_by_arr[$this->bo->site_object[object_id]] = $default_order;
		 }

		 $orderby=$order_by_arr[$this->bo->site_object[object_id]];

		 // do not sort is we have created new records
		 if($this->show_last_page)
		 {
			$current_page_arr[$this->bo->site_object[object_id]]='last';
			unset($orderby);
		 }

		 //$filter = ($_GET[filter]?$_GET[filter]:$this->bo->session['browse_settings']['filter']);

		 //the filter class takes care of detecting the current filter, compiling a where statement and compiling the filter options for the listbox
		 $filter_where = $this->filtermanager->get_filter_where();

		 $this->tplsav2->set_var('filter_list',$this->filtermanager->format_filter_options($_POST[filtername]));
		 $this->tplsav2->set_var('filter_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.edit'));
		 $this->tplsav2->set_var('refresh_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->tplsav2->set_var('filter_text',lang('activate filter'));
		 $this->tplsav2->set_var('filter_edit',lang('edit filter'));

		 $quick_filter_arr = $this->bo->session['browse_settings']['quick_filter'];
		 if( trim($_POST[quick_filter]) || $_POST[quick_filter_hidden] )
		 {
			$quick_filter_arr[$this->bo->site_object[object_id]] = trim( $_POST[quick_filter] );
			$current_page_arr[$this->bo->site_object[object_id]]=1;
		 }
		 $quick_filter=$quick_filter_arr[$this->bo->site_object[object_id]];

		 $this->bo->session['browse_settings'] = array
		 (
			'orderby'=>$order_by_arr,
			'quick_filter'=>$quick_filter_arr,
			'current_page'=>$current_page_arr
		 );


		 /* get one with many relations */
		 $relation1_array=$this->bo->extract_O2M_relations($this->bo->site_object['relations']);
		 if (count($relation1_array)>0)
		 {
			foreach($relation1_array as $relation1)
			{
			   $fields_with_relation1[]=$relation1[local_key];
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
		 $column_types = array();
		 if(!is_array($columns)) $columns=array();
		 /* walk through all table columns and fill different array */
		 $fields_show_default = array();
		 foreach($columns as $onecol)
		 {
			$ftype=$this->db_ftypes->complete_resolve($onecol);
			if(!$ftype) $ftype='string';
			$column_types[$onecol['name']] = $ftype;

			/* check for primaries and create array */
			if ($onecol[primary_key] && $onecol[type]!='blob') // FIXME howto select long blobs
			{						
			   $pkey_arr[]=$onecol[name];
			}
			elseif($onecol[type]!='blob') // FIXME howto select long blobs
			{
			   $akey_arr[]=$onecol[name];
			}

			$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$onecol[name]);

			if($field_conf_arr[field_enabled]=='0' && $field_conf_arr[field_enabled]!=null)
			{
			   continue; 
			}

			if($field_conf_arr[list_visibility]!='0')
			{
			   //continue; 
			   $fields_show_default[] = $onecol; //fields allowed
			}

			//create more simple col_list with only names //why
			$all_col_names_list[]=$onecol[name];


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
					 $where_condition.= " OR( `{$onecol[name]}` LIKE '%$like_str%')";
				  }
				  else
				  {
					 $where_condition = "( `{$onecol[name]}` LIKE '%$like_str%')";
				  }
			   }
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

		 $num_rows=$this->bo->so->num_rows_table($this->bo->session['site_id'],$this->bo->site_object['table_name'],$where_condition);
		 
		 $pager=$this->pager($current_page_arr[$this->bo->site_object[object_id]],$num_rows,$rec_per_page);

		 if($current_page_arr[$this->bo->site_object[object_id]]=='last')
		 {
			$current_page_arr[$this->bo->site_object[object_id]]=$this->last_page;
		 }

		 $offset = $this->bo->get_offset($current_page_arr[$this->bo->site_object[object_id]],$rec_per_page);
		 if($offset > $num_rows)
		 {
			unset($offset);
		 }

		 $records = $this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name'],'','',$offset,$rec_per_page,'name',$orderby,'*',$where_condition);

		 $record_count = count($records);


		 //$column zijn alle columns
		 //

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

		 /*
		 if( count($fields_show_default) > count($col_list) )
		 {
			$showextrafieldconf;
		 }
		 */

		 //if pref is less then allowed
		 //if no pref is set and allowed is more then 4




		 /*	check if orderbyfield exist else drop orderby it	*/
		 if(!in_array(trim(substr($orderby,0,(strlen($orderby)-4))),$all_col_names_list)) unset($orderby);

		 // make columnheaders
		 foreach ($col_list as $col)
		 {
			unset($testvalue);

			$field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object[object_id],$col[name]);

			$display_colname=($field_conf_arr[element_label]?$field_conf_arr[element_label]:$col[name]);

			unset($tipmouseover);
			if(trim($field_conf_arr[field_help_info]))
			{
			   $tooltip=$field_conf_arr[field_help_info];
			   if (!is_object($GLOBALS['phpgw']->html))
			   {
				  $GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
			   }
			   $options = array('width' => 'auto');
			   $tipmouseover='<img '.$GLOBALS[phpgw]->html->tooltip($tooltip, True, $options).' src="'.$GLOBALS[phpgw]->common->image('phpgwapi','info').'" alt="" />'; 
			}

			if(!$this->bo->field_is_enabled($this->bo->site_object[object_id], $col[name]))
			{
			   continue ;
			}

			$col_names_list[]=$col[name];
			unset($orderby_link);
			unset($orderby_image);

			$display_colname=($field_conf_arr[element_label]?$field_conf_arr[element_label]:$col[name]);

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

			$this->tplsav2->set_var('colhead_bg_color',$GLOBALS['phpgw_info']['theme']['th_bg']);
			$colname_arr['colhead_bg_color']=$GLOBALS['phpgw_info']['theme']['th_bg'];
			$colname_arr['colhead_order_link']=$GLOBALS[phpgw]->link("/index.php","menuaction=".$this->japielink."jinn.uiu_list_records.display&orderby=$orderby_link");
			$colname_arr['colhead_name']=str_replace('_','&nbsp;',$display_colname);
			$colname_arr['colhead_order_by_img']=$orderby_image;
			$colname_arr['tipmouseover']=$tipmouseover;

			$this->tplsav2->colnames[]=$colname_arr;	
		 }

		 $lang_total_records= lang('%1 records',$num_rows);
//		 $lang_rec_per_page= lang('%1 records per page', $rec_per_page);

		 $this->tplsav2->set_var('list_form_action',$GLOBALS['phpgw']->link('/index.php','menuaction='.$this->japielink.'jinn.bouser.multiple_actions'));
		 $this->tplsav2->set_var('menu_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->tplsav2->set_var('search_string',$quick_filter);

		 if(trim($quick_filter))
		 {
			$this->tplsav2->set_var('quick_filter_bgcolor','background-color:#ffcccc;');
		 }
		 if(trim($filter_where))
		 {
			$this->tplsav2->set_var('adv_filter_bgcolor','background-color:#ffcccc;');
		 }
		 
		 $this->tplsav2->config_columns_link=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.config_objects');

		 $this->tplsav2->set_var('total_records',$lang_total_records);
		 $this->tplsav2->set_var('rec_per_page',$rec_per_page);
		 $this->tplsav2->set_var('pager',$pager);
		 $this->tplsav2->set_var('newrec_link',$GLOBALS[phpgw]->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_edit_record.new_record'));

		 $this->tplsav2->set_var('th_bg',$GLOBALS['phpgw_info']['theme']['th_bg']);
		 $this->tplsav2->set_var('table_title',$this->bo->site_object['name']);
		 $this->tplsav2->set_var('table_descr',$this->bo->site_object['help_information']);
		 $this->tplsav2->popuplink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uiuser.img_popup');

		 if(!is_array($pkey_arr))
		 {
			$pkey_arr=$akey_arr;
			unset($akey_arr);
		 }

		 // Echo the WALK EVENT BUTTONS
		 $this->tplsav2->walklistblock = $this->getWalkListEventButtons();

		 if($this->tplsav2->enable_reports)
		 {
			$this->tplsav2->reportblock = $this->getReportBlock();
		 }
		 
		 if($record_count>0)
		 {
			$this->tplsav2->set_var('colfield_view_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','view'));
			$this->tplsav2->set_var('colfield_edit_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','edit'));
			$this->tplsav2->set_var('colfield_delete_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','delete'));
			$this->tplsav2->set_var('colfield_copy_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','copy'));
			$this->tplsav2->set_var('colfield_lang_confirm_delete_one',lang('Are you sure you want to delete this record?'));
			$this->tplsav2->set_var('colfield_export_img_src',$GLOBALS[phpgw]->common->image('phpgwapi','filesave'));

			$this->tplsav2->records_rows_arr=array();
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
			   $this->tplsav2->set_var('colfield_bg_color',$bgclr);
			   $recrow_arr['colfield_bg_color']=$bgclr;

			   if(count($recordvalues)>0)
			   {
				  $recrow_arr['colfield_check_name']='SEL'.$where_string;
				  $recrow_arr['colfield_check_val']=$where_string;

				  $recrow_arr['colfield_edit_link']=$GLOBALS[phpgw]->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_edit_record.edit_record&where_string='.$where_string);
				  $recrow_arr['colfield_view_link']=$GLOBALS[phpgw]->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_edit_record.read_record&where_string='.$where_string);
				  $recrow_arr['colfield_delete_link']=$GLOBALS[phpgw]->link('/index.php','menuaction='.$this->japielink.'jinn.bouser.del_record&where_string='.$where_string);
				  $recrow_arr['colfield_copy_link']=$GLOBALS[phpgw]->link('/index.php','menuaction='.$this->japielink.'jinn.bouser.copy_record&where_string='.$where_string);

				  //for keeping performance only run when plugins are attached
				  if($this->tplsav2->runonrec_amount>0)
				  {
					 //foreach record render a run on record link
					 $recrow_arr['runonrec_arr']=$this->getRunOnRecordEventIcons($where_string);
				  }

				  $fields_arr=array();

				  foreach($col_names_list  as $onecolname)
				  {
					 $field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object['object_id'],$onecolname);
					 $recordvalue=$recordvalues[$onecolname];
					 if ($recordvalue && is_array($fields_with_relation1) && in_array($onecolname,$fields_with_relation1))
					 {
						$related_value=$this->bo->get_related_value($relation1_array[$onecolname],$recordvalue);
						$recordvalue= '<span style="font-style:italic;">'.$related_value.'</span>';
					 }
					 else
					 {	
						$recordvalue=$this->bo->plug->call_plugin_bv($onecolname, $recordvalue, $where_string, $field_conf_arr, $column_types[$onecolname]);
					 }

					 if ($recordvalue == '')
					 {
						$recordvalue="&nbsp;";
					 }


					 $onefield_arr['value']=$recordvalue;
					 $fields_arr[]= $onefield_arr;
				  }

				  $recrow_arr['fields']=$fields_arr;

				  $this->tplsav2->records_rows_arr[]=$recrow_arr;
			   }
			}
		 }

		 $this->tplsav2->display('list_records.tpl.php');

		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * display_last_records_page 
	  * 
	  * @access public
	  * @return void
	  */
	  function display_last_records_page()
	  {
		 $this->show_last_page=true;
		 $this->display();
	  }

	  function set_activated_list_elements()
	  {
		 $this->tplsav2->action_colspan=0;
		 $this->tplsav2->multi_colspan=0;
		 //_debug_array($this->bo->objectelements);

		 if($this->bo->objectelements['enable_filters'])
		 {
			$this->tplsav2->enable_filters=true;
		 }

		 if($this->bo->objectelements['enable_simple_search'])
		 {
			$this->tplsav2->enable_simple_search=true;
		 }

		 if($this->bo->objectelements['enable_reports'])
		 {
			$this->tplsav2->enable_reports=true;
		 }

		 if($this->bo->objectelements['enable_create_rec']) 
		 {
			$this->tplsav2->enable_create_rec=true;
		 }

		 if($this->bo->objectelements['enable_del']) 
		 {
			$this->tplsav2->enable_del=true;
			$this->tplsav2->action_colspan++;
			$this->tplsav2->multi_colspan++;
		 }

		 if($this->bo->objectelements['enable_edit_rec']) 
		 {
			$this->tplsav2->enable_edit_rec=true;
			$this->tplsav2->action_colspan++;
			$this->tplsav2->multi_colspan++;
		 }

		 if($this->bo->objectelements['enable_export']) 
		 {
			$this->tplsav2->enable_export=true;
			$this->tplsav2->multi_colspan++;
		 }

		 if($this->bo->objectelements['enable_view_rec'])
		 {
			$this->tplsav2->enable_view_rec=true;
			$this->tplsav2->action_colspan++;
			$this->tplsav2->multi_colspan++;
		 }

		 if($this->bo->objectelements['enable_copy_rec']) 
		 {
			$this->tplsav2->enable_copy_rec=true;
			$this->tplsav2->action_colspan++;
		 }

		 if($this->bo->objectelements['enable_multi'] && $this->tplsav2->multi_colspan > 0) // ready for ACL
		 {
			$this->tplsav2->enable_multi=true;
			$this->tplsav2->action_colspan++;
			$this->tplsav2->multi_colspan++;
		 }
		 //echo $this->tplsav2->action_colspan;
	  }

	  function getWalkListEventButtons()
	  {
		 // Get Walk Events
		 $stored_configs = unserialize(base64_decode($this->bo->site_object[events_config]));
		 if(is_array($stored_configs))
		 {
			foreach($stored_configs as $conf_arr)
			{
			   if($conf_arr['conf']['event']=='on_walk_list_button')
			   {
				  $this->tplsav2->walkbuttons_arr[]=$conf_arr;
			   }
			}
		 }
		 $this->tplsav2->walkevent_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.popwalkevent');

		 $buttonrow=$this->tplsav2->fetch('walk_buttons.tpl.php');
		 return $buttonrow;

	  }
	  function getReportBlock()
	  {
		 $this->tplsav2->set_var('listoptions',$this->boreport->get_report_list($this->bo->site_object[unique_id]));

		 $r_edit_button =  $output .= "<input class='egwbutton'  type='button' value='".lang('Edit')."' onClick=\"if(document.report_actie.report.value.substr(0,4) != 'user'){alert('".lang('You can only edit your own templates')."');}else{parent.window.open('".$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.edit_report_popup&parent_site_id='.$this->bo->site_object[parent_site_id].'&obj_id='.$this->bo->site_object['unique_id'].'&table_name='.$this->bo->site_object[table_name].'&report_id=')."'+document.report_actie.report.value, 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')}\">";		 

		 $this->tplsav2->set_var('r_edit_button',$r_edit_button);

		 $r_new_from_button = "<input class='egwbutton'  type='button' value='".lang('New from selected')."' onClick=\"parent.window.open('".$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.add_report_from_selected&obj_id='.$this->bo->site_object['unique_id'].'&parent_site_id='.$this->bo->site_object[parent_site_id].'&table_name='.$this->bo->site_object[table_name].'&report_id=')."'+document.report_actie.report.value, 'pop', 'width=800,height=600,location=no,menubar=no,directories=no,toolbar=no,scrollbars=yes,resizable=yes,status=no')\">";		 

		 $this->tplsav2->set_var('r_new_from_button',$r_new_from_button);

		 $report_url=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.merge_report&obj_id='.$this->bo->site_object['unique_id']) ;

		 $add_report_url = $GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uireport.add_report_user&parent_site_id='.$this->bo->site_object[parent_site_id].'&table_name='.$this->bo->site_object[table_name].'&preference=1&obj_id='.$this->bo->site_object['unique_id']);
		 $this->tplsav2->set_var('lang_merge',lang('Merge'));
		 $this->tplsav2->set_var('add_report_url',$add_report_url);
		 $this->tplsav2->set_var('report_url',$report_url);
		 //		 $this->tplsav2->set_var('lang_search', lang('Search'));
		 $this->tplsav2->set_var('lang_new_report',lang('New Report'));

		 $reportblock=$this->tplsav2->fetch('list_rec_reportsblock.tpl.php');
		 return $reportblock;
	  }

	  function setRunOnRecordEvents()
	  {
		 $stored_configs = unserialize(base64_decode($this->bo->site_object['events_config']));
		 $this->tplsav2->runonrec_amount = 0;
		 if(is_array($stored_configs))
		 {
			foreach($stored_configs as $key => $conf_arr)
			{
			   if($conf_arr['conf']['event']=='run_on_record')
			   {
				  $conf_arr['runonrecordevent_link']=$GLOBALS['phpgw']->link('/index.php','menuaction='.$this->japielink.'jinn.uiuser.runonrecord&plgkey='.$key);
				  $this->runonrecord_arr[]=$conf_arr;
				  $this->tplsav2->runonrec_amount ++;
			   }
			}
		 }
	  }
	  
	  function getRunOnRecordEventIcons($where_string)
	  {
//		 $this->tplsav2->iconfilepath=$this->bo->site_fs->get_jinn_sitefile_url($object_arr[parent_site_id]).SEP.'object_events'.SEP.$edit_conf['name'].SEP.$edit_conf['iconfile'];
//		 $this->tplsav2->objevent_file_path=$this->bo->site_fs->get_jinn_sitefile_url($object_arr[parent_site_id]).SEP.'object_events';
		 foreach($this->runonrecord_arr as $key => $conf_arr)
		 {
			$conf_arr['runonrecordevent_link'].='&base64_where_string='.$where_string;
			if($conf_arr['iconfile'])
			{
			   $conf_arr['iconfilepath'] = $this->bo->site_fs->get_jinn_sitefile_url($this->bo->site_object['parent_site_id']).SEP.'object_events'.SEP.$conf_arr['name'].SEP.$conf_arr['iconfile'];
			}
			$this->tplsav2->runonrecordbuttons=$conf_arr;
			
			$buttonrow[]=$this->tplsav2->fetch('runonrecord_icons.tpl.php');
		 }
		 return $buttonrow;
	  }

   }

?>
