<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003, 2005 Pim Snel <pim@lingewoud.nl>

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
   * uiu_filter 
   * 
   * @uses uijinn
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class uiu_filter extends uijinn
   {
	  var $public_functions = Array
	  (
		 'edit'		=> True,
		 'delete'	=> True,
		 'save'		=> True
	  );
	  var $filterdata;
	  var $filterstore;
	  var $sessionfilter;

	  /**
	  * uiu_filter 
	  * 
	  * @access public
	  * @return void
	  */
	  function uiu_filter()
	  {
		 $this->bo = CreateObject('jinn.bouser');
		 parent::uijinn();

		 // get all available filters from preferences and session
		 $this->filterstore = $this->bo->read_preferences('filterstore'.$this->bo->site_object[unique_id]); 
		 $this->sessionfilter = $this->bo->read_session_filter($this->bo->site_object[unique_id]);
	  }

	  /**
	  * init_bo 
	  * 
	  * @param mixed $bo 
	  * @access public
	  * @return void
	  */
	  function init_bo(&$bo)
	  {
		 $this->bo = &$bo;
	  }


	  /**
	  * save_filterstore 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_filterstore()
	  {
//		 _debug_array($this->filterstore);
		 $this->bo->save_preferences('filterstore'.$this->bo->site_object[unique_id], $this->filterstore); 
	  }

	  /**
	  * save_sessionfilter 
	  * 
	  * @access public
	  * @return void
	  */
	  function save_sessionfilter()
	  {
	//	 _debug_array($this->sessionfilter);
		 $this->bo->save_session_filter($this->bo->site_object[unique_id], $this->sessionfilter);
		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * format_filter_options 
	  * 
	  * @param mixed $selected 
	  * @access public
	  * @return void
	  */
	  function format_filter_options($selected)
	  {
		 $this->tplsav2->optval = 'NO_FILTER';
		 $this->tplsav2->optselected = '';
		 $this->tplsav2->optdisplay = '-------';
		 $options .= $this->tplsav2->fetch('form_el_option.tpl.php');
		 if($selected == 'sessionfilter')
		 {
			$this->tplsav2->optval = 'sessionfilter';
			$this->tplsav2->optselected = 'selected="selected"';
			$this->tplsav2->optdisplay = lang('session filter');
			$options .= $this->tplsav2->fetch('form_el_option.tpl.php');
		 }
		 else
		 {
			$this->tplsav2->optval = 'sessionfilter';
			$this->tplsav2->optselected = '';
			$this->tplsav2->optdisplay = lang('session filter');
			$options .= $this->tplsav2->fetch('form_el_option.tpl.php');
		 }
		 if(is_array($this->filterstore))
		 {
			foreach($this->filterstore as $filter)
			{
			   if($filter[name] == $selected)
			   {
				  $this->tplsav2->optval = $filter[name];
				  $this->tplsav2->optselected = 'selected="selected"';
				  $this->tplsav2->optdisplay = $filter[name];
				  $options .= $this->tplsav2->fetch('form_el_option.tpl.php');
			   }
			   else
			   {
				  $this->tplsav2->optval = $filter[name];
				  $this->tplsav2->optselected = '';
				  $this->tplsav2->optdisplay = $filter[name];
				  $options .= $this->tplsav2->fetch('form_el_option.tpl.php');
			   }
			}
		 }
		 return $options;
	  }

	  /**
	  * get_filter_where 
	  * 
	  * @access public
	  * @return void
	  */
	  function get_filter_where()
	  {


		 // if not specified, get the current filter from the session, or specify empty
		 if($_POST[filtername] == '')
		 {
			$_POST[filtername] = $this->sessionfilter['selected'];
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

			if($filter['ANDOR']=='OR') 
			{
			   $filter_and_or = 'OR';
			}
			else
			{
			   $filter_and_or = 'AND';
			}

			// generate the WHERE clause using the loaded filter
			$filter_where = '';
			if(is_array($filter[elements]))
			{
			   foreach($filter[elements] as $element)
			   {
				  if($filter_where != '') $filter_where .= " $filter_and_or ";
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
	  * delete: public function to delete the filter
	  * 
	  * @access public
	  * @return void
	  */
	  function delete()
	  {
		 if($_POST[filtername])
		 {
			unset($this->filterstore[$_POST[filtername]]);
			$this->save_filterstore();
		 }

		 //redirect to list
		 $this->bo->sessionmanager->save();
		 $this->bo->exit_and_open_screen('jinn.uiu_list_records.display');

	  }

	  /**
	  * save: public function to save the filter data
	  * 
	  * @access public
	  * @return void
	  */
	  function save()
	  {
		 //_debug_array($_POST);
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

		 if($_POST[ANDOR] != '')
		 {
			$this->filterdata['ANDOR'] = $_POST[ANDOR];
		 }
		 else
		 {
			$this->filterdata['ANDOR'] = 'AND';
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
//		 _debug_array($this->filterdata);
		 for($element = 0; $element < $existing_elements; $element++)
		 {
			if(	$this->filterdata['elements'][$element]['field'] == '' || 
			$this->filterdata['elements'][$element]['operator'] == '' || 
			$this->filterdata['elements'][$element]['delete'] == true)
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
		 $this->header('edit filter');

		 $this->msg_box();

		 $this->tplsav2->set_var('form_action',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.save'));

		 //get the columns info
		 $fields_arr=$this->bo->so->site_table_metadata($this->bo->session['site_id'], $this->bo->site_object['table_name']);

		 //get the filter data
		 if(is_array($this->filterdata))
		 {
			//the save() function already filled $this->filterdata for us
			//we must do it like that, because the preferences/sessions are not updated right away after calling save
		 }
		 elseif($_POST[filtername]=='sessionfilter')
		 {
			$this->filterdata = $this->sessionfilter;
		 }
		 else
		 {
			$this->filterdata = $this->filterstore[$_POST[filtername]];
		 }

		 if($this->filterdata['ANDOR']=='OR')
		 {
			$this->tplsav2->andor_or_chk='checked="checked"';
		 }
		 else
		 {
			$this->tplsav2->andor_and_chk='checked="checked"';
		 }

		 //loop each filter element
		 $num=0;
		 $this->tplsav2->filterdata_elements=array();
		 if(is_array($this->filterdata[elements]))
		 {
			foreach($this->filterdata[elements] as $element)
			{
			   $f_el['element']=$num;
			   $f_el['fields']=$this->getFieldOptions($fields_arr, $element[field]);
			   $f_el['operators']=$this->getOperatorOptions($element[operator]);
			   $f_el['value']=$element['value'];
			   $f_el['set']=true;

			   $this->tplsav2->filterdata_elements[]=$f_el;
			   $num++;
			}
		 }
		 $f_el['element']=$num;
		 $f_el['fields']=$this->getFieldOptions($fields_arr, '');
		 $f_el['operators']=$this->getOperatorOptions('');
		 $f_el['value']='';
		 $f_el['set']=false;

		 $this->tplsav2->filterdata_elements[]=$f_el;

		 $this->tplsav2->set_var('filtername',$this->filterdata[name]);
		 $this->tplsav2->set_var('list_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_list_records.display'));
		 $this->tplsav2->set_var('delete_url',$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_filter.delete'));

		 $this->tplsav2->display('frm_edit_filters.tpl.php');

		 $this->bo->sessionmanager->save();
	  }

	  /**
	  * getFieldOptions 
	  * 
	  * @param mixed $fields_arr 
	  * @param mixed $selected 
	  * @access public
	  * @return void
	  */
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

	  /**
	  * getOperatorOptions 
	  * 
	  * @param mixed $selected 
	  * @access public
	  * @return void
	  */
	  function getOperatorOptions($selected)
	  {
		 $supported_operators = array
		 (
			'=',
			'!=',
			'>', 
			'<',
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
