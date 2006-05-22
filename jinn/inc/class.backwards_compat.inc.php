<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Authors: Pim Snel <pim@lingewoud.nl>, 
   Copyright (C) 2005 Pim Snel <pim@lingewoud.nl>

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

   /**
   * backwards_compat 
   * 
   * @package jinn_core
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class backwards_compat
   {
	  function backwards_compat()
	  {}

	  /**
	  * relations_up2date: do all upgrades to relations
	  */
	  function relations_up2date($rel)
	  {
		 $relations_arr = unserialize(base64_decode($rel));

		 if(is_array($relations_arr) && count($relations_arr)==0) return;

		 if(!is_array($relations_arr))
		 {
			$relations_arr=$this->convert_old_relations($rel);
		 }

		 $relations_arr = $this->convert_2modern_relations($relations_arr);
		 return $relations_arr;
	  }
	  
	  /**
	  * convert_old_relations 
	  *
	  * @param string $relation_string contains old relation information
	  */
	  function convert_old_relations($relation_string)
	  {
		 $old_fmt_relations=explode('|',$relation_string);
		 foreach($old_fmt_relations as $old_fmt_relation)
		 {
			$old_fmt_relation_parts=explode(':',$old_fmt_relation);
			if($old_fmt_relation_parts[0]==1)
			{
			   unset($new_fmt_relation);
			   $new_fmt_relation[type] = $old_fmt_relation_parts[0];
			   $new_fmt_relation[org_field] = $old_fmt_relation_parts[1];
			   $new_fmt_relation[related_with] = $old_fmt_relation_parts[3];
			   $new_fmt_relation[display_field] = $old_fmt_relation_parts[4];
			}
			else if($old_fmt_relation_parts[0]==2)
			{
			   unset($new_fmt_relation);
			   $new_fmt_relation[type] = $old_fmt_relation_parts[0];
			   $new_fmt_relation[via_primary_key] = $old_fmt_relation_parts[1];
			   $new_fmt_relation[via_foreign_key] = $old_fmt_relation_parts[2];
			   $new_fmt_relation[foreign_key] = $old_fmt_relation_parts[3];
			   $new_fmt_relation[display_field] = $old_fmt_relation_parts[4];
			}
			else if($old_fmt_relation_parts[0]==3)
			{
			   unset($new_fmt_relation);
			   $new_fmt_relation[type] = $old_fmt_relation_parts[0];
			   $new_fmt_relation[org_field] = $old_fmt_relation_parts[1];
			   $new_fmt_relation[related_with] = $old_fmt_relation_parts[3];
			   $new_fmt_relation[object_conf] = $old_fmt_relation_parts[4];
			}

			$relations_arr[] = $new_fmt_relation;
		 }

		 return $relations_arr;

	  }

	  /**
	  * convert_2modern_relations: convert to the second revision of the relations array
	  * this also sort on type
	  */
	  function convert_2modern_relations($relations_arr= array())
	  {
		 foreach($relations_arr as $relation)
		 {
			if ($relation[type]==1)
			{
			   if(!$relation[id])
			   {
				  $relation[id]=uniqid('');
			   }
			   
			   unset($_displ);

			   $_arr=explode('.',$relation[related_with]);
			   
				$relation[local_key]=($relation[local_key]?$relation[local_key]:$relation[org_field]);
				$relation[foreign_table]=($relation[foreign_table]?$relation[foreign_table]:$_arr[0]);
				$relation[foreign_key]=($relation[foreign_key]?$relation[foreign_key]:$_arr[1]);

				if(!$relation[foreign_showfields])
				{
				   if($relation[display_field]!='') 
				   {
					  $_t=explode('.',$relation[display_field]);
					  $_displ[] = $_t[1];
				   }
				   if($relation[display_field_1]!='') 
				   {
					  $_t=explode('.',$relation[display_field_1]);
					  $_displ[] = $_t[1];
				   }
				   if($relation[display_field_2]!='') 
				   {
					  $_t=explode('.',$relation[display_field_2]);
					  $_displ[] = $_t[1];
				   }

				   $relation[foreign_showfields]=serialize($_displ);
				}
				
				$ret_arr[]=$relation;
			}

			if ($relation[type]==2)
			{
			   if(!$relation[id])
			   {
				  $relation[id]=uniqid('');
			   }

			   /* This is very ugly to just say the primary field is called 'id', but 
			   this only needed for backwards compatibility, because we used to do this*/
			   $relation[local_key]=($relation[local_key]?$relation[local_key]:'id');
			   
			   
			   $_t=explode('.',$relation[foreign_key]);
			   if(count($_t)==2)
			   {
				  $relation[foreign_key]=$_t[1];
				  $relation[foreign_table] = $_t[0];
			   }
			   else
			   {
				  $relation[foreign_key] = ($relation[foreign_key]?$relation[foreign_key]:$_t[1]);
				  $relation[foreign_table] = ($relation[foreign_table]?$relation[foreign_table]:$_t[0]);
			   }
			   
			  
			   $relation[connect_key_local]=($relation[connect_key_local]?$relation[connect_key_local]:$relation[via_primary_key]);
			   $relation[connect_key_foreign]=($relation[connect_key_foreign]?$relation[connect_key_foreign]:$relation[via_foreign_key]);

			   if(!$relation[connect_table])
			   {
				  $_conn=explode('.',$relation[connect_key_local]);
				  $relation[connect_table]=$_conn[0];
			   }

			   $new_fmt_relation[display_field] = $old_fmt_relation_parts[4];

			   if(!$relation[foreign_showfields])
			   {
				  if($relation[display_field]!='') 
				  {
					 $_t=explode('.',$relation[display_field]);
					 $_displ[] = $_t[1];
				  }
				  if($relation[display_field_1]!='') 
				  {
					 $_t=explode('.',$relation[display_field_1]);
					 $_displ[] = $_t[1];
				  }
				  if($relation[display_field_2]!='') 
				  {
					 $_t=explode('.',$relation[display_field_2]);
					 $_displ[] = $_t[1];
				  }

				  $relation[foreign_showfields]=serialize($_displ);


			   }

			   $ret_arr[]=$relation;

			}

			if ($relation[type]==3)
			{
			   if(!$relation[id])
			   {
				  $relation[id]=uniqid('');
			   }
			   
			   $_arr=explode('.',$relation[related_with]);

			   $relation[local_key]=($relation[local_key]?$relation[local_key]:$relation[org_field]);
			   $relation[foreign_table]=($relation[foreign_table]?$relation[foreign_table]:$_arr[0]);
			   $relation[foreign_key]=($relation[foreign_key]?$relation[foreign_key]:$_arr[1]);
			   
			   $ret_arr[]=$relation;
			}

			if ($relation[type]==4)
			{
			   if(!$relation[id])
			   {
				  $relation[id]=uniqid('');
			   }
			   $ret_arr[]=$relation;
			}
		 }
		 
		 return $ret_arr;
	  }

	  /**
	  * convert old style to new style
	  * @param array $cfg with old configuration array
	  * @return array with new config array
	  */
	  function convert_old_dbplug_array($cfg)
	  {
		 $new_cfg=array();
		 /*  */
		 if(is_array($cfg))
		 {
			foreach($cfg as $cfg_key => $cfg_val)
			{
			   if(is_array($cfg_val[0]))
			   {
				  $option_arr=$cfg_val[0];
				  unset($option_arr_new);
				  foreach($option_arr as $_opts)
				  {
					 $option_arr_new[$_opts]=$_opts;
				  }
			   }
			   else
			   {
				  $def_val=$cfg_val[0];
			   }

			   $new_cfg[$cfg_key]  = array(
				  'name' => $cfg_key,
				  'label' => ereg_replace('_',' ',$cfg_key),
				  'type' => $cfg_val[1],
				  'extra_html' =>$cfg_val[2],
				  'def_val' => $def_val,
				  'select_arr' => $option_arr_new,
				  'radio_arr'=> $option_arr_new
			   );

			}

		 }
		 return $new_cfg;
	  }
   }
