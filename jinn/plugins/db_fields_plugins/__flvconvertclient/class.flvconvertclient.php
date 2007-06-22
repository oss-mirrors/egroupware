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

   /**
   * pathCmp compare url and filesystem path for unique and shared straing parts
   * 
   * @param string $url 
   * @param string $fspath 
   * @access public
   * @return array with 3 strings
   */
   function pathCmp($url,$fspath)
   {
	  $url=$this->local_bo->cur_upload_url().$extrasubdirsource;
	  $path=$this->local_bo->cur_upload_path().$extrasubdirsource;

	  $rurl=strrev($url);
	  $rpath=strrev($path);

	  $x=0;
	  while($cmp==0)
	  {
		 $x++;

		 $cmp=substr_compare($rurl, $rpath, 0, $x); // 0
		 if($cmp==0)
		 {
			$shared_path=substr($rurl,0,$x);
		 }
	  }
	  $ret['shared_path']= strrev($shared_path);

	  $ret['fspathbase']=substr($path,'0',strlen($path)-strlen($shared_path));
	  $ret['urlbase']=substr($url,'0',strlen($url)-strlen($shared_path));
	  return $ret;

	  /*		 echo $path.'<br/>';
	  echo $url.'<br/>';
	  echo '====<br/>';
	  echo $shared_path.'<br/>';
	  echo $fspathbase.'<br/>';
	  echo $urlbase.'<br/>';*/
   }


   function get_status_lang($value)
   {
	  switch($value)
	  {
		 case FLVCC_STAT_NOT_STARTED: return $value.lang('Not started');break;
		 case FLVCC_STAT_QUEUED: return lang('TASK Queued');break;
		 case FLVCC_STAT_BUSY_CONVERTING: return lang('Busy Converting');break;
		 case FLVCC_STAT_BUSY_INJECTING_META: return lang('Busy Injecting Meta Data');break;
		 case FLVCC_STAT_READY_FOR_SENDING: return lang('Ready for Sending');break;
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
		 //$this->pathCmp();
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
		 $this->tplsav2->assign('field_name',$field_name);

		 if(!$value)
		 {
			$this->tplsav2->assign('displayplayer','none');	
		 }
		 else
		 {
			$this->tplsav2->assign('displayplayer','block');	
		 }

		 $this->tplsav2->assign('fvalue',$value);

		 $this->tplsav2->assign('sourcefield',$prefix.'_FM__IMG_ORG_'.$config['source_movie_field']);
		 $this->tplsav2->assign('fmsourcefield',$prefix.'_FM__IMG_EDIT_'.$config['source_movie_field'].'1');

		 $this->tplsav2->assign('movieurl',$this->upload_urlsource);

		 $media_arr['display_menu']='false';
		 $media_arr['quality']='best';
		 $media_arr['scale']='noscale';
		 $media_arr['window_mode']='transparent';
		 $media_arr['width']=360;
		 $media_arr['height']=240;
		 $media_arr['do_loop']='false';
		 $this->tplsav2->assign('media_arr',$media_arr);

		 $querystring='_vidURL='.$this->upload_urlsource.'/'.$value.'&_phpURL=http://xi.lingewoud.nl/projects/pim/egwpbltrunk/egroupware/jinn/plugins/db_fields_plugins/__flvconvertclient/';
		 $this->tplsav2->assign('querystring',$querystring);



		 $widget=$this->tplsav2->fetch('flvconvertedit.tpl.php');
		 return $debug.$widget ;//. $this->get_status_lang($value);
	  }

	  function listview_read($value, $config,$attr_arr)
	  {
		 $imgiconsrc=$GLOBALS['phpgw']->common->image('jinn','imageicon');
		 $stripped_name=substr($field_name,6);	

		 $upload_url =$this->local_bo->cur_upload_url ();
		 $upload_path=$this->local_bo->cur_upload_path();

		 /* if value is set, show existing images */	
		 if($value)
		 {
			$value=explode(';',$value);

			/* there are more images */
			if (is_array($value))
			{
			   $i=0;
			   foreach($value as $file_path)
			   {
				  $i++;

				  unset($imglink); 
				  unset($popup); 

				  /* check for image and create previewlink */
				  if(is_file($upload_path . SEP . $file_path))
				  {
					 $imglink=$GLOBALS['phpgw']->link('/index.php','menuaction=jinn.uiuser.file_download&file='.$upload_path.SEP.$file_path);

					 // FIXME move code to class
					 $image_size=getimagesize($upload_path . SEP. $file_path);
					 $pop_width = ($image_size[0]+50);
					 $pop_height = ($image_size[1]+50);

					 $popup = "img_popup('".base64_encode($imglink)."','$pop_width','$pop_height');";
				  }

				  unset($thumblink); 

				  $path_array = explode('/', $file_path);
				  $path_array[count($path_array)-1] = '..'.$path_array[count($path_array)-1];
				  $thumb_path = implode('/', $path_array);

				  /* check for thumb and create previewlink */
				  if(is_file($upload_path . SEP . $thumb_path))
				  {
					 $thumblink='<img src="'.$upload_url . SEP . $thumb_path.'" alt="'.$i.'">';
				  }
				  else
				  {
					 $thumblink='<img src="'.$imgiconsrc.'" alt="'.$i.'">';
				  }

				  if($imglink) $display.='<a href="'.$imglink.'">'.$thumblink.'</a>';
				  else $display.=' '.$thumblink;
				  $display.=' ';

			   }
			}
		 }

		 return $display;
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

		 $this->destdir=$recordFieldInfo['field_config']['subdirdest'];

		 $upload_path = $this->local_bo->cur_upload_path();
		 $this->upload_url = $this->local_bo->cur_upload_url();
		 $this->destpath=$upload_path.SEP.$recordFieldInfo['field_config']['subdirdest'];
		 $this->desturl=$this->upload_url.SEP.$recordFieldInfo['field_config']['subdirdest'];

		 $this->host = $recordFieldInfo['field_config']['server'];
		 $this->url = $recordFieldInfo['field_config']['url'];
		 $this->port = $recordFieldInfo['field_config']['port'];
		 $this->meta_field_id = $recordFieldInfo['prefix'].$recordFieldInfo['field_config']['meta_field'];
		 $this->field_name = $recordFieldInfo['field_name'];
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
		 if($this->checkServerAvailability())
		 {
			return $this->response->getXML();
		 }

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

		 $this->response->addAlert(_debug_array(unserialize(base64_decode($result['extra_info'])),false));
		 if($result)
		 {
			$this->updateInfoField(lang('Current Status: ').get_status_lang($result['status']));
		 }
		 else
		 {
			$this->updateInfoField(lang('Could not communicate with ANY2FLV Server'));
		 }

		 return $this->response->getXML();
	  }

	  function checkServerAvailability()
	  {
		 $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		 socket_set_nonblock($sock);
		 @socket_connect($sock,$this->host, $this->port);
		 socket_set_block($sock);
		 switch(socket_select($r = array($sock), $w = array($sock), $f = array($sock), 5))
		 {
			case 2:
			   socket_close($sock);
			   $this->updateInfoField(lang('Could not communicate with ANY2FLV Server:').' Connection Refused');
			   return 1;
			   break;
			case 1:
			   socket_close($sock);
			   return;//echo "[+] Connected\n";
			   break;
			case 0:
			   socket_close($sock);
			   $this->updateInfoField(lang('Could not communicate with ANY2FLV Server:').' Connection Timeout');
			   return 1;
			   break;
			}
		 }

		 function getFile($recordFieldInfoPacked,$metafieldinfo)
		 {
			$this->_init($recordFieldInfoPacked);

			if($this->checkServerAvailability())
			{
			   return $this->response->getXML();
			}

			$meta_data_arr = unserialize(base64_decode($metafieldinfo));

			$result = xu_rpc_http_concise(
			   array(
				  'method' => "getFileURL",
				  'args'  => array($meta_data_arr['queueid']),
				  //	   'debug' => True,
				  'host'  => $this->host,
				  'uri'  => $this->uri,
				  'port'  => 31313
			   )
			);

			if($result)
			{
			   $newname=basename($result);
			   $filename = $this->destpath.'/'.$newname;
			   $filedir = $this->destdir.'/'.$newname;

			   $fcont=file_get_contents($result);

			   if (is_writable($this->destpath)) 
			   {
				  if (!$handle = fopen($filename, 'w')) 
				  {
					 $this->updateInfoField(lang('1 Problem writing file: '));
					 exit;
				  }

				  if (fwrite($handle, $fcont) === FALSE) 
				  {
					 $this->updateInfoField(lang('2 Problem writing file: '));
					 exit;
				  }

				  fclose($handle);


				  $this->updateInfoField($this->field_name);
				  $this->response->addAssign($this->field_name,"value",$filedir);

				  $querystring='jinn/plugins/db_fields_plugins/__flvconvertclient/stream.swf?_vidURL='.$this->desturl.SEP.$newname.'&_phpURL='.$GLOBALS['egw_info']['server']['webserver_url'].'/jinn/plugins/db_fields_plugins/__flvconvertclient/';
				  //$this->response->addAlert($querystring );
				  $this->response->addScriptCall("changeFlash",$querystring );
				  $this->response->addScriptCall("showPlayer" );

				  
				  //$querystring=	  'jinn/plugins/db_fields_plugins/__flvconvertclient/stream.swf?$this->querystring';

					 //UPDATE RECORD
					 //UPDATE STATUS
					 //PLAY FILE

				  } 
			   else 
			   {
				  $this->updateInfoField(lang('3 Problem writing file: '));
			   }

			}
			else
			{
			   $this->updateInfoField(lang('Problem recieving file.'));
			}

			return $this->response->getXML();

		 }

		 function addToQueue($recordFieldInfoPacked,$movieurl)
		 {
			$this->_init($recordFieldInfoPacked);

			if(substr($movieurl,0,4)!='http')
			{
			   $movieurl=$this->upload_url.'/'.$movieurl;
			}
//			$this->updateInfoField($movieurl);
//		return $this->response->getXML();

			if($this->checkServerAvailability())
			{
			   return $this->response->getXML();
			}

			$queueid = xu_rpc_http_concise(
			   array(
				  'method' => "addToQueue",
				  'args'  => array($movieurl),
				  'host'  => $this->host,
				  'uri'  => $this->uri,
				  'port'  => $this->port
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
