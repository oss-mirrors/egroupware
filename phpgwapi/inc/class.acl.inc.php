<?php
  /**************************************************************************\
  * eGroupWare API - Access Control List                                     *
  * This file written by Dan Kuykendall <seek3r@phpgroupware.org>            *
  * Security scheme based on ACL design                                      *
  * Copyright (C) 2000, 2001 Dan Kuykendall                                  *
  * -------------------------------------------------------------------------*
  * This library is part of the eGroupWare API                               *
  * http://www.egroupware.org/api                                            * 
  * ------------------------------------------------------------------------ *
  * This library is free software; you can redistribute it and/or modify it  *
  * under the terms of the GNU Lesser General Public License as published by *
  * the Free Software Foundation; either version 2.1 of the License,         *
  * or any later version.                                                    *
  * This library is distributed in the hope that it will be useful, but      *
  * WITHOUT ANY WARRANTY; without even the implied warranty of               *
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.                     *
  * See the GNU Lesser General Public License for more details.              *
  * You should have received a copy of the GNU Lesser General Public License *
  * along with this library; if not, write to the Free Software Foundation,  *
  * Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA            *
  \**************************************************************************/

  /* $Id$ */

	/*!
		@class acl
		@abstract Access Control List Security System
		@discussion This class provides an ACL security scheme.
		This can manage rights to 'run' applications, and limit certain features within an application.
		It is also used for granting a user "membership" to a group, or making a user have the security equivilance of another user.
		It is also used for granting a user or group rights to various records, such as todo or calendar items of another user.
		@syntax CreateObject('phpgwapi.acl',int account_id);
		@example $acl = CreateObject('phpgwapi.acl',5);  // 5 is the user id
		@example $acl = CreateObject('phpgwapi.acl',10);  // 10 is the user id
		@author Seek3r
		@copyright LGPL
		@package phpgwapi
		@access public
	*/
	class acl
	{
		/*! @var $account_id */
		var $account_id;
		/*! @var $account_type */
		var $account_type;
		/*! @var $data  */
		var $data = Array();
		/*! @var $db */
		var $db;
		var $table_name = 'phpgw_acl';

		/*!
		@function acl
		@abstract ACL constructor for setting account id
		@discussion Author: Seek3r <br>
		Sets the ID for $acl->account_id. Can be used to change a current instances id as well. <br>
		Some functions are specific to this account, and others are generic. <br>
		@syntax int acl(int account_id) <br>
		@example1 acl->acl(5); // 5 is the user id  <br>
		@param account_id int-the user id
		*/
		function acl($account_id = '')
		{
			$this->db = clone($GLOBALS['egw']->db);
			$this->db->set_app('phpgwapi');

			if ((int)$this->account_id != (int)$account_id)
			{
				$this->account_id = get_account_id((int)$account_id,@GLOBALS['egw_info']['user']['account_id']);
			}
		}

		function DONTlist_methods($_type='xmlrpc')
		{
			/*
			  This handles introspection or discovery by the logged in client,
			  in which case the input might be an array.  The server always calls
			  this function to fill the server dispatch map using a string.
			*/

			if (is_array($_type))
			{
				$_type = $_type['type'] ? $_type['type'] : $_type[0];
			}

			switch($_type)
			{
				case 'xmlrpc':
				$xml_functions = array(
						'read_repository' => array(
							'function'  => 'read_repository',
							'signature' => array(array(xmlrpcStruct)),
							'docstring' => lang('FIXME!')
						),
						'get_rights' => array(
							'function'  => 'get_rights',
							'signature' => array(array(xmlrpcStruct,xmlrpcStruct)),
							'docstring' => lang('FIXME!')

						),
						'list_methods' => array(
							'function'  => 'list_methods',
							'signature' => array(array(xmlrpcStruct,xmlrpcString)),
							'docstring' => lang('Read this list of methods.')
						)
					);
					return $xml_functions;
					break;
				case 'soap':
					return $this->soap_functions;
					break;
				default:
					return array();
					break;
			}
		}

		/**************************************************************************\
		* These are the standard $this->account_id specific functions              *
		\**************************************************************************/

		/*!
		@function read_repository
		@abstract Read acl records from reposity
		@discussion Author: Seek3r <br>
		Reads ACL records for $acl->account_id and returns array along with storing it in $acl->data.  <br>
		Syntax: array read_repository() <br>
		Example1: acl->read_repository(); <br>
		Should only be called within this class
		*/
		function read_repository()
		{
			// For some reason, calling this via XML-RPC doesn't call the constructor.
			// Here is yet another work around(tm) (jengo)
			if (! $this->account_id)
			{
				$this->acl();
			}

			$sql = 'select * from phpgw_acl where (acl_account in ('.$this->account_id.', 0'; 

			$groups = $this->get_location_list_for_id('phpgw_group', 1, $this->account_id);
			while($groups && list($key,$value) = each($groups))
			{
				if($value != '')
					$sql .= ','.$value;
			}
			$sql .= '))';
			$this->db->query($sql ,__LINE__,__FILE__);
			$count = $this->db->num_rows();
			$this->data = Array();
			for ($idx = 0; $idx < $count; ++$idx)
			{
				//reset ($this->data);
				//while(list($idx,$value) = each($this->data)){
				$this->db->next_record();
				$this->data[] = array(
					'appname' => $this->db->f('acl_appname'),
					'location' => $this->db->f('acl_location'), 
					'account' => $this->db->f('acl_account'), 
					'rights' => $this->db->f('acl_rights')
				);
			}
			reset ($this->data);
			return $this->data;
		}

		/*!
		@function read
		@abstract Read acl records from $acl->data
		@discussion Author: Seek3r <br>
		Returns ACL records from $acl->data. <br>
		Syntax: array read() <br>
		Example1: acl->read(); <br>
		*/
		function read()
		{
			if (count($this->data) == 0)
			{
				$this->read_repository();
			}
			reset ($this->data);
			return $this->data;
		}

		/*!
		@function add
		@abstract Adds ACL record to $acl->data
		@discussion Adds ACL record to $acl->data. <br>
		Syntax: array add() <br>
		Example1: acl->add();
		@param $appname default False derives value from $phpgw_info['flags']['currentapp']
		@param $location location
		@param $rights rights
		*/
		function add($appname = False, $location, $rights)
		{
			if ($appname == False)
			{
				settype($appname,'string');
				$appname = GLOBALS['egw_info']['flags']['currentapp'];
			}
			$this->data[] = array('appname' => $appname, 'location' => $location, 'account' => $this->account_id, 'rights' => $rights);
			reset($this->data);
			return $this->data;
		}

		/*!
		@function delete
		@abstract Delete ACL record
		@discussion 
		Syntax <br>
		Example: <br>
		@param $appname optional defaults to $phpgw_info['flags']['currentapp']
		@param $location app location
		*/
		function delete($appname = False, $location)
		{
			if ($appname == False)
			{
				settype($appname,'string');
				$appname = GLOBALS['egw_info']['flags']['currentapp'];
			}
			$count = count($this->data);
			reset ($this->data);
			while(list($idx,$value) = each($this->data))
			{
				if ($this->data[$idx]['appname'] == $appname && $this->data[$idx]['location'] == $location && $this->data[$idx]['account'] == $this->account_id)
				{
					$this->data[$idx] = Array();
				}
			}
			reset($this->data);
			return $this->data;
		}

		/*!
		@function save_repostiory
		@abstract save repository
		@discussion save the repository <br>
		Syntax: save_repository() <br>
		example: acl->save_repository()
		*/
		
		function save_repository()
		{
			reset($this->data);

			$sql = 'delete from phpgw_acl where acl_account = '. (int)$this->account_id;
			$this->db->query($sql ,__LINE__,__FILE__);

			$count = count($this->data);
			reset ($this->data);
			while(list($idx,$value) = each($this->data))
			{
				if ($this->data[$idx]['account'] == $this->account_id)
				{
					$sql = 'insert into phpgw_acl (acl_appname, acl_location, acl_account, acl_rights)';
					$sql .= " values('".$this->data[$idx]['appname']."', '"
						. $this->data[$idx]['location']."', ".$this->account_id.', '.$this->data[$idx]['rights'].')';
					$this->db->query($sql ,__LINE__,__FILE__);
				}
			}
			reset($this->data);
			return $this->data;
		}

		/**************************************************************************\
		* These are the non-standard $this->account_id specific functions          *
		\**************************************************************************/

		/*!
		@function get_rights
		@abstract get rights from the repository not specific to this->account_id (?)
		@discussion 
		@param $location app location to get rights from
		@param $appname optional defaults to $phpgw_info['flags']['currentapp'];
		*/
		function get_rights($location,$appname = False)
		{
			// For XML-RPC, change this once its working correctly for passing parameters (jengo)
			if (is_array($location))
			{
				$a        = $location;
				$location = $a['location'];
				$appname  = $a['appname'];
			}

			if (count($this->data) == 0)
			{
				$this->read_repository();
			}
			reset ($this->data);
			if ($appname == False)
			{
				settype($appname,'string');
				$appname = GLOBALS['egw_info']['flags']['currentapp'];
			}
			$count = count($this->data);
			if ($count == 0 && GLOBALS['egw_info']['server']['acl_default'] != 'deny')
			{
				return True;
			}
			$rights = 0;
			//for ($idx = 0; $idx < $count; ++$idx){
			reset ($this->data);
			while(list($idx,$value) = each($this->data))
			{
				if ($this->data[$idx]['appname'] == $appname)
				{
					if ($this->data[$idx]['location'] == $location || $this->data[$idx]['location'] == 'everywhere')
					{
						if ($this->data[$idx]['rights'] == 0)
						{
							return False;
						}

						$rights |= $this->data[$idx]['rights'];
					}
				}
			}
			return $rights;
		}
		/*!
		@function check
		@abstract check required rights (not specific to this->account_id?)
		@param $location app location
		@param $required required right to check against
		@param $appname optional defaults to currentapp
		*/
		function check($location, $required, $appname = False)
		{
			$rights = $this->get_rights($location,$appname);
			return !!($rights & $required);
		}
		/*!
		@function get_specific_rights
		@abstract get specific rights for this->account_id for an app location
		@param $location app location
		@param $appname optional defaults to currentapp
		@result $rights ?
		*/
		function get_specific_rights($location, $appname = False)
		{
			if ($appname == False)
			{
				settype($appname,'string');
				$appname = GLOBALS['egw_info']['flags']['currentapp'];
			}

			$count = count($this->data);
			if ($count == 0 && GLOBALS['egw_info']['server']['acl_default'] != 'deny')
			{
				return True;
			}
			$rights = 0;

			reset ($this->data);
			while(list($idx,$value) = each($this->data))
			{
				if ($this->data[$idx]['appname'] == $appname && 
					($this->data[$idx]['location'] == $location ||
					$this->data[$idx]['location'] == 'everywhere') &&
					$this->data[$idx]['account'] == $this->account_id)
				{
					if ($this->data[$idx]['rights'] == 0)
					{
						return False;
					}
					$rights |= $this->data[$idx]['rights'];
				}
			}
			return $rights;
		}
		/*!
		@function check_specific
		@abstract check specific
		@param $location app location
		@param $required required rights
		@param $appname optional defaults to currentapp
		@result boolean
		*/
		function check_specific($location, $required, $appname = False)
		{
			$rights = $this->get_specific_rights($location,$appname);
			return !!($rights & $required);
		}
		/*!
		@function get_location_list
		@abstract ?
		@param $app appname
		@param $required ?
		*/
		function get_location_list($app, $required)
		{
			// User piece
			$sql = "select acl_location, acl_rights from phpgw_acl where acl_appname = '$app' ";
			$sql .= " and (acl_account in ('".$this->account_id."', 0"; // group 0 covers all users
			$equalto = GLOBALS['egw']->accounts->security_equals($this->account_id);
			if (is_array($equalto) && count($equalto) > 0)
			{
				for ($idx = 0; $idx < count($equalto); ++$idx)
				{
					$sql .= ','.$equalto[$idx][0];
				}
			}
			$sql .= ')))';

			$this->db->query($sql ,__LINE__,__FILE__);
			$rights = 0;
			if ($this->db->num_rows() == 0 )
			{
				return False;
			}
			while ($this->db->next_record())
			{
				if ($this->db->f('acl_rights') == 0)
				{
					return False;
				}
				$rights |= $this->db->f('acl_rights');
				if (!!($rights & $required) == True)
				{
					$locations[] = $this->db->f('acl_location');
				}
				else
				{
					return False;
				}
			}
			return $locations;
		}

/*
		This is kinda how the function SHOULD work, so that it doesnt need to do its own sql query. 
		It should use the values in the $this->data

		function get_location_list($app, $required)
		{
			if ($appname == False)
			{
				$appname = GLOBALS['egw_info']['flags']['currentapp'];
			}

			$count = count($this->data);
			if ($count == 0 && GLOBALS['egw_info']['server']['acl_default'] != 'deny'){ return True; }
			$rights = 0;

			reset ($this->data);
			while(list($idx,$value) = each($this->data))
			{
				if ($this->data[$idx]['appname'] == $appname && $this->data[$idx]['rights'] != 0)
				{
					$location_rights[$this->data[$idx]['location']] |= $this->data[$idx]['rights'];
				}
			}
			reset($location_rights);
			for ($idx = 0; $idx < count($location_rights); ++$idx)
			{
				if (!!($location_rights[$idx] & $required) == True)
				{
					$location_rights[] = $this->data[$idx]['location'];
				}
			}
			return $locations;
		}
*/

		/**************************************************************************\
		* These are the generic functions. Not specific to $this->account_id       *
		\**************************************************************************/

		/**
		 * add repository information / rights for app/location/account_id
		 *
		 * @param $app appname
		 * @param $location location
		 * @param $account_id account id
		 * @param $rights rights
		 */
		function add_repository($app, $location, $account_id, $rights)
		{
			//echo "<p>acl::add_repository('$app','$location',$account_id,$rights);</p>\n";
			$this->db->insert($this->table_name,array(
				'acl_rights' => $rights,
			),array(
				'acl_appname' => $app,
				'acl_location' => $location,
				'acl_account'  => $account_id,
			),__LINE__,__FILE__);

			return True;
		}

		/**
		 * delete repository information / rights for app/location[/account_id]
		 * @param string $app appname
		 * @param string $location location
		 * @param int/boolean $account_id account id, default 0=$this->account_id, or false to delete all entries for $app/$location
		 * @return int number of rows deleted
		 */
		function delete_repository($app, $location, $accountid=0)
		{
			static $cache_accountid;

			$where = array(
				'acl_appname'  => $app,
				'acl_location' => $location,
			);
			if ($accountid !== false)
			{
				if(isset($cache_accountid[$accountid]) && $cache_accountid[$accountid])
				{
					$where['acl_account'] = $cache_accountid[$accountid];
				}
				else
				{
					$where['acl_account'] = $cache_accountid[$accountid] = get_account_id($accountid,$this->account_id);
				}
			}
			$this->db->delete($this->table_name,$where,__LINE__,__FILE__);

			return $this->db->affected_rows();
		}

		/*!
		@function get_app_list_for_id
		@abstract get application list for an account id
		@param $location location
		@param $required ?
		@param $account_id account id defaults to $phpgw_info['user']['account_id'];
		*/
		function get_app_list_for_id($location, $required, $accountid = '')
		{
			static $cache_accountid;

			if($cache_accountid[$accountid])
			{
				$account_id = $cache_accountid[$accountid];
			}
			else
			{
				$account_id = get_account_id($accountid,$this->account_id);
				$cache_accountid[$accountid] = $account_id;
			}
			$sql  = 'SELECT acl_appname, acl_rights from phpgw_acl ';
			$sql .= "where acl_location = '" . $this->db->db_addslashes($location) . "' ";
			$sql .= 'AND acl_account = ' . (int)$account_id;
			$this->db->query($sql ,__LINE__,__FILE__);
			$rights = 0;
			if ($this->db->num_rows() == 0 )
			{
				return False;
			}
			while ($this->db->next_record())
			{
				if ($this->db->f('acl_rights') == 0)
				{
					return False;
				}
				$rights |= $this->db->f('acl_rights');
				if (!!($rights & $required) == True)
				{
					$apps[] = $this->db->f('acl_appname');
				}
			}
			return $apps;
		}

		/*!
		@function get_location_list_for_id
		@abstract get location list for id
		@discussion ?
		@param $app app
		@param $required required
		@param $account_id optional defaults to $phpgw_info['user']['account_id'];
		*/
		function get_location_list_for_id($app, $required, $accountid = '')
		{
			static $cache_accountid;

			if($cache_accountid[$accountid])
			{
				$accountid = $cache_accountid[$accountid];
			}
			else
			{
				$accountid = $cache_accountid[$accountid] = get_account_id($accountid,$this->account_id);
			}
			$this->db->select($this->table_name,'acl_location,acl_rights',array(
				'acl_appname' => $app,
				'acl_account' => $accountid,
			),__LINE__,__FILE__);

			$locations = false;
			while ($this->db->next_record())
			{
				if ($this->db->f('acl_rights') & $required)
				{
					$locations[] = $this->db->f('acl_location');
				}
			}
			return $locations;
		}
		/*!
		@function get_ids_for_location
		@abstract get ids for location
		@param $location location
		@param $required required
		@param $app app optional defaults to $phpgw_info['flags']['currentapp'];
		*/
		function get_ids_for_location($location, $required, $app = False)
		{
			if ($app == False)
			{
				$app = GLOBALS['egw_info']['flags']['currentapp'];
			}
			$sql = "select acl_account, acl_rights from phpgw_acl where acl_appname = '$app' and ";
			$sql .= "acl_location = '".$location."'";
			$this->db->query($sql ,__LINE__,__FILE__);
			$rights = 0;
			if ($this->db->num_rows() == 0 )
			{
				return False;
			}
			while ($this->db->next_record())
			{
				$rights = 0;
				$rights |= $this->db->f('acl_rights');
				if (!!($rights & $required) == True)
				{
					$accounts[] = (int)$this->db->f('acl_account');
				}
			}
			@reset($accounts);
			return $accounts;
		}

		/*!
		@function get_user_applications
		@abstract get a list of applications a user has rights to
		@param $account_id optional defaults to $phpgw_info['user']['account_id'];
		@result $apps array containing list of apps
		*/
		function get_user_applications($accountid = '')
		{
			static $cache_accountid;

			if($cache_accountid[$accountid])
			{
				$account_id = $cache_accountid[$accountid];
			}
			else
			{
				$account_id = get_account_id($accountid,$this->account_id);
				$cache_accountid[$accountid] = $account_id;
			}
			$db2 = clone($this->db);
			$memberships = GLOBALS['egw']->accounts->membership($account_id);
			$sql = "select acl_appname, acl_rights from phpgw_acl where acl_location = 'run' and "
				. 'acl_account in ';
			$security = '('.$account_id;
			while($groups = @each($memberships))
			{
				$group = each($groups);
				$security .= ','.$group[1]['account_id'];
			}
			$security .= ')';
			$db2->query($sql . $security ,__LINE__,__FILE__);

			if ($db2->num_rows() == 0)
			{
				return False;
			}
			while ($db2->next_record())
			{
				if(isset($apps[$db2->f('acl_appname')]))
				{
					$rights = $apps[$db2->f('acl_appname')];
				}
				else
				{
					$rights = 0;
					$apps[$db2->f('acl_appname')] = 0;
				}
				$rights |= $db2->f('acl_rights');
				$apps[$db2->f('acl_appname')] |= $rights;
			}
			return $apps;
		}
		/*!
		@function get_grants
		@abstract ?
		@param $app optional defaults to $phpgw_info['flags']['currentapp'];
		*/
		function get_grants($app='')
		{
			$db2 = clone($this->db);

			if ($app=='')
			{
				$app = GLOBALS['egw_info']['flags']['currentapp'];
			}

			$sql = "select acl_account, acl_rights from phpgw_acl where acl_appname = '$app' and "
				. "acl_location in ";
			$security = "('". $this->account_id ."'";
			$myaccounts = CreateObject('phpgwapi.accounts');
			$my_memberships = $myaccounts->membership($this->account_id);
			unset($myaccounts);
			@reset($my_memberships);
			while($my_memberships && list($key,$group) = each($my_memberships))
			{
				$security .= ",'" . $group['account_id'] . "'";
			}
			$security .= ')';
			$db2->query($sql . $security ,__LINE__,__FILE__);
			$rights = 0;
			$accounts = Array();
			if ($db2->num_rows() == 0)
			{
				$grants[GLOBALS['egw_info']['user']['account_id']] = ~0;
				return $grants;
			}
			while ($db2->next_record())
			{
				$grantor = $db2->f('acl_account');
				$rights = $db2->f('acl_rights');

				if(!isset($accounts[$grantor]))
				// cache the group-members for performance
				{
					// if $grantor is a group, get its members
					$members = $this->get_ids_for_location($grantor,1,'phpgw_group');
					if(!$members)
					{
						$accounts[$grantor] = Array($grantor);
						$is_group[$grantor] = False;
					}
					else
					{
						$accounts[$grantor] = $members;
						$is_group[$grantor] = True;
					}
				}
				if(@$is_group[$grantor])
				{
					// Don't allow to override private!
					$rights &= (~ EGW_ACL_PRIVATE);
					if(!isset($grants[$grantor]))
					{
						$grants[$grantor] = 0;
					}
					$grants[$grantor] |= $rights;
					if(!!($rights & EGW_ACL_READ))
					{
						$grants[$grantor] |= EGW_ACL_READ;
					}
				}
				while(list($nul,$grantors) = each($accounts[$grantor]))
				{
					if(!isset($grants[$grantors]))
					{
						$grants[$grantors] = 0;
					}
					$grants[$grantors] |= $rights;
				}
				reset($accounts[$grantor]);
			}
			$grants[GLOBALS['egw_info']['user']['account_id']] = ~0;

			return $grants;
		}
		
		/**
		 * Deletes all ACL entries for an account (user or group)
		 *
		 * @param int $account_id acount-id
		 */
		function delete_account($account_id)
		{
			if ((int) $account_id)
			{
				$this->db->query('DELETE FROM phpgw_acl WHERE acl_account='.(int)$account_id,__LINE__,__FILE__);
				// delete all memberships in account_id (if it is a group)
				$this->db->query("DELETE FROM phpgw_acl WHERE acl_appname='phpgw_group' AND acl_location='".(int)$account_id."'",__LINE__,__FILE__);
			}
		}
	} //end of acl class
?>
