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
			$this->site_db->Host		= $this->phpgw_db->f('site_db_host');
			$this->site_db->Type		= $this->phpgw_db->f('site_db_type');
			$this->site_db->Database	= $this->phpgw_db->f('site_db_name');
			$this->site_db->User		= $this->phpgw_db->f('site_db_user');
			$this->site_db->Password	= $this->phpgw_db->f('site_db_password');

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

		function get_table_names($site_id)
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
					$object_values[$fieldmeta['name']]=$this->phpgw_db->f($fieldmeta['name']);
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


		function get_phpgw_fieldproperties($table)
		{

			$fieldproperties = $this->phpgw_db->metadata($table);

			return $fieldproperties;
		}

		function get_site_fieldproperties($site_id,$table)
		{
		
			$this->site_db_connection($site_id);
			$fieldproperties = $this->site_db->metadata($table);

			$this->site_close_db_connection();
			return $fieldproperties;
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
		        	$SQL = "SELECT site_id FROM phpgw_jinn_sites ";
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

		/****************************************************************************\
		* get sitename for site id                                                   *
		\****************************************************************************/

		function get_site_name($site_id)
		{
			$this->phpgw_db->query("SELECT site_name FROM phpgw_jinn_sites
			WHERE site_id='$site_id'",__LINE__,__FILE__);

			$this->phpgw_db->next_record();
			$site_name=$this->phpgw_db->f('site_name');
			return $site_name;

		}

		/****************************************************************************\
		* get objectname for object id                                               *
		\****************************************************************************/

		function get_object_name($object_id)
		{
			$this->phpgw_db->query("SELECT name FROM phpgw_jinn_site_objects
			WHERE object_id='$object_id'",__LINE__,__FILE__);

			$this->phpgw_db->next_record();
			$name=$this->phpgw_db->f('name');
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
				$SQL="SELECT object_id FROM phpgw_jinn_site_objects WHERE parent_site_id = '$site_id'";
			        $this->phpgw_db->query($SQL,__LINE__,__FILE__);

			        while ($this->phpgw_db->next_record())
		        	{
        				$objects[]= $this->phpgw_db->f('object_id');
        			}
			}
			// he's no admin so get all the objects which are assigned to the user
			else
			{
				$SQL="SELECT object_id FROM phpgw_jinn_site_objects WHERE parent_site_id = '$site_id'";
			        $this->phpgw_db->query($SQL,__LINE__,__FILE__);

			        while ($this->phpgw_db->next_record())
		        	{
					if ($object_sql) $object_sql.=' OR ';
					$object_sql .= "site_object_id='".$this->phpgw_db->f('object_id')."'";
        			}

				$SQL="SELECT site_object_id FROM phpgw_jinn_acl WHERE ($object_sql) AND (uid='$uid' $group_sql)";
			        $this->phpgw_db->query($SQL,__LINE__,__FILE__);

			        while ($this->phpgw_db->next_record())
		        	{
				       $objects[]= $this->phpgw_db->f('site_object_id');
				}

			}

			if (count($objects)>0)
			{
			        $objects=array_unique($objects);
			}

			return $objects;
        	}


		function get_phpgw_record_values($table,$where_condition,$offset,$limit,$value_reference)
		{

			if ($where_condition)
			{
			        $WHERE = ' WHERE '.stripslashes($where_condition);
			}

			$fieldproperties = $this->get_phpgw_fieldproperties($table);

			$SQL='SELECT * FROM '. $table . $WHERE;
			if (!$limit) $limit=1000000;

			$this->phpgw_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); // returns a limited result from start to limit

			while ($this->phpgw_db->next_record())
			{

				unset($row);
				foreach($fieldproperties as $field)
                                {
                                        if ($value_reference=='name')
                                        {
						$row[$field[name]] = $this->phpgw_db->f($field[name]);
                                        }
					else
				        {
                                                $row[] = $this->phpgw_db->f($field[name]);
                                        }
                                }
				$rows[]=$row;

			}

			return $rows;
		}


		function get_m2m_record_values($site_id,$object_id,$m2m_relation,$all_or_stored)
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

			//die (var_dump($m2m_relation));

			if($SQL)
			{
				$this->site_db->query($SQL, $offset,__LINE__,__FILE__); // returns a result

				while ($this->site_db->next_record())
				{

					$records[]=array(
						'name'=>$this->site_db->f($display_field),
						'value'=>$this->site_db->f($foreign_key),
						//'value1'=>$this->site_db->f(norm_name),
						);
				}
			}


			//die (var_dump($display_field));
			return $records;
		}



		function get_record_values($site_id,$table,$where_condition,$offset,$limit,$value_reference)
		{

			$this->site_db_connection($site_id);

			if ($where_condition)
			{
			        $WHERE = ' WHERE '.stripslashes($where_condition);
			}

			$fieldproperties = $this->get_site_fieldproperties($site_id,$table);

			$SQL='SELECT * FROM '. $table . $WHERE;
			if (!$limit) $limit=1000000;

			$this->site_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); // returns a limited result from start to limit

			while ($this->site_db->next_record())
			{

				unset($row);
				foreach($fieldproperties as $field)
                                {
					if ($field[type]=='blob' && ereg('binary',$field[flags]))
					{
					        $value=lang('binary');
					}
					else
					{
					        $value=$this->site_db->f($field[name]);
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
				$rows[]=$row;

			}

			return $rows;
		}


		function get_record_values_2($site_id,$table,$where_condition,$offset,$limit,$value_reference,$order_by)
		{
			$this->site_db_connection($site_id);

			if ($where_condition)
			{
			        $WHERE = ' WHERE '.stripslashes($where_condition);
			}

			if ($order_by)
			{
			        $ORDER_BY = ' ORDER BY '.stripslashes($order_by);
			}

			$fieldproperties = $this->get_site_fieldproperties($site_id,$table);

			$SQL='SELECT * FROM '. $table . $WHERE . $ORDER_BY;
			if (!$limit) $limit=1000000;

			$this->site_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); // returns a limited result from start to limit

			while ($this->site_db->next_record())
			{

				unset($row);
				foreach($fieldproperties as $field)
                                {
					if ($field[type]=='blob' && ereg('binary',$field[flags]))
					{
					        $value=lang('binary');
					}
					else
					{
					        $value=$this->site_db->f($field[name]);
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
				$rows[]=$row;

			}

			return $rows;
		}


		function delete_object_data($site_id,$table,$where_condition)
		{
		        $this->site_db_connection($site_id);

			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . stripslashes(stripslashes($where_condition));

			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
			        $status=1;
			}

			return $status;

		}

		function copy_object_data($site_id,$table,$where_condition)
		{

			$this->site_db_connection($site_id);

			$record=$this->get_site_fieldproperties($site_id,$table);
			$values=$this->get_record_values_2($site_id,$table,$where_condition,'0','1','name','');

			foreach($record as $field)
			{
				if ($field[name]!='id')
				{
					if ($SQLfields) $SQLfields .= ',';
					if ($SQLvalues) $SQLvalues .= ',';

					$SQLfields .= $field[name];
					$SQLvalues .= "'".$values[0][$field['name']]."'";
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
				if ($SQLfields) $SQLfields .= ',';
				if ($SQLvalues) $SQLvalues .= ',';

				$SQLfields .= $field[name];
				$SQLvalues .= "'".$field[value]."'";
			}

			$SQL='INSERT INTO ' . $site_object . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
			        $status=1;
			}

			return $status;


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
		
		
		
		
		function update_object_data($site_id,$site_object,$data,$where_condition)
		{
                        $this->site_db_connection($site_id);

			foreach($data as $field)
			{
				if ($SQL_SUB) $SQL_SUB .= ', ';
				$SQL_SUB .= "$field[name]='$field[value]'";
			}

			$SQL = 'UPDATE ' . $site_object . ' SET ' . $SQL_SUB . ' WHERE ' . stripslashes(stripslashes($where_condition));

			if ($this->site_db->query($SQL,__LINE__,__FILE__))
			{
			        $status=1;
			}

			return $status;
		}

		function delete_phpgw_data($site_id,$table,$where_condition)
		{

			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . stripslashes(stripslashes($where_condition));

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
			//die ($SQL);
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			       $status=$this->phpgw_db->get_last_insert_id($table,'x');
			}

			return $status;
		}


		function update_phpgw_data($table,$data,$where_condition)
		{

			foreach($data as $field)
			{
				if ($SQL_SUB) $SQL_SUB .= ', ';
				$SQL_SUB .= "$field[name]='$field[value]'";
			}

			$SQL = 'UPDATE ' . $table . ' SET ' . $SQL_SUB . ' WHERE ' . stripslashes(stripslashes($where_condition));
			//die($SQL);
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
			//die('');
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



		// end functions
	}

?>
