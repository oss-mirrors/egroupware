<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2004 Pim Snel <pim@lingewoud.nl>

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

   /* 
   class.date.php contains the standard image-upload plugin for 
   JiNN number off standardly available 
   plugins for JiNN. 
   */
   class db_fields_plugin_date
   {
	  var $javascript_inserted = false;

	  function db_fields_plugin_date()
	  {
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
	  }

	  function formview_edit($field_name,$value,$config,$attr_arr)
	  {
		 if (!is_object($GLOBALS['phpgw']->jscalendar))
		 {
			$GLOBALS['phpgw']->jscalendar = CreateObject('phpgwapi.jscalendar');
		 }
		 $today = date('Y-m-d');
		//echo  $GLOBALS['phpgw_info']['user']['preferences']['common']['dateformat']);
		 if($value=='0000-00-00')
		 {
			unset($value);
		 }

		 if(!$value && $config['defdate']=='today') 
		 {
			$value=$today; 		
		 }

		 list($tyear,$tmonth,$tday)=explode('-',$today);

		 list($year,$month,$day)=explode('-',$value);

		 $this->tplsav2->addPath('template',$this->plug_root.'/tpl');
		 $months_arr[0]='';
		 $months_arr[1]='januari';
		 $months_arr[2]='februari';
		 $months_arr[3]='maart';
		 $months_arr[4]='april';
		 $months_arr[5]='mei';
		 $months_arr[6]='juni';
		 $months_arr[7]='juli';
		 $months_arr[8]='augustus';
		 $months_arr[9]='september';
		 $months_arr[10]='oktober';
		 $months_arr[11]='november';
		 $months_arr[12]='december';

		 if($config[style] == 'DHTML-Calendar')
		 {
			$this->tplsav2->assign('datum',$day.' '.$months_arr[intval($month)].' '.$year);
			$this->tplsav2->assign('field_name',$field_name);
			if(!$plugins) $plugins=' ';
			$input .= $GLOBALS['phpgw']->jscalendar->input($field_name,'',$year,$month,$day);

			return $input;
		 }
		 else
		 {
			$stripped_name=substr($field_name,6);	

			//die(var_dump($input));
			$this->tplsav2->assign('months',$months_arr);
			$this->tplsav2->assign('day',$day);
			$this->tplsav2->assign('month',$month);
			$this->tplsav2->assign('year',$year);
			$this->tplsav2->assign('tday',$tday);
			$this->tplsav2->assign('tmonth',$tmonth);
			$this->tplsav2->assign('tyear',$tyear);

			$this->tplsav2->assign('field_name',$field_name);
			$input=$this->tplsav2->fetch('date.formview_edit.tpl.php');
			return $input;
		 }


	  }

	  function on_save_filter($field_name,$HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 if (!is_object($GLOBALS['phpgw']->jscalendar))
		 {
			$GLOBALS['phpgw']->jscalendar = CreateObject('phpgwapi.jscalendar');
		 }


		 if($config[style] == 'DHTML-Calendar')
		 {
			$date = $HTTP_POST_VARS[$field_name];
			$new_date_arr = $GLOBALS['phpgw']->jscalendar->input2date($date);
			$new_date = $new_date_arr[year].'-'.$new_date_arr[month].'-'.$new_date_arr[day];
		 }
		 else
		 {
			$new_date = $HTTP_POST_VARS['DATE_YY'.$field_name].'-'.$HTTP_POST_VARS['DATE_MM'.$field_name].'-'.$HTTP_POST_VARS['DATE_DD'.$field_name];
		 }
		 if($new_date) return $new_date;
		 return '-1'; /* return -1 when there no value to give but the function finished succesfully */
	  }

	  function formview_read($value,$conf_array)
	  {
		 if($value=='0000-00-00')
		 {
			unset($value);
		 }
		 return $this->listview_read($value,$conf_array,'');
	  }

	  function listview_read($value,$conf_array,$where_val_enc)
	  {
/*		 if($value=='0000-00-00')
		 {
			return $value;
		 }
*/
		 $months_arr[0]='';
		 $months_arr[1]='jan';
		 $months_arr[2]='feb';
		 $months_arr[3]='maa';
		 $months_arr[4]='apr';
		 $months_arr[5]='mei';
		 $months_arr[6]='jun';
		 $months_arr[7]='jul';
		 $months_arr[8]='aug';
		 $months_arr[9]='sep';
		 $months_arr[10]='okt';
		 $months_arr[11]='nov';
		 $months_arr[12]='dec';

		 list($year,$month,$day)=explode('-',$value);

		 $value=intval($day).' '.$months_arr[intval($month)].' '.$year;
		 return $value;
	  }
   }
?>
