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
	
	---------------------------------------------------------------------
	 
	plugin.standard_set.php contains a number off standardly available 
	plugins for JiNN. 
	
	*/


	
	/*-------------------------------------------------------------------
		NewLine to Break PLUGIN                                                                    
	-------------------------------------------------------------------*/
	$description = '
	The Newline2Break Plugin is the most simple WYSIWYG plugin there is. The 
	only thing it does is replacing \'newlines\' for the Break html-tag. It\'s 
	still very handy because creating paragraphs it often the only extra feature 
	a webmaster needs. For savety reasons this plugin removes all other html tags
	before storing the data to teh database
	';

	$this->plugins['nl2br']['name']				= 'nl2br';
	$this->plugins['nl2br']['title']			= 'Newline2Break Filter';
	$this->plugins['nl2br']['version']			= '1.0';
	$this->plugins['nl2br']['enable']			= 1;
	$this->plugins['nl2br']['description']		= $description;
	$this->plugins['nl2br']['db_field_hooks']	= array
	(
		'blob',
		'text'
	);
	$this->plugins['nl2br']['config']		= array
	(
		'Strip_HTML_TAGS'=>array(array('Yes','No'),'select','')
	);

	function plg_fi_nl2br($field_name, $value, $config)
	{
		$input='<textarea name="'.$field_name.'" style="width:100%; height:200">'.str_replace('<br />','',$value).'</textarea>';
		return $input;
	}

	function plg_sf_nl2br($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	{
		$input=$HTTP_POST_VARS[$key];
		if (!$config['Strip_HTML_TAGS'] || $config['Strip_HTML_TAGS']=='Yes')
		{
			$input=strip_tags($input);
		}
		
		$output=addslashes(nl2br($input));

		return $output;
	}

	/*-------------------------------------------------------------------
		Hide This Field PLUGIN                                                                     
	-------------------------------------------------------------------*/
	$this->plugins['hidefield']['name'] 			= 'hidefield';
	$this->plugins['hidefield']['title']			= 'Hide This Field';
	$this->plugins['hidefield']['version']			= '1.0';
	$this->plugins['hidefield']['enable']			= 1;
	$this->plugins['hidefield']['description']		= 'This just hides the input field for users';
	$this->plugins['hidefield']['db_field_hooks']	= array
	(
		'string',	
		'int',	
		'blob',	
		'date',
		'timestamp'	
	);

	function plg_fi_hidefield($field_name,$value, $config)
	{
		return 'hide';
	}

	/*-------------------------------------------------------------------
		Boolian PLUGIN                                                                     
	-------------------------------------------------------------------*/
	$this->plugins['boolian']['name'] 			= 'boolian';
	$this->plugins['boolian']['title']			= 'Boolian';
	$this->plugins['boolian']['version']		= '1.0';
	$this->plugins['boolian']['enable']			= 1;
	$this->plugins['boolian']['description']	= 'Input for on/off, yes/no, true/false etc....';
	$this->plugins['boolian']['db_field_hooks']	= array
	(
		'string',	
		'int',	
	);
	$this->plugins['boolian']['config']		= array
	(
		'ON_input_display_value'=>array('yes','text','maxlength=20'),
		'OFF_input_display_value'=>array('no','text','maxlength=20'), 
		'ON_output_value_If_not_the_same_as_input_value'=>array('','text','maxlength=20'),
		'OFF_output_value_If_not_the_same_as_input_value'=>array('','text','maxlength=20'),
		'Default_value'=>array(array('ON','OFF','NOTHING'),'select',''),
	);

	function plg_fi_boolian($field_name,$value, $config)
	{

		if($config['ON_output_value_If_not_the_same_as_input_value']) $val_on=$config['ON_output_value_If_not_the_same_as_input_value'];
		else $val_on=$config['ON_input_display_value'];

		if($config['OFF_output_value_If_not_the_same_as_input_value']) $val_off=$config['OFF_output_value_If_not_the_same_as_input_value'];
		else $val_off=$config['OFF_input_display_value'];

		if($value==$val_on) $on_select='SELECTED';
		elseif($value==$val_off) $off_select='SELECTED';
		elseif($value || $config['Default_value']=='NOTHING') $empty_option='<option value=""></option>';
		elseif(!$value && $config['Default_value']=='ON') $on_select='SELECTED'; 
		elseif(!$value && $config['Default_value']=='OFF') $off_select='SELECTED'; 


		$input='<select name="'.$field_name.'">';
		$input.=$empty_option;
		$input.='<option '.$on_select.' value="'.$val_on.'">'.$config['ON_input_display_value'].'</option>';
		$input.='<option '.$off_select.' value="'.$val_off.'">'.$config['OFF_input_display_value'].'</option>';
		$input.='</select>';

		return $input;
	}

?>
