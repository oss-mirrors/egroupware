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

	/* main ui class for user as administrator */


	class uicommon
	{

		var $app_title='JiNN';
		var $template;
		var $message;

		function uicommon()
		{
			$this->template = $GLOBALS['phpgw']->template;
		}

		/**
		* header renders the app & screen title,
		* 
		* @param string $screen_title 
		*/
		function header($screen_title,$phpgw_header=true)
		{
			unset($GLOBALS['phpgw_info']['flags']['noheader']);
			unset($GLOBALS['phpgw_info']['flags']['nonavbar']);
			unset($GLOBALS['phpgw_info']['flags']['noappheader']);
			unset($GLOBALS['phpgw_info']['flags']['noappfooter']);

			if($phpgw_header)$GLOBALS['phpgw']->common->phpgw_header();
			
			$this->template->set_file(array(
				'header' => 'header.tpl'
			));

			$this->template->set_var('app_title',$this->app_title);
			$this->template->set_var('screen_title',$screen_title);
			$this->template->pparse('out','header');
		}



		/**
		* format a standard msg_box with 
		* 
		* print errors in a red font and info messages in green
		*
		* @param array $msg_arr ['info'] contains the info msg and ['error'] the error msg
		*/
		function msg_box($msg_arr)
		{
			if ($msg_arr['info']) $info='<p><font color=green>'.$msg_arr['info'].'</font></p>';
			if ($msg_arr['error']) $error='<p><font color=red>'.$msg_arr['error'].'</font></p>';

			if($info || $error)
			{
				$this->template->set_file(array(
					'msg_box' => 'msg_box.tpl'
				));

				$this->template->set_var('error',$error);
				$this->template->set_var('info',$info);
				$this->template->pparse('out','msg_box');
			}
		}


		/**
		* returns the options of a selectbox
		* 
		* @return string html formatted options 
		* @param array $list_array array with values and names for the options 
		* @param mixed $selected_value value that must be selected
		* @param boolean $allow_empty allow emty options
		*/
		function select_options($list_array,$selected_value,$allow_empty=false)
		{
			if($allow_empty) $options.="<option value=\"\">------------------</option>\n";

			if(is_array($list_array))
			{
				foreach ( $list_array as $array ) {

					unset($SELECTED);
					if ($array[value]==$selected_value)
					{
						$SELECTED='SELECTED';
					}				
					if ($array[name]) $name = $array[name];
					else $name = $array[value];


					$options.="<option value=\"".$array[value]."\" $SELECTED>".stripslashes($name)."</option>\n";
				}

			}
			return $options;
		}

	}		

	?>
