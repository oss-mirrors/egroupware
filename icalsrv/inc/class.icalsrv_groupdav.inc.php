<?php
/**
 * eGroupWare: GroupDAV access
 *
 * Using the PEAR HTTP/WebDAV/Server class (which need to be installed!)
 * 
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package icalsrv
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

require_once('HTTP/WebDAV/Server.php');

/**
 * eGroupWare: GroupDAV access
 *
 * Using the PEAR HTTP/WebDAV/Server class (which need to be installed!)
 */
class icalsrv_groupdav extends HTTP_WebDAV_Server
{
	var $dav_powered_by = 'eGroupWare GroupDAV server';
	
	/**
	 * Debug level: 0 = nothing, 1 = function calls, 2 = more info, eg. complete $_SERVER array
	 * 
	 * The debug messages are send to the apache error_log
	 *
	 * @var integer
	 */
	var $debug = 1;
	
	/**
	 * eGW's charset
	 *
	 * @var string
	 */
	var $egw_charset;
	/**
	 * Reference to the translation class
	 *
	 * @var translation
	 */
	var $translation;

	function icalsrv_groupdav()
	{
		if ($this->debug === 2) foreach($_SERVER as $name => $val) error_log("icalsrv_groupdav: \$_SERVER[$name]='$val'");

		parent::HTTP_WebDAV_Server();
		
		$this->translation =& $GLOBALS['egw']->translation;
		$this->egw_charset = $this->translation->charset();
	}
	
	/**
	 * PROPFIND method handler
	 *
	 * @param  array  general parameter passing array
	 * @param  array  return array for file properties
	 * @return bool   true on success
	 */
	function PROPFIND(&$options, &$files) 
	{
		if ($this->debug) error_log('icalsrv_groupdav::PROPFIND('.print_r($options,true).')');
		
		// parse path in form [/account_lid]/app[/more]
		$parts = explode('/',$options['path']);
		array_shift($parts);
		$app = array_shift($parts);
		if ($app && !in_array($app,array('addressbook','calendar','infolog')))
		{
			error_log("icalsrv_groupdav::PROPFIND: user=$app, rest=".implode('/',$parts));
			if (!($user = $GLOBALS['egw']->accounts->name2id($app,'account_lid','u')))
			{
				return '404 Not Found';
			}
			$app = array_shift($parts);
		}
		error_log("icalsrv_groupdav::PROPFIND: user=$user, app='$app', rest=".implode('/',$parts));
		
		if ($app && !$GLOBALS['egw_info']['user']['apps'][$app])
		{
			error_log("icalsrv_groupdav::PROPFIND(path=$options[path]) 403 Forbidden: no app rights");
			return '403 Forbidden';	// no rights for the given app
		}
		$files['files'] = array();
		
		switch((string)$app)
		{
			case '':	// root folder containing apps
				foreach(array(
					'calendar' => array('type' => 'vevent-collection', 'label' => lang('Calendar')),
					'addressbook' => array('type' => 'vcard-collection', 'label' => lang('Addressbook')),
					'infolog' => array('type' => 'vtodo-collection', 'label' => lang('Tasks')),
				) as $app => $data)
				{
					if (!$GLOBALS['egw_info']['user']['apps'][$app]) continue;	// no rights for the given app

					$files['files'][] = array(
		            	'path'  => '/'.$app.'/',
		            	'props' => array(
		            		$this->mkprop('displayname',$this->translation->convert($data['label'],$this->egw_charset,'utf-8')),
		            		$this->mkprop('resourcetype', /*$name == 'addressbook' ? $this->mkprop('collection','collection') :*/ array(
		            			$this->mkprop('collection','collection'),
		            			$this->mkprop('http://groupdav.org/','resourcetype', $data['type']),
		            		)),
		            	),
		            );
				}
				return true;
				
			case 'calendar':
				return $this->_calendar_propfind($options['path'],$options,$files,$user);

			case 'addressbook':
				return $this->_addressbook_propfind($options['path'],$options,$files,$user);
				
			case 'infolog':
				return $this->_infolog_propfind($options['path'],$options,$files,$user);
		}
		return '501 Not Implemented';
	}
	
	/**
	 * GET method handler
	 * 
	 * @param  array  parameter passing array
	 * @return bool   true on success
	 */
	function GET(&$options) 
	{
		if ($this->debug) error_log('icalsrv_groupdav::GET('.print_r($options,true).')');
		
		if (!$this->_parse_path($options['path'],$id,$app,$user))
		{
			return '404 Not Found';
		}
		switch($app)
		{
			case 'calendar':
				return $this->_calendar_get_put_delete('GET',$options,$id);
			case 'addressbook':
				return $this->_addressbook_get_put_delete('GET',$options,$id);
			case 'infolog':
				return $this->_infolog_get_put_delete('GET',$options,$id);
		}
		return '501 Not Implemented';
	}

	/**
	 * PUT method handler
	 * 
	 * @param  array  parameter passing array
	 * @return bool   true on success
	 */
	function PUT(&$options) 
	{
		// read the content in a string, if a stream is given
		if (isset($options['stream']))
		{
			$options['content'] = '';
			while(!feof($options['stream']))
			{
				$options['content'] .= fread($options['stream'],8192);
			}
		}
		if ($this->debug) error_log('icalsrv_groupdav::PUT('.print_r($options,true).')');

		if (!$this->_parse_path($options['path'],$id,$app,$user))
		{
			return '404 Not Found';
		}
		switch($app)
		{
			case 'calendar':
				$ok = $this->_calendar_get_put_delete('PUT',$options,$id,$user);
				break;
			case 'addressbook':
				$ok = $this->_addressbook_get_put_delete('PUT',$options,$id,$user);
				break;
			case 'infolog':
				$ok = $this->_infolog_get_put_delete('PUT',$options,$id,$user);
				break;
		}
		if (!is_null($ok))
		{
			return $ok;		// maybe 204 would be better: ? '204 No Content' : false;
		}
		return '501 Not Implemented';
	}
	
	/**
	 * DELETE method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function DELETE($options) 
	{
		if ($this->debug) error_log('icalsrv_groupdav::DELETE('.print_r($options,true).')');

		if (!$this->_parse_path($options['path'],$id,$app,$user))
		{
			return '404 Not Found';
		}
		switch($app)
		{
			case 'calendar':
				$ok = $this->_calendar_get_put_delete('DELETE',$options,$id,$user);
				break;
			case 'addressbook':
				$ok = $this->_addressbook_get_put_delete('DELETE',$options,$id,$user);
				break;
			case 'infolog':
				$ok = $this->_infolog_get_put_delete('DELETE',$options,$id,$user);
				break;
		}
		if (!is_null($ok))
		{
			return $ok ? '204 No Content' : false;
		}
		return '501 Not Implemented';
	}

	/**
	 * MKCOL method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function MKCOL($options) 
	{           
		if ($this->debug) error_log('icalsrv_groupdav::MKCOL('.print_r($options,true).')');

		return '501 Not Implemented';
	}

	/**
	 * MOVE method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function MOVE($options) 
	{
		if ($this->debug) error_log('icalsrv_groupdav::MOVE('.print_r($options,true).')');
		
		return '501 Not Implemented';
	}
	
	/**
	 * COPY method handler
	 *
	 * @param  array  general parameter passing array
	 * @return bool   true on success
	 */
	function COPY($options, $del=false)
	{
		if ($this->debug) error_log('icalsrv_groupdav::'.($del ? 'MOVE' : 'COPY').'('.print_r($options,true).')');
		
		return '501 Not Implemented';
	}

	/**
	 * Instanciates a virtual calendar
	 *
	 * @param int $user=0 account_id, defaults to $GLOBALS['egw_info']['user']['account_id']
	 * @param string $path='/events.ics'
	 * @return icalvircal
	 */
	function &_instanciate_icalvc($user=0,$path='/events.ics')
	{
		if (!$user) $user = $GLOBALS['egw_info']['user']['account_id'];

		include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.personal_vircal_ardb.inc.php');
		$system_vircal_ardb = new personal_vircal_ardb($user);
		$vircal_arstore = $system_vircal_ardb->calendars[$path];
		require_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.icalvircal.inc.php');
		$icalvc =& new icalvircal;
		$icalvc->fromArray($vircal_arstore);
		$icalvc->uid_mapping_export = UMM_UID2UID; 
		$icalvc->uid_mapping_import = UMM_UID2UID; 
		$icalvc->reimport_missing_elements = false;

		return $icalvc;
	}
	
	/**
	 * Parse a path into it's id, app and user parts
	 *
	 * @param string $path
	 * @param int &$id
	 * @param string &$app addressbook, calendar, infolog (=infolog)
	 * @param int &$user
	 * @return boolean true on success, false on error
	 */
	function _parse_path($path,&$id,&$app,&$user)
	{
		$parts = explode('/',$path);
		
		list($id) = explode('.',array_pop($parts));		// remove evtl. .ics extension
		
		$app = array_pop($parts);
		
		if (($user = array_pop($parts)))
		{
			$user = $GLOBALS['egw']->accounts->name2id($user,'account_lid',$app != 'addressbook' ? 'u' : null);
		}
		else
		{
			$user = $GLOBALS['egw_info']['user']['account_id'];
		}
		if (!($ok = $id && in_array($app,array('addressbook','calendar','infolog')) && $user))
		{
			error_log("icalsrv_groupdav::_parse_path('$path') returning false: id=$id, app='$app', user=$user");
		}
		return $ok;
	}
	
	function _abowner2path($owner)
	{
		if (!$owner)
		{
			$name = 'accounts';
		}
		elseif ($owner == $GLOBALS['egw_info']['user']['account_id'])
		{
			$name = 'personal';
		}
		elseif ((!$name = $GLOBALS['egw']->accounts->id2name($owner)))
		{
			return false;
		}
		return '/addressbook/'.$name;
	}
	
	/**
	 * Handle propfind in the calendar folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _calendar_propfind($path,$options,&$files,$user)
	{
		// add the calendar folder itself, as other WebDAV clients expect it
		$name = 'calendar'; $type = 'vevent-collection';
		$files['files'][] = array(
        	'path'  => '/'.$name.'/',
        	'props' => array(
        		$this->mkprop('displayname',$name),
        		$this->mkprop('resourcetype', array(
        			$this->mkprop('collection','collection'),
//					$this->mkprop('http://groupdav.org/','resourcetype', $type),	// GroupDAV type doubles the folder in Kontact
        		)),
        	),
        );
		$icalvc =& $this->_instanciate_icalvc($user);
		include_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.bocal.inc.php');
		$bocal =& new bocal;
		// ToDo: add parameter to only return id & etag
		if (($events = $bocal->search($icalvc->_caldef['rscs']['calendar.bocalupdate'])))
		{
			foreach($events as $event)
			{
				$files['files'][] = array(
	            	'path'  => '/calendar/'.$event['id'],
	            	'props' => array(
	            		$this->mkprop('getetag','"'.$event['id'].':'.$event['modified'].'"'),
	            		$this->mkprop('getcontenttype', 'text/calendar'),
	            	),
				);
			}
		}
		return true;
	}
	
	/**
	 * Handle get, put and delete in the calendar folder
	 *
	 * @param string $method GET, PUT, DELETE
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _calendar_get_put_delete($method,&$options,$id)
	{
		if (!$GLOBALS['egw_info']['user']['apps']['calendar'])
		{
			error_log("icalsrv_groupdav::_calendar_get_put_delete($method,,$id) 403 Forbidden: no app rights");
			return '403 Forbidden';		// no calendar rights
		}
		include_once(EGW_INCLUDE_ROOT.'/calendar/inc/class.bocalupdate.inc.php');
		$bocalupdate =& new bocalupdate;
		
		switch($method)
		{
			case 'GET':
				if (!($event = $bocalupdate->read($id,null,false,'server')))
				{
					error_log("icalsrv_groupdav::_calendar_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: read($id)==".($event===false?'false':'null'));
					return $event === false ? '403 Forbidden' : '404 Not Found';
				}
				include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.bocalupdate_vevents.inc.php');
				$handler =& new bocalupdate_vevents($bocalupdate);
				$options['data'] = $handler->export_vcal($events=array($event));
				$options['mimetype'] = 'text/calendar; charset=utf-8';
				header('Content-Encoding: identity');
				header('ETag: "'.$event['id'].':'.$event['modified'].'"');
				return true;
				
			case 'PUT':
				if (is_numeric($id) && ($ok = $bocalupdate->check_perms(EGW_ACL_EDIT,$id,0,'server')) === false)
				{
					// ToDo: check if just changing his participant status
					error_log("icalsrv_groupdav::_calendar_get_put_delete($method,,$id) 403 Forbidden: check_perms(EGW_ACL_EDIT,$id)==false");
					return '403 Forbidden';
				}
				include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.bocalupdate_vevents.inc.php');
				$handler =& new bocalupdate_vevents($bocalupdate);
				$vcalelm =& $handler->parse_vcal2velt($options['content']);
				if (!($cal_id = $handler->import_vevent($vcalelm, $uid_mapping_import=UMM_UID2UID, $reimport_missing_events=false, $id)) > 0)
				{
					error_log("icalsrv_groupdav::_calendar_get_put_delete($method,,$id) import_vevent($options[content]) returned false");
					return false;	// something went wrong ...
				}
				if (!($event = $bocalupdate->read($cal_id,null,false,'server'))) return false;	// something went wrong ...
				
				header('ETag: "'.$event['id'].':'.$event['modified'].'"');
				if (is_null($ok) || $id != $cal_id)
				{
					header('Location: '.$this->base_uri.'/calendar/'.$cal_id);
					return '201 Created';
				}
				return true;
				
			case 'DELETE':
				if (!($ok = $bocalupdate->check_perms(EGW_ACL_DELETE,$id,0,'server')))
				{
					error_log("icalsrv_groupdav::_calendar_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: check_perms(EGW_ACL_DELETE,$id)==".($ok===false?'false':'null'));
					return $ok === false ? '403 Forbidden' : '404 Not Found';
				}
				return $bocalupdate->delete($id);
		}
		return '501 Not Implemented';
	}
	
	/**
	 * Handle propfind in the addressbook folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user=null account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _addressbook_propfind($path,$options,&$files,$user)
	{
		// add the calendar folder itself, as other WebDAV clients expect it
		$name = 'addressbook'; $type = 'vcard-collection';
		$files['files'][] = array(
        	'path'  => '/'.$name.'/',
        	'props' => array(
        		$this->mkprop('displayname',$name),
        		$this->mkprop('resourcetype', array(
        			$this->mkprop('collection','collection'),
//					$this->mkprop('http://groupdav.org/','resourcetype', $type),	// GroupDAV type doubles the folder in Kontact
        		)),
        	),
        );
        if ($user) $filter = array('contact_owner' => $user);
		include_once(EGW_INCLUDE_ROOT.'/addressbook/inc/class.bocontacts.inc.php');
		$handler =& new bocontacts();
		if (($contacts =& $handler->search(array(),array('id','modified'),'contact_id','','',False,'AND',false,$filter)))
		{
			foreach($contacts as $contact)
			{
				$files['files'][] = array(
	            	'path'  => '/addressbook/'.$contact['id'],
	            	'props' => array(
	            		$this->mkprop('getetag','"'.$contact['id'].':'.$contact['modified'].'"'),
	            		$this->mkprop('getcontenttype', 'text/x-vcard'),
	            	),
				);
			}
		}
		return true;
	}
	
	/**
	 * Handle get, put and delete in the addressbook folder
	 *
	 * @param string $method GET, PUT, DELETE
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _addressbook_get_put_delete($method,&$options,$id)
	{
		if (!$GLOBALS['egw_info']['user']['apps']['addressbook'])
		{
			error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) 403 Forbidden: no app rights");
			return '403 Forbidden';		// no addressbook rights
		}
		include_once(EGW_INCLUDE_ROOT.'/addressbook/inc/class.vcaladdressbook.inc.php');
		$handler =& new vcaladdressbook();
		
		switch($method)
		{
			case 'GET':
				if (!($contact = $handler->read($id)))
				{
					error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: read($id)==".($contact===false?'false':'null'));
					return $event === false ? '403 Forbidden' : '404 Not Found';
				}
				$options['data'] = $handler->getVCard($id);
				$options['mimetype'] = 'text/x-vcard; charset=utf-8';
				header('Content-Encoding: identity');
				header('ETag: "'.$contact['id'].':'.$contact['modified'].'"');
				return true;
				
			case 'PUT':
				if (($ok = $handler->check_perms(EGW_ACL_EDIT,$id)) === false)
				{
					error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) 403 Forbidden: check_perms(EGW_ACL_EDIT,$id)==false");
					return '403 Forbidden';
				}
				$contact = $handler->vcardtoegw($options['content']);
				if (!is_null($ok)) $contact['id'] = $id;
				
				if (!$handler->save($contact)) return false;

				header('ETag: "'.$contact['id'].':'.$contact['modified'].'"');
				if (is_null($ok))
				{
					header($h='Location: '.$this->base_uri.'/addressbook/'.$contact['id']);
					error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) header('$h'): 201 Created");
					return '201 Created';
				}
				return true;
				
			case 'DELETE':
				if (!($ok = $handler->check_perms(EGW_ACL_DELETE,$id)))
				{
					error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: check_perms(EGW_ACL_DELETE,$id)==".($ok===false?'false':'null'));
					return $ok === false ? '403 Forbidden' : '404 Not Found';
				}
				return $handler->delete($id);
		}
		return '501 Not Implemented';
	}

	/**
	 * Handle propfind in the infolog folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _infolog_propfind($path,$options,&$files,$user)
	{
		// add the calendar folder itself, as other WebDAV clients expect it
		$name = 'infolog'; $type = 'vtodo-collection';
		$files['files'][] = array(
        	'path'  => '/'.$name.'/',
        	'props' => array(
        		$this->mkprop('displayname',$name),
        		$this->mkprop('resourcetype', array(
        			$this->mkprop('collection','collection'),
//					$this->mkprop('http://groupdav.org/','resourcetype', $type),	// GroupDAV type doubles the folder in Kontact
        		)),
        	),
        );
		$icalvc =& $this->_instanciate_icalvc($user);
		include_once(EGW_INCLUDE_ROOT.'/infolog/inc/class.boinfolog.inc.php');
		$boinfo =& new boinfolog();
		// ToDo: add parameter to only return id & etag
		if (($tasks = $boinfo->search($icalvc->_caldef['rscs']['infolog.boinfolog'])))
		{
			foreach($tasks as $task)
			{
				$files['files'][] = array(
	            	'path'  => '/infolog/'.$task['info_id'],
	            	'props' => array(
	            		$this->mkprop('getetag','"'.$task['info_id'].':'.$task['info_modified'].'"'),
	            		$this->mkprop('getcontenttype', 'text/calendar'),
	            	),
				);
			}
		}
		return true;
	}
	
	/**
	 * Handle get, put and delete in the infolog folder
	 *
	 * @param string $method GET, PUT, DELETE
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function _infolog_get_put_delete($method,&$options,$id)
	{
		if (!$GLOBALS['egw_info']['user']['apps']['infolog'])
		{
			error_log("icalsrv_groupdav::_infolog_get_put_delete($method,,$id) 403 Forbidden: no app rights");
			return '403 Forbidden';		// no calendar rights
		}
		include_once(EGW_INCLUDE_ROOT.'/infolog/inc/class.boinfolog.inc.php');
		$boinfolog =& new boinfolog();
		
		switch($method)
		{
			case 'GET':
				if (!($task = $boinfolog->read($id,false)))
				{
					error_log("icalsrv_groupdav::_infolog_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: read($id)==".($task===false?'false':'null'));
					return $task === false ? '403 Forbidden' : '404 Not Found';
				}
				include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.boinfolog_vtodos.inc.php');
				$handler =& new boinfolog_vtodos($boinfolog);
				$vtodo = $handler->export_vtodo($task,UMM_UID2UID);
				$options['data'] = $handler->render_velt2vcal($vtodo);
				$options['mimetype'] = 'text/calendar; charset=utf-8';
				header('Content-Encoding: identity');
				header('ETag: "'.$task['info_id'].':'.$task['info_modified'].'"');
				return true;
				
			case 'PUT':
				if (is_numeric($id) && ($ok = $boinfolog->check_access($id,EGW_ACL_EDIT)) === false)
				{
					error_log("icalsrv_groupdav::_infolog_get_put_delete($method,,$id) 403 Forbidden: check_access($id,EGW_ACL_EDIT)==false");
					return '403 Forbidden';
				}
				include_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.boinfolog_vtodos.inc.php');
				$handler =& new boinfolog_vtodos($boinfolog);
				$vcalelm =& $handler->parse_vcal2velt($options['content']);
				error_log("_infolog_get_put_delete($method,,$id) vcalelm=".print_r($vcalelm,true));
				if (!($info_id = $handler->import_vtodo($vcalelm, $uid_mapping_import=UMM_UID2UID, $reimport_missing_events=false, $id)) > 0)
				{
					error_log("icalsrv_groupdav::_infolog_get_put_delete($method,,$id) import_vtodo($options[content]) returned false");
					return false;	// something went wrong ...
				}
				if (!($task = $boinfolog->read($info_id,false))) return false;	// something went wrong ...
				
				header('ETag: "'.$task['info_id'].':'.$task['info_modified'].'"');
				if (is_null($ok) || $id != $info_id)
				{
					header('Location: '.$this->base_uri.'/infolog/'.$info_id);
					return '201 Created';
				}
				return true;
				
			case 'DELETE':
				if (!($ok = $boinfolog->check_access($id,EGW_ACL_DELETE)))
				{
					error_log("icalsrv_groupdav::_infolog_get_put_delete($method,,$id) 403 Forbidden/404 Not Found: check_access($id,EGW_ACL_DELETE)==".($ok===false?'false':'null'));
					return $ok === false ? '403 Forbidden' : '404 Not Found';
				}
				return $boinfolog->delete($id);
		}
		return '501 Not Implemented';
	}
}