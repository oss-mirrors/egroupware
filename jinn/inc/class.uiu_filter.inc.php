<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

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

   /* $Id$ */

   /**
   @package jinn_users_classes
   */
   class uiu_filter
   {
	  var $public_functions = Array
	  (
		 'edit'		=> True,
		 'delete'	=> True,
		 'save'		=> True
	  );
	  var $bo;
	  var $template;
	  var $ui;
	  var $filterdata;
	  var $filterstore;
	  var $sessionfilter;

	  

  	  function init_bo(&$bo)
	  {
		$this->bo = &$bo;
	  }

	  function uiu_filter()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->ui = CreateObject('jinn.uicommon',$this->bo);
		 
		 // get all available filters from preferences and session
		 $this->filterstore = $this->bo->read_preferences('filterstore'.$this->bo->site_object[unique_id]); 
		 $this->sessionfilter = $this->bo->read_session_filter($this->bo->site_object[unique_id]);
		 if($this->bo->so->config[server_type]=='dev')
		 {
			$dev_title_string='<font color="red">'.lang('Development Server').'</font> ';
		 }
		 $this->ui->app_title=$dev_title_string;//.lang('Moderator Mode');
	  }
	  
	  function save_filterstore()
	  {
		$this->bo->save_preferences('filterstore'.$this->bo->site_object[unique_id], $this->filterstore); 
	  }

	  function save_sessionfilter()
	  {
		 $this->bo->save_session_filter($this->bo->site_object[unique_id], $this->sessionfilter);
		 $this->bo->save_sessiondata();
	  }
	  
	  /**
	  @function format_filter_options
	  */
	  function format_filter_options($selected)
	  {
		 $options  = '<option value="NO_FILTER">'.lang('empty filter').'</option>';
		 $options .= '<option value="NO_FILTER">------------</option>';
		 if($selected == 'sessionfilter')
		 {
			$options .= '<option value="sessionfilter" selected>'.lang('session filter').'</option>';
		 }
		 else
		 {
			$options .= '<option value="sessionfilter">'.lang('session filter').'</option>';
		 }
		 $options .= '<option value="NO_FILTER">------------</option>';
		 if(is_array($this->filterstore))
		 {
			foreach($this->filterstore as $filter)
			{
			   if($filter[name] == $selected)
			   {
				  $options .= '<option value="'.$filter[name].'" selected>'.$filter[name].'</option>';
			   }
			   else
			   {
				  $options .= '<option value="'.$filter[name].'">'.$filter[name].'</option>';
			   }
			}
		 }
		 return $options;
	  }
	  
	  function get_filter_where()
	  {
	  // if not specified, get the current filter from the session, or specify empty
		 if($_POST[filtername] == '')
		 {
			$_POST[filtername] = $this->sessionfilter[selected];
			if($_POST[filtername] == '')
			{
				$_POST[filtername] == 'NO_FILTER';
			}

		 }

		 // check if an existing filter is selected
		 if($_POST[filtername] != 'NO_FILTER')
		 {
			//check if it is a temporary (session) filter or permanently (preferences) stored filter and load accordingly
			if($_POST[filtername] == 'sessionfilter')
			{
			   $filter = $this->sessionfilter;
			}
			else
			{
			   $filter = $this->filterstore[$_POST[filtername]];
			}

			// generate the WHERE clause using the loaded filter
			$filter_where = '';
			if(is_array($filter[elements]))
			{
			   foreach($filter[elements] as $element)
			   {
				  if($filter_where != '') $filter_where .= ' AND ';
				  $filter_where .= "`".$element[field]."`".$element[operator]."'".$element[value]."'";
			   }
			}
		 }
		 
		 // save filtername in session
		 $this->sessionfilter[selected] = $_POST[filtername];
		 $this->save_sessionfilter();
		 
		 return $filter_where;
	  }


	  /**
	  @function delete
	  @abstract public function to delete the filter
	  */
	  function delete()
	  {
		if($_POST[filtername])
		{
			unset($this->filterstore[$_POST[filtername]]);
			$this->save_filterstore();
		}
		
			//redirect to list
		 $this->bo->save_sessiondata();
		 $this->bo->common->exit_and_open_screen('jinn.uiu_list_records.display');

	  }
	  
	  /**
	  @function save 
	  @abstract public function to save the filter data
	  */
	  function save()
	  {
				//start compiling this filter from the post form
		$this->filterdata = array();

		if($_POST[filtername] != '')
		{
			$this->filterdata['name'] = $_POST[filtername];
		}
		else
		{
			$this->filterdata['name'] = 'sessionfilter';
		}
		
			//compile the filter elements
		foreach($_POST as $key => $value)
		{
			if(substr_count($key, '_') > 0)
			{
				$indices = explode('_', $key);
				$this->filterdata['elements'][$indices[1]][$indices[0]] = $value;
			}
		}
			
			//test if any of the existing elements has been erased
		$existing_elements = count($this->filterdata['elements']);
		for($element = 0; $element < $existing_elements; $element++)
		{
			if(	$this->filterdata['elements'][$element][field] == '' || 
				$this->filterdata['elements'][$element][operator] == '' || 
				$this->filterdata['elements'][$element][value] == '')
			{
				unset($this->filterdata['elements'][$element]);
			}
		}
			//reorder the array.
		if (is_array($this->filterdata['elements']))
		{
			$this->filterdata['elements'] = array_values($this->filterdata['elements']);
		}
				
			//check if a new filter element has been added. If YES then compile it and add it
		if($_POST[field] != '' && $_POST[operator] != '' && $_POST[value] != '')
		{
			$newid = count($this->filterdata[elements]);
			$this->filterdata['elements'][$newid][field]    = $_POST[field];
			$this->filterdata['elements'][$newid][operator] = $_POST[operator];
			$this->filterdata['elements'][$newid][value]    = $_POST[value];
		
		}

			
		if ($this->filterdata['name'] == 'sessionfilter')
		{
				//save in session
			$this->sessionfilter = $this->filterdata;
			$this->save_sessionfilter();
		}
		else
		{
				//get the already stored filters, add or replace this one, save them all.
			$this->filterstore[$_POST[filtername]]=$this->filterdata;
			$this->save_filterstore();
		}
		
			//redirect to edit form
		$filtername = $_POST[filtername];
		unset($_POST);
		$_POST[filtername] = $filtername;
		$this->edit();
	  }	  
	  
	  /**
	  @function edit 
	  @abstract main public function to create a filter edit page
	  */
	  function edit()
	  {
		 $this->template->set_file(array(
			'frm_edit_filter' => 'frm_edit_filter.tpl'
		 ));

 		 $this->template->set_block('frm_edit_filter','pre_block','');
 		 $this->template->set_block('frm_edit_filter','column_block','');
 		 $this->template->set_block('frm_edit_filter','post_block','');

		  
		 $this->ui->header('edit filter');
		  
		 $this->ui->msg_box($this->bo->session['message']);
		 unset($this->bo->session['message']);

		 
		 /////////////////////////
		 //process the first block
		 /////////////////////////
			
  		 $this->template->set_var('field_label',lang('column'));
 		 $this->template->set_var('operator_label',lang('filter operator'));
 		 $this->template->set_var('value_label',lang('criterium'));
		 $this->template->set_var('form_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.save'));

 		 $this->template->parse('pre','pre_block');	//parses the right argument block into the left argument variable ('fetch')
		 $this->template->pparse('out','pre'); 		//prints the right argument into the left argument buffer ('parse')

		 /////////////////////////
		 //process the filter elements block
		 /////////////////////////

			//get the columns info
		 $fields_arr=$this->bo->so->site_table_metadata($this->bo->site_id, $this->bo->site_object['table_name']);
		 
			//get the filter data
		 if(is_array($this->filterdata))
		 {
			//the save() function already filled $this->filterdata for us
			//we must do it like that, because the preferences/sessions are not updated right away after calling save
		 }
		 else if($_POST[filtername]=='sessionfilter')
		 {
			$this->filterdata = $this->sessionfilter;
		 }
		 else
		 {
			$this->filterdata = $this->filterstore[$_POST[filtername]];
		 }

			//loop each filter element
		 $num=0;
		 if(is_array($this->filterdata[elements]))
		 {
			 foreach($this->filterdata[elements] as $element)
			 {
				 $this->template->set_var('element', '_'.$num);
				 $this->template->set_var('fields', $this->getFieldOptions($fields_arr, $element[field]));
				 $this->template->set_var('operators', $this->getOperatorOptions($element[operator]));
				 $this->template->set_var('value', $element[value]);
				 $this->template->parse('column', 'column_block');	//parses the right argument block into the left argument variable ('fetch')
				 $this->template->pparse('out', 'column'); 		//prints the right argument into the left argument buffer ('parse')
				 $num++;
			 }
		 }
		 
			//add one empty row for more filter elements
		 $this->template->set_var('element','');
		 $this->template->set_var('fields', $this->getFieldOptions($fields_arr, ''));
		 $this->template->set_var('operators', $this->getOperatorOptions(''));
		 $this->template->set_var('value','');
		 $this->template->parse('column','column_block');	//parses the right argument block into the left argument variable ('fetch')
		 $this->template->pparse('out','column'); 			//prints the right argument into the left argument buffer ('parse')

		 /////////////////////////
		 //process the last block
		 /////////////////////////

 		 $this->template->set_var('name_label',lang('save as'));
		 $this->template->set_var('filtername',$this->filterdata[name]);
 		 $this->template->set_var('submit',lang('store filter'));
 		 $this->template->set_var('submit_exit',lang('activate filter'));
		 $this->template->set_var('list_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->template->set_var('delete_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.delete'));
 		 $this->template->set_var('delete',lang('delete filter'));
 		 $this->template->set_var('delete_confirm',lang('are you sure you want to delete this filter?'));
 		 $this->template->set_var('sessionfilter_alert',lang('you cannot delete the session filter'));
		 $this->template->parse('post','post_block');	//parses the right argument block into the left argument variable ('fetch')
		 $this->template->pparse('out','post'); 		//prints the right argument into the left argument buffer ('parse')

		 $this->bo->save_sessiondata();
	  }

	function getFieldOptions($fields_arr, $selected)
	{
		 $fields='<option value="">------------</option>';
		 foreach($fields_arr as $field)
		 {
			if($field[name] == $selected)
			{
				$fields .= '<option value="'.$field[name].'" SELECTED>'.$field[name].'</option>';
			}
			else
			{
				$fields .= '<option value="'.$field[name].'">'.$field[name].'</option>';
			}
		 }
		 return $fields;
	}			 

	function getOperatorOptions($selected)
	{
		 $supported_operators = array
		 (
			'=', '>', '<'
		 );

		 $operators='<option value="">------------</option>';
		 foreach($supported_operators as $operator)
		 {
			if($operator == $selected)
			{
				$operators .= '<option value="'.htmlspecialchars($operator).'" SELECTED>'.htmlspecialchars($operator).'</option>';
			}
			else
			{
				$operators .= '<option value="'.htmlspecialchars($operator).'">'.htmlspecialchars($operator).'</option>';
			}
		 }
		 return $operators;
	}

   }

?>
