<?php
   require_once(EGW_SERVER_ROOT.SEP.'jinn/plugins'.SEP.'db_fields_plugins'.SEP.'class.db_field_super.inc.php');
   include(EGW_SERVER_ROOT.SEP.'jinn/plugins'.SEP.'db_fields_plugins'.SEP.'__flvconvertclient'.SEP.'xmlrpcutils.php');

   define('FLVCC_STAT_NOT_STARTED',0);
   define('FLVCC_STAT_QUEUED',1);
   define('FLVCC_STAT_BUSY_RETRIEVING',2);
   define('FLVCC_STAT_BUSY_CONVERTING',4);
   define('FLVCC_STAT_BUSY_INJECTING_META',6);
   define('FLVCC_STAT_READY_FOR_SENDING',8);
   define('FLVCC_STAT_BUSY_SENDING',9);
   define('FLVCC_STAT_FINISHED',12);
   define('FLVCC_STAT_ERROR',15);
   define('FLVCC_STAT_CANCELED',20);
   function get_status_lang($value)
   {
	  switch($value)
	  {
		 case FLVCC_STAT_NOT_STARTED: return $value.lang('Not started');break;
		 case FLVCC_STAT_QUEUED: return lang('TASK Queued');break;
		 case FLVCC_STAT_BUSY_CONVERTING: return lang('Busy Converting');break;
		 case FLVCC_STAT_BUSY_INJECTING_META: return lang('Busy Injecting Meta Data');break;
		 case FLVCC_STAT_BUSY_PUSHING_FILE: return lang('Busy Retrieving File');break;
		 case FLVCC_STAT_FINISHED: return lang('Finished');break;
		 case FLVCC_STAT_ERROR: return lang('An error occured');break;
		 default:
	  }
   }

   /* 
   todo handle not possible double fields of flvconvertclient
   todo make dl url complete
   todo remove redundant code
   */

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
		 /*
		 $wherestring_enc='';
		 $field='';
		 $object_id='';
		 */
//		 $debug=_debug_array($this->makeSerializedRecordFieldInfo($field_name,$config),false);

		 $this->_initVars($config);

		 $req=$this->_requirementsCheck($config);
		 if($req)
		 {
			return $req;
		 }

		 $prefix=$this->getFieldPrefix($field_name);

		 $this->tplsav2->assign('recordfieldinfo',$this->makeSerializedRecordFieldInfo($field_name,$config));
		 $this->tplsav2->assign('metafieldname',$prefix.$config['meta_field']);

		 $this->tplsav2->assign('sourcefield',$prefix.'_FM__IMG_ORG_'.$config['source_movie_field']);
		 $this->tplsav2->assign('fmsourcefield',$prefix.'_FM__IMG_EDIT_'.$config['source_movie_field'].'1');

		 $widget=$this->tplsav2->fetch('flvconvertedit.tpl.php');
		 return $debug.$widget ;//. $this->get_status_lang($value);
	  }


	  function listview_read($value, $config,$attr_arr)
	  {
		 //return $this->get_status_lang($value);
	  }

	  function xxxon_save_filter($key, $HTTP_POST_VARS,$HTTP_POST_FILES,$config)
	  {
		 //
	  }


	  function _initVars($config)
	  {

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
	  var $host = "localhost"; 
	  var $uri = null;

	  function ajax_db_fields_plugin_flvconvertclient()
	  {
		 $this->response = new xajaxResponse();
	  }

	  function _init($recordFieldInfoPacked)
	  {
		 $recordFieldInfo=unserialize(base64_decode($recordFieldInfoPacked));

		 $this->host = $recordFieldInfo['field_config']['server'];
		 $this->url = $recordFieldInfo['field_config']['url'];
		 $this->meta_field_id = $recordFieldInfo['prefix'].$recordFieldInfo['field_config']['meta_field'];
	  }

	  function fixmetaid()
	  {
		 $this->response->addScriptCall("setIdToMetaField");
		 return $this->response->getXML();
	  }

	  function updateInfoField($msg,$type='info')
	  {
		 $this->response->addScriptCall("showMessage", $msg, $type );
	  }

	  function getStatus($recordFieldInfoPacked,$metafieldinfo)
	  {
		 $this->_init($recordFieldInfoPacked);

		 $meta_data_arr = unserialize(base64_decode($metafieldinfo));

		 $result = xu_rpc_http_concise(
			array(
			   'method' => "getStatus",
			   'args'  => array($meta_data_arr['queueid']),
			   'host'  => $this->host,
			   'uri'  => $this->uri,
			   'port'  => 31313
			)
		 );

		 if($result)
		 {
			$this->updateInfoField(lang('Current Status: ').get_status_lang($result));
		 }
		 else
		 {
			$this->updateInfoField(lang('Could not communicate with ANY2FLV Server'));
		 }

		 return $this->response->getXML();
	  }

	  function addToQueue($recordFieldInfoPacked,$movieurl)
	  {
		 $this->_init($recordFieldInfoPacked);

		 $queueid = xu_rpc_http_concise(
			array(
			   'method' => "addToQueue",
			   'args'  => array($movieurl),
			   'host'  => $this->host,
			   'uri'  => $this->uri,
			   'port'  => 31313
			)
		 );

		 $this->meta_data_arr['queueid'] = $queueid;
		 if($queueid)
		 {
			$this->updateInfoField(lang('Conversion started.').$queueid);
			$this->response->addAssign($this->meta_field_id,"value",base64_encode(serialize($this->meta_data_arr)));
		 }
		 else
		 {
			$this->updateInfoField(lang('Could not communicate with ANY2FLV Server'));
		 }

		 return $this->response->getXML();
	  }
   }
