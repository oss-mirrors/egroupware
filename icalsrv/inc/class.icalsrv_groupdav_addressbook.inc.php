<?php
/**
 * eGroupWare: GroupDAV access: addressbook handler
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package icalsrv
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2007 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

require_once(EGW_INCLUDE_ROOT.'/icalsrv/inc/class.icalsrv_groupdav_handler.inc.php');

/**
 * eGroupWare: GroupDAV access: addressbook handler
 */
class icalsrv_groupdav_addressbook extends icalsrv_groupdav_handler
{
	/**
	 * bo class of the application
	 *
	 * @var vcaladdressbook
	 */
	var $bo;

	function icalsrv_groupdav_addressbook($debug=null)
	{
		$this->icalsrv_groupdav_handler('addressbook',$debug);

		$this->bo =& new addressbook_bo();
	}

	/**
	 * Handle propfind in the addressbook folder
	 *
	 * @param string $path
	 * @param array $options
	 * @param array &$files
	 * @param int $user account_id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function propfind($path,$options,&$files,$user)
	{
        if ($user) $filter = array('contact_owner' => $user);

		if (($contacts =& $this->bo->search(array(),array('id','modified'),'contact_id','','',False,'AND',false,$filter)))
		{
			foreach($contacts as $contact)
			{
				$files['files'][] = array(
	            	'path'  => '/addressbook/'.$contact['id'],
	            	'props' => array(
	            		HTTP_WebDAV_Server::mkprop('getetag',$this->get_etag($contact)),
	            		HTTP_WebDAV_Server::mkprop('getcontenttype', 'text/x-vcard'),
	            	),
				);
			}
		}
		return true;
	}

	/**
	 * Handle get request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function get(&$options,$id)
	{
		if (!is_array($contact = $this->_common_get_put_delete('GET',$options,$id)))
		{
			return $contact;
		}
		$handler = self::_get_handler();
		// SoGo Connector for Thunderbird works only with iso-8859-1!
		$charset = strpos($_SERVER['HTTP_USER_AGENT'],'Thunderbird') !== false ? 'iso-8859-1' : 'utf-8';
		$options['data'] = $handler->getVCard($id,$charset);
		$options['mimetype'] = 'text/x-vcard; charset='.$charset;
		header('Content-Encoding: identity');
		header('ETag: '.$this->get_etag($contact));
		return true;
	}

	/**
	 * Handle put request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @param int $user=null account_id of owner, default null
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function put(&$options,$id,$user=null)
	{
		$ok = $this->_common_get_put_delete('PUT',$options,$id);
		if (!is_null($ok) && !is_array($ok))
		{
			return $ok;
		}
		$handler = self::_get_handler();
		$contact = $handler->vcardtoegw($options['content']);
		if (!is_null($ok)) $contact['id'] = $id;

		if (!$this->bo->save($contact)) return false;

		header('ETag: '.$this->get_etag($contact));
		if (is_null($ok))
		{
			header($h='Location: '.$this->base_uri.'/addressbook/'.$contact['id']);
			error_log("icalsrv_groupdav::_addressbook_get_put_delete($method,,$id) header('$h'): 201 Created");
			return '201 Created';
		}
		return true;
	}

	/**
	 * Get the handler and set the supported fields
	 *
	 * @return vcaladdressbook
	 */
	private function _get_handler()
	{
		include_once(EGW_INCLUDE_ROOT.'/addressbook/inc/class.vcaladdressbook.inc.php');
		$handler =& new vcaladdressbook();
		if (strpos($_SERVER['HTTP_USER_AGENT'],'KHTML') !== false)
		{
			$handler->setSupportedFields('KDE');
		}
		return $handler;
	}

	/**
	 * Handle delete request for an event
	 *
	 * @param array &$options
	 * @param int $id
	 * @return mixed boolean true on success, false on failure or string with http status (eg. '404 Not Found')
	 */
	function delete(&$options,$id)
	{
		if (!is_array($event = $this->_common_get_put_delete('DELETE',$options,$id)))
		{
			return $event;
		}
		return $this->bo->delete($id);
	}

	/**
	 * Read a contact
	 *
	 * @param string/id $id
	 * @return array/boolean array with entry, false if no read rights, null if $id does not exist
	 */
	function read($id)
	{
		return $this->bo->read($id);
	}

	/**
	 * Check if user has the neccessary rights on a contact
	 *
	 * @param int $acl EGW_ACL_READ, EGW_ACL_EDIT or EGW_ACL_DELETE
	 * @param array/int $contact contact-array or id
	 * @return boolean null if entry does not exist, false if no access, true if access permitted
	 */
	function check_access($acl,$contact)
	{
		return $this->bo->check_perms($acl,$contact);
	}
}