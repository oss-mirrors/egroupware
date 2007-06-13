<?php
   require_once(EGW_SERVER_ROOT.SEP.'jinn/plugins'.SEP.'db_fields_plugins'.SEP.'class.db_field_super.inc.php');
   include(EGW_SERVER_ROOT.SEP.'jinn/plugins'.SEP.'db_fields_plugins'.SEP.'__flvconvertclient'.SEP.'xmlrpcutils.php');

   /**
   * db_fields_plugin_flvconvertclient 
   * 
   * @package dbfieldplugins
   * @version $Id$
   * @copyright Lingewoud B.V.
   * @author Pim Snel <pim-AT-lingewoud-DOT-nl> 
   * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
   */
   class db_fields_plugin_flvconvertclient extends db_field_super
   {
	  function formview_edit($field_name, $value, $config,$attr_arr,$record_values)
	  {
		 $this->_initVars($config);

		 $req=$this->_requirementsCheck($config);
		 if($req)
		 {
			return $req;
		 }

		 $prefix=substr($field_name,0,6);

		 $this->tplsav2->assign('sourcefield',$prefix.'_FM__IMG_ORG_'.$config['source_movie_field']);
		 $this->tplsav2->assign('fmsourcefield',$prefix.'_FM__IMG_EDIT_'.$config['source_movie_field'].'1');

//		 MLTX00_FM__IMG_ORG_movie_source
		 $widget=$this->tplsav2->fetch('flvconvertedit.tpl.php');
		 return $widget . $this->get_status_lang($value);
	  }


	  function listview_read($value, $config,$attr_arr)
	  {
		 return $this->get_status_lang($value);
	  }

	  function xxxon_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 //
	  }

	  function get_status_lang($value)
	  {
		 switch($value)
		 {
			case FLVCC_STAT_NOT_STARTED: return lang('Not started');
			case FLVCC_STAT_BUSY_COMMAND_SEND: return lang('Busy Command Send');
			case FLVCC_STAT_BUSY_CONVERTING: return lang('Busy Converting');
			case FLVCC_STAT_BUSY_INJECTING_META: return lang('Busy Injecting Meta Data');
			case FLVCC_STAT_BUSY_PUSHING_FILE: return lang('Busy Retrieving File');
			case FLVCC_STAT_FINISHED: return lang('Finished');
			case FLVCC_STAT_ERROR: return lang('An error occured');
			default:
		 }
	  }

	  function _initVars($config)
	  {
		 define('FLVCC_STAT_NOT_STARTED',0);
		 define('FLVCC_STAT_COMMAND_SEND',2);
		 define('FLVCC_STAT_BUSY_CONVERTING',3);
		 define('FLVCC_STAT_BUSY_INJECTING_META',6);
		 define('FLVCC_STAT_BUSY_PUSHING_FILE',9);
		 define('FLVCC_STAT_FINISHED',12);
		 define('FLVCC_STAT_ERROR',15);

		 if($config['subdirsource'])
		 {
			$extrasubdir=SEP.$config['subdirsource'];
		 }
		 $this->upload_urlsource=$this->local_bo->cur_upload_url().$extrasubdirsource;
		 $this->upload_pathsource=$this->local_bo->cur_upload_path().$extrasubdirsource;

		 if($config['subdirdest'])
		 {
			$extrasubdir=SEP.$config['subdirdest'];
		 }
		 $this->upload_urldest=$this->local_bo->cur_upload_url().$extrasubdirsource;
		 $this->upload_pathdest=$this->local_bo->cur_upload_path().$extrasubdirsource;

	  }

	  function _requirementsCheck($config)
	  {
		 if(!is_dir($this->upload_pathsource))
		 {
			mkdir($this->upload_pathsource);
		 }
		 if(!is_dir($this->upload_pathdest))
		 {
			mkdir($this->upload_pathdest);
		 }
		 if(!$config['source_movie_field'])
		 {
			return lang('No source field configured. Please check field plugin configuration.');
		 }
	  }
   }

   class ajax_db_fields_plugin_flvconvertclient
   {
	  function helloWorld($yourName)
	  {
		 $response = new xajaxResponse();
		 //$response->addAlert('Hello World '.$yourName);
		 return $response->getXML();
	  }

	  function addToQueue($movieurl)
	  {
		 $response = new xajaxResponse();

		 $host = "localhost";
		 $uri = "/projects/pim/flvconvert/server.php";

		 $result = xu_rpc_http_concise(
			array(
			   'method' => "addToQueue",
			   'args'  => array($movieurl),
			   'host'  => $host,
			   'uri'  => $uri,
			   'port'  => 80
			)
		 );
		 $response->addAlert($result);

		  $result = xu_rpc_http_concise(
			array(
			   'method' => "greeting",
			   'args'  => array($movieurl),
			   'host'  => $host,
			   'uri'  => $uri,
			   'port'  => 80
			)
		 );
		 
		 $response->addAlert('Hello World '.$result);
		 //print $result;

		 return $response->getXML();
	  }
   }
