<?php
   /*
   JiNN - Jinn is Not Nuke, a mutli-user, multi-site CMS for eGroupWare
   Copyright (C)2002, 2003 Pim Snel <pim@lingewoud.nl>

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

   /* $id$ */

   class sojinn
   {
	  var $phpgw_db;
	  var $site_db;
	  var $common;
	  var $config;

	  var $db_ftypes;

	  function sojinn()
	  {
		 $c = CreateObject('phpgwapi.config',$config_appname);
		 $c->read_repository();
		 if ($c->config_data)
		 {
			$this->config = $c->config_data;
		 }

		 $this->phpgw_db    	= $GLOBALS['phpgw']->db;
		 $this->phpgw_db->Debug	= False;

		 $this->db_ftypes = CreateObject('jinn.dbfieldtypes');
	  }

	  /*!
	  @function site_db_connection
	  @abstract make connection to the site database and set this->site_db
	  @param int $site_id unique site_id to select the site from the sites-table
	  */

	  function site_db_connection($site_id)
	  {
		 if($site_id=='') $site_id=-1;//pgsql hack
		 
		 $SQL="SELECT * FROM egw_jinn_sites WHERE site_id='$site_id'";

		 $this->phpgw_db->free();
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);
		 $this->phpgw_db->next_record();

		 $this->site_db 				= CreateObject('phpgwapi.db');

		 // if servertype is develment use dev site settings else use normal settings
		 if($this->config["server_type"]=='dev' && $this->phpgw_db->f('dev_site_db_name'))
		 {
			$this->site_db->Host		= $this->phpgw_db->f('site_db_host');
			$this->site_db->Type		= $this->phpgw_db->f('dev_site_db_type');
			$this->site_db->Database	= $this->phpgw_db->f('dev_site_db_name');
			$this->site_db->User		= $this->phpgw_db->f('dev_site_db_user');
			$this->site_db->Password	= $this->phpgw_db->f('dev_site_db_password');
		 }
		 else
		 {
			$this->site_db->Host		= $this->phpgw_db->f('site_db_host');
			$this->site_db->Type		= $this->phpgw_db->f('site_db_type');
			$this->site_db->Database	= $this->phpgw_db->f('site_db_name');
			$this->site_db->User		= $this->phpgw_db->f('site_db_user');
			$this->site_db->Password	= $this->phpgw_db->f('site_db_password');
		 }
	  }

	  function site_close_db_connection()
	  {
		 $this->site_db->disconnect;
	  }

	  function test_db_conn($data)
	  {
		 $this->test_db = CreateObject('phpgwapi.db');

		 // if servertype is develment use dev site settings else use normal settings
		 if($this->config["server_type"]=='dev' && $data[dev_site_db_name])
		 {
			$this->test_db->Host	 = $data['dev_db_host'];
			$this->test_db->Type     = $data['dev_db_type'];
			$this->test_db->Database = $data['dev_db_name'];
			$this->test_db->User     = $data['dev_db_user'];
			$this->test_db->Password = $data['dev_db_password'];

		 }
		 else
		 {
			$this->test_db->Host		= $data['db_host'];
			$this->test_db->Type		= $data['db_type'];
			$this->test_db->Database	= $data['db_name'];
			$this->test_db->User		= $data['db_user'];
			$this->test_db->Password	= $data['db_password'];
		 }

		 if($test=@$this->test_db->table_names())
		 {
			$this->test_db->disconnect;
			return true;
		 }
		 else
		 {
			$this->test_db->disconnect;
			return false;
		 }
	  }

	  /****************************************************************************\
	  * get sitevalues for site id                                                 *
	  \****************************************************************************/

	  function get_site_values($site_id)
	  {
		 $site_metadata=$this->phpgw_db->metadata('egw_jinn_sites');
		 $this->phpgw_db->free();	


		 //FIXME psql error;
		if($site_id=='') $site_id=-1;
		 
		 $SQL="SELECT * FROM egw_jinn_sites WHERE site_id='$site_id';";
		 $this->phpgw_db->query($SQL,__LINE__,__FILE__);

		 $this->phpgw_db->next_record();

		 foreach($site_metadata as $fieldmeta)
		 {
			$site_values[$fieldmeta['name']]=$this->phpgw_db->f($fieldmeta['name']);
		 }

		 if($this->config["server_type"]=='dev') $pre='dev_';

		 $site_values[cur_site_db_name] = $site_values[$pre.'site_db_name'];
		 $site_values[cur_site_db_host] = $site_values[$pre.'site_db_host'];
		 $site_values[cur_site_db_user] = $site_values[$pre.'site_db_user'];
		 $site_values[cur_site_db_password] = $site_values[$pre.'site_db_password'];
		 $site_values[cur_site_db_type] = $site_values[$pre.'site_db_type'];
		 $site_values[cur_upload_path] =$site_values[$pre.'upload_path'];
		 $site_values[cur_upload_url] =$site_values[$pre.'upload_url'];

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

	  /**
	  @function get_object_values
	  @abstract get objectvalues by object id or serialnumber
	  @param $object_id int default behaviour to look by object_id
	  @param $serialnumber int optional
	  */
	  function get_object_values($object_id,$serialnumber=false)
	  {
		 if($serialnumber)
		 {
			$sql="SELECT * FROM egw_jinn_objects WHERE serialnumber=$serialnumber";
		 }
		 else
		 {
			$sql="SELECT * FROM egw_jinn_objects WHERE object_id=$object_id";
		 }
		 //echo($sql);
		 
		 $object_metadata=$this->phpgw_db->metadata('egw_jinn_objects');
		 $this->phpgw_db->free();	

		 $this->phpgw_db->query("$sql",__LINE__,__FILE__);

		 $this->phpgw_db->next_record();
		 foreach($object_metadata as $fieldmeta)
		 {
			$object_values[$fieldmeta['name']]=$this->strip_magic_quotes_gpc($this->phpgw_db->f($fieldmeta['name']));
		 }

		 if($this->config["server_type"]=='dev') $pre='dev_';

		 $object_values[cur_upload_path] =$object_values[$pre.'upload_path'];

		 return $object_values;
	  }

	  function get_field_values($object_id,$field_name)
	  {
		 $field_metadata=$this->phpgw_db->metadata('egw_jinn_obj_fields');
		 $this->phpgw_db->free();	

		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object='$object_id' AND field_name='$field_name'";
//		die($sql);
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 $this->phpgw_db->next_record();
		 foreach($field_metadata as $fieldmeta)
		 {
			$field_values[$fieldmeta['name']]=$this->strip_magic_quotes_gpc($this->phpgw_db->f($fieldmeta['name']));
		 }

//			_debug_array($field_values);
		 return $field_values;

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

		 if($where_condition)
		 {
			   $WHERE='WHERE '.$where_condition;
		 }

		 $sql="SELECT * FROM $table $WHERE";
		 $this->site_db->query($sql,__LINE__,__FILE__);

		 $num_rows=$this->site_db->num_rows();

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
			   $ret_meta[$col[name]]=$col;
			}
			return $ret_meta;
		 }
		 else
		 {
			return $this->phpgw_db->metadata($table);
		 }
	  }


	  // FIXME arg has to be site object_id in stead site_id and tablename
	  function site_table_metadata($site_id,$table,$associative=false)
	  {
		 $this->site_db_connection($site_id);
		 if($associative)
		 {
			$meta=$this->site_db->metadata($table);
			foreach ($meta as $col)
			{
			   $meta_data[$col[name]]=$col;
			}
//			return $meta_data;
		 }
		 else
		 {
			 $meta_data = $this->site_db->metadata($table);
		 }
		 
		 $this->site_close_db_connection();

		 return $meta_data;
	  }

	  // FIXME arg has to be site object_id in stead site_id and tablename
	  function site_table_metadata2($site_id,$table)
	  {
		 $this->site_db_connection($site_id);

		 $metadata = $this->site_db->metadata($table);

		 foreach($metadata as $mdat)
		 {
			$redat[$mdat[name]]=array
			(
			   'type'=>$mdat[type],
			   'flags'=>$mdat[flags],
			   'len'=>$mdat[len]
			);

		 }

		 $this->site_close_db_connection();

		 return $redat;
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
		 $sql="SELECT site_name FROM egw_jinn_sites WHERE site_id='$site_id'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);

		 $this->phpgw_db->next_record();
		 $site_name=$this->strip_magic_quotes_gpc($this->phpgw_db->f('site_name'));
		 return $site_name;

	  }

	  /****************************************************************************\
	  * get sitename for site id                                                   *
	  \****************************************************************************/

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

//		 echo $egwadmin;
//		 echo $site_id;
//		 echo($SQL);
		 

		 /* yes it's an admin so we can get all objects for this site */
		 if ($egwadmin)
		 {
			$SQL="SELECT object_id FROM egw_jinn_objects WHERE {$egw_bt}parent_site_id{$egw_bt} = '$site_id' AND ({$egw_bt}hide_from_menu{$egw_bt} != '1' OR {$egw_bt}hide_from_menu{$egw_bt}=NULL) ORDER BY name";
//			$SQL="SELECT object_id FROM egw_jinn_objects WHERE {$egw_bt}parent_site_id{$egw_bt} = '$site_id' ORDER BY name";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			while ($this->phpgw_db->next_record())
			{
			   $objects[]= $this->phpgw_db->f('object_id');
			}
		 }
		 // he's no admin so get all the objects which are assigned to the user
		 else
		 {
			$SQL="SELECT object_id FROM egw_jinn_objects WHERE parent_site_id = '$site_id'  AND ({$egw_bt}hide_from_menu{$egw_bt} != '1' OR {$egw_bt}hide_from_menu{$egw_bt}=NULL) ORDER BY name";
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
			$objects=array_unique($objects);
		 }

		 return $objects;
	  }


	  function get_phpgw_record_values($table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by=false)
	  {
		 if ($where_key && $where_value)
		 {
			$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
			$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
			$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
		 }


		 $fieldproperties = $this->phpgw_table_metadata($table);

		 $SQL="SELECT * FROM  $table $WHERE $order_by";
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

	  function get_record_values($site_id,$table,$where_key,$where_value,$offset,$limit,$value_reference,$order_by='',$field_list='*',$where_condition='')
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
		 $s_bt=$this->backtick();

		 if ($where_key && $where_value)
		 {
			$SQL_WHERE_KEY = $this->strip_magic_quotes_gpc($where_key);
			$SQL_WHERE_VALUE = $this->strip_magic_quotes_gpc($where_value);
			$WHERE="WHERE $SQL_WHERE_KEY='$SQL_WHERE_VALUE'";
		 }
		 //			 elseif($where_condition)


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
		 //die ($SQL);
		 if (!$limit) $limit=1000000;

		 $this->site_db->limit_query($SQL, $offset,__LINE__,__FILE__,$limit); 

		 while ($this->site_db->next_record())
		 {
			unset($row);
			foreach($fieldproperties as $field)
			{
			   if($field_list=='*' || in_array($field[name],$field_list_arr))
			   {
				  if ($field[type]=='blob' && ereg('xxxbinary',$field[flags]))// FIXME cripled
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
		 //die($SQL);

		 if ($this->site_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=1;
		 }

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
	  function insert_object_data($site_id,$site_object,$data)
	  {

		 $this->site_db_connection($site_id);

		 $s_bt=$this->backtick();
		 $metadata=$this->site_table_metadata($site_id,$site_object,true);
		 
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
				continue;
			 }
			 if($field[value]=='' && eregi('int',$metadata[$field['name']]['type']) )
			 {
				continue;
			 }

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

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


		 $SQL='INSERT INTO ' . $site_object . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';

		 if ($this->site_db->query($SQL,__LINE__,__FILE__))
		 {
			$status[status]=1; // for backwards compatibility
			$status[ret_code]=0;

			$status[id]=$this->site_db->get_last_insert_id($site_object, $autokey);

			if($autokey) $where_string= $autokey.'=\''.$status[id].'\'';
			elseif(is_array($pkey_arr))
			{
			   foreach($pkey_arr as $pkey)
			   {
				  if($where_string) $where_string.=' AND ';
				  $where_string.= '('.$pkey.' = \''. $aval[$pkey].'\')';
			   }
			}

			$status[where_string]=$where_string;
		 }
		 else
		 {
			$status[ret_code]=1;
		 }

		 $status[sql]=$SQL;
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

	  /*!
	  @function update_object_record  
	  @abstract update record data
	  @param $site_id id of site for resolving db connection data
	  @param $site_object
	  @param $data array of data
	  @param where_key which field to use as id-key (depreciated?)
	  @param where_value which value to use as value for id-key (depreciated?)
	  @param where_string complete string which comes after "... WHERE " in the sql-string
	  @fixme for all fieldtype a default_value mechanisme must be implemented, atm int is finished
	  @fixme better naming
	  @fixme implement NULL for eg ints
	  @fixme code cleanup
	  */
	  function update_object_record($site_id,$site_object,$data,$where_key,$where_value,$curr_where_string='')
	  {
		 $this->site_db_connection($site_id);

		 $s_bt=$this->backtick();

		 $metadata=$this->site_table_metadata($site_id,$site_object,true);
		 
		 foreach($data as $field)
		 {
			$jinn_field_type=$this->db_ftypes->complete_resolve($metadata[$field[name]]);

			/* use '' in SQL yes/no */	
			if($jinn_field_type=='int' || $jinn_field_type=='auto')
			{
			   $fortick='';//FIXME this is the same as doing nothing!!!
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

			
			
			/* check for primaries and create array */
			if (eregi("auto_increment", $metadata[$field[name]][flags]))
			{
			   $autokey=$field[name].'=\''.$field[value].'\'';
			}
			elseif($this->db_ftypes->complete_resolve($metadata[$field[name]])=='int')
			{
			   if(strval($field[value]!='0'))
			   {
				  if(empty($field[value]))
				  {
					 /* if there is a default value set it to this value */
					 if($this->db_ftypes->has_default($metadata[$field[name]]))
					 {
						$field[value]=$this->db_ftypes->get_default($metadata[$field[name]]);
					 }
					 else
					 {
						continue;
					 }
				  }
			   }
			}
			elseif (!$autokey && eregi("primary_key", $metadata[$field[name]][flags]) && $metadata[$field[name]][type]!='blob') // FIXME howto select long blobs
			{						
			   $pkey_arr[]=$field[name];
			}
			elseif(!$autokey && $metadata[$field[name]][type]!='blob') // FIXME howto select long blobs
			{
			   $akey_arr[]=$field[name];
			}

			$aval[$field[name]]=substr($field[value],0,$metadata[$field[name]][len]);

			if ($SQL_SUB) $SQL_SUB .= ', ';
			$SQL_SUB .= "{$s_bt}$field[name]{$s_bt}={$fortick}".$this->strip_magic_quotes_gpc($field[value])."{$fortick}";
		 }

		 if(!is_array($pkey_arr))
		 {
			$pkey_arr=$akey_arr;
			unset($akey_arr);
		 }

		 if($curr_where_string)
		 {
			$SQL = 'UPDATE ' . $site_object . ' SET ' . $SQL_SUB . ' WHERE ' . $curr_where_string ." LIMIT 1";
		 }
		 else
		 {
			$SQL = 'UPDATE ' . $site_object . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_key))."='".$this->strip_magic_quotes_gpc($this->strip_magic_quotes_gpc($where_value))."'";

		 }
		
		 if ($this->site_db->query($SQL,__LINE__,__FILE__))
		 {
			$value[ret_code]=0;
			$value[status]=1;

			if($autokey) $where_string= $autokey;
			elseif(is_array($pkey_arr))
			{
			   foreach($pkey_arr as $pkey)
			   {
				  if($where_string) $where_string.=' AND ';
				  $where_string.= '('.$pkey.' = \''. $aval[$pkey].'\')';
			   }
			}

			$value[where_string]=$where_string;
		 }
		 else
		 {
			$value[ret_code]=1;
		 }
		 
		 $value[sql]=$SQL;
		 return $value;
	  }



	  function update_object_many_data($site_id, $data)
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
			   if (!$this->site_db->query($SQL,__LINE__,__FILE__))
			   {
				  $status=False;
			   }

			}

			$i++;
		 }
		 return $status;

	  }

	  /*!
	  @function delete_phpgw_data
	  @fixme create dedicatet function and delete garbage! DELETE site &> objects &> obj_fields
	  */
	  function delete_phpgw_data($table,$where_key,$where_value)
	  {
		 $SQL = 'DELETE FROM ' . $table . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."=".$this->strip_magic_quotes_gpc($where_value);

		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=1;
		 }

		 return $status;
	  }


	  function validateAndInsert_phpgw_data($table,$data)
	  {
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
			$SQLvalues .= "'".$field[value]."'";
		 }


		 $SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=$this->phpgw_db->get_last_insert_id($table,$last_insert_id_col);
		 }

		 return $status;
	  }


	  function insert_new_site($data)
	  {
		 $meta=$this->phpgw_table_metadata('egw_jinn_sites',true);

		 foreach($data as $field)
		 {
			if($meta[$field['name']]['auto_increment'] || eregi('seq_egw_jinn_sites',$meta[$field['name']]['default'])) 
			{
			   $last_insert_id_col=$field['name'];
			   continue;
			}
			
			if( $field['name'] == 'site_id') 
			{
			   continue;
			}

			if(trim($field[name])=='serialnumber')
			{
			   $serial=time();
			   $field[value]=$serial;
//			   echo $field[value];
//			   echo 'hallo';
			}
			
//			echo $field[name];
//			echo $serial;
			
			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $field[name];
			$SQLvalues .= "'".$field[value]."'";
		 }

		 $SQL='INSERT INTO egw_jinn_sites (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status[ret_code]=0;
   
			$SQL='SELECT * FROM egw_jinn_sites WHERE serialnumber='.$serial;
//			echo $SQL;
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			$this->phpgw_db->next_record();

				
			$status[where_value]=$this->phpgw_db->f('site_id');

		 }

		 return $status;
	  }

	  function insert_new_object($data)
	  {
		 $meta=$this->phpgw_table_metadata('egw_jinn_objects',true);

		 foreach($data as $field)
		 {
			if($meta[$field['name']]['auto_increment'] || eregi('seq_egw_jinn_objects',$meta[$field['name']]['default'])) 
			{
			   $last_insert_id_col=$field['name'];
			   continue;
			}

			$serial=time();
			if( $field['name'] == 'object_id') 
			{
			   continue;
			}

			if($serial && $field[name]=='serialnumber')
			{
			   $field[value]=$serial;
			}

			// safety hack for pgsql which doesn't allow '' for integers
			if($field[value]=='' && eregi('int',$meta[$field['name']]['type']) )
			{
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $field[name];
			$SQLvalues .= "'".$field[value]."'";
		 }

		 $SQL='INSERT INTO egw_jinn_objects (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$SQL='SELECT * FROM egw_jinn_objects WHERE serialnumber='.$serial;
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			$this->phpgw_db->next_record();

			$status[where_value]=$this->phpgw_db->f('object_id');
			
			$status[ret_code]=0;
		 }

		 return $status;
	  }



	  // fixme remove this when the function is replaces everywhere in the code
	  // now use it as a wrapper
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
		 
		 $meta=$this->phpgw_table_metadata($table,true);

		 foreach($data as $field)
		 {
			if($meta[$field['name']]['auto_increment'] || eregi('seq_'.$table,$meta[$field['name']]['default'])) 
			{
			   $last_insert_id_col=$field['name'];
			   continue;
			}

			if ($SQLfields) $SQLfields .= ',';
			if ($SQLvalues) $SQLvalues .= ',';

			$SQLfields .= $field[name];
			$SQLvalues .= "'".$field[value]."'";
		 }


		 $SQL='INSERT INTO ' . $table . ' (' . $SQLfields . ') VALUES (' . $SQLvalues . ')';
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status=$this->phpgw_db->get_last_insert_id($table,$last_insert_id_col);
		 }

		 return $status;
	  }

	  
	  function upAndValidate_phpgw_data($table,$data,$where_key,$where_value)
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

	  function update_phpgw_data($table,$data,$where_key,$where_value,$where_string='')
	  {

		 $meta=$this->phpgw_table_metadata($table,true);

		 foreach($data as $field)
		 {

			if($field[value]=='' && eregi('int',$meta[$field['name']]['type'])) 
			{
			   $field[value]="null";
//			   continue;
			}
			else $field[value]="'$field[value]'";
			
			if ($SQL_SUB) $SQL_SUB .= ', ';
			$SQL_SUB .= "$field[name]=$field[value]";
		 }

		 if($where_string)
		 {
			$SQL = 'UPDATE ' . $table . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($where_string);
		 }
		 elseif($where_key && $where_value)
		 {

			$SQL = 'UPDATE ' . $table . ' SET ' . $SQL_SUB . ' WHERE ' . $this->strip_magic_quotes_gpc($where_key)."='".$this->strip_magic_quotes_gpc($where_value)."'";
		 }
		 //		 die($SQL);
		 if ($this->phpgw_db->query($SQL,__LINE__,__FILE__))
		 {
			$status[ret_code]=0;
		 }
		 else
		 {
			$status[ret_code]=1;
		 }

		 return $status;
	  }

	  function save_field($object_ID,$fieldname,$conf_serialed_string,$mandatory,$show_default,$position)
	  {
		if(!$object_ID) $object_ID=-1;
		$sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object=$object_ID AND field_name='$fieldname'";
		$this->phpgw_db->query($sql,__LINE__,__FILE__);
		if($this->phpgw_db->num_rows()>0)
		{
			$this->phpgw_db->next_record();
			$old_setting=unserialize(base64_decode($this->phpgw_db->f('field_plugins')));
			$new_setting=unserialize(base64_decode($conf_serialed_string));

			// test if conf is set is not and new plugin is the same as old plugin don't save 
			if(is_array($old_setting) AND ($old_setting[name]==$new_setting[name]) AND !is_array($new_setting[conf]) )
			{
				$sql="UPDATE egw_jinn_obj_fields SET field_mandatory='$mandatory', field_show_default='$show_default', field_position='$position' WHERE (field_parent_object=$object_ID) AND (field_name='$fieldname')";
			}
			else
			{
				$sql="UPDATE egw_jinn_obj_fields SET field_plugins='$conf_serialed_string', field_mandatory='$mandatory', field_show_default='$show_default', field_position='$position'  WHERE (field_parent_object=$object_ID) AND (field_name='$fieldname')";
			}
		}
		else
		{
			$sql="INSERT INTO egw_jinn_obj_fields (field_parent_object,field_name,field_plugins,field_mandatory,field_show_default,field_position) VALUES ($object_ID, '$fieldname', '$conf_serialed_string', '$mandatory', '$show_default', '$position')";
		}

		$status[sql]=$sql;

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

	  function save_field_info_conf($object_id,$fieldname,$data,$where_string)
	  {
		 if(!$object_id) $object_id=-1;
		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object=$object_id AND field_name='$fieldname'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);
		 if($this->phpgw_db->num_rows()>0)
		 {
			$status = $this->update_phpgw_data('egw_jinn_obj_fields',$data,'','',$where_string);
			
			// update
			//$sql="UPDATE egw_jinn_obj_fields SET field_plugins='$conf_serialed' WHERE (field_parent_object=$object_id) AND (field_name='$fieldname')";
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
			// insert
//			$sql="INSERT INTO egw_jinn_obj_fields (field_parent_object,field_name,field_plugins) VALUES ($object_id,'$fieldname','$conf_serialed')";
		 }
		 //			die($sql);

		 return $status;
	  }


	  
	  
	  function save_field_plugin_conf($object_id,$fieldname,$conf_serialed)
	  {
		 if(!$object_id) $object_id=-1;
		 $sql="SELECT * FROM egw_jinn_obj_fields WHERE field_parent_object=$object_id AND field_name='$fieldname'";
		 $this->phpgw_db->query($sql,__LINE__,__FILE__);
		 if($this->phpgw_db->num_rows()>0)
		 {
			$this->phpgw_db->next_record();
			$old_setting=unserialize(base64_decode($this->phpgw_db->f('field_plugins')));
			$new_setting=unserialize(base64_decode($conf_serialed));
			
			// test if conf is set is not and new plugin is the same as old plugin don't save 
			if(is_array($old_setting) AND ($old_setting[name]==$new_setting[name]) AND !is_array($new_setting[conf]) )
			{
			   $status[ret_code]=0;	
			   return $status;
			}
			
			// update
			$sql="UPDATE egw_jinn_obj_fields SET field_plugins='$conf_serialed' WHERE (field_parent_object=$object_id) AND (field_name='$fieldname')";
		 }
		 else
		 {
			// insert
			$sql="INSERT INTO egw_jinn_obj_fields (field_parent_object,field_name,field_plugins) VALUES ($object_id,'$fieldname','$conf_serialed')";
		 }
//			die($sql);
		 
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
			$SQL="DELETE FROM egw_jinn_acl WHERE site_id='$site_id' AND uid IS NOT NULL";
			$this->phpgw_db->query($SQL,__LINE__,__FILE__);

			if (is_array($editors))
			{
			   foreach ($editors as $editor)
			   {
				  $SQL="INSERT INTO egw_jinn_acl (site_id, uid) VALUES ('$site_id','$editor')";
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
			$status[ret_code]=0;
		 }

		 $status[sql]=$SQL;

		 return $status;
	  }
   }
?>
