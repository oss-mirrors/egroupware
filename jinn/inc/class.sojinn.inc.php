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

	class sojinn
	{
		var $phpgw_db;
		var $site_db;

		function sojinn()
		{
			$this->phpgw_db    	= $GLOBALS['phpgw']->db;
			$this->phpgw_db->Debug	= False;
		}

		/****************************************************************************\
		* make connection to the site database and set this->site_db                 *
		\****************************************************************************/

		function site_db_connection($site_id)
		{
			$SQL="SELECT * FROM phpgw_jinn_sites WHERE site_id='$site_id'";

			$this->phpgw_db->free();
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);
			$this->phpgw_db->next_record();

			$this->site_db 				= CreateObject('phpgwapi.db');
			//$this->site_db->Host		= $this->phpgw_db->f('site_db_host');
			// FIXME if db_host is set it gives error messages, but why???
			$this->site_db->Type		= $this->phpgw_db->f('site_db_type');
			$this->site_db->Database	= $this->phpgw_db->f('site_db_name');
			$this->site_db->User		= $this->phpgw_db->f('site_db_user');
			$this->site_db->Password	= $this->phpgw_db->f('site_db_password');
			//die($this->phpgw_db->f('site_db_host'));
		}

		function site_close_db_connection()
		{
			$this->site_db->disconnect;
		}

		function test_db_conn($data)
		{
			$this->site_db = CreateObject('phpgwapi.db');
			$this->site_db->Host		= $data['db_host'];
			$this->site_db->Type		= $data['db_type'];
			$this->site_db->Database	= $data['db_name'];
			$this->site_db->User		= $data['db_user'];
			$this->site_db->Password	= $data['db_password'];

			if($this->site_db->query("CREATE TABLE `JiNN_TEMP_TEST_TABLE` (`test` TINYINT NOT NULL)",__LINE__,__FILE__))
			{
				$x=1;
			}

			if($this->site_db->query("DROP TABLE `JiNN_TEMP_TEST_TABLE`",__LINE__,__FILE__)) 
			{
				$this->site_close_db_connection();

				return true;
			}

			$this->site_close_db_connection();

			return false;
		}

		/****************************************************************************\
		* get sitevalues for site id                                                 *
		\****************************************************************************/

		function get_site_values($site_id)
		{
			$site_metadata=$this->phpgw_db->metadata('phpgw_jinn_sites');
			$this->phpgw_db->free();	

			$SQL="SELECT * FROM phpgw_jinn_sites WHERE site_id='$site_id'";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			$this->phpgw_db->next_record();

			foreach($site_metadata as $fieldmeta)
			{
				$site_values[$fieldmeta['name']]=$this->phpgw_db->f($fieldmeta['name']);
			}

			return $site_values;
		}

		/**
		* return table names for a site by site site_id
		*
		* @return array table names
		* @param int JiNN Site id
		*/
		function site_tables_names($site_id)
		{
			$this->site_db_connection($site_id);
			$tables=$this->site_db->table_names();
			return $tables;
		}

		/****************************************************************************\
		* get objectvalues for object id                                             *
		\****************************************************************************/

		function get_object_values($object_id)
		{
			$object_metadata=$this->phpgw_db->metadata('phpgw_jinn_site_objects');
			$this->phpgw_db->free();	

			$this->phpgw_db->query("SELECT * FROM phpgw_jinn_site_objects
			WHERE object_id='$object_id'",__LINE__,__FILE__);

			$this->phpgw_db->next_record();
			foreach($object_metadata as $fieldmeta)
			{
				$object_values[$fieldmeta['name']]=$this->strip_magic_quotes_gpc($this->phpgw_db->f($fieldmeta['name']));
			}
			return $object_values;

		}

		/****************************************************************************\
		* get all tablefield in array for table                                      *
		\****************************************************************************/

		function get_phpgw_fieldnames($table)
		{
			$this->phpgw_db->query("SHOW FIELDS FROM $table",__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
				$fieldnames[] = $this->phpgw_db->f('Field');
			}
			return $fieldnames;
		}

		/****************************************************************************\
		* get all tablefield in array for table                                      *
		\****************************************************************************/

		function num_rows_table($site_id,$table)
		{
			$this->site_db_connection($site_id);

			$this->site_db->query("SELECT * FROM $table",__LINE__,__FILE__);

			$num_rows=$this->site_db->num_rows();

			$this->site_close_db_connection();
			return $num_rows;
		}

		
		function phpgw_table_metadata($table)
		{
			return $this->phpgw_db->metadata($table);
		}


		function site_table_metadata($site_id,$table)
		{
			$this->site_db_connection($site_id);

			$metadata = $this->site_db->metadata($table);

			$this->site_close_db_connection();

			return $metadata;
		}



		// new, without group(this has to be done seperately) and without objectsection(this also has to be done seperately)
		function get_sites_for_user2($uid)
		{
			$SQL = "SELECT site_id FROM phpgw_jinn_acl WHERE uid='$uid' $group_sql GROUP BY site_id";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
				if ($this->phpgw_db->f('site_id')!=null)
				{
					$sites[]= $this->phpgw_db->f('site_id');
				};

			}


			if (is_array($sites)) $sites=array_unique($sites);

			return $sites;
		}


		/****************************************************************************\
		* get all sites_id's which user has access to in array                       *
		\****************************************************************************/

		function get_sites_for_user($uid,$gid)
		{

			if($GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				$SQL = "SELECT site_id FROM phpgw_jinn_sites ORDER BY site_name";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				while ($this->phpgw_db->next_record())
				{
					$sites[]= $this->phpgw_db->f('site_id');
				}
			}
			else
			{
				if (isset($gid))
				{
					foreach ( $gid as $group ) {
						$group_sql.=' OR ';
						$group_sql .= "uid='$group'";
					}
				}

				$SQL = "SELECT site_id FROM phpgw_jinn_acl WHERE uid='$uid' $group_sql GROUP BY site_id";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				while ($this->phpgw_db->next_record())
				{
					if ($this->phpgw_db->f('site_id')!=null)
					{
						$sites[]= $this->phpgw_db->f('site_id');
					};

				}


				// this has to be removed

				/* get sites from site_objects of which user is owner */
				$objects = $this->get_site_objects_for_user($uid,$gid);

				if (count($objects)>0)
				{
					foreach ($objects as $object)
					{
						if ($SUB_SQL)$SUB_SQL.=' OR ';
						$SUB_SQL.="(object_id='$object')";
					}
				}

				$SQL="SELECT parent_site_id FROM phpgw_jinn_site_objects WHERE $SUB_SQL GROUP BY parent_site_id";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				while ($this->phpgw_db->next_record())
				{
					$sites[]= $this->phpgw_db->f('parent_site_id');

				}

			}


			if (is_array($sites)) $sites=array_unique($sites);

			return $sites;

		}

		/****************************************************************************\
		* get all site object id's which user has access to                          *
		\****************************************************************************/

		function get_site_objects_for_user($uid,$gid)
		{

			// als user phpGWADMIN is alle sites geven
			if($GLOBALS['phpgw_info']['user']['apps']['admin'])
			{
				$SQL="SELECT object_id FROM phpgw_jinn_site_objects ";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);
			}
			else
			{
				if (isset($gid))
				{
					foreach ( $gid as $group ) {
						$group_sql.=' OR ';
						$group_sql .= "uid='$group'";
					}
				}

				$SQL="SELECT site_object_id FROM phpgw_jinn_acl WHERE uid='$uid' $group_sql";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			}

			while ($this->phpgw_db->next_record())
			{
				$site_objects[]= $this->phpgw_db->f('site_object_id');
			}

			return $site_objects;
		}
		
		/**
		* test if table from site_objecte exists in site database
		*
		* @param array $JSO_arr standard JiNN Site Object properties array
		*
		*/
		

		function test_JSO_table($JSO_arr)
		{
			
			$this->site_db_connection($JSO_arr['parent_site_id']);
			$this->site_db->Halt_On_Error='no';

			if(@$this->site_db->query("SELECT * FROM ".$JSO_arr['table_name'],__LINE__,__FILE__))
			{
				$test=true;
			}
			else
			{
				$test=false;
			}
			
			$this->site_close_db_connection();
			return $test;

		}

		/****************************************************************************\
		* get sitename for site id                                                   *
		\****************************************************************************/

		function get_site_name($site_id)
		{
			$this->phpgw_db->query("SELECT site_name FROM phpgw_jinn_sites
			WHERE site_id='$site_id'",__LINE__,__FILE__);

			$this->phpgw_db->next_record();
			$site_name=$this->strip_magic_quotes_gpc($this->phpgw_db->f('site_name'));
			return $site_name;

		}
				
		/****************************************************************************\
		* get sitename for site id                                                   *
		\****************************************************************************/

		function get_sites_by_name($name)
		{
			$this->phpgw_db->query("SELECT * FROM phpgw_jinn_sites
			WHERE site_name='$name'",__LINE__,__FILE__);

			while($this->phpgw_db->next_record())
			{
				$ids[]=$this->phpgw_db->f('id');
			}
			return $ids;

		}	
		
		/* 
			strip_magic_quotes_gpc checks if magic_quotes_gpc is set on in 
			the current php configuration. If this is true it removes the slashes
		*/
		function strip_magic_quotes_gpc($value)
		{
			if (get_magic_quotes_gpc()==1)
			{
				return stripslashes($value);
			}
			else return $value;
		}
		
		
		/****************************************************************************\
		* get objectname for object id                                               *
		\****************************************************************************/

		function get_object_name($object_id)
		{
			$this->phpgw_db->query("SELECT name FROM phpgw_jinn_site_objects
			WHERE object_id='$object_id'",__LINE__,__FILE__);

			$this->phpgw_db->next_record();
			$name=$this->strip_magic_quotes_gpc($this->phpgw_db->f('name'));
			return $name;
		}

		function get_objects_for_user($uid)
		{

			$SQL="SELECT site_object_id FROM phpgw_jinn_acl WHERE uid='$uid'";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{	
				$objects[]= $this->phpgw_db->f('site_object_id');
			}

			return $objects;
		}



		/****************************************************************************\
		* ADMIN insert site data in phpgw_jinn_sites                       *
		\****************************************************************************/

		function get_objects($site_id,$uid,$gid)
		{

			if (count($gid>0) )
			{
				foreach ( $gid as $group )
				{
					$group_sql.=' OR ';
					$group_sql .= "uid='$group'";
				}
			}

			/* check if user or group administers this site */
			$SQL="SELECT site_id FROM phpgw_jinn_acl WHERE uid='$uid' $group_sql";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
				if ($site_id == $this->phpgw_db->f('site_id'))
				{
					$admin='yes';
				}
			}

			/* yes it's an admin so we can get all objects for this site */
			if ($admin=='yes')
			{
				$SQL="SELECT object_id FROM phpgw_jinn_site_objects WHERE parent_site_id = '$site_id' ORDER BY name";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				while ($this->phpgw_db->next_record())
				{
					$objects[]= $this->phpgw_db->f('object_id');
				}
			}
			// he's no admin so get all the objects which are assigned to the user
			else
			{
				$SQL="SELECT object_id FROM phpgw_jinn_site_objects WHERE parent_site_id = '$site_id' ORDER BY name";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				while ($this->phpgw_db->next_record())
				{
					if ($object_sql) $object_sql.=' OR ';
					$object_sql .= "site_object_id='".$this->phpgw_db->f('object_id')."'";
				}

				if($object_sql)
				{
					$SQL="SELECT site_object_id FROM phpgw_jinn_acl WHERE ($object_sql) AND (uid='$uid' $group_sql)";
					$this->phpgw_db->query($SQL,__LINE__,__FILE__);

					while ($this->phpgw_db->next_record())
					{
						$objects[]= $this->phpgw_db->f('site_object_id');
					}
				}

			}

			if (count($objects)>0)
			{
				$objects=array_unique($objects);
			}

			return $objects;
		}


		function get_phpgw_record_values($table,$where_key,$where_value,$offset,$limit,$value_reference)
		{
			if ($where_key && $where_value)
			{
				$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
				$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
				$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
			}
			
			
			$fieldproperties = $this->phpgw_table_metadata($table);

			$SQL="SELECT * FROM  $table $WHERE";
			if (!$limit) $limit=1000000;

			$this->phpgw_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); // returns a limited result from start to limit

			while ($this->phpgw_db->next_record())
			{

				unset($row);
				foreach($fieldproperties as $field)
				{
					if ($value_reference=='name')
					{
						$row[$field[name]] = $this->strip_magic_quotes_gpc($this->phpgw_db->f($field[name]));
					}
					else
					{
						$row[] = $this->strip_magic_quotes_gpc($this->phpgw_db->f($field[name]));
					}
				}
				$rows[]=$row;

			}

			return $rows;
		}


		function get_1wX_record_values($site_id,$object_id,$m2m_relation,$all_or_stored)
		{

			$this->site_db_connection($site_id);

			if ($all_or_stored=="all")
			{
				$SQL="SELECT $m2m_relation[foreign_key],$m2m_relation[display_field] FROM $m2m_relation[display_table] ORDER BY $m2m_relation[display_field] ";
			}
			elseif($object_id)
			{
				$SQL="SELECT $m2m_relation[foreign_key],$m2m_relation[display_field] FROM $m2m_relation[display_table] INNER JOIN $m2m_relation[via_table]
				ON $m2m_relation[via_foreign_key]=$m2m_relation[foreign_key] WHERE $m2m_relation[via_primary_key]=$object_id ORDER BY $m2m_relation[display_field]";

			}
			else
			{
				$SQL=false;
			}

			$tmp=explode('.',$m2m_relation[foreign_key]);
			$foreign_key=$tmp[1];
			$tmp=explode('.',$m2m_relation[display_field]);
			$display_field=$tmp[1];

			if($SQL)
			{
				$this->site_db->query($SQL, $offset,__LINE__,__FILE__); // returns a result

				while ($this->site_db->next_record())
				{

					$records[]=array(
						'name'=>$this->site_db->f($display_field),
						'value'=>$this->site_db->f($foreign_key)
					);
				}
			}


			return $records;
		}

		function get_record_values($site_id,$table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by='',$field_list='*')
		{
/*			
			echo "site_id 1 $site_id <br>";
			echo "table 2 $table<br>";
			echo "where_key 3$where_key<br>";
			echo "where_value 4 $where_value<br>";
			echo "offset 5 $offset <br>";
			echo "limit 6 $limit <br>";
			echo "value_ref 7 $value_reference<br>";
			echo "order by 8 $order_by<br>";
			echo "field_list 9 $field_list<br>";
//		die();	
*/			
			$this->site_db_connection($site_id);

			if ($where_key && $where_value)
			{
				$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
				$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
				$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
			}

			if ($order_by)
			{
				$ORDER_BY = ' ORDER BY '.$order_by;
			}

			$fieldproperties = $this->site_table_metadata($site_id,$table);
			$field_list_arr=(explode(',',$field_list));
			$SQL="SELECT $field_list FROM $table $WHERE $ORDER_BY";
		//	echo($SQL);
			if (!$limit) $limit=1000000;

			$this->site_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); 

			while ($this->site_db->next_record())
			{
				unset($row);
				foreach($fieldproperties as $field)
				{
					if($field_list=='*' || in_array($field[name],$field_list_arr))
					{
						if ($field[type]=='blob' && ereg('binary',$field[flags]))
						{
							$value=lang('binary');
						}
						else
						{
							$value=$this->strip_magic_quotes_gpc($this->site_db->f($field[name]));
						}


						if ($value_reference=='name')
						{
							$row[$field[name]] = $value;
						}
						else
						{
							$row[] = $value;
						}

						
					}
				}
				$rows[]=$row;
			}

			return $rows;
		}


		function delete_object_data($site_id,$table,$where_key,$where_value)
		{
			$this->site_db_connection($site_id);

			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key) ."='".$this->strip_magic_quotes_gpc($where_value)."'";

			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
				$status=1;
			}

			return $status;

		}

		function copy_object_data($site_id,$table,$where_key,$where_value)
		{

			$this->site_db_connection($site_id);

			$record=$this->site_table_metadata($site_id,$table);
			$values=$this->get_record_values_2($site_id,$table,$this->strip_magic_quotes_gpc($where_key),$this->strip_magic_quotes_gpc($where_value),'0','1','name','');

			foreach($record as $field)
			{
				if ($field[name]!='id')
				{
					if ($SQLfields) $SQLfields .= ',';
					if ($SQLvalues) $SQLvalues .= ',';

					$SQLfields .= $field[name];
					$SQLvalues .= "'".addslashes($values[0][$field['name']])."'";
				}
			}

			$SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';


			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
				$status=1;
			}

			return $status;

		}



		function insert_object_data($site_id,$site_object,$data)
		{

			$this->site_db_connection($site_id);

			foreach($data as $field)
			{
				if(!$thirstfield) $thirstfield=$field[name];
				if ($SQLfields) $SQLfields .= ',';
				if ($SQLvalues) $SQLvalues .= ',';

				$SQLfields .= $field[name];
//				$SQLvalues .= "'".addslashes($this->strip_magic_quotes_gpc($field[value]))."'"; // FIX THIS magic kut quotes
				$SQLvalues .= "'".$this->strip_magic_quotes_gpc($field[value])."'"; // FIX THIS magic kut quotes
//				$SQLvalues .= "'$field[value]'";
			}

			$SQL='INSERT INTO ' . $site_object . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

			//die($SQL);
			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
				$value[status]=1;
				$value[idfield]=$thirstfield;
				$value[id]=$this->site_db->get_last_insert_id($site_object, $thirstfield);

			}
			return $value;


		}

		function update_object_many_data($site_id, $data)
		{

			$this->site_db_connection($site_id);
			$status=True;
			$i=1;

			while (isset($data['MANY_REL_STR_'.$i]))
			{
				list($via_primary_key,$via_foreign_key) = explode("|",$data['MANY_REL_STR_'.$i]);
				list($table,) = explode(".",$via_primary_key);

				$SQL="DELETE FROM $table WHERE $via_primary_key='$data[FLDid]'";

				if (!$this->site_db->query($SQL,__LINE__,__FILE__))
				{
					$status=-1;
				}

				$related_data=explode(",",$data['MANY_OPT_STR_'.$i]);
				foreach($related_data as $option)
				{
					$SQL="INSERT INTO $table ($via_primary_key,$via_foreign_key) VALUES ('$data[FLDid]', '$option')";

			if (!$this->site_db->query($SQL,__LINE__,__FILE__))
					{
						$status=False;
					}

				}

				$i++;
			}
			return $status;

		}




		function update_object_data($site_id,$site_object,$data,$where_key,$where_value)
		{
			$this->site_db_connection($site_id);

			foreach($data as $field)
			{
				if ($SQL_SUB) $SQL_SUB .= ', ';
				$SQL_SUB .= "$field[name]='".$this->strip_magic_quotes_gpc($field[value])."'";
			}

			$SQL = 'UPDATE ' . $site_object . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_key))."='".$this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_value))."'";

			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
				$status=1;
			}

			return $status;
		}

		function delete_phpgw_data($site_id,$table,$where_key,$where_value)
		{

			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";

			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
				$status=1;
			}

			return $status;

		}

		function insert_phpgw_data($table,$data)
		{
			foreach($data as $field)
			{
				if ($SQLfields) $SQLfields .= ',';
				if ($SQLvalues) $SQLvalues .= ',';

				$SQLfields .= $field[name];
				$SQLvalues .= "'".$field[value]."'";
			}


			$SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			
				//$status=1;//$this->phpgw_db->get_last_insert_id($table,'x');
				$status=$this->phpgw_db->get_last_insert_id($table,'x');
			}

			return $status;
		}


		function update_phpgw_data($table,$data,$where_key,$where_value)
		{

			foreach($data as $field)
			{
				if ($SQL_SUB) $SQL_SUB .= ', ';
				$SQL_SUB .= "$field[name]='$field[value]'";
			}

			$SQL = 'UPDATE ' . $table . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
				$status=1;
			}

			return $status;
		}

		function update_object_access_rights($editors,$object_id)
		{
			$error=0;
			if ($object_id)
			{

				$SQL="DELETE FROM phpgw_jinn_acl WHERE site_object_id='$object_id' AND uid IS NOT NULL";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				if (count($editors)>0){
					foreach ($editors as $editor)
					{
						$SQL="INSERT INTO phpgw_jinn_acl (site_object_id, uid) VALUES ('$object_id','$editor')";
						if(!$this->phpgw_db->query($SQL,__LINE__,__FILE__))
						{
							$error++;
						}
					}
				}

			}
			else
			{
				$error++;
			}

			if ($error==0)
			{
				$status=1;
			}

			return $status;
		}

		function update_site_access_rights($editors,$site_id)
		{
			$error=0;
			if ($site_id)
			{
				$SQL="DELETE FROM phpgw_jinn_acl WHERE site_id='$site_id' AND uid IS NOT NULL";
				$this->phpgw_db->query($SQL,__LINE__,__FILE__);

				if (count($editors)>0){
					foreach ($editors as $editor)
					{
						$SQL="INSERT INTO phpgw_jinn_acl (site_id, uid) VALUES ('$site_id','$editor')";
						if(!$this->phpgw_db->query($SQL,__LINE__,__FILE__))
						{
							$error++;
						}
					}
				}
			}
			else
			{
				$error++;
			}
			
			if ($error==0)
			{
				$status=1;
			}

			return $status;
		}
	}
?>
