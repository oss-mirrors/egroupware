<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2006 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

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

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.uijinn.inc.php');

   /**
   * uiu_edit_record 
   * 
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiu_edit_record extends uijinn
   {
	  /**
	  * public_functions 
	  * 
	  * @var mixed
	  * @access public
	  */
	  var $public_functions = Array
	  (
		 'new_record'				=> True,
		 'edit_record'				=> True,
		 'read_record'				=> True,
		 'ajax_get_m2o_frm'			=> True,//depr
		 'ajax_save_m2o_frm'		=> True,//depr
		 'ajax_delete_m2o'			=> True,//depr
		 'ajax_get_m2o_list'		=> True,//depr
		 'dev_change_field_order'	=> True,
		 'delete_element'			=> True,
		 'dev_edit_record' 			=> True,
		 'ajax2_get_m2o_list'   	=> True,
		 'ajax2_get_m2o_frm' 		=> True,
		 'ajax2_save_m2o_frm' 		=> True,
		 'ajax2_del_m2o_rec' 		=> True
	  );

	  var $mult_records;	
	  var $mult_index;

	  var $o2o_index;

	  var $record_id_key;
	  var $record_id_val;

	  var $submit_javascript;
	  var $jstips;

	  /**
	  * hiddenfields: stores hidden inputs for rendering outside the form tables
	  * 
	  * @var mixed
	  * @access public
	  */
	  var $hiddenfields; 
	  var $jshidden;

	  var $db_ftypes;

	  var $tplsav2;

	  /**
	  * relation1_array: one-2-many info array
	  * 
	  * @var mixed
	  * @access public
	  */
	  var $relation1_array;  

	  var $japielink;

	  /**
	  * uiu_edit_record: contructor
	  * 
	  * @access public
	  * @return void
	  */
	  function uiu_edit_record($session_name='jinn')
	  {
		 $this->bo = CreateObject('jinn.bouser',$session_name);
		 parent::uijinn();

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');

		 /* todo: test if this works */
		 if (!is_object($GLOBALS['phpgw']->js))
		 {
			$GLOBALS['phpgw']->js = CreateObject('phpgwapi.javascript');
		 }
		 if (!strstr($GLOBALS['phpgw_info']['flags']['java_script'],'jinn'))
		 {
			$GLOBALS['phpgw']->js->validate_file('jinn','display_func','jinn');
			$GLOBALS['phpgw']->js->validate_file('wz_dragdrop','wz_dragdrop');
			$GLOBALS['phpgw']->js->validate_file('jinn','ajax','jinn');
		 }

		 if (!is_object($GLOBALS['phpgw']->html))
		 {
			$GLOBALS['phpgw']->html = CreateObject('phpgwapi.html');
		 }

		 // prevent ugly error
		 if(!$this->bo->site_object['object_id'])
		 {
			$this->bo->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
		 }

		 
		 $this->test_object();
	  }

	  function delete_element()
	  {
		 $this->bo->so->delete_obj_field_rec($_GET['object_id'], $_GET['field_name']);
		 $this->dev_edit_record();
	  }
	  
	  function dev_change_field_order()
	  {
		 if($_GET['movefield'] && $_GET['up']=='true')
		 {
			$this->bo->so->reorder_obj_fields_table($this->bo->site_object['object_id'],$_GET['movefield'],'up');
		 }
		 elseif($_GET['movefield'] && $_GET['down']=='true')
		 {
			$this->bo->so->reorder_obj_fields_table($this->bo->site_object['object_id'],$_GET['movefield'],'down');
		 }

		 $this->dev_edit_record();
	  }

	  /**
	  * dev_edit_record: shortcut to edit_record in developers mode
	  * 
	  * @todo move to uiadmin
	  * @access public
	  * @return void
	  */
	  function dev_edit_record()
	  {
		 $this->tplsav2->devtoolbar=$this->get_developer_object_toolbar();

		 if($_POST['objectsaved'])
		 {
			$data[]=array(
			   'name'=>'layoutmethod',
			   'value'=>$_POST['formtype']
			);
			$data[]=array(
			   'name'=>'formheight',
			   'value'=>$_POST['formheight']
			);
			$data[]=array(
			   'name'=>'formwidth',
			   'value'=>$_POST['formwidth']
			);

			$where_string="`object_id`='{$_POST['object_id']}'";
			$status = $this->bo->so->update_phpgw_data('egw_jinn_objects',$data,'','',$where_string,true);

			$this->bo->site_object=	$this->bo->so->get_object_values($this->bo->site_object['object_id']);

			$fields_arr=$this->bo->filter_array_with_prefix($_POST,'FIELDS',true);

			if(is_array($fields_arr))
			{
			   foreach($fields_arr as $field)
			   {
				  unset($data);
				  $data[]=array(
					 'name'=>'canvas_label_x',
					 'value'=>$_POST['POS'.$field.'canvas_label_x']
				  );

				  $data[]=array(
					 'name'=>'canvas_label_y',
					 'value'=>$_POST['POS'.$field.'canvas_label_y']
				  );	
				  $data[]=array(
					 'name'=>'canvas_field_x',
					 'value'=>$_POST['POS'.$field.'canvas_field_x']
				  );
				  $data[]=array(
					 'name'=>'canvas_field_y',
					 'value'=>$_POST['POS'.$field.'canvas_field_y']
				  );

				  $where_string="`field_parent_object`='{$this->bo->site_object['object_id']}' AND `field_name`='{$field}'";

				  $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true);

			   }
			}

			$_POST=array();
		 }

		 $_GET['edit_obj']='yes';

		 /* FIXME move reports to list view. tempary place to add/edit reports */
		 $this->boreport = CreateObject('jinn.boreport');
		 $this->tplsav2->report_vals['table_name']=$this->bo->site_object['table_name'];
		 $this->tplsav2->report_vals['parent_site_id']=$this->bo->site_object['parent_site_id'];
		 //$this->tplsav2->report_vals['object_id']=$this->bo->site_object['unique_id'];
		 $this->tplsav2->assign('report_list',$this->boreport->get_report_list($this->bo->site_object['object_id'],2));

		 $this->tplsav2->edit_object=True;

		 $this->tplsav2->add_element_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.add_element&object_id='.$this->bo->site_object['object_id']);
		 $this->tplsav2->link_delete_element=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.delete_element&object_id='.$this->bo->site_object['object_id']);
		 
//		 $this->tplsav2->relation_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_relation_widgets&object_id='.$this->bo->site_object['object_id']);
//		 $this->tplsav2->gen_obj_options_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.edit_gen_obj_options&object_id='.$this->bo->site_object['object_id']);
//		 $this->tplsav2->obj_event_plugins_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.object_events_config&object_id='.$this->bo->site_object['object_id']);

//		 $this->tplsav2->normal_mode_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.edit_record&where_string='.$this->bo->where_string_encoded);

		 $this->tplsav2->img_eyehidden=$GLOBALS['phpgw']->common->image('jinn','eyehidden');
		 $this->tplsav2->img_eyevisible=$GLOBALS['phpgw']->common->image('jinn','eyevisible');
		 $this->tplsav2->img_fld_enabled=$GLOBALS['phpgw']->common->image('jinn','fld_enabled');
		 $this->tplsav2->img_fld_disabled=$GLOBALS['phpgw']->common->image('jinn','fld_disabled');
		 $this->tplsav2->xmlhttp_visible_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.xmlhttp_req_toggle_field_visible');
		 $this->tplsav2->xmlhttp_listvisible_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.xmlhttp_req_toggle_field_listvisible');
		 $this->tplsav2->xmlhttp_enabled_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiadmin.xmlhttp_req_toggle_field_enabled');

		 $this->tplsav2->gridimg=$GLOBALS['phpgw']->common->image('jinn','grid.gif');
		 $this->tplsav2->draghandle=$GLOBALS['phpgw']->common->image('jinn','draghandle');

		 $this->edit_record(); 
	  }

	  /**
	  * read_record: shortcut to edit_record in readonly mode
	  * 
	  * @access public
	  * @return void
	  */
	  function read_record()
	  {
		 $this->readonly=true;
		 $this->tplsav2->readonly=true;
		 $this->tplsav2->viewrecord=true;
		 $this->tplsav2->edit_record_link=$GLOBALS['phpgw']->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_edit_record.edit_record&where_string='.$this->bo->where_string_encoded);

		 $this->edit_record(); 
	  }

	  /**
	  * new_record 
	  * 
	  * @access public
	  * @return void
	  */
	  function new_record()
	  {
		 unset($this->bo->session['mult_where_array']);
		 $this->edit_record();
	  }

	  /**

	  * edit_record: make form to insert record record
	  * 
	  * @access public
	  * @todo o2m relations
	  * @todo multiple o2m/o2o relations
	  * @todo field order
	  * @todo more fields on one row
	  * @todo nicer buttons
	  * @todo more quick edit icons in developer mode
	  * @todo cleanup class
	  * @todo multiple record update/insert
	  * @todo read record / read multiple
	  * @todo fix hide fields
	  * @todo remove record update/insert code everything is multiple
	  * @todo protect record
	  * @todo protect field
	  * @todo test ... 
	  * @todo test ... 
	  * @todo test ...
	  * @return void
	  */
	  function edit_record()
	  {
		 if($_POST['submitted'])
		 {
			if($_POST['num_records'] && $_POST['changerecnumbers']=='true')
			{
			   $this->bo->mult_change_num_records();
			}
			elseif(is_array($this->bo->session['mult_where_array']) and is_numeric($_POST['MLTNUM']) and intval($_POST['MLTNUM'])>0)
			{
			   $ill_prefix=array('M2O');
			   $status=$this->bo->multiple_records_update($this->bo->session['mult_where_array'],intval($_POST['MLTNUM']),$this->bo->site_object,$ill_prefix);
			   if($status['eventstatus']['error']) 
			   {
				  $this->bo->addError(lang('Error in event plugin'));
			   }
   
			   if ($status['record']['error'])	
			   {
				  if(intval($_POST['MLTNUM'])==1)
				  {
					 $this->bo->addError(lang('Record was NOT succesfully saved.'));
				  }
				  else
				  {
					 $this->bo->addError(lang('Records were NOT succesfully saved.'));
				  }
			   }
			   else 
			   {
				  if(intval($_POST['MLTNUM'])==1)
				  {
					 $this->bo->addInfo(lang('Record was succesfully saved.'));
				  }
				  else
				  {
					 $this->bo->addInfo(lang('Records were succesfully saved.'));
				  }
			   }

			   $this->bo->addDebug(__LINE__,__FILE__,$status['record']['sql'],$status['record']['where_string']);
			   if($this->bo->site_object['max_records']==1)
			   {
				  $this->bo->exit_and_open_screen($this->japielink.'jinn.uiuser.index');
			   } 
			}
			elseif($_POST['MLTNUM'] and intval($_POST['MLTNUM'])>0)
			{
			   $status=$this->bo->multiple_records_insert(intval($_POST['MLTNUM']),$this->bo->site_object); 
			   if ($status['error'])
			   {
				  if(intval($_POST['MLTNUM'])==1)
				  {
					 $this->bo->addError(lang('Record is NOT succesfully added.'));
				  }
				  else
				  {
					 $this->bo->addError(lang('Records were NOT succesfully added.'));
				  }
				  
			   }
			   else 
			   {
				  if(intval($_POST['MLTNUM'])==1)
				  {
					 $this->bo->addInfo(lang('Record is succesfully added.'));
				  }
				  else
				  {
					 $this->bo->addInfo(lang('Records were succesfully added.'));
				  }
				  
				  $this->bo->addDebug(__LINE__,__FILE__,$status['sql']);

				  if($_POST['savereopen'])
				  {
					 $this->bo->session['mult_where_array']=$status['mult_where_array'];
				  }
				  else
				  {
					 // open page with last created records
					 $this->bo->exit_and_open_screen($this->japielink.'jinn.uiu_list_records.display_last_records_page');
				  }
			   }
			}
		 }	

		 $this->relation1_array = $this->bo->extract_O2M_relations($this->bo->site_object['relations']);

		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			   $this->tplsav2->edit_object_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.dev_edit_record&edit_obj=yes&where_string='.$this->bo->where_string_encoded);
			   $this->tplsav2->change_field_order_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.dev_change_field_order&edit_obj=yes&where_string='.$this->bo->where_string_encoded);
		 }

		 /*
		 every situation is a mulitedit situation
		 */
		 if(!is_array($this->bo->session['mult_where_array']) && $this->bo->where_string) 
		 {
			//the new appoach for inserting just one record
			$this->bo->session['mult_where_array']= array($this->bo->where_string);
			$this->bo->sessionmanager->save();
		 }

		 if(is_array($this->bo->session['mult_where_array']))
		 {
			$this->mult_records=count($this->bo->session['mult_where_array']);
		 }
		 else
		 {
			//the new appoach for inserting just one record
			if(!is_numeric($this->bo->session['mult_records_amount']))
			{
			   $this->mult_records=1;
			}
			elseif(intval($this->mult_records)>99)
			{
			   $this->bo->addError(lang("Can't edit more then 99 record at once"));
			   $this->mult_records=3;// FIXME get from user
			}
			else
			{
			   $this->mult_records=$this->bo->session['mult_records_amount'];// FIXME get from user
			}
		 }

		 for($i=0;$i<$this->mult_records;$i++)
		 {
			$this->mult_index=sprintf("%02d",$i);

			if($this->bo->session['mult_where_array'])
			{
			   $this->bo->where_string=$this->bo->session['mult_where_array'][$i];

			   $where_string_record_arr['MLTWHR'.$this->mult_index]=base64_encode($this->bo->where_string);
			}

			$this->tplsav2->runonrecordbuttons=$this->getRunOnRecordEventButtons($this->bo->where_string);

			// FIXME make this standard available via GET
			if($this->bo->where_string)
			{
			   $this->values_object=$this->bo->so->get_record_values($this->bo->session['site_id'],$this->bo->site_object['table_name']
			   ,'','','','','name','','*',$this->bo->where_string);
			}

			//FIXME move to the constructor
			//FIXME improve performance, remove doubles
			$this->all_fields_conf_arr = $this->bo->so->mk_field_conf_arr_for_obj($this->bo->site_object['object_id']);

			// get regular fields and o2m relations
			$fields_arr=$this->mk_fields_array('MLTX'.$this->mult_index,$this->bo->site_object,$this->values_object[0],$this->readonly);
			$elements_arr=$this->mk_elements_array('ELEX'.$this->mult_index,$this->bo->site_object,$this->values_object[0],$this->readonly);

			$m2m_arr=array();
			$m2o_arr=array();//todo set order
			$o2o_arr=array();//todo set order

			$m2m_arr=$this->mk_m2m_array();
			$o2o_arr=$this->mk_o2o_array();

			if($this->bo->where_string)
			{
			   $m2o_arr=$this->mk_m2o_array();//todo set order
			}

			$complete_elements_array=array_merge($fields_arr,$elements_arr,$o2o_arr,$m2m_arr,$m2o_arr,$o2o_arr);

			$_tmp_order_arr=array();
			$big=100000;
			foreach($complete_elements_array as $one_el)
			{
			   $one_el['orig_list_order']=$one_el['form_listing_order'];
			   //make array with existing numbers
			   //set pointer

			   //if($one_el['form_listing_order']==0 || $one_el['form_listing_order']==999) 
			   if(in_array($one_el['form_listing_order'],$_tmp_order_arr)) 
			   {
				  $one_el['form_listing_order']=$big;
				  $big++;
			   }
			   $_tmp_order_arr[]=$one_el['form_listing_order'];


			   $new_elements_array[$one_el['form_listing_order']]=$one_el;
			}
			ksort($new_elements_array);
			$sorted_elements=$new_elements_array;

			$this->tplsav2->records_arr[]=$this->parse_fields_to_layout($sorted_elements);
		 }

		 /* FIXME make sure a new record can not be made in any way */
		 $this->tplsav2->max_records=$this->bo->site_object['max_records']; 

		 if(!$this->tplsav2->edit_object)
		 {
			$this->tplsav2->assign('hiddenfields',$this->hiddenfields);

			if(is_array($this->jshidden))
			{
			   $param=implode(',',$this->jshidden);
			   $jinnHideFields="jinnHideFields($param);";
			}

			$this->tplsav2->assign('jshidefields',$jinnHideFields);
		 }

		 $this->tplsav2->mult_records=$this->mult_records;
		 $this->tplsav2->listing_link=$GLOBALS['phpgw']->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_list_records.display');
		 $this->tplsav2->popuplink=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.img_popup');
		 $this->tplsav2->form_action = $GLOBALS['phpgw']->link('/index.php','menuaction='.$this->japielink.'jinn.uiu_edit_record.edit_record&where_string='.$_GET['where_string']);
		 $this->tplsav2->where_string_form = base64_encode($this->bo->where_string);
		 $this->tplsav2->where_string_record_arr=$where_string_record_arr;
		 $this->tplsav2->submit_script=$this->submit_javascript;
		 $this->tplsav2->site_object_arr= $this->bo->site_object;
		 $this->tplsav2->assign('helplink',$GLOBALS['phpgw']->link('/manual/index.php'));

		 $this->tplsav2->assign('img_edit',$GLOBALS['phpgw']->common->image('phpgwapi','edit'));
//		 $this->tplsav2->assign('tooltip_img',$GLOBALS['phpgw']->common->image('phpgwapi','info'));

		 $this->header('edit record');

		 $this->msg_box();

		 $this->tplsav2->display('frm_edit_record.tpl.php');
	  }


	  /**
	  * parse_fields_to_layout: all fields (real, relations, etc..) are sorted and extra info is added  
	  * 
	  * visible 
	  * enabled
	  * alt name
	  * help info
	  *    
	  * @param array $fields_arr all fields in array
	  * @access public
	  * @return void
	  */
	  function parse_fields_to_layout($fields_arr)
	  {
		 foreach($fields_arr as $single_fld_arr)
		 {
			$single_fld_arr['parent_object']=($single_fld_arr['parent_object']?$single_fld_arr['parent_object']:$this->bo->site_object['object_id']);
			//fixme improve speed!!!
			$field_conf_arr=$this->bo->so->get_field_values($single_fld_arr['parent_object'],$single_fld_arr['fieldname']);

			if(!$this->tplsav2->edit_object && $field_conf_arr['field_enabled']!='1' && $field_conf_arr['field_enabled']!=0)
			{
			   continue;
			}

			$single_fld_arr['canvas_field_x']=$field_conf_arr['canvas_field_x'];
			$single_fld_arr['canvas_field_y']=$field_conf_arr['canvas_field_y'];
			$single_fld_arr['canvas_label_x']=$field_conf_arr['canvas_label_x'];
			$single_fld_arr['canvas_label_y']=$field_conf_arr['canvas_label_y'];

			if($field_conf_arr['element_label'])
			{
			   $single_fld_arr['display_name']=$field_conf_arr['element_label'];
			}
			else
			{
			   $single_fld_arr['display_name']=$display_name = ucfirst(strtolower(ereg_replace("_", " ", $single_fld_arr['fieldname']))); 
			}

			if(trim($field_conf_arr['field_help_info']))
			{
			   $tooltip=$field_conf_arr['field_help_info'];

			   $options = array('width' => 'auto');

			   //$single_fld_arr['tooltip_mouseover']=$GLOBALS['phpgw']->html->tooltip($tooltip, True, $options);
			   $single_fld_arr['field_help_info']=$field_conf_arr['field_help_info'];
			}

			/* set all extra's for the developers edit mode */
			if($this->tplsav2->edit_object)
			{
			   if($field_conf_arr['list_visibility']=='0' && $field_conf_arr['list_visibility']!=null)
			   {
				  // to render the sweet little eye
				  $single_fld_arr['listvisible']='hide';
			   }
			   if($field_conf_arr['form_visibility']=='0')
			   {
				  // to render the sweet little eye
				  $single_fld_arr['visible']='hide';
			   }

			   if($field_conf_arr['field_enabled']=='0' && $field_conf_arr['field_enabled']!=null && $field_conf_arr['field_enabled']!='')
			   {
				  // to render the sweet little eye
				  $single_fld_arr['disabled']='disabled';
			   }
			   // fixme remove unneeded getvars
			   $single_fld_arr['editfieldlink']=$GLOBALS['phpgw']->link('/jinn/plgconfwrapper.php','screen=editfield&plug_orig='.$plug_arr['plugname']
			   .'&plug_name='.$plug_arr['plugname'].'&field_name='.$single_fld_arr['fieldname'].'&object_id='.$single_fld_arr['parent_object']);
			}

			if(!$this->tplsav2->edit_object && $field_conf_arr['form_visibility'] == 0 && $field_conf_arr['form_visibility'] !="")
			{
			   $this->jshidden[] ="'".'TR'.$single_fld_arr['fieldname']."'";
			}

			$ret_arr[]=$single_fld_arr;
		 }

		 return $ret_arr; 
	  }

	  /**
	  * mk_fields_array: create the array of all regular fields that are automaticly rendered
	  * 
	  * @param string $field_prefix to use for the input name
	  * @param array $object_arr the object array containing values like table_name etc...
	  * @param mixed $record_values 
	  * @param mixed $readonly 
	  * @access public
	  * @return array with VALUE INPUT_NAME FIELDNAME AND INPUT (FORM INPUT ELEMENTS) per field
	  */
	  function mk_fields_array($field_prefix,$object_arr,$record_values=false,$readonly=false)
	  {
		 $all_fields_conf_arr = $this->bo->so->mk_field_conf_arr_for_obj($object_arr['object_id']);
		 $fields_meta_data= $this->bo->so->site_table_metadata($this->bo->session['site_id'],$object_arr['table_name']);

		 //FIXME  maybe we can cat meta data per field with func below?
		 //$field_meta_arr=$this->bo->so->object_field_metadata($_GET['object_id'],$field_conf_arr['data_source']);

		 foreach ($fields_meta_data as $fprops)
		 {	
			unset($single_fld_arr);
			unset($fld_readonly);
			/* get the field type according to the JiNN classification */
			$ftype=$this->db_ftypes->complete_resolve($fprops);
			if(!$ftype)
			{
			   $ftype='string';
			}

			$field_conf_arr=$all_fields_conf_arr[$fprops['name']];

			if($this->tplsav2->edit_object)
			{
			   //check if obj_field record exist and make one if not
			   if(!is_array($all_fields_conf_arr[$fprops['name']]))
			   {
				  //add new entry
				  $new_field_name=$fprops['name'];
				  unset($data);
				  $data[]=array(
					 'name'=>'field_name',
					 'value'=> $new_field_name //zie relaties
				  );

				  $data[]=array(
					 'name'=>'field_parent_object',
					 'value'=>$object_arr['object_id']
				  );

				  $data[]=array(
					 'name'=>'element_type',
					 'value'=>'auto'
				  );

				  $where_string="`field_parent_object`='{$_GET['object_id']}' AND  `field_name`='{$_GET['field_name']}'";

				  $status = $this->bo->so->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string,true); // do insert when not existing
				  $this->bo->addInfo(lang('Add metadata for unknown field: %1.',$new_field_name));
			   }
			}

			if(!$this->tplsav2->edit_object && $field_conf_arr['field_enabled']=='0' && $field_conf_arr['field_enabled']!=null)
			{
			   continue;
			}
			if(!$this->tplsav2->edit_object && $field_conf_arr['form_visibility']=='0' && $field_conf_arr['form_visibility']!=null)
			{
			   continue;
			}

			$single_fld_arr['form_listing_order']=$field_conf_arr['form_listing_order'];
			$single_fld_arr['label_visibility']=$field_conf_arr['label_visibility'];
			$single_fld_arr['single_col']=$field_conf_arr['single_col'];
			$single_fld_arr['fe_readonly']=$field_conf_arr['fe_readonly'];

			$single_fld_arr['parent_object']=$object_arr['object_id'];
			$single_fld_arr['element_type']='auto';

			if($readonly || $field_conf_arr['fe_readonly'] || $field_conf_arr['readonly'])
			{
			   $fld_readonly=true;	
			}

			/* get value */
			if($record_values)
			{
			   $single_fld_arr['value']=$record_values[$fprops['name']];				
			}

			/* add FLD or other prefix so we can identify the real input keys in _POST */
			$single_fld_arr['input_name']=$field_prefix.$fprops['name'];	

			$single_fld_arr['fieldname']=$fprops['name'];	

			/* auto increment */
			// fixme move to a more logical place e.g. constructor
			if ($ftype=='auto')
			{
			   $this->record_id_key=$single_fld_arr['input_name']; // this one is very needed by m2m relations FIXME
			   $this->record_id_val=$single_fld_arr['value'];
			}

			/* If this field has a relation, get that options */
			/* FIXME rel1 arr must be given as arg */
			if( ( $ftype=='string' || $ftype=='int' )  &&  (is_array($this->relation1_array) && is_array($this->relation1_array[$fprops['name']])) )
			{
			   $single_fld_arr['input']=$this->mk_o2m($fprops,$single_fld_arr['input_name'],$single_fld_arr['value'],$fld_readonly);
			}
			else
			{
			   if($fprops['len'] && $fprops['len']!=-1)
			   {
				  $attr_arr=array(
					 'max_size'=>$fprops['len'],
				  );
			   }

			   if($fld_readonly)
			   {
				  $plug_arr['html']=$this->bo->plug->call_plugin_ro($single_fld_arr['value'], $field_conf_arr, $ftype);
			   }
			   else
			   {
				  $plug_arr = $this->bo->plug->call_plugin_fi($single_fld_arr['input_name'], $single_fld_arr['value'], $ftype, $field_conf_arr, $attr_arr);
			   }

			   //some plugins return an array containing extra info to be considered:
			   // TODO: fixme this has to be done some way else
			   if(is_array($plug_arr))
			   {
				  if($plug_arr['__hidden__'])	//render this field as a hidden parameter
				  {
					 $this->hiddenfields .= $plug_arr['html'];
					 $single_fld_arr['input']='__disabled__';
				  }
				  else
				  {
					 $single_fld_arr['input']=$plug_arr['html'];
					 $single_fld_arr['eval']=$plug_arr['eval']; //fixme what does this? hmmmm
				  }
			   }
			}

			// FIXME code below is depreciated
			/* if there is something to render to this */
			if($single_fld_arr['input']!='__disabled__')
			{
			   $return_fld_arr[$fprops['name']]=$single_fld_arr;
			}
		 }

		 return $return_fld_arr;
	  }

	  /**
	  * mk_elements_array: create array of all not auto rendered object elements like lay-out elements and table_fields with uniqid's  
	  * 
	  * @access public
	  * @return void
	  */
	  function mk_elements_array($field_prefix,$object_arr,$record_values=false,$readonly=false)
	  {
		 $elements_arr = $this->bo->so->mk_element_conf_arr_for_obj($object_arr['object_id']);
		
		 if(count($elements_arr)<1)
		 {
			$elements_arr=array(); 
		 }
		 foreach($elements_arr as $el)
		 {
			$fieldname=$el['field_name'];
			$el_input_name=$field_prefix.'UNIQ'.$el['field_name'];
			if($el['element_type']=='table_field' && $el['data_source'])
			{
			   $el_input_name.='SOURCE'.$el['data_source'];
			   $el_value=$record_values[$el['data_source']];

			   $field_meta_arr=$this->bo->so->object_field_metadata($object_arr['object_id'],$el['data_source']);
			   $fieldtype_for_plugin=$field_meta_arr['type'];
			}
			else
			{
			   $fieldtype_for_plugin='joker';
			}

			$plug_arr = $this->bo->plug->call_plugin_fi($el_input_name,$el_value,$fieldtype_for_plugin, $el, $attr_arr);

			$ret_elements_arr[$el['field_name']]=array(
			   'parent_object'=>$el['field_parent_object'],
			   'value' => $el_value,
			   'form_listing_order' => $el['form_listing_order'],
			   'input_name' => $el_input_name,
			   'fieldname' => $fieldname,
			   'element_type' => $el['element_type'],
			   'input' => $plug_arr['html'],
			   'single_col' => $el['single_col'], 
			   'fe_readonly' => $el['fe_readonly'], 
			   'label_visibility' => $el['label_visibility'], 
			   'eval' => ''
			);
		 }

		 return $ret_elements_arr;
	  }

	  /**
	  * mk_o2m: creates the one-to-many widgets
	  * 
	  * @param mixed $fprops 
	  * @param mixed $input_name 
	  * @param mixed $value 
	  * @param mixed $read_only 
	  * @access public
	  * @return void
	  */
	  function mk_o2m($fprops,$input_name,$value,$read_only)
	  {
		 if($read_only)
		 {
			if($value)
			{
			   $this->tplsav2->related_value=$this->bo->get_related_value($this->relation1_array[$fprops['name']],$value);
			}
		 }
		 else
		 {
			$related_fields=$this->bo->get_related_field($this->relation1_array[$fprops['name']]);

			if(is_array($related_fields))
			{
			   foreach ($related_fields as $rel_field)
			   {
				  $this->tplsav2->related_fields_keyed[$rel_field['value']]=$rel_field['name'];
			   }
			}
			$this->tplsav2->fprops=$fprops;
			$this->tplsav2->related_fields=$related_fields;
			$this->tplsav2->input_name=$input_name;
			$this->tplsav2->value=$value;

		 }

		 $widget=$this->tplsav2->fetch('one-to-many.1.tpl.php');
		 return $widget;
	  }

	  /**
	  * make all m2m relation widgeds and return as array
	  * 
	  * @access public
	  * @return void
	  */
	  function mk_m2m_array()
	  {

		 // fixme create read only widget
		 if($this->readonly)
		 {
			return;	
		 }

		 if($this->mult_records>1 && is_numeric($this->mult_index)) 
		 {
			$prefix1='M2MX'.$this->mult_index; 
			$prefix2='M2MA'.$this->mult_index;
			$prefix3='M2MO'.$this->mult_index;
			$prefix4='M2MR'.$this->mult_index;
		 }
		 else
		 {
			$prefix1='M2MXXX'; // related options
			$prefix2='M2MAXX'; // all options
			$prefix3='M2MOXX'; // related options (stored in db)
			$prefix4='M2MRXX'; // info about relation
		 }

		 $relation2_array=$this->bo->extract_M2M_relations($this->bo->site_object['relations']);

		 if (count($relation2_array)>0)
		 {
			$rel_i=0;
			foreach($relation2_array as $relation2)
			{
			   unset($single_m2m);

			   $related_table=$relation2['display_table'];
			   $rel_i++;

			   $display_name=lang('relation %1',$rel_i);
			   $sel1_all_from=lang('All possible entries from %1', $relation2['foreign_table']);
			   $on_dbl_click1='SelectPlace(\''.$prefix1.$rel_i.'\',\''.$prefix2.$rel_i.'\')';
			   $on_dbl_click2='DeSelectPlace(\''.$prefix1.$rel_i.'\')';

			   $sel1_name=''.$prefix2.$rel_i;
			   $sel2_name=''.$prefix1.$rel_i;

			   $options_arr= $this->bo->so->get_m2m_record_values($this->bo->session['site_id'],'',$relation2,'all');

			   $sel1_options = $this->select_options($options_arr,'',false);
			   $lang_related=lang('related').' '.$related_table;

			   $this->submit_javascript.='saveOptions(\''.$prefix1.$rel_i.'\',\''.$prefix3.$rel_i.'\');'."\n";

			   if($this->record_id_val)
			   {
				  $record_id=$this->record_id_val;
				  $options_arr= $this->bo->so->get_m2m_record_values($this->bo->session['site_id'],$record_id,$relation2,'stored');
				  $sel2_options= $this->select_options($options_arr,'',false);
			   }
			   elseif(!$this->record_id_key)
			   {
				  $sel2_options= '<option>'.lang('This table has not unique identifier field').'</option>';
				  $sel2_options.= '<option>'.lang('Many 2 Many relations will not work').'</option>';
			   }

			   $m2m_rel_string_name=''.$prefix4.$rel_i;
			   $m2m_rel_string_val= base64_encode(serialize($relation2));
			   $m2m_opt_string_name=''.$prefix3.$rel_i;

			   // fixme cleanup
			   $this->tplsav2->set_var('sel1_all_from',$sel1_all_from);
			   $this->tplsav2->set_var('on_dbl_click1',$on_dbl_click1);
			   $this->tplsav2->set_var('on_dbl_click2',$on_dbl_click2);
			   $this->tplsav2->set_var('sel1_name',$sel1_name);
			   $this->tplsav2->set_var('sel2_name',$sel2_name);
			   $this->tplsav2->set_var('sel1_options',$sel1_options);
			   $this->tplsav2->set_var('sel2_options',$sel2_options);
			   $this->tplsav2->set_var('m2m_rel_string_name',$m2m_rel_string_name);
			   $this->tplsav2->set_var('m2m_rel_string_val',$m2m_rel_string_val);
			   $this->tplsav2->set_var('m2m_opt_string_name',$m2m_opt_string_name);

			   $this->tplsav2->set_var('m2mrow_color',$row_color);
			   $this->tplsav2->set_var('m2mfieldname',$display_name);

			   $field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object['object_id'],$relation2['id']);
			   $single_m2m['form_listing_order']=$field_conf_arr['form_listing_order'];
			   $single_m2m['fieldname']=$relation2['id'];
			   $single_m2m['input']=$this->tplsav2->fetch('many-to-many.1.tpl.php');

			   $ret_arr[]=$single_m2m;
			}

			return $ret_arr;
		 }
	  }

	  /**
	  * mk_m2o_array: creates array with one 2 many relations
	  * 
	  * @access public
	  * @return void
	  */
	  function mk_m2o_array()
	  {
		 // fixme create read only widget
		 if($this->readonly)
		 {
			return;	
		 }

		 $m2o_arr=$this->bo->extract_m2o_relations($this->bo->site_object['relations']);
		 
		 if (count($m2o_arr)>0)
		 {
			$i=1;

			foreach($m2o_arr as $m2o_rule_arr)
			{
			   $m2o_rule_arr['id'].=$this->mult_index;

			   $field_conf_arr=$this->bo->so->get_field_values($this->bo->site_object['object_id'],$m2o_rule_arr['id']);

			   $this->tplsav2->localkey=$this->values_object[0][$m2o_rule_arr['local_key']];
			   $this->tplsav2->field_label=$field_conf_arr['element_label'];

			   $this->tplsav2->initial_list=$this->create_m2o_list($m2o_rule_arr,$this->values_object[0][$m2o_rule_arr['local_key']]);
			   $this->tplsav2->m2o_rule_arr_enc=base64_encode(serialize($m2o_rule_arr));
			   $single_m2o['fieldname']=$m2o_rule_arr['id'];
			   $single_m2o['single_col']=true; //TODO use config
			   $single_m2o['label_visibility']=0; //TODO use config
			   $single_m2o['input']=$this->tplsav2->fetch('many-to-one.1.tpl.php');

			   $ret_arr[]=$single_m2o;
			}

			$this->tplsav2->xmlhttp_get_m2o_list=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_get_m2o_list');
			$this->tplsav2->xmlhttp_get_m2o_list2=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax2_get_m2o_list');
			$this->tplsav2->m2ojavascript=$this->tplsav2->fetch('many-to-one_js.tpl.php');

		 }

		 return $ret_arr;	
	  }

	  /**
	  * mk_o2o_array: make array of fields that come from o2o relations
	  * 
	  * @access public
	  * @return void
	  */
	  function mk_o2o_array()
	  {
		 // fixme create read only widget
		 if($this->readonly)
		 {
			return;	
		 }

		 $O2O_arr=$this->bo->extract_O2O_relations($this->bo->site_object['relations']);

		 if (count($O2O_arr)>0)
		 {
			$i=1;

			foreach($O2O_arr as $O2O_rule_arr)
			{
			   $this->o2o_index=sprintf("%02d",$i);

			   $O2O_where_value=$this->values_object[0][$O2O_rule_arr['local_key']];
			   $O2O_where_string="({$O2O_rule_arr['foreign_table']}.{$O2O_rule_arr['foreign_key']}='$O2O_where_value')";

			   if(is_numeric($O2O_rule_arr['object_conf']))
			   {
				  $this->bo->addError(lang('One-to-one relation is broken, please fix it or contact site-developer.<br/>%1',_debug_array($O2O_rule_arr,false)));
				  continue;
			   }
			   $O2O_object_arr=$this->bo->so->get_object_values_by_uniq($O2O_rule_arr['object_conf']); // uniqid
			   
			   $O2O_values_record = $this->bo->so->get_record_values($this->bo->session['site_id'],$O2O_object_arr['table_name'],'','','','','name','','*',$O2O_where_string);
			   if($O2O_values_record)
			   {
				  $this->tplsav2->extrahiddens.='<input type="hidden" name="O2OW'.$this->o2o_index.'" value="'.$O2O_where_string.'" />';
			   }

			   $o2o_info_arr= base64_encode(serialize($O2O_rule_arr));

			   $this->tplsav2->extrahiddens.='<input type="hidden" name="O2OT'.$this->o2o_index.'" value="'.$O2O_object_arr['table_name'].'" />';
			   $this->tplsav2->extrahiddens.='<input type="hidden" name="O2OO'.$this->o2o_index.'" value="'.$O2O_rule_arr['object_conf'].'" />';
			   $this->tplsav2->extrahiddens.='<input type="hidden" name="O2OR'.$this->o2o_index.$O2O_rule_arr['foreign_key'].'" value="'.$o2o_info_arr.'" />'; // info about relation
			   $input_prefix='O2OX'.$this->o2o_index;

			   $o2o_fields_metadata = $this->bo->so->site_table_metadata($this->bo->session['site_id'],$O2O_object_arr['table_name']);

			   $_o2o_fields_arr=$this->mk_fields_array($input_prefix,$O2O_object_arr,$O2O_values_record[0]);

			   //FIXME improve preformence of this loop
			   foreach($_o2o_fields_arr as $fld_key=>$fld_val)
			   {
				  if($fld_key!=$O2O_rule_arr['foreign_key'])
				  {
					 $o2o_fields_arr[$fld_key]=$fld_val;
				  }
			   }

			   $ret_arr=array_merge($ret_arr,$o2o_fields_arr);

			   $i++;
			}

			return $ret_arr;
		 }
	  }

	  /**
	  * create_m2o_list 
	  * 
	  * @param mixed $m2o_rule_arr 
	  * @param mixed $localkey 
	  * @access public
	  * @return void
	  */
	  function create_m2o_list($m2o_rule_arr,$localkey)
	  {
		 unset($this->tplsav2->linked_records);
		 $this->tplsav2->m2o_arr=$m2o_rule_arr;

		 $where_value=$localkey;
		 $where_string="{$m2o_rule_arr['foreign_key']}='$where_value'";

		 $object_arr=$this->bo->so->get_object_values_by_uniq($m2o_rule_arr['object_conf']);
	/*	 $this->tplsav2->xmlhttp_get_m2o_list=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_get_m2o_list');
		 $this->tplsav2->xmlhttp_get_m2o_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_get_m2o_frm');
		 $this->tplsav2->xmlhttp_delete_m2o_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_delete_m2o&object_id='.$object_arr['object_id']);
		 $this->tplsav2->xmlhttp_save_m2o_link=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_save_m2o_frm&object_id='.$object_arr['object_id'].'&localkeyvalue='.$where_value.'&m2o_rule_arr='.base64_encode(serialize($m2o_rule_arr)));
*/
		 $this->tplsav2->xmlhttp_get_m2o_list		= $GLOBALS['egw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax_get_m2o_list');
		 $this->tplsav2->xmlhttp_get_m2o_link2		= $GLOBALS['egw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax2_get_m2o_frm');
		 $this->tplsav2->xmlhttp_delete_m2o_link2	= $GLOBALS['egw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax2_del_m2o_rec&object_id='.$object_arr['object_id']);
		 $this->tplsav2->xmlhttp_save_m2o_link2		= $GLOBALS['egw']->link('/index.php','menuaction=jinn.uiu_edit_record.ajax2_save_m2o_frm&object_id='.$object_arr['object_id'].'&localkeyvalue='.$where_value.'&m2o_rule_arr='.base64_encode(serialize($m2o_rule_arr)));

		 $columns=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $m2o_rule_arr['foreign_table']);

		 $column_types = array();
		 if(!is_array($columns)) $columns=array();

		 /* get one with many relations */
		 $relation1_array=$this->bo->extract_O2M_relations($object_arr['relations']);
		 if (count($relation1_array)>0)
		 {
			foreach($relation1_array as $relation1)
			{
			   $fields_with_relation1[]=$relation1['local_key'];
			}
		 }
	 
		 foreach($columns as $onecol)
		 {
			$field_conf_arr=$this->bo->so->get_field_values($object_arr['object_id'],$onecol['name']);


			if($field_conf_arr['field_enabled']=='0' && $field_conf_arr['field_enabled']!=null)
			{
			   continue; 
			}

			if($field_conf_arr['list_visibility']=='0')
			{
			   continue; 
			}

			if($field_conf_arr['element_label'])
			{
			   $col_names_item['label']=$field_conf_arr['element_label'];
			}
			else
			{
			   $col_names_item['label']= ucfirst(strtolower(ereg_replace("_", " ", $onecol['name']))); 
			}
			$col_names_item['name']=$onecol['name'];
			$col_names_list[]=$col_names_item;

			$ftype=$this->db_ftypes->complete_resolve($onecol);

			if(!$ftype)
			{
			   $ftype='string';
			}
			$column_types[$onecol['name']] = $ftype;

			/* check for primaries and create array */
			if (eregi("primary_key", $onecol['flags']) && $onecol['type']!='blob') // FIXME howto select long blobs
			{						
			   $pkey_arr[]=$onecol['name'];
			}
			elseif($onecol['type']!='blob') // FIXME howto select long blobs
			{
			   $akey_arr[]=$onecol['name'];
			}
		 }

		 if($where_value)
		 {
			$linked_records = $this->bo->so->get_record_values($this->bo->session['site_id'],$m2o_rule_arr['foreign_table'],'','','','','name','','*',$where_string);
		 }
		 if(is_array($linked_records))
		 {
			foreach($linked_records as $linked_rec_arr)
			{
			   unset($where_string);
			   unset($_where_string);
			   if(count($pkey_arr)>0)
			   {
				  foreach($pkey_arr as $pkey)
				  {
					 if($where_string) $where_string.=' AND ';
					 $where_string.= '('.$pkey.' = \''. addslashes($linked_rec_arr[$pkey]).'\')';
				  }

				  $_where_string=base64_encode($where_string);
			   }

			   foreach($col_names_list  as $onecolname)
			   {
				  $field_conf_arr=$this->bo->so->get_field_values($object_arr['object_id'],$onecolname['name']);
				  $recordvalue=$linked_rec_arr[$onecolname['name']];

				  if ($recordvalue && is_array($fields_with_relation1) && in_array($onecolname['name'],$fields_with_relation1))
				  {
					 $related_value=$this->bo->get_related_value($relation1_array[$onecolname['name']],$recordvalue);
					 $display_recordvalue= '<span style="font-style:italic;">'.$related_value.'</span>';
				  }
				  else
				  {	
					 $display_recordvalue=$this->bo->plug->call_plugin_bv($onecolname['name'], $recordvalue, $where_string, $field_conf_arr, $column_types[$onecolname['name']]);
				  }
				  
				  $linked_rec_parsed_arr[$onecolname['name']]=$display_recordvalue; // replaced value from plugin
			   }

			   $linked_rec['where_string']=$_where_string;
			   $linked_rec['rec_arr']=$linked_rec_arr;

			   $linked_rec['rec_parsed_arr']=$linked_rec_parsed_arr;

			   $this->tplsav2->linked_records[]=$linked_rec;
			}
		 }

		 $this->tplsav2->img_delete=$GLOBALS['phpgw']->common->image('phpgwapi','delete');
		 $this->tplsav2->img_copy=$GLOBALS['phpgw']->common->image('phpgwapi','copy');
		 $this->tplsav2->img_edit=$GLOBALS['phpgw']->common->image('phpgwapi','edit');
//		 $this->tplsav2->tooltip_img=$GLOBALS['phpgw']->common->image('phpgwapi','info');
		 $this->tplsav2->visible_cols=$col_names_list;

		 return $this->tplsav2->fetch('many-to-one_list.1.tpl.php');
	  }

	  /************************************\
	  ******                           *****
	  ******    NEW M2O AJAX IMPL..    *****
	  ******                           *****
	  \************************************/

	  function init_ajax2()
	  {
		 $this->json = CreateObject('jinn.JSON');
	  }

	  function ajax2_example()
	  {
		 $this->init_ajax2();

		 $value['tinymce']=$this->tplsav2->fetch('tpl/init_tinymce.tpl.php');
		 $output = $this->json->encode($value);

		 print($output);
	  }

	  function ajax2_get_m2o_frm()
	  {
		 $this->init_ajax2();
		 
		 $object_arr=$this->bo->so->get_object_values_by_uniq($_GET['obj_conf']);

		 $this->bo->session['m2o_obj_id']=$object_arr['object_id'];
		 $this->bo->sessionmanager->save();
		 
		 $where_string=base64_decode($_GET['where_string']);

		 if($this->bo->where_string && !$alt_object_arr)
		 {
			$values_object= $this->bo->so->get_record_values($this->bo->session['site_id'],$object_arr['table_name'],'','','','','name','','*',$where_string);
		 }

		 $this->relation1_array = $this->bo->extract_O2M_relations($object_arr['relations']);
		 $fields_arr=$this->mk_fields_array('M2OX00',$object_arr,$values_object[0]);

		 $this->tplsav2->form_rows=$this->parse_fields_to_layout($fields_arr);
		 
		 unset($this->bo->session['m2o_obj_id']);
		 $this->bo->sessionmanager->save();

		 $this->tplsav2->m2oid=$_GET['m2oid'];

		 $value['justdata']=$this->tplsav2->fetch('frm_xmlhttp_req_edit_record2.tpl.php');
		 
		 //$value['justdata']=$this->tplsav2->fetch('tpl/init_tinymce.tpl.php');
		 $output = $this->json->encode($value);

		 print($output);
	  }

	  function ajax2_save_m2o_frm()
	  {
		 $this->init_ajax2();
		 
		 if($_POST)
		 {
			$object_arr=$this->bo->so->get_object_values($_GET['object_id']);

			$_m2orule_arr=unserialize(base64_decode($_GET[m2o_rule_arr]));
			$foreign_key=$_m2orule_arr['foreign_key'];
			$_POST['M2OX00'.$foreign_key]=$_GET['localkeyvalue'];

			if(trim($_GET['where_string']))
			{
			   $_POST['MLTWHR00']=$_GET['where_string'];

			   $status = $this->bo->multiple_records_update($where_arr,1,$object_arr);
			}
			else
			{
			   $status=$this->bo->multiple_records_insert(1,$object_arr); 
			}
		 }
		 
		 $value['status']='tja';
		 
		 $output = $this->json->encode($value);

		 print($output);
	  }

	  function ajax2_del_m2o_rec()
	  {
		 $this->init_ajax2();

		 $object_arr=$this->bo->so->get_object_values($_GET['object_id']);
		 $where_arr[]=base64_decode($_GET['where_string']);

		 $status=$this->bo->multiple_records_delete($where_arr,$object_arr,false);
		 
		 $value['status']=$status;

		 $output = $this->json->encode($value);

		 print($output);
	  }

	  function ajax2_get_m2o_list()
	  {
		 $this->init_ajax2();
		 // get list of related
		 $m2o_rule_arr=unserialize(base64_decode($_GET[m2o_rule_arr_enc]));
		 $value['list'] = $this->create_m2o_list($m2o_rule_arr,$_GET['localkey']);
		 
		 $output = $this->json->encode($value);

		 print($output);
	  }

	  /************************************\
	  ******                           *****
	  ******    END M2O AJAX IMPL..    *****
	  ******                           *****
	  \************************************/


	  /**
	  * test_object: test we can use the database table else go to index with error
	  * 
	  * @access public
	  * @return void
	  */
	  function test_object()
	  {
		 if(!$this->bo->so->test_site_object_table($this->bo->site_object))
		 {
			unset($this->bo->session['site_object_id']);

			$this->bo->addError(lang('Failed to open table. Please check if table <i>%1</i> still exists in database',$this->bo->site_object['table_name']));
			$this->bo->exit_and_open_screen('jinn.uiuser.index');
		 }				
	  }

	  function getRunOnRecordEventButtons($where_string)
	  {
		 $stored_configs = unserialize(base64_decode($this->bo->site_object['events_config']));
		 if(is_array($stored_configs))
		 {
			foreach($stored_configs as $key => $conf_arr)
			{
			   if($conf_arr['conf']['event']=='run_on_record')
			   {
				  $conf_arr['runonrecordevent_link']=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.runonrecord&where_string='.$where_string.'&plgkey='.$key);
				  $this->tplsav2->runonrecordbuttons_arr[]=$conf_arr;
			   }
			}
		 }

		 $buttonrow=$this->tplsav2->fetch('runonrecord_buttons.tpl.php');
		 return $buttonrow;
	  }

   }

?>
