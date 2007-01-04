<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002 - 2006 Pim Snel <pim@lingewoud.nl>

   eGroupWare - http://www.egroupware.org

   This file is part of JiNN

   JiNN is free software; you can redistribute it and/or modify it under
   the terms of the GNU General Public License as published by the Free
   Software Foundation; either version 2 of the License.

   JiNN is distributed in the hope that it will be useful,but WITHOUT ANY
   WARRANTY; without even the implied warranty of MERCHANTABILITY or 
   FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
   for more details.

   You should have received a copy of the GNU General Public License 
   along with JiNN; if not, write to the Free Software Foundation, Inc.,
   59 Temple Place, Suite 330, Boston, MA 02111-1307  USA
   */

   /* $Id$ */

   include_once(PHPGW_INCLUDE_ROOT.'/jinn/inc/functions.inc.php');

   class sojinn
   {
	  var $phpgw_db;
	  var $site_db;
	  var $common;
	  var $config;

	  var $db_ftypes;

//	  var $debug_sql=true;

	  function sojinn()
	  {
		 $c = CreateObject('phpgwapi.config',$config_appname);
		 $c->read_repository();
		 if ($c->config_data)
		 {
			$this->config = $c->config_data;
		 }

		 $this->phpgw_db = $GLOBALS['phpgw']->db;

		 if(!$this->debug_sql)
		 {
			$this->phpgw_db->Debug	= False;
			$this->phpgw_db->Halt_On_Error='no';
		 }

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
	  }

	  function get_site_values($site_id)
	  {
		 $site_metadata=$this->phpgw_db->metadata('egw_jinn_sites');
		 
		 $this->phpgw_db->select('egw_jinn_sites','*',"site_id=$site_id",__LINE__,__FILE__);
		 $this->phpgw_db->next_record();

		 foreach($site_metadata as $fieldmeta)
		 {
			$site_values[$fieldmeta['name']]=$this->phpgw_db->f($fieldmeta['name']);
		 }

		 if($this->phpgw_db->f('host_profile')=='development')
		 {
			$pre='dev_';
		 }

		 $site_values['cur_site_db_name'] = $site_values[$pre.'site_db_name'];
		 $site_values['cur_site_db_host'] = $site_values[$pre.'site_db_host'];
		 $site_values['cur_site_db_user'] = $site_values[$pre.'site_db_user'];
		 $site_values['cur_site_db_password'] = $site_values[$pre.'site_db_password'];
		 $site_values['cur_site_db_type'] = $site_values[$pre.'site_db_type'];
		 $site_values['cur_upload_path'] =$site_values[$pre.'upload_path'];
		 $site_values['cur_upload_url'] =$site_values[$pre.'upload_url'];

		 $base_path="";

		 return $site_values;
	  }

	  /**
	  * site_db_connection 
	  *
	  * make connection to the site database and set this->site_db
	  * 
	  * @param int $site_id unique site_id to select the site from the sites-table
	  * @access private
	  * @return void
	  * @todo set database debugging level
	  * @todo rename to site init
	  */
	  function site_db_connection($site_id)
	  {
		 $data = $this->get_site_values($site_id);

		 if($data['site_db_type']=='egw')
		 {
			$this->site_db = $this->phpgw_db;
		 }
		 else
		 {
			$this->site_db = new egw_db();

			$this->site_db->Host		= $data['cur_site_db_host'];
			$this->site_db->Type		= $data['cur_site_db_type'];
			$this->site_db->Database	= $data['cur_site_db_name'];
			$this->site_db->User		= $data['cur_site_db_user'];
			$this->site_db->Password	= $data['cur_site_db_password'];
		 }

		 if(!$this->debug_sql)
		 {
			$this->site_db->Debug	= False;
			$this->site_db->Halt_On_Error='no';
		 }
	  }

	  /**
	  * site_close_db_connection 
	  * 
	  * @access private
	  * @return void
	  */
	  function site_close_db_connection()
	  {
		 $this->site_db->disconnect;
	  }

	  /**
	  * return table names for a site by site site_id
	  *
	  * @return array table names
	  * @param int $site_id JiNN Site id
	  * @param bool $easy_arr give an simple array back or not 
	  * @todo give back simple by default/remove all not simple code from all the codebase 
	  */
	  function site_tables_names($site_id,$easy_arr=false)
	  {
		 $this->site_db_connection($site_id);
		 $tables=$this->site_db->table_names();

		 if($easy_arr)
		 {
			foreach($tables as $table)
			{
			   $tables_arr[]=$table['table_name'];
			}
			return $tables_arr;
		 }
		 else
		 {
			return $tables;
		 }
	  }

	  // FIXME arg has to be site object_id in stead site_id and tablename
	  // FIXME whats the difference between those two????
	  function site_table_metadata($site_id,$table,$associative=false)
	  {
		 $this->site_db_connection($site_id);
		 if($associative)
		 {
			$meta=$this->site_db->metadata($table);
			foreach ($meta as $col)
			{
			   $meta_data[$col['name']]=$col;
			}
		 }
		 else
		 {
			$meta_data = $this->site_db->metadata($table);
		 }

		 $this->site_close_db_connection();

		 return $meta_data;
	  }

	  /**
	  * test_db_conn 
	  * 
	  * @param array $data 
	  * @access public
	  * @return boolean false for failed, true for success
	  */
	  function test_site_db_by_array($data)
	  {
		 if($data['host_profile']=='development')
		 {
			$data2['cur_site_db_host']    			= $data['dev_site_db_host'];
			$data2['cur_site_db_type']    			= $data['dev_site_db_type'];
			$data2['cur_site_db_name']    			= $data['dev_site_db_name'];
			$data2['cur_site_db_user']    			= $data['dev_site_db_user'];
			$data2['cur_site_db_password']			= $data['dev_site_db_password'];
		 }
		 else
		 {
			$data2['cur_site_db_host']    			= $data['site_db_host'];
			$data2['cur_site_db_type']    			= $data['site_db_type'];
			$data2['cur_site_db_name']    			= $data['site_db_name'];
			$data2['cur_site_db_user']    			= $data['site_db_user'];
			$data2['cur_site_db_password']			= $data['site_db_password'];
		 }

		 return $this->test_db($data2);
	  }

	  /**
	  * test_site_db_by_id 
	  * 
	  * @param int $site_id 
	  * @access public
	  * @return boolean true succes, false failed
	  */
	  function test_site_db_by_id($site_id)
	  {
		 $data = $this->get_site_values($site_id);
		 return $this->test_db($data);
	  }

	  /**
	  * test_db 
	  * 
	  * @param array $data 
	  * @access private
	  * @return boolean true succes, false failed
	  */
	  function test_db($data)
	  {
		 if($data['cur_site_db_type']=='egw')
		 {
			$this->test_db =  $this->phpgw_db;
		 }
		 else
		 {
			$this->test_db = & new egw_db();

			$this->test_db->Host		= $data['cur_site_db_host'];
			$this->test_db->Type		= $data['cur_site_db_type'];
			$this->test_db->Database	= $data['cur_site_db_name'];
			$this->test_db->User		= $data['cur_site_db_user'];
			$this->test_db->Password	= $data['cur_site_db_password'];
		 }

		 $this->test_db->Halt_On_Error='no';
		 @$this->test_db->table_names();
		 if($this->test_db->Link_ID->_connectionID)
		 {
			$this->test_db->disconnect;
			return true;
		 }
		 else
		 {
			return false;
		 }
	  }

	  /**
	  * test_site_object_table 
	  *
	  * test if table from site_objecte exists in site database
	  * 
	  * @param array $data standard JiNN Site Object properties array 
	  * @access public
	  * @return void
	  */
	  function test_site_object_table($data)
	  {
		 $this->site_db_connection($data['parent_site_id']);

		 $_table_arr=$this->site_db->table_names();

		 foreach($_table_arr as $tab)
		 {
			if ($tab['table_name']==$data['table_name'])
			{
			   return true;
			}
		 }
		 return false;
	  }

	  /**
	  * get_object_values_by_uniq: get objectvalues by object uniq id
	  *
	  * @param int $uniqid 
	  */
	  function get_object_values_by_uniq($uniqid)
	  {
		 return $this->get_object_values($uniqid);
	  }

	  /**
	  * get objectvalues by object id or uniqid
	  *
	  * @param int $object_id default behaviour to look by object_id
	  * @param string $uniqid optional
	  */
	  function get_object_values($object_id,$uniqid=false)
	  {
		 if($uniqid)
		 {
			$this->phpgw_db->select('egw_jinn_objects','*',"unique_id='$uniqid'",__LINE__,__FILE__);
		 }
		 else
		 {
			$this->phpgw_db->select('egw_jinn_objects','*',"object_id='$object_id'",__LINE__,__FILE__);
		 }

		 $this->phpgw_db->next_record();
		 
		 $object_metadata=$this->phpgw_db->metadata('egw_jinn_objects');
		 foreach($object_metadata as $fieldmeta)
		 {
			$object_values[$fieldmeta['name']]=$this->strip_magic_quotes_gpc($this->phpgw_db->f($fieldmeta['name']));
		 }

		 if($this->config["server_type"]=='dev') $pre='dev_';

		 $object_values['cur_upload_path'] =$object_values[$pre.'upload_path'];
		 $object_values['cur_upload_url'] =$object_values[$pre.'upload_url'];

		 return $object_values;
	  }

	  /**
	  * get the site id when by known object id
	  *
	  * @param int $object_id
	  * @return int site_id
	  */
	  function get_site_id_by_object_id($object_id)
	  {
		 $obj_arr=$this->get_object_values($object_id);
		 return $obj_arr['parent_site_id'];
	  }

	  /**
	  * get_field_values: get configured valued of one field
	  */
	  function get_field_values($object_id,$field_name)
	  {
		 $field_metadata=$this->phpgw_db->metadata('egw_jinn_obj_fields');
		 $this->phpgw_db->free();	

		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id' AND field_name='$field_name'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 $this->phpgw_db->next_record();

		 foreach($field_metadata as $fieldmeta)
		 {
			$field_values[$fieldmeta['name']]=$this->strip_magic_quotes_gpc($this->phpgw_db->f($fieldmeta['name']));
		 }
		 return $field_values;
	  }

	  /**
	  * mk_field_arr_for_obj: make configuration array for all field in an object 
	  */
	  function mk_field_conf_arr_for_obj($object_id)
	  {
		 $this->phpgw_db->free();	

		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 while ($this->phpgw_db->next_record())
		 {	
			$row=$this->phpgw_db->row();
			$field_conf_arr[$row['field_name']]=$row;
		 }

		 return $field_conf_arr;
	  }

	  /**
	  * mk_element_conf_arr_for_obj: non-automatic fields
	  * 
	  * @access public
	  * @return void
	  */
	  function mk_element_conf_arr_for_obj($object_id)
	  {
		 //		  	table_field,lay_out
		 $this->phpgw_db->free();	

		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id' AND (element_type='table_field' OR element_type='lay_out')";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 while ($this->phpgw_db->next_record())
		 {	
			$field_conf_arr[]=$this->phpgw_db->row();
//			$field_conf_arr[]=$row;
		 }

		 return $field_conf_arr;

		 }


		 /****************************************************************************\
		 * get all tablefield in array for table                                      *
		 \****************************************************************************/

	  function get_phpgw_fieldnames($table)
	  {
		 $meta=$this->phpgw_db->metadata($table);
		 foreach($meta as $col)
		 {
			$fieldnames[] = $col['name'];
		 }

		 return $fieldnames;
	  }

	  /**
	  @function num_rows_table
	  @ abstract get all tablefield in array for table                                      
	  */
	  function num_rows_table($site_id,$table,$where_condition=false)
	  {
		 $this->site_db_connection($site_id);
		 
		 if($where_condition and $where_condition != 'all')
		 {
			$WHERE='WHERE '.$where_condition;
		 }

		 $sql="SELECT COUNT(*) AS num FROM $table $WHERE";
		 $this->site_db->query($sql,__LINE__,__FILE__);
		 $this->site_db->next_record();

		 $num_rows=$this->site_db->f('num');

		 $this->site_close_db_connection();
		 return $num_rows;
	  }


	  function phpgw_table_metadata($table,$associative=false)
	  {
		 if($associative)
		 {
			$meta=$this->phpgw_db->metadata($table);
			foreach ($meta as $col)
			{
			   $ret_meta[$col['name']]=$col;
			}
			return $ret_meta;
		 }
		 else
		 {
			return $this->phpgw_db->metadata($table);
		 }
	  }

	  /**
	  * object_field_metadata: gets all database metadata of a particular field
	  *
	  * @param int $object needed to know the table name and database
	  * @param string fieldname the fieldname to query for metadata
	  * @return array with all metadata
	  */
	  function object_field_metadata($object_id, $fieldname)
	  {
		 $obj_arr=$this->get_object_values($object_id);
		 $this->site_db_connection($obj_arr['parent_site_id']);
		 $meta=$this->site_db->metadata($obj_arr['table_name']);
		 foreach ($meta as $col)
		 {
			$meta_data[$col['name']]=$col;
		 }

		 $this->site_close_db_connection();

		 return $meta_data[$fieldname];
	  }

	  /*!
	  @function user_is_site_admin
	  @abstract checks is user is admin a site
	  @param int $site_id site_id
	  @param $uid if empty current user is used
	  @returns true is user is admin else returns false
	  @depreciated
	  */
	  function user_is_site_admin($site_id,$uid=false)
	  {
		 if(!$uid)
		 {
			$uid=$GLOBALS['phpgw_info']['user']['account_id'];
		 }

		 $sites=$this->get_sites_for_user2($uid);

		 if(in_array($site_id,$sites))
		 {
			return true;
		 }
		 else
		 {
			return false;
		 }
	  }

	  /*
	  @function get_sites_to_admin
	  @abstract get all sites the is user is assign to (site administrator)
	  @note new, without group(this has to be done seperately) and without object section
	  (this also has to be done seperately)
	  @note an administrator has automaticly access to all objects and can change the accessrights for this site
	  */
	  function get_sites_to_admin($uid,$gid)
	  {
		 $sites=array();

		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			$SQL = "SELECT site_id FROM egw_jinn_sites ORDER BY site_name";
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

			$SQL = "SELECT site_id FROM egw_jinn_acl WHERE uid='$uid' $group_sql GROUP BY site_id";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
			   if ($this->phpgw_db->f('site_id')!=null)
			   {
				  $sites[]= $this->phpgw_db->f('site_id');
			   };

			}
		 }

		 //		 if (is_array($sites)) $sites=array_unique($sites);
		 $sites=array_unique($sites);

		 return $sites;
	  }



	  /*
	  @function get_sites_for_user2
	  @abstract get all sites the is user is assign to (site administrator)
	  @note new, without group(this has to be done seperately) and without object section
	  (this also has to be done seperately)
	  @note an administrator has automaticly access to all objects and can change the accessrights for this site
	  @fixme where the gid
	  @depreciated
	  */
	  function get_sites_for_user2($uid)
	  {
		 $sites=array();

		 $SQL = "SELECT site_id FROM egw_jinn_acl WHERE uid='$uid' $group_sql GROUP BY site_id";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 while ($this->phpgw_db->next_record())
		 {
			if ($this->phpgw_db->f('site_id')!=null)
			{
			   $sites[]= $this->phpgw_db->f('site_id');
			};

		 }

		 //		 if (is_array($sites)) $sites=array_unique($sites);
		 $sites=array_unique($sites);

		 return $sites;
	  }

	  /**
	  @function get_sites_for_user
	  @abstract get all sites_id's which user has access and return array      
	  @param $uid int
	  @param $gid int
	  */
	  function get_sites_for_user($uid,$gid)
	  {
		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			$SQL = "SELECT site_id FROM egw_jinn_sites ORDER BY site_name";
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

			$SQL = "SELECT site_id FROM egw_jinn_acl WHERE uid='$uid' $group_sql GROUP BY site_id";
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

			if (is_array($objects))
			{
			   foreach ($objects as $object)
			   {
				  if ($SUB_SQL)$SUB_SQL.=' OR ';
				  $SUB_SQL.="(object_id='$object')";
			   }

			   $SQL="SELECT parent_site_id FROM egw_jinn_objects WHERE $SUB_SQL GROUP BY parent_site_id";
			   $this->phpgw_db->query($SQL,__LINE__,__FILE__);

			   while ($this->phpgw_db->next_record())
			   {
				  $sites[]= $this->phpgw_db->f('parent_site_id');

			   }


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
			$SQL="SELECT object_id FROM egw_jinn_objects ";
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

			$SQL="SELECT site_object_id FROM egw_jinn_acl WHERE uid='$uid' $group_sql";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 }

		 while ($this->phpgw_db->next_record())
		 {
			if($this->phpgw_db->f('site_object_id'))
			{
			   $site_objects[]= $this->phpgw_db->f('site_object_id');
			}
		 }

		 return $site_objects;
	  }


	  /****************************************************************************\
	  * get sitename for site id                                                   *
	  \****************************************************************************/

	  function get_site_name($site_id)
	  {
		 $sql="SELECT site_name FROM egw_jinn_sites WHERE site_id='$site_id'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 $this->phpgw_db->next_record();
		 $site_name=$this->strip_magic_quotes_gpc($this->phpgw_db->f('site_name'));
		 return $site_name;

	  }

	  /**
	  * get_sites_by_name 
	  * 
	  * @param mixed $name 
	  * @access public
	  * @return void
	  */
	 function get_sites_by_name($name)
	 {
		$this->phpgw_db->query("SELECT * FROM egw_jinn_sites WHERE site_name='$name'",__LINE__,__FILE__);

		while($this->phpgw_db->next_record())
		{
		   $ids[]=$this->phpgw_db->f('site_id');
		}
		return $ids;
	 }
	
	  function get_objects_by_name($name,$parent_site_id)
	  {
		 $SQL="SELECT * FROM egw_jinn_objects WHERE name='$name' AND parent_site_id='$parent_site_id'";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 while($this->phpgw_db->next_record())
		 {
			$ids[]=$this->phpgw_db->f('object_id');
		 }
		 return $ids;

	  }	

	  function get_objects_by_table($tablename,$parent_site_id)
	  {
		 $SQL="SELECT * FROM egw_jinn_objects WHERE table_name='$tablename' AND parent_site_id='$parent_site_id'";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);
		 //die($SQL);

		 while($this->phpgw_db->next_record())
		 {
			$ids[]=$this->phpgw_db->f('object_id');
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

	  /*!
	  @function get_object_name
	  @abstract get objectname for object id
	  */
	  function get_object_name($object_id)
	  {
		 $this->phpgw_db->query("SELECT name FROM egw_jinn_objects
		 WHERE object_id='$object_id'",__LINE__,__FILE__);

		 $this->phpgw_db->next_record();
		 $name=$this->strip_magic_quotes_gpc($this->phpgw_db->f('name'));
		 return $name;
	  }

	  /*!
	  @abstract get all objects for a user
	  @fixme enable gid
	  */
	  function get_all_objects($uid,$gid)
	  {
		 return	$this->get_objects_for_user($uid);		
	  }

	  /*!
	  @abstract get all objects for a user
	  @fixme enable gid
	  @fixme merge with get_all_objects
	  */
	  function get_objects_for_user($uid)
	  {
		 $objects=array();
		 $SQL="SELECT site_object_id FROM egw_jinn_acl WHERE uid='$uid'";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 while ($this->phpgw_db->next_record())
		 {	
			$objects[]= $this->phpgw_db->f('site_object_id');
		 }

		 return $objects;
	  }

	  /**
	  @function backtick
	  @abstract returns a backtick if we're dealing with a mysql database. This function is used by every sql statement
	  @discission why does mysql has backticks and psql not
	  */
	  function backtick($db='')
	  {
		 if($db=='egw' | $db=='phpgw')
		 {
			if($this->phpgw_db->Type=='mysql') $backtick='`';

		 }
		 else
		 {
			if($this->site_db->Type=='mysql') $backtick='`';

		 }
		 return $backtick;
	  }

	  /**
	  @function get_objects
	  @astract gets all objects of current site_object depending on ACL or ADMIN-perms
	  @return array array objects-ids
	  @param $site_id int
	  @param $uid int
	  @param @gid int
	  @todo better naming
	  */
	  function get_objects($site_id,$uid,$gid)
	  {
		 $egw_bt=$this->backtick('egw');

		 if (is_array($gid>0) )
		 {
			foreach ( $gid as $group )
			{
			   $group_sql.=' OR ';
			   $group_sql .= "uid='$group'";
			}
		 }

		 /* check if user is eGW Administrator */
		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			$egwadmin=true;
		 }

		 /* check if user or group administers this site */
		 $SQL="SELECT site_id FROM egw_jinn_acl WHERE uid='$uid' $group_sql";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 while ($this->phpgw_db->next_record())
		 {
			if ($site_id == $this->phpgw_db->f('site_id'))
			{
			   $egwadmin=true;
			}
		 }

		 /* yes it's an admin so we can get all objects for this site */
		 if ($egwadmin)
		 {
			$SQL="SELECT object_id FROM egw_jinn_objects WHERE {$egw_bt}parent_site_id{$egw_bt} = '$site_id' AND ({$egw_bt}hide_from_menu{$egw_bt} != '1' OR {$egw_bt}hide_from_menu{$egw_bt} IS NULL) ORDER BY name";

			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
			   $objects[]= $this->phpgw_db->f('object_id');
			}
		 }
		 // he's no admin so get all the objects which are assigned to the user
		 else
		 {
			$SQL="SELECT object_id FROM egw_jinn_objects WHERE parent_site_id = '$site_id'  AND ({$egw_bt}hide_from_menu{$egw_bt} != '1' OR {$egw_bt}hide_from_menu{$egw_bt} IS NULL) ORDER BY name";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
			   if ($object_sql) $object_sql.=' OR ';
			   $object_sql .= "site_object_id='".$this->phpgw_db->f('object_id')."'";
			}

			if($object_sql)
			{
			   $SQL="SELECT site_object_id FROM egw_jinn_acl WHERE ($object_sql) AND (uid='$uid' $group_sql)";
			   $this->phpgw_db->query($SQL,__LINE__,__FILE__);

			   while ($this->phpgw_db->next_record())
			   {
				  $objects[]= $this->phpgw_db->f('site_object_id');
			   }
			}

		 }

		 if (is_array($objects))
		 {
			$objects = array_unique($objects);
		 }
		 else
		 {
			$objects = array();
		 }

		 return $objects;
	  }

	  /**
	  * reorder_obj_fields_table: very smart function to reorder the obj_fields and keeping a consistant table order
	   * 
	   * @param mixed $obj_id 
	   * @param mixed $movefield_name 
	   * @param mixed $direc 
	   * @access public
	   * @return void
	   */
	  function reorder_obj_fields_table($obj_id,$movefield_name,$direc)
	  {
		 // check if order is consistant
		 //quick check on doubles
		 $sql="SELECT field_id,COUNT(field_id) AS numrecs FROM egw_jinn_obj_fields WHERE field_parent_object='$obj_id' GROUP BY form_listing_order HAVING COUNT(field_id)>1";
		 $this->phpgw_db->free();	
		 $this->phpgw_db->query($sql, __LINE__, __FILE__); 
		
		 //we must reorder the records
		 if($this->phpgw_db->num_rows()>0)
		 {
			//get max order
			$sql2="SELECT max(form_listing_order) as maxorder FROM egw_jinn_obj_fields WHERE field_parent_object='$obj_id'";
			$this->phpgw_db->free();	
			$this->phpgw_db->query("$sql2",__LINE__,__FILE__);
			$this->phpgw_db->next_record();
			$maxorder=$this->phpgw_db->f('maxorder');

			//select all doubles
			$sql3="SELECT field_name FROM egw_jinn_obj_fields WHERE field_parent_object='$obj_id' AND form_listing_order IN (SELECT form_listing_order FROM egw_jinn_obj_fields WHERE field_parent_object='$obj_id' GROUP BY form_listing_order HAVING COUNT(field_id)>1 ORDER BY form_listing_order ASC)";
			$this->phpgw_db->free();	
			$this->phpgw_db->query("$sql3",__LINE__,__FILE__);
			while ($this->phpgw_db->next_record())
			{
			   $field_names_arr[]=$this->phpgw_db->f('field_name');
			}
			
			//and walk doubles and give them new order starting with the max+1
			foreach($field_names_arr as $field_name)
			{
			  $maxorder++;
			  $sql4="UPDATE egw_jinn_obj_fields SET form_listing_order=$maxorder WHERE field_parent_object='$obj_id' AND field_name='$field_name'";
			  $this->phpgw_db->free();	
			  //FIXME REPLACE WITH EGWDB
			  $this->phpgw_db->query("$sql4",__LINE__,__FILE__);
		   }
		 }
		 
		 //select order from movefield
		 $sql5="SELECT form_listing_order FROM egw_jinn_obj_fields WHERE field_parent_object='$obj_id' AND field_name='$movefield_name'";
		 $this->phpgw_db->free();	
		 $this->phpgw_db->query("$sql5",__LINE__,__FILE__);
		 $this->phpgw_db->next_record();
		 $myorder=$this->phpgw_db->f('form_listing_order'); // get my current order
		 
		 $nbr_search['operator'] = ($direc == "down")? '>' : '<'; // decides to search for lower or higher neighbour 
		 $nbr_search['orderdir'] = ($direc == "down")? 'ASC' : 'DESC'; // decides to search for lower or higher neighbour 
		 
		 //select neighbour record and set it form_listing_order to myorder
		 $sql6="SELECT field_name,form_listing_order FROM egw_jinn_obj_fields 
		 WHERE field_parent_object='$obj_id' AND form_listing_order {$nbr_search['operator']} $myorder ORDER BY form_listing_order {$nbr_search['orderdir']} LIMIT 1";
		 $this->phpgw_db->free();	
		 $this->phpgw_db->query("$sql6",__LINE__,__FILE__);
		 $this->phpgw_db->next_record();
		 $swaprecord=$this->phpgw_db->f('field_name'); // get my current order
		 $neworder=$this->phpgw_db->f('form_listing_order'); // get my current order

		 $sql7="UPDATE egw_jinn_obj_fields SET form_listing_order = $myorder 
		 WHERE field_parent_object='$obj_id' AND field_name = '$swaprecord'";
		 $this->phpgw_db->free();	
		 //FIXME REPLACE WITH EGWDB
		 $this->phpgw_db->query("$sql7",__LINE__,__FILE__);
	   
		 //increase or decrease our form_listing_order
		 $sql8="UPDATE egw_jinn_obj_fields SET form_listing_order = $neworder  WHERE field_parent_object='$obj_id' AND field_name='$movefield_name'"; 
		 $this->phpgw_db->free();	
		 //FIXME REPLACE WITH EGWDB
		 $this->phpgw_db->query("$sql8",__LINE__,__FILE__);
			 
		 // pooh....
	  }

	  
	  function get_phpgw_record_values($table,$where_key,$where_value,$offset,$numrows,$value_reference,$order_by=false)
	  {
		 if ($where_key && $where_value)
		 {
			$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
			$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
			$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
		 }

		 $fieldproperties = $this->phpgw_table_metadata($table);
		 $SQL="SELECT * FROM  $table $WHERE $order_by";
		 if (!$numrows) $numrows=-1;

		 //$this->phpgw_db->limit_query($SQL, $offset,__LINE__,__FILE__,$numrows); // returns a limited result from start to limit
		 $this->phpgw_db->query($SQL, __LINE__, __FILE__, $offset, $numrows,false); 

		 while ($this->phpgw_db->next_record())
		 {
			unset($row);
			foreach($fieldproperties as $field)
			{
			   if ($value_reference=='name')
			   {
				  $row[$field['name']] = $this->strip_magic_quotes_gpc($this->phpgw_db->f($field['name']));
			   }
			   else
			   {
				  $row[] = $this->strip_magic_quotes_gpc($this->phpgw_db->f($field['name']));
			   }
			}
			$rows[]=$row;
		 }
		 return $rows;
	  }



	  /**
	  * get_m2m_record_values: get linked many to many records
	  * 
	  * @param mixed $site_id 
	  * @param mixed $object_id 
	  * @param mixed $m2m_relation 
	  * @param mixed $all_or_stored 
	  * @access public
	  * @return void
	  */
	  function get_m2m_record_values($site_id,$object_id,$m2m_relation,$all_or_stored)
	  {
		 $this->site_db_connection($site_id);
		 $_displayfields = unserialize($m2m_relation['foreign_showfields']);
		 
		 foreach($_displayfields as $displfield)
		 {
			if($displayfields)	$displayfields.=',';
			$displayfields.=$displfield;
		 }
		 if(count($_displayfields)>1)
		 {
		 $displayfields = "CONCAT_WS(' ',".$displayfields.")";
		 }
		 if ($all_or_stored=="all")
		 {
			$SQL="SELECT {$m2m_relation[foreign_table]}.{$m2m_relation[foreign_key]},$displayfields AS display 
			FROM {$m2m_relation[foreign_table]} 
			ORDER BY {$m2m_relation[foreign_table]}.{$_displayfields[0]} ";
		 }
		 elseif($object_id)
		 {
			$SQL="SELECT {$m2m_relation[foreign_table]}.{$m2m_relation[foreign_key]},$displayfields AS display 
			FROM {$m2m_relation[foreign_table]} 
			INNER JOIN {$m2m_relation[connect_table]}
			ON {$m2m_relation[connect_table]}.{$m2m_relation[connect_key_foreign]}={$m2m_relation[foreign_table]}.{$m2m_relation[foreign_key]} 
			WHERE {$m2m_relation[connect_table]}.{$m2m_relation[connect_key_local]}='$object_id' 
			ORDER BY {$m2m_relation[foreign_table]}.{$_displayfields[0]}";
		 }
		 else
		 {
			$SQL=false;
		 }

		 if($SQL)
		 {
			if(@$this->site_db->query($SQL,__LINE__,__FILE__))
			{
			   while ($this->site_db->next_record())
			   {
				  $records[]=array(
					 'name'=>$this->site_db->f('display'),
					 'value'=>$this->site_db->f($m2m_relation[foreign_key])
				  );
			   }
			}
			else
			{
			   $error; // FIXME print/mail error 
			}
		 }
		 return $records;
	  }


	  function get_1wX_record_values($site_id,$object_id,$m2m_relation,$all_or_stored)
	  {
		 $this->site_db_connection($site_id);
		 $displayfields = $m2m_relation[display_field];
		 if($m2m_relation[display_field_2]!='') $displayfields .= ', '.$m2m_relation[display_field_2];
		 if($m2m_relation[display_field_3]!='') $displayfields .= ', '.$m2m_relation[display_field_3];
		 if($m2m_relation[display_field_2]!='' || $m2m_relation[display_field_3]!='' ) $displayfields = "CONCAT_WS(' ', ".$displayfields.")";


		 if ($all_or_stored=="all")
		 {
			$SQL="SELECT $m2m_relation[foreign_key],$displayfields AS display FROM $m2m_relation[display_table] ORDER BY $m2m_relation[display_field] ";
		 }
		 elseif($object_id)
		 {
			$SQL="SELECT $m2m_relation[foreign_key],$displayfields AS display FROM $m2m_relation[display_table] INNER JOIN $m2m_relation[via_table]
			ON $m2m_relation[via_foreign_key]=$m2m_relation[foreign_key] WHERE $m2m_relation[via_primary_key]='$object_id' ORDER BY $m2m_relation[display_field]";
		 }
		 else
		 {
			$SQL=false;
		 }

		 $tmp=explode('.',$m2m_relation[foreign_key]);
		 $foreign_key=$tmp[1];

		 if($SQL)
		 {
			$this->site_db->query($SQL, $offset,__LINE__,__FILE__); // returns a result

			while ($this->site_db->next_record())
			{

			   $records[]=array(
				  'name'=>$this->site_db->f('display'),
				  'value'=>$this->site_db->f($foreign_key)
			   );
			}
		 }
		 return $records;
	  }

	  function get_O2M_subselect($relation_info)
	  {
		 $related_arr = explode(".", $relation_info[related_with]);
		 $related_table = $related_arr[0];

		 $subselect = '';
		 $subselect .= "(SELECT CONCAT_WS(' ', $relation_info[display_field]";
		 if($relation_info[display_field_2] != '') $subselect .= ", $relation_info[display_field_2]";
		 if($relation_info[display_field_3] != '') $subselect .= ", $relation_info[display_field_3]";
		 $subselect .= ") FROM $related_table";
		 $subselect .= " WHERE $relation_info[related_with] = $relation_info[org_field]";
		 $subselect .= ") AS $relation_info[org_field]";
		 return $subselect;
	  }

	  function get_M2M_subselect($relation_info, $site_id, $table)
	  {
		 //first get the identity column
		 $fields = $this->site_table_metadata($site_id,$table);
		 foreach ( $fields as $fprops )
		 {
			if (eregi("primary_key", $fprops[flags]) || eregi("auto_increment", $fprops[flags]) || eregi("nextval",$fprops['default']))
			{
			   $id_field = $table.'.'.$fprops[name];
			   break;
			}
		 }

		 if($id_field)
		 {
			$subselect = '';
			$subselect .= "(SELECT GROUP_CONCAT(CONCAT_WS(' ', $relation_info[display_field]";
			if($relation_info[display_field_2] != '') $subselect .= ", $relation_info[display_field_2]";
			if($relation_info[display_field_3] != '') $subselect .= ", $relation_info[display_field_3]";
			$subselect .= ") SEPARATOR ';')";
			$subselect .= " FROM $relation_info[via_table], $relation_info[display_table]";
			$subselect .= " WHERE $relation_info[via_primary_key] = $id_field";
			$subselect .= " AND $relation_info[via_foreign_key] = $relation_info[foreign_key]";
			$subselect .= ") AS $relation_info[name]";
			return $subselect;
		 }
		 else
		 {
			return '';
		 }
	  }


	  function get_fast_record_values()
	  {
		 
		 }
	  

	  function get_data($site_id, $table, $columns_arr, $filter_where, $limit = false,$key_prefix='')
	  {
		 //new function for fast and generic retrieval of object data, including 1-1, 1-many and many-many relations
		 //partly implemented in bouser, partly in sojinn

		 //select
		 if($columns_arr == 'all' || $columns_arr == '*')
		 {
			$select = 'SELECT *';
		 }
		 else
		 {
			$select = 'SELECT ';
			foreach($columns_arr as $col)
			{
			   if($select!='SELECT ') $select .= ', ';
			   if(is_array($col))
			   {
				  switch($col[type])
				  {
					 case 1: //one to many
						$select .= $this->get_O2M_subselect($col);
						break;
					 case 2: //many to many
						$select .= $this->get_M2M_subselect($col, $site_id, $table);
						break;
					 default:
						break;
				  }
			   }
			   else
			   {
				  $select .= "$col";
			   }
			}
		 }

		 //from
		 $from = "FROM $table";

		 //where
		 if($filter_where=='all')
		 {
			$where = '';
		 }
		 elseif(is_array($filter_where))
		 {
			$where = 'WHERE ';
			foreach($filter_where as $filter)
			{
			   if($where!='WHERE ') $where .= 'AND ';
			   $where .= "$filter";
			}
		 }
		 elseif(strlen($filter_where) > 0)
		 {
			$where = 'WHERE '.$filter_where;
		 }

		 //order
		 $order = "";
		 if(!$limit)
		 {
			$limit="";
		 }
		 $sql = "$select $from $where $order $limit";
		 if($sql)
		 {
			$this->site_db_connection($site_id);
			$this->site_db->query($sql,__LINE__,__FILE__); // returns a result
			$data = array();
//			die($sql);
			while ($this->site_db->next_record())
			{
			   $row = $this->site_db->row();
			   $data[] = $row;
			}
		 }
		 return $data;
	  }

	  /**
	  * get_record_values: get record(s) values from site tables 
	  *
	  * @todo add documentation
	  */
	  function get_record_values($site_id,$table,$where_key,$where_value,$offset,$numrows,$value_reference,$order_by='',$field_list='*',$where_condition='')
	  {
		 $this->site_db_connection($site_id);
		 $s_bt=$this->backtick();

		 if ($where_key && $where_value)
		 {
			$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
			$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
			$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
		 }

		 if($where_condition)
		 {
			$where_condition = $where_condition;
			if($WHERE)
			{
			   $WHERE.=' AND ('.$where_condition.')';
			}
			else
			{
			   $WHERE=' WHERE '.$where_condition;
			}
		 }
		 if ($order_by)
		 {

			if(substr($order_by,-2)=='SC')
			{
			   $order_by_new=trim(substr($order_by,0,(strlen($order_by)-4)));
			   $order_direction=trim(substr($order_by,-4));
			}
			else
			{
			   $order_by_new=$order_by;
			}

			$ORDER_BY = ' ORDER BY '.$s_bt.$table.$s_bt.'.'.$s_bt.$order_by_new.$s_bt.' '.$order_direction;
		 }

		 $fieldproperties = $this->site_table_metadata($site_id,$table);
		 $field_list_arr=(explode(',',$field_list));

		 $SQL="SELECT $field_list FROM $table $WHERE $ORDER_BY";
		 if (!$numrows) $numrows=-1;
		 if($this->site_db->query($SQL, __LINE__, __FILE__, $offset, $numrows,false)); 
		 {
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
		 }
		 return $rows;
	  }

	  function delete_object_data($site_id,$table,$where_key,$where_value,$where_string='')
	  {
		 $this->site_db_connection($site_id);

		 // make pgsql hack because Limit is not allowed in DELETE statements
		 if(!$this->site_db->Type=='pgsql')
		 {
			$limit_statement=' LIMIT 1';	
		 }

		 if($where_string)
		 {
			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . $where_string . $limit_statement;
		 }
		 else
		 {
			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key) ."='".$this->strip_magic_quotes_gpc($where_value)."'";
		 }

		 if (!$this->site_db->query($SQL,__LINE__,__FILE__))
		 {
			$status[error]=true;
		 }

		 $status[sql]=$SQL;

		 return $status;

	  }

	  function check_auto_incr($site_id,$table)
	  {
		 $meta=$this->site_table_metadata($site_id,$table);

		 foreach($meta as $field)
		 {
			if($field['auto_increment'] =='1') 
			{
			   return $field['name'];
			}
		 }
	  }

	  function copy_record($site_id,$table,$where_string,$autokey)
	  {
		 $this->site_db_connection($site_id);

		 $s_bt=$this->backtick();

		 $values_record = $this->get_record_values($site_id,$table,'','','','','name','','*',$where_string);

		 while(list($key, $val) = each($values_record[0])) 
		 {
			if($key==$autokey) continue;

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $s_bt.$key.$s_bt;
			$SQLvalues .= "'".addslashes($this->strip_magic_quotes_gpc($val))."'"; // FIX THIS magic kut quotes

		 }

		 $SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

		 //FIXME REPLACE WITH EGWDB
		 if ($this->site_db->query($SQL,__LINE__,__FILE__))
		 {
			$value[status]=1;
			$value[ret_code]=0;
			$value[id]=$this->site_db->get_last_insert_id($table, $autokey);

			if($autokey) $where_string= $autokey.'=\''.$value[id].'\'';

			$value[where_string]=$where_string;
		 }

		 $value[sql]=$SQL;
		 return $value;
	  }


	  /**
	  @function insert_object_data
	  @abstract insert data info site database
	  @fixme better naming
	  @fixme code cleanup
	  */
	  function insert_object_data($site_id,$table,$data,$set_auto_val=false)
	  {
		 $this->site_db_connection($site_id);

		 $s_bt=$this->backtick();
		 $metadata=$this->site_table_metadata($site_id,$table,true);

		 foreach($data as $field)
		 {
			$jinn_field_type=$this->db_ftypes->complete_resolve($metadata[$field[name]]);

			//FIXME create a fortick standard function

			/* use '' in SQL yes/no */	
			if($jinn_field_type=='int' || $jinn_field_type=='auto')
			{
			   $fortick='';
			}
			/* put here all sql-functions
			NOW() for time, date, timestamp and datestamp
			PASSWORD for string
			etc....
			*/
			elseif( ($jinn_field_type=='timestamp' || $jinn_field_type=='date') && $field[value]=='Now()')
			{
			   $fortick='';//FIXME this is the same as doing nothing!!!
			}
			else
			{
			   $fortick='\'';
			}

			if($metadata[$field['name']]['auto_increment'] || eregi('nextval',$metadata[$field['name']]['default']) || eregi("auto_increment", $metadata[$field['name']]['flags'])) 
			{
			   $autokey=$field['name'];
			   //$value[idfield]=$field['name']; //FIXME: can this line be a mistake, and be deleted?
			   $status[idfield]=$field['name'];

			   // normally auto field are not explicitly given a value, but in some cases we want this
			   if(!$set_auto_val)
			   {
				  continue;
			   }
			}

			if($field[value]=='' && eregi('int',$metadata[$field['name']]['type']) )
			{
			   continue;
			}

			if(eregi('int',$metadata[$field['name']]['type']) && !is_numeric($field[value]))
			{
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if (strval($SQLvalues)=='0' || $SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $s_bt.$field[name].$s_bt;
			$SQLvalues .= "$fortick".$this->strip_magic_quotes_gpc($field[value])."$fortick"; // FIX THIS magic kut quotes


			/* check for primaries and create array */
			/*			if (eregi("auto_increment", $metadata[$field[name]][flags]))
			{
			   $autokey=$field[name];
			}*/
			if (!$autokey && eregi("primary_key", $metadata[$field[name]][flags]) && $metadata[$field[name]][type]!='blob') // FIXME howto select long blobs
			{						
			   $pkey_arr[]=$field[name];
			}
			elseif(!$autokey && $metadata[$field[name]][type]!='blob') // FIXME howto select long blobs
			{
			   $akey_arr[]=$field[name];
			}

			$aval[$field[name]]=substr($field[value],0,$metadata[$field[name]][len]);

		 }

		 if(!is_array($pkey_arr))
		 {
			$pkey_arr=$akey_arr;
			unset($akey_arr);
		 }

		 $new_data=$this->oldData2newData($data);
		 $table_def=$this->table2definition( $table ,&$this->site_db);

		 if ($this->site_db->insert($table,$new_data,'',__LINE__,__FILE__,False,false,$table_def))
		 {
			$status['status']=1; // for backwards compatibility
			$status['ret_code']=0;

			$status['id']=$this->site_db->get_last_insert_id($table, $autokey);

			if($autokey) 
			{
			   $where_string= $autokey.'=\''.$status['id'].'\'';
			   $status['autokey']=$autokey;
			   $status['autoval']=$status['id'];
			}
			elseif(is_array($pkey_arr))
			{
			   foreach($pkey_arr as $pkey)
			   {
				  if($where_string) $where_string.=' AND ';
				  $where_string.= '('.$pkey.' = \''. $aval[$pkey].'\')';
			   }
			}

			$status['where_string']=$where_string;
		 }
		 else
		 {
			$status['error']=true;
		 }

		 $status['sql']=$SQL;
		 return $status;


	  }

	  /*!
	  @function update_object_data  
	  @abstract update object record data depreciated, use update_object_record
	  @param $site_id id of site for resolving db connection data
	  @param $site_object
	  @param $data array of data
	  @param where_key which field to use as id-key (depreciated?)
	  @param where_value which value to use as value for id-key (depreciated?)
	  @param where_string complete string which comes after "... WHERE " in the sql-string
	  */
	  function update_object_data($site_id,$site_object,$data,$where_key,$where_value,$curr_where_string='')
	  {
		 return  $this->update_object_record($site_id,$site_object,$data,$where_key,$where_value,$curr_where_string);
	  }

	  /**
	  @function generate_unique_id  
	  @abstract returns a 13 character unique id used as an object identifier for saving preferences
	  */
	  function generate_unique_id()
	  {
		 return uniqid('');
	  }

	  /**
	  @function set_unique_id  
	  @abstract set the unique_id field of the specified Object to a unique value
	  */
	  function set_unique_id($object_id)
	  {
		 $uid = $this->generate_unique_id();

		 $SQL = "UPDATE egw_jinn_objects SET unique_id = '$uid' WHERE object_id = '$object_id'";
		 //FIXME REPLACE WITH EGWDB
		 $result = $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 if($result)
		 {
			$status[ret_code]=0;
			$status[status]=1;
		 }
		 else
		 {
			$status[ret_code]=1;
		 }
		 $status[uid]=$uid;
		 $status[sql]=$SQL;
		 return $status;
	  }


	  /**
	  * site_table_exist_or_create: check if table exist and had the correct fields, else create it  
	  * 
	  * @todo complete doc
	  * @todo complete schema check
	  * @param mixed $site_id 
	  * @param mixed $table_name 
	  * @param mixed $fields 
	  * @access public
	  * @return void
	  */
	  function site_table_exist_or_create($site_id,$table_name,$fields) 
	  {
		 $this->site_db_connection($site_id);
		 $site_tables=$this->site_tables_names($site_id,true);

		 // table exist so now check the schema
		 if(in_array($table_name,$site_tables))
		 {
			$metadata=$this->site_table_metadata($site_id,$table_name);
		 }
		 // create table
		 else
		 {
			foreach($fields as $field)
			{
			   if($fields_sql) $fields_sql.=',';
			   $fields_sql.="{$field['name']} INT NOT NULL";
			}

			//FIXME REPLACE WITH EGWDB
			$sql="CREATE TABLE $table_name ( $fields_sql ) TYPE = MYISAM ;";

			$this->site_db->query($sql,__LINE__,__FILE__);
		 }

	  }

	  /*!
	  @function update_object_record  
	  @abstract update record data
	  @param $site_id id of site for resolving db connection data
	  @param $table
	  @param $data array of data
	  @param where_key which field to use as id-key (depreciated?)
	  @param where_value which value to use as value for id-key (depreciated?)
	  @param where_string complete string which comes after "... WHERE " in the sql-string
	  @fixme for all fieldtype a default_value mechanisme must be implemented, atm int is finished
	  @fixme better naming
	  @fixme implement NULL for eg ints
	  @fixme code cleanup
	  */
	  function update_object_record($site_id,$table,$data,$where_key,$where_value,$curr_where_string='',$try_insert=false)
	  {
		 $this->site_db_connection($site_id);

		 $metadata=$this->site_table_metadata($site_id,$table,true);

		 foreach($data as $field)
		 {
			// check for primaries and create array 
			if (eregi("auto_increment", $metadata[$field['name']]['flags']))
			{
			   $autowhere=$field['name'].'=\''.$field['value'].'\'';
			   $autokey=$field['name'];
			   $autoval=$field['value'];
			}
			elseif($this->db_ftypes->complete_resolve($metadata[$field['name']])=='int')
			{
			   //what to do with empty value
			   if(!trim($field['value']))
			   {
				  // if there is a default value set it to this value 
				  if($this->db_ftypes->has_default($metadata[$field['name']]))
				  {
					 $field['value']=$this->db_ftypes->get_default($metadata[$field['name']]);
				  }
				  elseif($this->db_ftypes->nullable($metadata[$field['name']]))
				  {
					 $field['value']='null';
				  }
				  else
				  {
					 continue;
				  }
			   }

			   if($field['value']!='null' && !is_numeric($field['value']))
			   {
				  //fixme add error
				  continue;
			   }
			}
			elseif (!$autowhere && eregi("primary_key", $metadata[$field['name']]['flags']) && $metadata[$field['name']]['type']!='blob') // FIXME howto select long blobs
			{						
			   $pkey_arr[]=$field['name'];
			}
			elseif(!$autowhere && $metadata[$field['name']]['type']!='blob') // FIXME howto select long blobs
			{
			   $akey_arr[]=$field['name'];
			}

			$aval[$field['name']]=substr($field['value'],0,$metadata[$field['name']]['len']);
		 }

		 if(!is_array($pkey_arr))
		 {
			$pkey_arr=$akey_arr;
			unset($akey_arr);
		 }

		 if($curr_where_string)
		 {
			$WHERE =  $curr_where_string;
		 }
		 else
		 {
			$WHERE = $this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_key))."='".$this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_value))."'";
		 }

		 //todo test this
		 if($try_insert)
		 {
			$selsql="SELECT * FROM $table WHERE $WHERE";

			$this->site_db->query($selsql,__LINE__,__FILE__);
			if($this->site_db->num_rows()<1)
			{
			   return $this->insert_object_data($site_id,$table,$data,true);
			}
		 }

		 $new_data=$this->oldData2newData($data);
		 $table_def=$this->table2definition( $table , $this->site_db);
 
		 if($this->site_db->update($table,$new_data,$WHERE,__LINE__,__FILE__,False,false,$table_def))
		 {
			$value['ret_code']=0;
			$value['status']=1;

			if($autowhere) 
			{
			   $where_string= $autowhere;
			   $value['autokey']=$autokey;
			   $value['autoval']=$autoval;
			}
			elseif(is_array($pkey_arr))
			{
			   foreach($pkey_arr as $pkey)
			   {
				  if($where_string) $where_string.=' AND ';
				  $where_string.= '('.$pkey.' = \''. $aval[$pkey].'\')';
			   }
			}

			$value['where_string']=$where_string;
		 }
		 else
		 {
			$value['error']=true;
		 }

		 $value['sql']=$SQL;
		 return $value;
	  }


	  /**
	  * update_object_many_data: 
	  */
	  function update_object_many_data($site_id, $data)
	  {
		 $this->site_db_connection($site_id);
		 $status=True;
		 $i=1;
		 while (isset($data['M2MRXX'.$i]))
		 {
			$rel_info=unserialize(base64_decode($data['M2MRXX'.$i]));

			/* need to know
			- connection table name
			- connection local key
			- connection foreig key
			Array
			(
			   [id] => M2M433bc5e884530
			   [type] => 2
			   [local_key] => id
			   [foreign_table] => blokken
			   [foreign_key] => id
			   [connect_key_local] => JM2MCONN_pages_blokken.id_pages
			   [connect_key_foreign] => JM2MCONN_pages_blokken.id_blokken
			   [foreign_showfields] => a:3:{i:0;s:12:"interne_naam";i:1;s:9:"lang_code";i:2;s:2:"id";}
			   [connect_table] => JM2MCONN_pages_blokken
			)
			*/


			//			list($table,) = explode(".",$via_primary_key);

			$SQL="DELETE FROM {$rel_info[connect_table]} WHERE {$rel_info[connect_key_local]}='$data[FLDXXXid]'";
			if (!$this->site_db->query($SQL,__LINE__,__FILE__))
			{
			   $status=-1;
			}
			if(trim($data['M2MOXX'.$i]))
			{
			   $related_data=explode(",",$data['M2MOXX'.$i]);
			   foreach($related_data as $option)
			   {
				  $SQL="INSERT INTO {$rel_info[connect_table]} ({$rel_info[connect_key_local]},{$rel_info[connect_key_foreign]}) VALUES ('$data[FLDXXXid]', '$option')";
				  //FIXME REPLACE WITH EGWDB
				  if (!$this->site_db->query($SQL,__LINE__,__FILE__))
				  {
					 $status=False;
				  }
			   }
			}
			$i++;
		 }
		 return $status;
	  }

	  /**
	  * update_object_many_data: depreciated
	  */
	  function update_object_many_dataOLD($site_id, $data)
	  {
		 $this->site_db_connection($site_id);
		 $status=True;
		 $i=1;
		 while (isset($data['M2MRXX'.$i]))
		 {
			list($via_primary_key,$via_foreign_key) = explode("|",$data['M2MRXX'.$i]);
			list($table,) = explode(".",$via_primary_key);
			$SQL="DELETE FROM $table WHERE $via_primary_key='$data[FLDXXXid]'";
			if (!$this->site_db->query($SQL,__LINE__,__FILE__))
			{
			   $status=-1;
			}
			$related_data=explode(",",$data['M2MOXX'.$i]);
			foreach($related_data as $option)
			{
			   $SQL="INSERT INTO $table ($via_primary_key,$via_foreign_key) VALUES ('$data[FLDXXXid]', '$option')";
			   //FIXME REPLACE WITH EGWDB
			   if (!$this->site_db->query($SQL,__LINE__,__FILE__))
			   {
				  $status=False;
			   }
			}
			$i++;
		 }
		 return $status;
	  }


	  function delete_obj_field_rec($obj_id,$field_name)
	  {
		 $SQL = "DELETE FROM egw_jinn_obj_fields WHERE  field_parent_object='$obj_id' AND field_name='$field_name'";
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			return $status = 1;
		 }
	  }

	  /**
	  * delete_phpgw_data 
	  * 
	  * run these sql statements (in this specific order!) to clean up orphans in the jinn tables that may have been left by old versions of delete_phpgw_data:
	  *
	  * DELETE
	  * FROM	egw_jinn_objects
	  * WHERE	parent_site_id NOT IN ( SELECT	site_id FROM egw_jinn_sites )
	  *
	  * DELETE
	  * FROM	egw_jinn_obj_fields
	  * WHERE	field_parent_object NOT IN  ( SELECT object_id FROM egw_jinn_objects )
	  *
	  * @fixme ? cleanup orphan garbage
	  * @fixme ? create dedicated function
	  * @fixme ? add delete routine for object fields
	  * @param mixed $table 
	  * @param mixed $where_key 
	  * @param mixed $where_value 
	  * @access public
	  * @return void
	  */
	  function delete_phpgw_data($table, $where_key, $where_value)
	  {
		 if($table == 'egw_jinn_sites')
		 {	
			$SQL = 'SELECT * FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."=".$this->strip_magic_quotes_gpc($where_value);
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			   $cascade = array();
			   while ($this->phpgw_db->next_record())
			   {
				  $cascade[] = $this->phpgw_db->Record['site_id'];
			   }
			   foreach($cascade as $child)
			   {
				  $this->delete_phpgw_data('egw_jinn_objects', 'parent_site_id' , $child);

				  if($this->delete_phpgw_data('egw_jinn_objects', 'parent_site_id' , $child))
				  {
					 $status = 1;
				  }
				  else
				  {
					 $status = 0;
				  }
			   }
			}
		 }
		 elseif($table == 'egw_jinn_objects')
		 {
			$SQL = 'SELECT * FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			   if($this->phpgw_db->num_rows()>0) 
			   {
				  $cascade = array();
				  while ($this->phpgw_db->next_record())
				  {
					 $cascade[] = $this->phpgw_db->Record['object_id'];
				  }
				  foreach($cascade as $child)
				  {
					 if($this->delete_phpgw_data('egw_jinn_obj_fields', 'field_parent_object' , $child))
					 {
						$status = 1;
					 }
					 else
					 {
						$status = 0;
					 }
				  }

			   }
			   else $status = 1;
			}
		 }
		 else
		 {
			$status = 1;
		 }
		 
		 if($status == 1)
		 {
			$SQL = 'DELETE FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			   $status = 1;
			}

		 }
		 return $status;
	  }

	  /**
	  * $oldData2newData: convert old sql data array to new sql data array
	  * 
	  * old array was like $data['name']='id';$data['value']=2;
	  * new array is like $data['id']=2;
	  * @access public
	  * @param array $olddata  old data array
	  * @return array new data array
	  */
	  function oldData2newData($olddata)
	  {
		 foreach($olddata as $old_el)
		 {
			$newdata[$old_el['name']]=$old_el['value'];
		 }
		 return $newdata;
	  }

	  function insert_new_site($data)
	  {
		 $meta=$this->phpgw_table_metadata('egw_jinn_sites',true);
			
		 $newdata=$this->oldData2newData($data);
		 if(!$newdata['uniqid'])
		 {
			$uniqid=uniqid('');
			$newdata['uniqid'] = $uniqid;
		 }

		 foreach($newdata as $colname => $colval)
		 {
			if($meta[$colname]['auto_increment'] || eregi('seq_egw_jinn_sites',$meta[$colval]['default'])) 
			{
			   $last_insert_id_col=$colname;
			   continue;
			}

			if( $colname == 'site_id') 
			{
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $colname;
			$SQLvalues .= "'".$colval."'"; //FIXME check for integers //FIXME POSTGRESQL
		 }


		 $SQL='INSERT INTO egw_jinn_sites (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 //FIXME REPLACE WITH EGWDB
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status[ret_code]=0;

			$SQL="SELECT * FROM egw_jinn_sites WHERE uniqid='$uniqid'";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			$this->phpgw_db->next_record();

			$status[where_value]=$this->phpgw_db->f('site_id');
		 }

		 return $status;
	  }
	  
	  /**
	  * insert_new_object: the one and only function for creating new objects 
	  * 
	  * @param mixed $data 
	  * @access public
	  * @return void
	  */
	  function insert_new_object($data)
	  {
		 $meta=$this->phpgw_table_metadata('egw_jinn_objects',true);

		 $uniqid=uniqid('');

		 $newdata=$this->oldData2newData($data);

		 if(!$newdata['object_id'])
		 {
			$newdata['object_id'] = $uniqid;
		 }
		 else
		 {
			$uniqid = $newdata['object_id'];
		 }

		 //FIXME remove
//		 if(!$newdata['unique_id'])
//		 {
			$newdata['unique_id'] = $uniqid;
//		 }

		 foreach($newdata as $colname => $colval)
		 {

			// safety hack for pgsql which doesn't allow '' for integers
			if($colval=='' && eregi('int',$meta[$colname]['type']) )
			{
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $colname;
			
			if( !$meta[$colname]['not_null'] && $colval==null)
			{
			   $SQLvalues .= "NULL";
			}
			else
			{
			   $SQLvalues .= "'".$colval."'";
			}
		 }
	
		 if(!$newmethod)
		 {
			$SQL='INSERT INTO egw_jinn_objects (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

			//FIXME REPLACE WITH EGWDB
			if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
			{
			   $status[where_value]=$uniqid;
			   $status[ret_code]=0;
			}
		 }
		 else
		 {
			$where="object_id='$uniqid'";

			if($this->phpgw_db->insert('egw_jinn_objects',$newdata,$where,__LINE__,__FILE__))
			{
			   $status[where_value]=$uniqid;
			   $status[ret_code]=0;
			}
		 }

		 return $status;
	  }

	  /**
	  * validateAndInsert_phpgw_data 
	  * 
	  * @param mixed $table 
	  * @param mixed $data 
	  * @access public
	  * @return void
	  * @todo improve status
	  */
	  //depreciated: every table must has it's  own functions
	  function validateAndInsert_phpgw_data($table,$data)
	  {
		 if($table=='egw_jinn_objects')
		 {
			return $this->insert_new_object($data);
		 }
		 
		 $meta=$this->phpgw_table_metadata($table,true);
		 $fieldnames=$this->get_phpgw_fieldnames($table);

		 foreach($data as $field)
		 {
			if(!in_array($field[name],$fieldnames)) continue;

			if($meta[$field['name']]['auto_increment'] || eregi('seq_'.$table,$meta[$field['name']]['default'])) 
			{
			   $last_insert_id_col=$field['name'];
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $field[name];
			if( !$meta[$field['name']]['not_null']&& $field[value]==null)
			{
			   $SQLvalues .= "NULL";
			}
			else
			{
			   $SQLvalues .= "'".$field[value]."'";
			}
		 }

		 $SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 //FIXME REPLACE WITH EGWDB
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=$this->phpgw_db->get_last_insert_id($table,$last_insert_id_col);
		 }

		 return $status;
	  }

	  /**
	  * insert_phpgw_data 
	  * 
	  * @fixme remove this when the function is replaces everywhere in the code // now use it as a wrapper
	  * @param mixed $table 
	  * @param mixed $data 
	  * @access public
	  * @return void
	  */
	  function insert_phpgw_data($table,$data)
	  {
		 if($table=='egw_jinn_sites') 
		 {
			return $this->insert_new_site($data);
		 }
		 elseif($table=='egw_jinn_objects')
		 {
			return $this->insert_new_object($data);
		 }

		 $new_data=$this->oldData2newData($data);
		 $table_def=$this->table2definition( $table );

		 if ($this->phpgw_db->insert($table,$new_data,'',__LINE__,__FILE__,False,false,$table_def))
		 {
			$status['newid']=$this->phpgw_db->get_last_insert_id($table,$last_insert_id_col);
		 }
		 else
		 {
			$status['error']=true;	
		 }
		 
		 $status['sql']=$sql;	


		 return $status;
	  }

	  function phpgw_table_fields($table)
	  {
		 $fields = $this->phpgw_table_metadata($table);
		 $out = array();
		 foreach($fields as $field)
		 {
			$out[$field['name']] = true;
		 }
		 return $out;
	  }

	  function upAndValidate_phpgw_data($table,$data,$where_key,$where_value)
	  {
		 foreach($data as $field)
		 {
			if ($SQL_SUB) $SQL_SUB .= ', ';
			$SQL_SUB .= "$field[name]='$field[value]'";
		 }

		 $SQL = 'UPDATE ' . $table . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
		 //FIXME REPLACE WITH EGWDB
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=1;
		 }

		 return $status;
	  }

	  /**
	  * update_phpgw_data 
	  * 
	  * @param mixed $table 
	  * @param mixed $data 
	  * @param mixed $where_key 
	  * @param mixed $where_value 
	  * @param string $where_string 
	  * @param mixed $try_insert 
	  * @access public
	  * @return void
	  */
	  function update_phpgw_data($table,$data,$where_key,$where_value,$where_string='',$try_insert=false)
	  {
		 if($where_string)
		 {
			$WHERE = $this->strip_magic_quotes_gpc($where_string);
		 }
		 elseif($where_key && $where_value)
		 {
			$WHERE = $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
		 }

		 if($try_insert)
		 {
			$sql="SELECT * FROM $table WHERE $WHERE";

			$this->phpgw_db->query($sql,__LINE__,__FILE__);
			if($this->phpgw_db->num_rows()<1)
			{
			   return $this->insert_phpgw_data($table,$data);
			}
		 }
		 
		 $new_data=$this->oldData2newData($data);
		 $table_def=$this->table2definition( $table );

		 if (!$this->phpgw_db->update($table,$new_data,$WHERE,__LINE__,__FILE__,False,false,$table_def))
		 if (!$this->phpgw_db->query($sql,__LINE__,__FILE__))
		 {
			$status['error']=true;
		 }
		 //$status[sql]=$sql;	 
		 $status['where_value']=$where_value;	 

		 return $status;
	  }

	  /**
	  * table2definition: translates a real table into a eGW tabel definition so 
	  *		it can be used by class.egw_db.inc.php
	  * 
	  * @param mixed $table_name 
	  * @param mixed $db_obj 
	  * @access public
	  * @return void
	  */
	  function table2definition($table_name,$db_obj=false)
	  {
		 $oProc =& CreateObject('phpgwapi.schema_proc',$db_obj->Type,$db_obj);

		 if (method_exists($oProc,'GetTableDefinition'))
		 {
			$newdefinition = $oProc->GetTableDefinition($table_name);
			return $newdefinition;
		 }
	  }


	  /**
	  * save_field: save all field properties except plugins and help info
	  * 
	  * @todo set all args
	  * @todo rename
	  * @todo test trough and trough
	  **/
	  function save_field($object_ID,$fieldname,$conf_serialed_string,$show_default,$show_in_form,$position)
	  {
		 if(!$object_ID) $object_ID=-1;
		 if($show_in_form == '')
		 {
			$show_in_form = 0;
		 }
		 if($this->phpgw_db->num_rows()>0)
		 {
			$this->phpgw_db->next_record();
			$sql="UPDATE egw_jinn_obj_fields SET list_visibility='$show_default', form_visibility='$show_in_form',field_position='$position' WHERE (field_parent_object='$object_ID') AND (field_name='$fieldname')";
		 }
		 else
		 {
			$sql="INSERT INTO egw_jinn_obj_fields (field_parent_object,field_name,list_visibility,field_position) VALUES ('$object_ID', '$fieldname', '$show_default', '$position')";
		 }

		 $status['sql']=$sql;

		 //FIXME REPLACE WITH EGWDB
		 if($this->phpgw_db->query($sql,__LINE__,__FILE__))
		 {
			$status[ret_code]=0;
		 }
		 else
		 {
			$status[ret_code]=1;
		 }
		 return $status;

	  }

	  //remove
	  function save_field_info_conf($object_id,$fieldname,$data,$where_string)
	  {
		 if(!$object_id) $object_id=-1;
		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id' AND field_name='$fieldname'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 if($this->phpgw_db->num_rows()>0)
		 {

			// update
		 }
		 else
		 {
			$data[] = array
			(
			   'name' =>  field_parent_object,
			   'value' => $object_id 
			);

			$data[] = array
			(
			   'name' =>   field_name,
			   'value' =>  $fieldname
			);


			$status	= $this->insert_phpgw_data('egw_jinn_obj_fields',$data);
		 }

		 return $status;
	  }


	  /**
	  * save_object_events_plugin_conf 
	  * 
	  * @param mixed $object_id 
	  * @param mixed $conf_serialed 
	  * @access public
	  * @return void
	  */
	  function save_object_events_plugin_conf($object_id,$conf_serialed)
	  {
		 if(!$object_id) $object_id=-1;
		 $sql="UPDATE egw_jinn_objects SET events_config='$conf_serialed' WHERE object_id='$object_id'";
		 //FIXME REPLACE WITH EGWDB
		 if(!$this->phpgw_db->query($sql,__LINE__,__FILE__))
		 {
			$status[error]=true;
		 }
		 $status[sql]=$sql;

		 return $status;
	  }

	  function save_field_plugin_conf($object_id,$fieldname,$conf_serialed)
	  {
		 if(!$object_id) $object_id=-1;
		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id' AND field_name='$fieldname'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);
		 if($this->phpgw_db->num_rows()>0)
		 {
			// update
			$sql="UPDATE egw_jinn_obj_fields SET field_plugins='$conf_serialed' WHERE (field_parent_object='$object_id') AND (field_name='$fieldname')";
		 }
		 else
		 {
			// insert
			$sql="INSERT INTO egw_jinn_obj_fields (field_parent_object,field_name,field_plugins) VALUES ('$object_id','$fieldname','$conf_serialed')";
		 }
		 //die($sql);

		 //FIXME REPLACE WITH EGWDB
		 if($this->phpgw_db->query($sql,__LINE__,__FILE__))
		 {
			$status[ret_code]=0;
		 }
		 else
		 {
			$status[ret_code]=1;
		 }

		 return $status;
	  }


	  function update_object_access_rights($editors,$object_id)
	  {
		 $error=0;
		 if ($object_id)
		 {

			$SQL="DELETE FROM egw_jinn_acl WHERE site_object_id='$object_id' AND uid IS NOT NULL";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			if (is_array($editors))
			{
			   foreach ($editors as $editor)
			   {
				  $SQL="INSERT INTO egw_jinn_acl (site_object_id, uid) VALUES ('$object_id','$editor')";
				  //FIXME REPLACE WITH EGWDB
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

		 if ($error>0)
		 {
			$status[error]=false;
		 }

		 $status[sql]=$SQL;

		 return $status;
	  }

	  function update_site_access_rights($editors,$site_id)
	  {
		 $error=0;
		 if ($site_id)
		 {
			$SQL="DELETE FROM egw_jinn_acl WHERE site_id='$site_id' AND uid IS NOT NULL";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			if (is_array($editors))
			{
			   foreach ($editors as $editor)
			   {
				  $SQL="INSERT INTO egw_jinn_acl (site_id, uid) VALUES ('$site_id','$editor')";
				  //FIXME REPLACE WITH EGWDB
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

		 if ($error>0)
		 {
			$status[error]=true;
		 }

		 $status[sql]=$SQL;

		 return $status;
	  }
	  function insert_report($name, $object_id, $header, $body, $footer, $html_title, $html)
	  {
		 if($html == 'on')
		 {
			$html = 1;
		 }
		 else
		 {
			$html = 0;
		 }
		 $SQLfields = 'report_id , report_naam , report_object_id , report_header , report_body , report_footer,report_html,report_html_title';
		 $SQLvalues='\'\',\''.$name.'\',\''.$object_id.'\',\''.$header.'\',\''.$body.'\',\''.$footer.'\',\''.$html.'\',\''.$html_title.'\'';
		 $SQL= 'INSERT INTO egw_jinn_report (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

		 //FIXME REPLACE WITH EGWDB
		 if (!$this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			return 0;
		 }
		 else
		 {
			return 1;
		 }

	  }
	  function update_report($name, $object_id, $header, $body, $footer, $html_title, $html, $report_id)
	  {
		 if($html == 'on')
		 {
			$html = 1;
		 }
		 else
		 {
			$html = 0;
		 }
		 $SQL= 'UPDATE egw_jinn_report ';
		 $SQL .= 'SET report_naam = \''.$name;
		 $SQL .= '\',report_header = \''.$header;
		 $SQL .=  '\',report_body = \''.$body;
		 $SQL .=  '\',report_footer = \''.$footer;
		 $SQL .=  '\',report_html = \''.$html;
		 $SQL .=  '\',report_html_title = \''.$html_title;
		 $SQL .=  '\' WHERE report_id ='.$report_id.' LIMIT 1';
		 //FIXME REPLACE WITH EGWDB
		 if (!$this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			return 0;
		 }
		 else
		 {
			return 1;
		 }

	  }

	  function delete_report($report_id)
	  {
		 $SQL= 'DELETE FROM egw_jinn_report WHERE report_id = '.$report_id.' LIMIT 1';
		 if (!$this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			return 0;
		 }
		 else
		 {
			return 1;
		 }

	  }
	  function get_report_list($id)
	  {
		 if($id)
		 {
			$SQL="SELECT report_id, report_naam  FROM egw_jinn_report WHERE report_object_id = '".$id."'" ;
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);
			$i=0;
			while ($this->phpgw_db->next_record())
			{
			   $report_arr[$i][name]=$this->phpgw_db->f('report_naam');
			   $report_arr[$i][id]= $this->phpgw_db->f('report_id');
			   $i++;
			}
			return $report_arr;
		 }
		 else
		 {
			return false;
		 }
	  }

	  function get_single_report($id)
	  {
		 $SQL="SELECT * FROM egw_jinn_report WHERE report_id =".$id ;
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);
		 while ($this->phpgw_db->next_record())
		 {
			$report_arr[r_id]=($this->phpgw_db->f('report_id'));
			$report_arr[r_name]=($this->phpgw_db->f('report_naam'));
			$report_arr[r_obj_id]=($this->phpgw_db->f('report_object_id'));
			$report_arr[r_header]=($this->phpgw_db->f('report_header'));
			$report_arr[r_footer]=($this->phpgw_db->f('report_footer'));
			$report_arr[r_body]=($this->phpgw_db->f('report_body'));
			$report_arr[r_html]=($this->phpgw_db->f('report_html'));
			$report_arr[r_html_title]=($this->phpgw_db->f('report_html_title'));
		 }
		 return $report_arr;
	  }
	
	  function increase_site_version($site_id)
	  {
		 $data['site_version']='site_version+1';
		 $where='site_id='.$site_id;
		 $sql="UPDATE egw_jinn_sites SET site_version=site_version+1 WHERE site_id=$site_id";
		 //FIXME REPLACE WITH EGWDB
		 $status=$this->phpgw_db->query("$sql",__LINE__,__FILE__);

		 return $status;
	  }
   }
?>
