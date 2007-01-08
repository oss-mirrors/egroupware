<?php

   /**
   * bojinn 
   * 
   * @package 
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class bojinn
   {
	  var $so;
	  var $session;
	  var $sessionmanager;

	  var $site_object; 
	  var $site; 
	  var $local_bo;
	  var $magick;

	  var $plug;
	  var $object_events_plugin_manager;

	  var $current_config;
	  var $action;
	  var $common;

	  var $repeat_input;
	  var $where_key;
	  var $where_value;
	  var $where_string;

	  /* debugging vars set them in preferences */
	  var $debug_sql = false;
	  var $debug_session = false;
	  var $debug_site_arr =false;
	  var $debug_object_arr =false;

	  var $bcompat;

	  var $objectelements = array();

	  /**
	  * bojinn 
	  * 
	  * @access public
	  * @return void
	  */
	  function bojinn($session_name='jinnitself')
	  {
		 $this->so = CreateObject('jinn.sojinn');

		 $this->sessionmanager = CreateObject('jinn.sojinnsession',$session_name);
		 $this->session	= & $this->sessionmanager->sessionarray;	//reference to session array

		 $this->set_site_and_object();

		 //_debug_array($this->session);

		 $this->prefs = $this->read_preferences_all();

		 $this->current_config=$this->get_config();		

		 $this->bcompat = CreateObject('jinn.backwards_compat.inc.php');

		 $this->magick = CreateObject('jinn.boimagemagick.inc.php');	
		 $this->magick->imagemagickdir=$this->current_config['imagemagickdir'];

		 /* do stuff for debugging */
		 if($GLOBALS['phpgw_info']['user']['apps']['admin'])
		 {
			if($this->read_preferences('debug_sql')=='yes') $this->debug_sql=true;
		 }

		 $this->site_fs = createObject('jinn.site_fs');

		 $this->set_activated_object_elements();

	  }

	  function reset_site_and_object($site_id=null,$object_id=null)
	  {
		 $this->session['site_id'] = $site_id;
		 $this->session['site_object_id']=$object_id;
		 //_debug_array($this->session);
		 $this->sessionmanager->save();

		 $this->set_site_and_object(); 
	  }

	  function set_site_and_object()
	  {
		 if($this->session['site_id'])
		 {
			$this->site = $this->so->get_site_values($this->session['site_id']);

			if ($this->session['site_object_id'])
			{
			   $this->site_object = $this->so->get_object_values($this->session['site_object_id']);
			}
		 }
		 else
		 {
			unset($this->session);
			$this->sessionmanager->save();
		 }
	  }


	  /**
	  * read_preferences 
	  * 
	  * @param mixed $key 
	  * @access public
	  * @return void
	  */
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

	  /**
	  * save_preferences 
	  * 
	  * @param mixed $key 
	  * @param mixed $prefs 
	  * @access public
	  * @return void
	  */
	  function save_preferences($key,$prefs)
	  {
		 $GLOBALS['phpgw']->preferences->read_repository();

		 $GLOBALS['phpgw']->preferences->change('jinn',$key,$prefs);
		 $GLOBALS['phpgw']->preferences->save_repository(True);
	  }

	  /**
	  * read_preferences_all 
	  * 
	  * @access public
	  * @return void
	  */
	  function read_preferences_all()
	  {
		 $GLOBALS['phpgw']->preferences->read_repository();

		 $prefs = array();

		 if ($GLOBALS['phpgw_info']['user']['preferences']['jinn'])
		 {
			$prefs = $GLOBALS['phpgw_info']['user']['preferences']['jinn'];
		 }
		 return $prefs;
	  }

	  /**
	  * addError 
	  * 
	  * @param mixed $msg 
	  * @access public
	  * @return void
	  */
	  function addError($msg)
	  {
		 //	 $error['msg']=$msg;
		 $this->session['message']['error'][]=$msg;
		 $this->sessionmanager->save();
	  }

	  /**
	  * addInfo 
	  * 
	  * @param mixed $msg 
	  * @access public
	  * @return void
	  */
	  function addInfo($msg)
	  {
		 $this->session['message']['info'][]=$msg;
		 $this->sessionmanager->save();
	  }

	  /**
	  * addHelp 
	  * 
	  * @param mixed $msg 
	  * @access public
	  * @return void
	  */
	  function addHelp($msg)
	  {
		 $this->session['message']['help'][]=$msg;
		 $this->sessionmanager->save();
	  }

	  /**
	  * addDebug 
	  * 
	  * @param mixed $line 
	  * @param mixed $file 
	  * @param mixed $sql 
	  * @param mixed $other 
	  * @access public
	  * @return void
	  */
	  function addDebug($line,$file,$sql=null,$other=null)
	  {
		 if($this->debug_sql==true)
		 {
			$debug['sql']=$sql;
			$debug['other']=$other;
			$debug['line']=$line;
			$debug['file']=$file;
			$debug['session']=_debug_array($this->session,false);
			$debug['post']=_debug_array($_POST,false);
			$debug['get']=_debug_array($_GET,false);
			$debug['site_arr']=_debug_array($this->site,false);
			$debug['object_arr']=_debug_array($this->site_object,false);

			$this->session['message']['debug'][]=$debug;
		 }
		 $this->sessionmanager->save();
	  }

	  /**
	  * exit_and_open_screen: exit and redirect within session
	  * 
	  * @param string $menu_action phpgw link to function in class
	  * @access public
	  * @return void
	  */
	  function exit_and_open_screen($menu_action)
	  {
		 $this->sessionmanager->save();
		 //		 Header('Location: '.$GLOBALS['phpgw']->link('/index.php','menuaction='.$menu_action));
		 $this->redirect_rel($GLOBALS['phpgw']->link('/index.php','menuaction='.$menu_action));
		 $GLOBALS['phpgw']->common->phpgw_exit();
	  }

	  function redirect_rel($url)
	  {
		 if (!headers_sent())
		 {
			header("Location: $url");
		 }
		 else
		 {
			echo "<meta http-equiv=\"refresh\" content=\"0;url=$url\">\r\n";
		 }
	  }

	  /**
	  * format_date: format timestamp date to europian format 
	  * 
	  * @fixme replace with a general egwapi function
	  * @param mixed $input 
	  * @access public
	  * @return void
	  */
	  function format_date($input)
	  {
		 // Deze functie converteert bv. 200124061216  naar:  24-06-2001 12:16
		 $jaar = substr($input,0,4);
		 $maand = substr($input,4,2);
		 $dag = substr($input,6,2);
		 $uren = substr($input,8,2);
		 $minuten = substr($input,10,2);
		 return("$dag-$maand-$jaar $uren:$minuten");
	  }

	  /**
	  * get_config: return egw configuration setting for JiNN
	  * 
	  * @access public
	  * @return void
	  */
	  function get_config()
	  {
		 $c = CreateObject('phpgwapi.config',$config_appname);

		 $c->read_repository();

		 if ($c->config_data)
		 {
			return $c->config_data;
		 }
	  }

	  /**
	  * sql_data_pairs: this function will replace or support all http_post_vars variaties
	  * 
	  * @param array $post_arr e.g. $_POST
	  * @param string $prefix optional
	  * @access public
	  * @return array prepared to use in sojinn
	  */
	  function sql_data_pairs($post_arr,$prefix='') 
	  {
		 while(list($key, $val) = each($post_arr)) 
		 {
			if (substr($key,0,strlen($prefix))==$prefix)
			{
			   $data[] = array
			   (
				  'name' => substr($key,strlen($prefix)),
				  'value' => addslashes($val) 
			   );
			}
		 }

		 return $data;
	  }




	  /**
	  * strip_prefix_from_keys: strips of a given prefix from all keys in an array
	  * 
	  * @param array $array containing the keys
	  * @param string $prefix to strip
	  * @access public
	  * @return array stripped array
	  */
	  function strip_prefix_from_keys($array,$prefix)
	  {
		 if(!is_array($array))
		 {
			return;
		 }

		 while (list ($key, $val) = each ($array)) 
		 {
			$new_key=substr($key,strlen($prefix));
			$return_array[$new_key]=$val;
		 }
		 return $return_array;

	  }

	  /**
	  * filter_array_with_prefix: this function can return a new array 
	  *
	  * with only elements that match the prefix. 
	  *
	  * @param array $array to filter
	  * @param string $prefix to use
	  * @param boolean $keep_keys when true it keeps the keys else numeric keys are used
	  * @param boolean $inverse if true the only elements that not match the prefix are returned
	  * @return array filtered array
	  * @fixme inverse seem to work focked up!!! check trhough all the jinn code
	  */
	  function filter_array_with_prefix($array,$prefix,$keep_keys=false,$inverse=false)
	  {
		 if(!is_array($array))
		 {
			return;
		 }
		 while (list ($key, $val) = each ($array)) 
		 {
			if($inverse)
			{
			   if (substr($key,0,strlen($prefix))!=$prefix)
			   {
				  if($keep_keys)
				  {
					 $return_array[$key]=$val;
				  }
				  else
				  {
					 $return_array[]=$val;
				  }
			   }
			}
			else
			{
			   if (substr($key,0,strlen($prefix))==$prefix)
			   {
				  if($keep_keys)
				  {
					 $return_array[$key]=$val;
				  }
				  else
				  {
					 $return_array[]=$val;
				  }
			   }
			}
		 }
		 return $return_array;
	  }


	  /**
	  * get_global_var: Function to retrieve a global get or post var where get overrules post
	  * 
	  * @fixme remove this
	  * @param mixed $name 
	  * @param string $priority 
	  * @access public
	  * @return void
	  */
	  function get_global_var($name,$priority='get')
	  {
		 if($priority=='post')
		 {
			$tmp_var=($_POST[$name]?$_POST[$name]:$_GET[$name]);
		 }
		 else
		 {
			$tmp_var=($_GET[$name]?$_GET[$name]:$_POST[$name]);
		 }

		 if($tmp_var)
		 {
			return $tmp_var;
		 }
		 else
		 {
			return false;
		 }
	  }

	  /**
	  * get_global_vars 
	  * 
	  * @fixme remove this
	  * @param mixed $name_arr 
	  * @param string $priority 
	  * @access public
	  * @return void
	  */
	  function get_global_vars($name_arr,$priority='get')
	  {
		 if(is_array($name_arr))
		 {
			foreach($name_arr as $name)
			{
			   $tmp_arr[]=$this->get_global_var($name,$priority);
			}
			return $tmp_arr;
		 }
	  }

	  /**
	  * check_safe_mode 
	  * 
	  * @access public
	  * @return void
	  */
	  function check_safe_mode()
	  {
		 if (ini_get('safe_mode'))
		 {
			$safe_mode='On';
		 }
		 else
		 {
			$safe_mode='Off';
		 }
		 return $safe_mode;
	  }

	  /**
	  * get_sites_allowed: get sites to which user has access too
	  * 
	  * @fixme	move to boacl
	  * @fixme maybe rename
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function get_sites_allowed($uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields['account_id'];
			}
		 }

		 $user_sites=$this->so->get_sites_for_user($uid,$group);
		 return $user_sites;
	  }

	  /**
	  * get_objects_allowed: get objects to which user has access too
	  * 
	  * @fixme move to boacl
	  * @param mixed $site_id 
	  * @param mixed $uid 
	  * @access public
	  * @return void
	  */
	  function get_objects_allowed($site_id,$uid)
	  {
		 $groups=$GLOBALS['phpgw']->accounts->membership();

		 if (is_array($groups))
		 {
			foreach ( $groups as $groupfields )
			{
			   $group[]=$groupfields['account_id'];
			}
		 }

		 $objects=$this->so->get_objects($site_id,$uid,$group);
		 return $objects;
	  }

	  /**
	  * addmsg: add message to the session array
	  * 
	  * @param mixed $msg_string 
	  * @param string $type 
	  * @param string $error_code 
	  * @access public
	  * @return void
	  */
	  function addmsg($msg_string,$type='info',$error_code='')
	  {
		 if(!is_array($this->sessionmanager->sessionarray['message'][$type]))
		 {
			$this->sessionmanager->sessionarray['message'][$type]= array();
		 }
		 $this->sessionmanager->sessionarray['message'][$type][]=$msg_string;
	  }

	  /**
	  * clearmsg: empty all messages
	  * 
	  * @access public
	  * @return void
	  */
	  function clearmsg()
	  {
		 $this->sessionmanager->sessionarray['message']=array();
	  }

	  function set_activated_object_elements()
	  {

		 if(!$this->site_object['disable_filters']) // ready for ACL
		 {
			$this->objectelements['enable_filters']=true;
		 }
		 if(!$this->site_object['disable_simple_search']) // ready for ACL
		 {
			$this->objectelements['enable_simple_search']=true;
		 }

		 if(!$this->site_object['disable_reports']) // ready for ACL
		 {
			$this->objectelements['enable_reports']=true;
		 }

		 if(!$this->site_object['disable_create_rec']) // ready for ACL
		 {
			$this->objectelements['enable_create_rec']=true;
		 }

		 if(!$this->site_object['disable_del_rec']) // ready for ACL
		 {
			$this->objectelements['enable_del']=true;
		 }

		 if(!$this->site_object['disable_edit_rec']) // ready for ACL
		 {
			$this->objectelements['enable_edit_rec']=true;
		 }

		 if(!$this->site_object['disable_import']) // ready for ACL
		 {
			$this->objectelements['enable_import']=true;
		 }

		 if(!$this->site_object['disable_export']) // ready for ACL
		 {
			$this->objectelements['enable_export']=true;
		 }

		 if(!$this->site_object['disable_view_rec']) // ready for ACL
		 {
			$this->objectelements['enable_view_rec']=true;
		 }

		 if(!$this->site_object['disable_copy_rec']) // ready for ACL
		 {
			$this->objectelements['enable_copy_rec']=true;
		 }

		 if(!$this->site_object['disable_multi']) // ready for ACL
		 {
			$this->objectelements['enable_multi']=true;
		 }
	  }



   }


?>
