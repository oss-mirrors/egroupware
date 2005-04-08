<?php
   /*
   JiNN - Jinn is Not Nuke, a multi-user, multi-site CMS for eGroupWare
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

   class boacl 
   {

	  var $session;
	  var $sessionmanager;

	  var $site_object_id; //depreciated
	  var $site_object; 
	  var $site; 
	  var $local_bo;
	  var $so;
	  var $common;
	 
	  /* debugging vars set them in preferences */
	  var $debug_sql = false;
	  var $debug_site_arr =false;
	  var $debug_object_arr =false;

	  var $public_functions = Array(
		 'save_access_rights_object'=> True,
		 'save_access_rights_site'=> True
	  );

	  /*!
	  @function boacl constructor
	  */
	  function boacl()
	  {
		 $this->so = CreateObject('jinn.sojinn');
		 $this->common = CreateObject('jinn.bocommon');
		 $this->session 		= &$this->common->session->sessionarray;	//shortcut to session array
		 $this->sessionmanager	= &$this->common->session;					//shortcut to session manager object
		 $this->site_object_id	= $this->session['site_object_id'];//depreciated

		 /* this is for the sidebox */
		 global $local_bo;
		 $local_bo=$this;

		 /* do stuff for debugging */
		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			if($this->read_preferences('debug_sql')=='yes') $this->debug_sql=true;
			if($this->read_preferences('debug_site_arr')=='yes') 
			{
			   $this->session['message']['debug'][]='SITE_ARRAY: '._debug_array($this->site,false);
			}
			if($this->read_preferences('debug_object_arr')=='yes')
			{
			   $this->session['message']['debug'][]='OBJECT_ARRAY: '._debug_array($this->site_object,false);
			}
		 }
	  }

	  function save_sessiondata()
	  {
	 	$this->session['site_object_id'] 	= $this->site_object_id;	//depreciated
		
		$this->sessionmanager->save();
	  }

	  function read_preferences($key)
	  {
		 $GLOBALS['phpgw']->preferences->read_repository();

		 $prefs = array();

		 if ($GLOBALS['phpgw_info']['user']['preferences']['jinn'])
		 {
			$prefs = $GLOBALS['phpgw_info']['user']['preferences']['jinn'][$key];
		 }
		 return $prefs;
	  }
   function save_access_rights_site()
	  {
		 reset ($GLOBALS[HTTP_POST_VARS]);
		 $site_id=$GLOBALS[HTTP_POST_VARS]['site_id'];

		 while (list ($key, $val) = each ($GLOBALS[HTTP_POST_VARS])) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_site_access_rights($editors,$site_id);

		 if ($status[ret_code])
		 {
			$this->session['message'][error]=lang('Access rights for Site NOT succesfully saved');
			$this->session['message'][error_code]=113;
		 }
		 else
		 {
			$this->session['message'][info]=lang('Access rights for Site succesfully saved');
		 }

		 if($this->debug_sql==true)
		 {
			$this->session['message']['debug'][]='SQL: '.$status[sql];
		 }

		 $this->save_sessiondata();
		 $this->common->exit_and_open_screen('jinn.uiacl.main_screen');
	  }


	  function save_access_rights_object()
	  {
		 reset ($GLOBALS[HTTP_POST_VARS]);
		 $site_id=$GLOBALS[HTTP_POST_VARS]['site_id'];
		 $object_id=$GLOBALS[HTTP_POST_VARS]['object_id'];

		 while (list ($key, $val) = each ($GLOBALS[HTTP_POST_VARS])) 
		 {
			if (substr($key,0,6)=='editor')	$editors[]=$val;
		 }

		 if (is_array($editors)) $editors=array_unique($editors);

		 $status=$this->so->update_object_access_rights($editors,$GLOBALS[HTTP_POST_VARS]['object_id']);

		 if ($status[ret_code])
		 {
			$this->session['message'][error]=lang('Access rights for site-object NOT succesfully saved');
			$this->session['message'][error_code]=114;
		 }
		 else
		 {
			$this->session['message'][info]=lang('Access rights for site-object succesfully saved');
		 }
		 
		 if($this->debug_sql==true)
		 {
			$this->session['message']['debug'][]='SQL: '.$status[sql];
		 }

		 $this->save_sessiondata();

		 $this->common->exit_and_open_screen('jinn.uiacl.main_screen');
	  }

	  /*! 
	  @function get_sites_allowed
	  @abstract get sites which the user can administrate
	  @fixme maybe rename
	  */
	  function get_sites_to_admin($uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array ($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields[account_id];
			}
		 }

		 $user_sites=$this->so->get_sites_to_admin($uid,$group);
		 return $user_sites;
	  }

	  /*!
	  @function user_is_site_admin
	  @abstract checks is user is admin a site
	  @param int $site_id site_id
	  @param $uid if empty current user is used
	  @returns true is user is admin else returns false
	  */
	  function user_is_site_admin($site_id,$uid=false)
	  {
		 if(!$uid)
		 {
			$uid=$GLOBALS['phpgw_info']['user']['account_id'];
		 }

		 $sites=$this->get_sites_to_admin($uid);

		 if(in_array($site_id,$sites))
		 {
			return true;
		 }
		 else
		 {
			return false;
		 }
	  }

	 function has_object_access($object_id,$uid=false)
	 {
		if(!$uid)
		{
		   $uid=$GLOBALS['phpgw_info']['user']['account_id'];
		}

		$objects=$this->get_all_objects_allowed($uid);

//		_debug_array($objects);
//		echo $object_id;
		if(in_array($object_id,$objects))
		{
		   return true;
		}
		else
		{
		   return false;
		}
	 }

	 /*! 
	 @function get_all_objects_allowed 
	 @abstract get objects to which user has access too
	 */
	 function get_all_objects_allowed($uid)
	 {
		$groups=$GLOBALS['phpgw']->accounts->membership();

		if (is_array ($groups))
		{
		   foreach ( $groups as $groupfields )
		   {
			  $group[]=$groupfields[account_id];
		   }
		}

		$objects=$this->so->get_all_objects($uid,$group);

		$sites=$this->get_sites_to_admin($uid);

		foreach($sites as $site_id)
		{
		   $objects_from_sites=$this->get_objects_allowed_in_site($site_id,$uid);
		   $objects=array_merge($objects,$objects_from_sites);
		
		
		}

		return $objects;
	 }
	 
	 /*! 
	 @function get_objects_allowed 
	 @abstract get objects to which user has access too
	 @fixme move to boacl
	 */
	 function get_objects_allowed_in_site($site_id,$uid)
	 {
		$groups=$GLOBALS['phpgw']->accounts->membership();

		if (is_array($groups))
		{
		   foreach ( $groups as $groupfields )
		   {
			  $group[]=$groupfields[account_id];
		   }
		}

		$objects=$this->so->get_objects($site_id,$uid,$group);
		return $objects;
	 }


  }
