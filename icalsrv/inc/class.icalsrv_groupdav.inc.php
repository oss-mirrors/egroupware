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
require_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.icalsrv_groupdav_handler.inc.php');

/**
 * eGroupWare: GroupDAV access
 *
 * Using the PEAR HTTP/WebDAV/Server class (which need to be installed!)
 * 
 * @link http://www.groupdav.org GroupDAV spec
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
	/**
	 * Instance of our application specific handler
	 *
	 * @var icalsrv_groupdav_handler
	 */
	var $handler;

	function icalsrv_groupdav()
	{
		if ($this->debug === 2) foreach($_SERVER as $name => $val) error_log("icalsrv_groupdav: \$_SERVER[$name]='$val'");

		parent::HTTP_WebDAV_Server();
		
		$this->translation =& $GLOBALS['egw']->translation;
		$this->egw_charset = $this->translation->charset();
	}
	
	function _instancicate_handler($app)
	{
		$this->handler = icalsrv_groupdav_handler::app_handler($app);
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
		
		$files = array();

		$root = array(
			'calendar' => array('type' => 'vevent-collection', 'label' => lang('Calendar')),
			'addressbook' => array('type' => 'vcard-collection', 'label' => lang('Addressbook')),
			'infolog' => array('type' => 'vtodo-collection', 'label' => lang('Tasks')),
		);
		if (!$app)	// root folder containing apps
		{
			foreach($root as $app => $data)
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
		}
		if (!$GLOBALS['egw_info']['user']['apps'][$app])
		{
			error_log("icalsrv_groupdav::PROPFIND(path=$options[path]) 403 Forbidden: no app rights");
			return '403 Forbidden';	// no rights for the given app
		}
		if (($handler = icalsrv_groupdav_handler::app_handler($app,$this->debug)))
		{
			// adding the application folder itself
			$files['files'][] = array(
	        	'path'  => '/'.$app.'/',
	        	'props' => array(
	        		$this->mkprop('displayname',$app),
	        		$this->mkprop('resourcetype', array(
	        			$this->mkprop('collection','collection'),
	//					$this->mkprop('http://groupdav.org/','resourcetype', $root[$app]['type']),	// GroupDAV type doubles the folder in Kontact
	        		)),
	        	),
	        );
			return $handler->propfind($options['path'],$options,$files,$user);
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
		if (($handler = icalsrv_groupdav_handler::app_handler($app,$this->debug)))
		{
			return $handler->get($options,$id);
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
		if (($handler = icalsrv_groupdav_handler::app_handler($app,$this->debug)))
		{
			return $handler->put($options,$id,$user);	// maybe 204 would be better: ? '204 No Content' : false;
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
		if (($handler = icalsrv_groupdav_handler::app_handler($app,$this->debug)))
		{
			return $handler->delete($options,$id);	// maybe 204 would be better: ? '204 No Content' : false;
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
}
