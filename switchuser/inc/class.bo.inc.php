<?php
   /**************************************************************************\
   * eGroupWare - switchuser Application                                        *
   * http://www.egroupware.org                                                *
   * -----------------------------------------------                          *
   *  This program is free software; you can redistribute it and/or modify it *
   *  under the terms of the GNU General Public License as published by the   *
   *  Free Software Foundation; either version 2 of the License, or (at your  *
   *  option) any later version.                                              *
   \**************************************************************************/

   class bo
   {
	  var $so;

	  var $debug = false;

	  var $sessionid;
	  var $kp3;
	  var $key;
	  var $iv;

	  var $public_functions = array(
		 'switchfrompost'	=> true,
	  );

	  function bo($session = false)
	  {
		 $GLOBALS['phpgw']->session = CreateObject('phpgwapi.sessions');
	  }


	  function admincheck()
	  {
		 if(!$GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			$noadminlink='/index.php';
			$noadminvars='menuaction=switchuser.ui.nopermission';
			$GLOBALS['phpgw']->redirect_link($noadminlink,$noadminvars);

			$GLOBALS['phpgw']->common->phpgw_exit();
		 }

	  }

	  function switchfrompost()
	  {

		 $this->admincheck();

		 $GLOBALS['sessionid'] = $this->create($_POST[newuser],'u');

		 $this->account_domain	= '';// set to current domain

		 if ($_POST['lang'] && preg_match('/^[a-z]{2}(-[a-z]{2}){0,1}$/',$_POST['lang']) &&
		 $_POST['lang'] != $GLOBALS['phpgw_info']['user']['preferences']['common']['lang'])
		 {
			$GLOBALS['phpgw']->preferences->add('common','lang',$_POST['lang'],'session');
		 }

		 if(!$GLOBALS['phpgw_info']['server']['disable_autoload_langfiles'])
		 {
			$GLOBALS['phpgw']->translation->autoload_changed_langfiles();
		 }
		 $forward = isset($_GET['phpgw_forward']) ? urldecode($_GET['phpgw_forward']) : @$_POST['phpgw_forward'];
		 if (!$forward)
		 {
			$extra_vars['cd'] = 'yes';
			$forward = '/home.php';
		 }
		 else
		 {
			list($forward,$extra_vars) = explode('?',$forward,2);
		 }

		 $GLOBALS['phpgw']->redirect_link($forward,$extra_vars);




	  }

	  /**
	  * Create a new session
	  *
	  * @param string $login user login
	  * @return string session id
	  */
	  function create($login)
	  {
		 if (is_array($login))
		 {
			$this->login       = $login['login'];
			$this->passwd      = $login['passwd'];
			$this->passwd_type = $login['passwd_type'];
			$login             = $this->login;
		 }
		 else
		 {
			$this->login       = $login;
			$this->passwd      = $passwd;
			$this->passwd_type = $passwd_type;
		 }

		 $this->account_lid='u';
		 $now = time();

		 $user_ip = $GLOBALS['phpgw']->session->getuser_ip();

		 $this->account_id = $GLOBALS['phpgw']->accounts->name2id($this->account_lid);
		 $GLOBALS['phpgw_info']['user']['account_id'] = $this->account_id;
		 $GLOBALS['phpgw']->accounts->accounts($this->account_id);
		 $this->sessionid = $GLOBALS['phpgw']->session->new_session_id();
		 $this->kp3       = md5($GLOBALS['phpgw']->common->randomstring(15));

		 if ($GLOBALS['phpgw_info']['server']['usecookies'])
		 {
			$GLOBALS['phpgw']->session->phpgw_setcookie('sessionid',$this->sessionid);
			$GLOBALS['phpgw']->session->phpgw_setcookie('kp3',$this->kp3);
			$GLOBALS['phpgw']->session->phpgw_setcookie('domain',$this->account_domain);
		 }
		 if ($GLOBALS['phpgw_info']['server']['usecookies'] || isset($_COOKIE['last_loginid']))
		 { 
			$GLOBALS['phpgw']->session->phpgw_setcookie('last_loginid', $this->account_lid ,$now+1209600); /* For 2 weeks */
			$GLOBALS['phpgw']->session->phpgw_setcookie('last_domain',$this->account_domain,$now+1209600);
		 }
		 unset($GLOBALS['phpgw_info']['server']['default_domain']); /* we kill this for security reasons */

		 /* init the crypto object */
		 $this->key = md5($this->kp3 . $this->sessionid . $GLOBALS['phpgw_info']['server']['encryptkey']);
		 $this->iv  = $GLOBALS['phpgw_info']['server']['mcrypt_iv'];
		 $GLOBALS['phpgw']->crypto->init(array($this->key,$this->iv));

		 $GLOBALS['phpgw']->session->read_repositories(False); // pim mod
		 if ($GLOBALS['phpgw']->session->user['expires'] != -1 && $$GLOBALS['phpgw']->session->user['expires'] < time())
		 {
			if(is_object($GLOBALS['phpgw']->log))
			{
			   $GLOBALS['phpgw']->log->message(array(
				  'text' => 'W-LoginFailure, account loginid %1 is expired',
				  'p1'   => $this->account_lid,
				  'line' => __LINE__,
				  'file' => __FILE__
			   ));
			   $GLOBALS['phpgw']->log->commit();
			}
			$this->reason = 'account is expired';
			$this->cd_reason = 98;

			return False;
		 }

		 $GLOBALS['phpgw_info']['user']  = $GLOBALS['phpgw']->session->user;
		 $GLOBALS['phpgw_info']['hooks'] = $GLOBALS['phpgw']->session->hooks;

		 $GLOBALS['phpgw']->db->transaction_begin();
		 $GLOBALS['phpgw']->session->register_session($login,$user_ip,$now,$session_flags);
		 if ($session_flags != 'A')		// dont log anonymous sessions
		 {
			$GLOBALS['phpgw']->session->log_access($this->sessionid,$login,$user_ip,$this->account_id);
		 }
		 $GLOBALS['phpgw']->session->appsession('account_previous_login','phpgwapi',$GLOBALS['phpgw']->auth->previous_login);
		 $GLOBALS['phpgw']->auth->update_lastlogin($this->account_id,$user_ip);
		 $GLOBALS['phpgw']->db->transaction_commit();

		 return $this->sessionid;
	  }
   }
?>
