<?php
   /**************************************************************************\
   * eGroupWare - JiNN main ui class                                          *
   * http://www.egroupware.org                                                *
   * Written by Pim Snel <pim@lingewoud.nl>                                   *
   * --------------------------------------------                             *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   /**
    * uijinn 
    * 
    * @package 
    * @version $Id$
    * @copyright Lingewoud B.V.
    * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
    * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
    */
   class uijinn
   {
	  var $bo;
	  var $ui;
	  var $tplsav2;
	  var $nextmatch;

	  var $public_functions = Array
	  (
		 'debugwindow' => True
	  );

/**
	   * uijinn: constructor
	   * 
	   * @access public
	   * @return void
	   */
	  function uijinn()
	  {
		 $this->template = $GLOBALS['phpgw']->template;
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $this->nextmatchs=CreateObject('phpgwapi.nextmatchs');
	  }

	  /**
	  * header: header renders the app & screen title, 
	  *
	  * @param mixed $screen_title 
	  * @param mixed $phpgw_header 
	  * @access public
	  * @return void
	  */
	  function header($screen_title,$phpgw_header=true)
	  {

		 unset($GLOBALS['phpgw_info']['flags']['noheader']);
		 unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
		 unset($GLOBALS['phpgw_info']['flags']['noappheader']);
		 unset($GLOBALS['phpgw_info']['flags']['noappfooter']);
		 $GLOBALS['phpgw_info']['flags']['app_header'] = $GLOBALS['phpgw_info']['apps']['jinn']['title']. ' - '.$screen_title . $extra_title;

		 if($phpgw_header && !$this->no_header)
		 {
			$GLOBALS['phpgw']->common->phpgw_header();
		 }
	  }

	  /**
	   * msg_box 
	   *
	   * format a standard msg_box print errors in a red font and info messages in green
	   *
	   * @param mixed $msg_arr 
	   * @access public
	   * @return void
	   */
	  function msg_box($msg_arr=false)
	  {
		 $msg_arr=$this->bo->session['message'];

		 if($msg_arr[info] || $msg_arr[error] || $msg_arr[help] || $msg_arr[debug])
		 {
			$this->tplsav2->msg_arr=$this->bo->session['message'];
			$this->tplsav2->display('msg_box.tpl.php');
		 }

		 $this->tplsav2->debugwindowlink=$GLOBALS[phpgw]->link('/index.php','menuaction=jinn.uijinn.debugwindow');

		 $this->bo->clearmsg();
		 $this->bo->sessionmanager->save();
	  }

	  /**
	   * debugwindow 
	   * 
	   * @access public
	   * @return void
	   */
	   function debugwindow()
	   {
		  $this->tplsav2->display('debugwindow.tpl.php');
	   }


	  /**
	  * returns the options of a selectbox
	  * 
	  * @return string html formatted options 
	  * @param array $list_array array with values and names for the options 
	  * @param mixed $selected_value value that must be selected
	  * @param boolean $allow_empty allow emty options
	  */
	  function select_options($list_array,$selected_value,$allow_empty=false,$emptyvalue='')
	  {
		 if($allow_empty) $options.='<option value="'.$emptyvalue.'">------------------</option>\n';
		 if(is_array($list_array))
		 {
			foreach ( $list_array as $array ) 
			{
			   unset($SELECTED);
			   if ($array[value]==$selected_value)
			   {
				  $SELECTED='selected="selected"';
			   }				
			   if ($array[name])
			   {
				  $name = $array[name];
			   }
			   else
			   {
				  $name = $array[value];
			   }

			   $options.='<option value="'.$array[value].'" '.$SELECTED.'>'.stripslashes($name).'</option>\n';
			}

		 }
		 return $options;
	  }

	  /**
	  * main_menu 
	  * 
	  * DEPRECIATED 
	  *
	  * @access public
	  * @return void
	  */
	  function main_menu()
	  {
		 return;
	  }
   }
?>
