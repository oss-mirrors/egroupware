<?php
/**
 * eGroupWare API: VFS - stream wrapper for linked files
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage vfs
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2008 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id: class.sqlfs_stream_wrapper.inc.php 24997 2008-03-02 21:44:15Z ralfbecker $
 */

/**
 * eGroupWare API: stream wrapper for linked files
 * 
 * The files stored by the sqlfs_stream_wrapper in a /apps/$app/$id directory
 * 
 * The links stream wrapper extends the sqlfs one, to implement an own ACL based on the access
 * of the entry the files are linked to.
 * 
 * Applications can define a 'file_access' method in the link registry with the following signature:
 * 
 * 		boolean function file_access(string $id,int $check,string $rel_path)
 * 
 * If the do not implement such a function the title function is used to test if the user has
 * at least read access to an entry, and if true full (write) access to the files is granted.
 * 
 * The stream wrapper interface is according to the docu on php.net
 *  
 * @link http://de.php.net/manual/de/function.stream-wrapper-register.php
 */
class links_stream_wrapper extends sqlfs_stream_wrapper
{
	/**
	 * Scheme / protocoll used for this stream-wrapper
	 */
	const SCHEME = 'links';
	/**
	 * Prefix to predend to get an url from a path
	 */
	const PREFIX = 'links://default';
	/**
	 * Base url to store links
	 */
	const BASEURL = 'links://default/apps';
	/**
	 * Enable some debug output to the error_log
	 */
	const DEBUG = false;

	/**
	 * Implements ACL based on the access of the user to the entry the files are linked to.
	 * 
	 * @param string $url url to check
	 * @param int $check mode to check: one or more or'ed together of: 4 = read, 2 = write, 1 = executable
	 * @return boolean
	 */
	static function check_extended_acl($url,$check)
	{
		$path = parse_url($url,PHP_URL_PATH);
		
		list(,$apps,$app,$id,$rel_path) = explode('/',$path,5);

		if ($apps != 'apps')
		{
			$access = false;							// no access to anything, but /apps
		}
		elseif (!$app)
		{
			$access = !($check & egw_vfs::WRITABLE);	// always grant read access to /apps
		}
		elseif(!isset($GLOBALS['egw_info']['user']['apps'][$app]))
		{
			$access = false;							// user has no access to the $app application
		}
		elseif (!$id)
		{
			$access = true;								// grant read&write access to /apps/$app
		}
		// allow applications to implement their own access control to the file storage
		elseif(($method = egw_link::get_registry($app,'file_access')))
		{
			$access = ExecMethod2($method,$id,$check,$rel_path);
		}
		// otherwise use the title method to check if user has (at least read access) to the entry
		// which gives him then read AND write access to the file store of the entry
		else
		{
			$access = !!egw_link::title($app,$id);
		}
		if (self::DEBUG) error_log(__METHOD__."($url,$check) ".($access?"access granted ($app:$id:$rel_path)":'no access!!!'));
		return $access;
	}

	/**
	 * This method is called in response to stat() calls on the URL paths associated with the wrapper.
	 * 
	 * Reimplemented from sqlfs, as we have to pass the value of check_extends_acl(), due to the lack of late static binding.
	 * 
	 * @param string $path
	 * @param int $flags holds additional flags set by the streams API. It can hold one or more of the following values OR'd together:
	 * - STREAM_URL_STAT_LINK	For resources with the ability to link to other resource (such as an HTTP Location: forward, 
	 *                          or a filesystem symlink). This flag specified that only information about the link itself should be returned, 
	 *                          not the resource pointed to by the link. 
	 *                          This flag is set in response to calls to lstat(), is_link(), or filetype().
	 * - STREAM_URL_STAT_QUIET	If this flag is set, your wrapper should not raise any errors. If this flag is not set, 
	 *                          you are responsible for reporting errors using the trigger_error() function during stating of the path.
	 *                          stat triggers it's own warning anyway, so it makes no sense to trigger one by our stream-wrapper!
	 * @return array 
	 */
	static function url_stat ( $url, $flags )
	{
		return parent::url_stat($url,$flags,self::check_extended_acl($url,egw_vfs::READABLE));
	}

	/**
	 * Set or delete extended acl for a given path and owner (or delete  them if is_null($rights)
	 * 
	 * Reimplemented, to NOT call the sqlfs functions, as we dont allow to modify the ACL (defined by the apps)
	 *
	 * @param string $path string with path
	 * @param int $rights=null rights to set, or null to delete the entry
	 * @param int/boolean $owner=null owner for whom to set the rights, null for the current user, or false to delete all rights for $path
	 * @param int $fs_id=null fs_id to use, to not query it again (eg. because it's already deleted)
	 * @return boolean true if acl is set/deleted, false on error
	 */
	static function eacl($path,$rights=null,$owner=null,$fs_id=null)
	{
		return false;
	}
	
	/**
	 * Get all ext. ACL set for a path
	 * 
	 * Reimplemented, to NOT call the sqlfs functions, as we dont allow to modify the ACL (defined by the apps)
	 *
	 * @param string $path
	 * @return array/boolean array with array('path'=>$path,'owner'=>$owner,'rights'=>$rights) or false if $path not found
	 */
	function get_eacl($path)
	{
		return false;
	}
}

stream_register_wrapper(links_stream_wrapper::SCHEME ,'links_stream_wrapper');
