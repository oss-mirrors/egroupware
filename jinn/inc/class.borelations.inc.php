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

	class borelations extends bojinn
	{

		//var $bo;
		//var $so;

		function borelations()
		{
			//$this->so = CreateObject('jinn.sojinn');
			//$this->bo = CreateObject('jinn.bojinn');
		}

		/****************************************************************************\
		* make array of one with many relational information                         *
		\****************************************************************************/

		function get_fields_with_1_relation($relations)
		{
			$relations_array = explode('|',$relations);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='1')
				{
					$return_relation[$relation_part[1]] = array
					(
						'type'=>$relation_part[0],
						'field_org'=>$relation_part[1],
						'related_with'=>$relation_part[3],
						'display_field'=>$relation_part[4]
					);
				}

			}
			//die(var_dump($return_relation));
			return $return_relation;
		}

		/****************************************************************************\
		* make array of many with many relational information                        *
		\****************************************************************************/

		function get_fields_with_2_relation($relations)
		{
			$relations_array = explode('|',$relations);

			foreach($relations_array as $relation)
			{
				$relation_part=explode(':',$relation);
				if ($relation_part[0]=='2')
				{
					$tmp=explode('.',$relation_part[1]);
					$via_table=$tmp[0];
					$tmp=explode('.',$relation_part[4]);
					$display_table=$tmp[0];

					$return_array[] = array
					(
						'type'=>$relation_part[0],
						'via_primary_key'=>$relation_part[1],
						'via_foreign_key'=>$relation_part[2],
						'via_table'=>$via_table,
						'foreign_key'=>$relation_part[3],
						'display_field'=>$relation_part[4],
						'display_table'=>$display_table
					);
				}
			}
			return $return_array;
		}

		function get_related_field($relation_array)
		{
			$table_info=explode('.',$relation_array[related_with]);
			$table=$table_info[0];
			$related_field=$table_info[1];

			$table_info2=explode('.',$relation_array[display_field]);
			$table_display=$table_info2[0];
			$display_field=$table_info2[1];

			$allrecords=$this->bo->get_records_2($table,'','','','name',$display_field);

			//die(count($allrecords));
			foreach ($allrecords as $record)
			{
				$related_fields[]=array
				(
					'value'=>$record[$related_field],
					'name'=>$record[$display_field]
				);
			}
			//die(var_dump($related_fields));
			return $related_fields;
		}

		function get_m2m_options($relation2,$all_or_stored,$object_id)
		{

			return $this->bo->so->get_m2m_record_values($this->bo->site_id,$object_id,$relation2,$all_or_stored);

		}



	}

?>
