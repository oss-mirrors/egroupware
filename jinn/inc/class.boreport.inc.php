<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
			Lex Vogelaar <lex_vogelaar@users.sourceforge.net>
   Copyright (C)2002, 2003, 2004, 2005 Pim Snel <pim@lingewoud.nl>

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
   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/class.bojinn.inc.php');
   class boreport extends bojinn
   {
	  var $public_functions = Array(
		 'get_report_list' 		=> True,
		 'get_single_report' 	=> True,
		 'update_single_report' => True,
		 'delete_report'		=>True,
	  );

	  var $tplsav2;

	  function boreport($session_name='jinnitself')
	  {
		 parent::bojinn($session_name);
		 $this->tplsav2 = CreateObject('phpgwapi.tplsavant2');
		 $this->include_report_plugins();
	  }

	  function include_report_plugins()
	  {
		 //include_once(EGW_SERVER_ROOT.'/jinn/plugins/report_engines/class.registry.php');
		 //$this->registry = new db_fields_registry();

		 if ($handle = opendir(EGW_SERVER_ROOT.'/jinn/plugins/report_engines/')) 
		 {
			while (false !== ($file = readdir($handle))) 
			{ 
			   if(substr($file,0,2)=='__') //plugins have their individual folders which start with two underscores (i.e. __boolean)
			   {
				  include_once(EGW_SERVER_ROOT.'/jinn/plugins/report_engines/'.$file.'/register.php');	
			   }
			}
			closedir($handle); 
		 }
	  }
	  function createtypeobject($report_type_name)
	  {
		 include_once(EGW_SERVER_ROOT.'/jinn/plugins/report_engines/__'.$report_type_name.'/class.'.$report_type_name.'.php');	
		 return new $report_type_name;
	  }


	  /*
	  This function return a list of reports, depending on the $all opties, 
	  it will return all(0), only the globals(1) or only the reports of the user(2) 
	  */
	  function get_report_list($id, $all=1)
	  {
		 //print_r($id);
		 $output='';
		 if ($all ==1 or $all ==2)
		 {
			$report_arr = $this->so->get_report_list($id);	
	 		if(is_array($report_arr))
		 	{
		 		foreach($report_arr as $report)
		 		{
					$output .='<option value=\''.$report['id'].'\'>'.$report['name'].'</option>';
				}
		 	}
		 }
		 if($all == 1)
		 {
			 $output .='<option>-------------------</option>';
		 }
		 if($all == 1 or $all == 3)
		 {
			$pref_arr = $this->read_preferences('reports');	
			$i=0;
			if(is_array($pref_arr))
			{
			   foreach($pref_arr as $pref)
			   {
				  if($pref['report_object_id'] == $id)
				  $output .='<option value=\''.$pref['report_id'].'\'>'.$pref['report_naam'].'</option>';
				  $i++;
			   }
			}
	  	 }
		 return $output;
	  }

	  /**
	  * get_single_report pass through function 
	  * 
	  * @param mixed $id 
	  * @access public
	  * @return void
	  */
	  function get_single_report($id)
	  {
		 return($this->so->get_single_report($id));
	  }

	  /**
	   * save_report 
	   * 
	   * @param mixed $report_id if null new report will be inserted else the report will be updated 
	   * @access public
	   * @return void
	   */
	  function save_report($report_id=null)
	  {	

		 $post_extra_conf=$this->filter_array_with_prefix($_POST,'PLGXXX',true);
		 $post_extra_conf=$this->strip_prefix_from_keys($post_extra_conf,'PLGXXX');
		 if(is_array($post_extra_conf))
		 {
			$report_type_confdata=serialize($post_extra_conf);
		 }

		 if(is_null($report_id))
		 {
			if($this->so->insert_report($_POST['name'],$_POST['obj_id'] ,$_POST['text1'] ,$_POST['text2'] ,$_POST['text3'],$_POST['report_type_name'],$report_type_confdata) ==1)
			{
			   $this->addInfo(lang('Successfully saved new report.'));
			}
			else
			{
			   $this->addError(lang('Saving new report failed.'));
			}
		 }
		 else
		 {
			if($this->so->update_report($_POST['name'], $_POST['obj_id'], $_POST['text1'], $_POST['text2'], $_POST['text3'],$_POST['report_type_name'],$report_type_confdata, $report_id))
			{
			   $this->addInfo(lang('Successfully saved report.'));
			}
			else
			{
			   $this->addError(lang('Saving report failed.'));
			}
		 }
	  }
	  
	  /*
	  This function deletes one report from the JiNN-database
	  */
	  function delete_report()
	  {
		 $this->so->delete_report($_GET['report_id']);
		 header('location:'.$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiu_edit_record.dev_edit_record'));
	  }

	  function parse_records_through_header_source($records,$report_arr)
	  {
		 $header = $report_arr['report_header'];
		 $header = $this->replace_tiny_php_tags($header);
		 $header = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$header);
		 return $this->tplsav2->fetch_string($header);  			 

	  }
	  function parse_records_through_body_source($records,$report_arr)
	  {
		 foreach($records as $record)
		 {
			$input = $report_arr['report_body'];
			$this->tplsav2->assign('record',$record);
			$input = $this->replace_tiny_php_tags($input);
			$input = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$input);
			$output .= $this->tplsav2->fetch_string($input);
		 }
		 return $output;
	  }

	  function parse_records_through_footer_source($records,$report_arr)
	  {
		 $footer = $report_arr['report_footer'];
		 $footer = $this->replace_tiny_php_tags($footer);
		 $footer = preg_replace('/%%(.*?)%%/',"<?=\$this->record['$1'];?>",$footer );

		 return $this->tplsav2->fetch_string($footer);
	  }

	  
	 
	  /*
	  This function replaces the own php-tags from the template with the normal php-tags and makes it a valid php-file
	  */
	  function replace_tiny_php_tags($input)
	  {
		if(!function_exists('replace_php'))
		{
		   function replace_php($text)
		   {
			  $text =  preg_replace('/%%(.*?)%%/',"\$this->record['$1']",$text);
			  $x= "<?"."php "; 
			  $x .= strip_tags(str_replace('&nbsp;','',str_replace('\\','',$text)));
			  $x.= "?>";
		   return $x;
		   }
		}
		
		$output = preg_replace("#\[OPENPHP\](.*?)\[CLOSEPHP\]#ise", 'replace_php(\'$1\')', $input);
		$output = preg_replace('/&lt;/','<',$output);	
		$output = preg_replace('/&gt;/','>',$output);
		return $output;
	 }

	 }
   ?>
