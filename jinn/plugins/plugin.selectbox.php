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

	/*-------------------------------------------------------------------
	SelectBox PLUGIN
	-------------------------------------------------------------------*/
	$this->plugins['selectbox']['name'] 			= 'selectbox';
	$this->plugins['selectbox']['title']			= 'Select Box';
	$this->plugins['selectbox']['version']		= '0.2';
	$this->plugins['selectbox']['enable']			= 1;
	$this->plugins['selectbox']['description']	= 'List a couple of values in a listbox....';
	$this->plugins['selectbox']['db_field_hooks']	= array
	(
		'string'
	);
	$this->plugins['selectbox']['config']		= array
	(
		'Value_seperated_by_commas'=>array('one,two,three','text',''),
		'Default_value'=>array('one','text',''),
		'Empty_option_available'=> array(array('yes','no'),'select','')
	);

	function plg_fi_selectbox($field_name,$value, $config)
	{
		$pos_values=explode(',',$config['Value_seperated_by_commas']);
		if(is_array($pos_values))
		{
			$input='<select name="'.$field_name.'">';
			if($config['Empty_option_available']=='yes') $input.='<option>';
			foreach($pos_values as $pos_val)
			{
				unset($selected);
				if(empty($value) && $pos_val==$config['Default_value']) $selected='SELECTED';
				//	  die($value.' '.$pos_val);
				if($value==$pos_val) $selected='SELECTED';
				$input.='<option '.$selected.' value="'.$pos_val.'">'.$pos_val.'</option>';
			}
			$input.='</select>';
		}
		else
		{
			$input= '<input name="'.$field_name.'" type=text value="'.$value.'">';

		}

		return $input;
	}
 ?>
