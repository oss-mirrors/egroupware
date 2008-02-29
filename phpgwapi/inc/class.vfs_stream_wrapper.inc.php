<?php
/**
 * eGroupWare API: VFS - stream wrapper interface
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package api
 * @subpackage vfs
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2008 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @version $Id$
 */

/**
 * eGroupWare API: VFS - stream wrapper interface
 *
 * The new vfs stream wrapper uses a kind of fstab to mount different filesystems / stream wrapper types
 * together for eGW's virtual file system.
 *  
 * @link http://www.php.net/manual/en/function.stream-wrapper-register.php
 */
class vfs_stream_wrapper implements iface_stream_wrapper
{
	/**
	 * Scheme / protocol used for this stream-wrapper
	 */
	const SCHEME = 'vfs';
	/**
	 * Mime type of directories, the old vfs used 'Directory', while eg. WebDAV uses 'httpd/unix-directory'
	 */
	const DIR_MIME_TYPE = 'httpd/unix-directory';
	/**
	 * optional context param when opening the stream, null if no context passed
	 *
	 * @var mixed
	 */
	var $context;
	
	/**
	 * Our fstab in the form mount-point => url
	 * 
	 * The entry for root has to be the first, or more general if you mount into subdirs the parent has to be before!
	 *
	 * @var array
	 */
	protected static $fstab = array(
		'/' => 'sqlfs://$user:$pass@$host/',
//		'/' => 'oldvfs://$user:$pass@$host/',
//		'/files' => 'oldvfs://$user:$pass@$host/home/Default',
//		'/images' => 'http://localhost/egroupware/phpgwapi/templates/idots/images',
//		'/home/ralf/linux' => '/home/ralf',		// we probably need to forbid direct filesystem access for security reasons!
	);
	
	/**
	 * stream / ressouce this class is opened for by stream_open
	 *
	 * @var ressource
	 */
	private $opened_stream;
	/**
	 * directory-ressouce this class is opened for by dir_open
	 *
	 * @var ressource
	 */
	private $opened_dir;
	/**
	 * URL of the opened dir, used to build the complete URL of files in the dir
	 *
	 * @var string
	 */
	private $opened_dir_url;
	/**
	 * Flag if opened dir is writable, in which case we return un-readable entries too
	 *
	 * @var boolean
	 */
	private $opened_dir_writable;
	/**
	 * Extra dirs from our fstab in the current opened dir
	 *
	 * @var array
	 */
	private $extra_dirs;
	/**
	 * Pointer in the extra dirs
	 *
	 * @var int
	 */
	private $extra_dir_ptr;
	
	private static $wrappers;

	/**
	 * Resolve the given path according to our fstab
	 *
	 * @param string $path
	 * @return string/boolean false if the url cant be relsolved, should not happen if fstab has a root entry
	 */
	static function resolve_url($path)
	{
		static $cache = array();
		
		// we do some caching here
		if (isset($cache[$path]))
		{
			return $cache[$path];
		}
		// setting default user, passwd and domain, if it's not contained int the url
		static $defaults;
		if (is_null($defaults))
		{
			$defaults = array(
				'user' => $GLOBALS['egw_info']['user']['account_lid'],
				'pass' => $GLOBALS['egw_info']['user']['passwd'],
				'host' => $GLOBALS['egw_info']['user']['domain'],
			);
		}
		$parts = array_merge(parse_url($path),$defaults);

		if (empty($parts['path'])) $parts['path'] = '/';
		
		foreach(array_reverse(self::$fstab) as $mounted => $url)
		{
			if ($mounted == substr($parts['path'],0,strlen($mounted)))
			{
				$scheme = parse_url($url,PHP_URL_SCHEME);
				if (is_null(self::$wrappers) || !in_array($scheme,self::$wrappers))
				{
					self::load_wrapper($scheme);
				}
				$url .= substr($parts['path'],strlen($mounted));

				$url = str_replace(array('$user','$pass','$host'),array($parts['user'],$parts['pass'],$parts['host']),$url);
				
				//error_log(__METHOD__."($path) = $url");
				return $cache[$path] = $url;
			}
		}
		//error_log(__METHOD__."($path) can't resolve path!\n");
		trigger_error(__METHOD__."($path) can't resolve path!\n",E_USER_WARNING);
		return false;
	}
	
	/**
	 * This method is called immediately after your stream object is created.
	 * 
	 * @param string $path URL that was passed to fopen() and that this object is expected to retrieve
	 * @param string $mode mode used to open the file, as detailed for fopen()
	 * @param int $options additional flags set by the streams API (or'ed together): 
	 * - STREAM_USE_PATH      If path is relative, search for the resource using the include_path.
	 * - STREAM_REPORT_ERRORS If this flag is set, you are responsible for raising errors using trigger_error() during opening of the stream. 
	 *                        If this flag is not set, you should not raise any errors.
	 * @param string $opened_path full path of the file/resource, if the open was successfull and STREAM_USE_PATH was set
	 * @return boolean true if the ressource was opened successful, otherwise false
	 */
	function stream_open ( $path, $mode, $options, &$opened_path )
	{
		$this->opened_stream = null;

		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		if (!($this->opened_stream = fopen($url,$mode,$options)))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * This method is called when the stream is closed, using fclose(). 
	 * 
	 * You must release any resources that were locked or allocated by the stream.
	 */
	function stream_close ( )
	{
		$ret = fclose($this->opened_stream);

		$this->opened_stream = null;
		
		return $ret;
	}
	
	/**
	 * This method is called in response to fread() and fgets() calls on the stream.
	 * 
	 * You must return up-to count bytes of data from the current read/write position as a string. 
	 * If there are less than count bytes available, return as many as are available. 
	 * If no more data is available, return either FALSE or an empty string. 
	 * You must also update the read/write position of the stream by the number of bytes that were successfully read.
	 *
	 * @param int $count
	 * @return string/false up to count bytes read or false on EOF
	 */
	function stream_read ( $count )
	{
		return fread($this->opened_stream,$count);
	}

	/**
	 * This method is called in response to fwrite() calls on the stream.
	 * 
	 * You should store data into the underlying storage used by your stream. 
	 * If there is not enough room, try to store as many bytes as possible. 
	 * You should return the number of bytes that were successfully stored in the stream, or 0 if none could be stored. 
	 * You must also update the read/write position of the stream by the number of bytes that were successfully written.
	 *
	 * @param string $data
	 * @return integer
	 */
	function stream_write ( $data )
	{
		return fwrite($this->opened_stream,$data);
	}

 	/**
 	 * This method is called in response to feof() calls on the stream.
 	 * 
 	 * Important: PHP 5.0 introduced a bug that wasn't fixed until 5.1: the return value has to be the oposite!
 	 * 
 	 * if(version_compare(PHP_VERSION,'5.0','>=') && version_compare(PHP_VERSION,'5.1','<'))
  	 * {
 	 * 		$eof = !$eof;
 	 * }
  	 * 
 	 * @return boolean true if the read/write position is at the end of the stream and no more data availible, false otherwise
 	 */
	function stream_eof ( )
	{
		return feof($this->opened_stream);
	}

	/**
	 * This method is called in response to ftell() calls on the stream.
	 * 
	 * @return integer current read/write position of the stream
	 */
 	function stream_tell ( )
 	{
 		return ftell($this->opened_stream);
 	}

 	/**
 	 * This method is called in response to fseek() calls on the stream.
 	 *
 	 * You should update the read/write position of the stream according to offset and whence. 
 	 * See fseek() for more information about these parameters. 
 	 * 
 	 * @param integer $offset
 	 * @param integer $whence	SEEK_SET - Set position equal to offset bytes
 	 * 							SEEK_CUR - Set position to current location plus offset.
 	 * 							SEEK_END - Set position to end-of-file plus offset. (To move to a position before the end-of-file, you need to pass a negative value in offset.)
 	 * @return boolean TRUE if the position was updated, FALSE otherwise.
 	 */
	function stream_seek ( $offset, $whence )
	{
		return fseek($this->opened_stream,$offset,$whence);
	}

	/**
	 * This method is called in response to fflush() calls on the stream.
	 * 
	 * If you have cached data in your stream but not yet stored it into the underlying storage, you should do so now.
	 * 
	 * @return booelan TRUE if the cached data was successfully stored (or if there was no data to store), or FALSE if the data could not be stored.
	 */
	function stream_flush ( )
	{
		return fflush($this->opened_stream);
	}

	/**
	 * This method is called in response to fstat() calls on the stream.
	 * 
	 * If you plan to use your wrapper in a require_once you need to define stream_stat().  
	 * If you plan to allow any other tests like is_file()/is_dir(), you have to define url_stat().
	 * stream_stat() must define the size of the file, or it will never be included.  
	 * url_stat() must define mode, or is_file()/is_dir()/is_executable(), and any of those functions affected by clearstatcache() simply won't work.
	 * It's not documented, but directories must be a mode like 040777 (octal), and files a mode like 0100666.  
	 * If you wish the file to be executable, use 7s instead of 6s.  
	 * The last 3 digits are exactly the same thing as what you pass to chmod.  
	 * 040000 defines a directory, and 0100000 defines a file.  
	 *
	 * @return array containing the same values as appropriate for the stream.
	 */
	function stream_stat ( )
	{
		return fstat($this->opened_stream);
	}

	/**
	 * This method is called in response to unlink() calls on URL paths associated with the wrapper.
	 * 
	 * It should attempt to delete the item specified by path.
	 * In order for the appropriate error message to be returned, do not define this method if your wrapper does not support unlinking!
	 *
	 * @param string $path
	 * @return boolean TRUE on success or FALSE on failure
	 */
	static function unlink ( $path )
	{
		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		return unlink($url);
	}

	/**
	 * This method is called in response to rename() calls on URL paths associated with the wrapper.
	 * 
	 * It should attempt to rename the item specified by path_from to the specification given by path_to. 
	 * In order for the appropriate error message to be returned, do not define this method if your wrapper does not support renaming.
	 *
	 * The regular filesystem stream-wrapper returns an error, if $url_from and $url_to are not either both files or both dirs!
	 *
	 * @param string $path_from
	 * @param string $path_to
	 * @return boolean TRUE on success or FALSE on failure
	 */
	static function rename ( $path_from, $path_to )
	{
		if (!($url_from = self::resolve_url($path_from)) ||
			!($url_to = self::resolve_url($path_to)))
		{
			return false;
		}
		return rename($url_from,$url_to);
	}

	/**
	 * This method is called in response to mkdir() calls on URL paths associated with the wrapper.
	 * 
	 * It should attempt to create the directory specified by path. 
	 * In order for the appropriate error message to be returned, do not define this method if your wrapper does not support creating directories. 
	 *
	 * @param string $path
	 * @param int $mode
	 * @param int $options Posible values include STREAM_REPORT_ERRORS and STREAM_MKDIR_RECURSIVE
	 * @return boolean TRUE on success or FALSE on failure
	 */
	static function mkdir ( $path, $mode, $options )
	{
		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		return mkdir($url,$mode,$options);
	}

	/**
	 * This method is called in response to rmdir() calls on URL paths associated with the wrapper.
	 * 
	 * It should attempt to remove the directory specified by path. 
	 * In order for the appropriate error message to be returned, do not define this method if your wrapper does not support removing directories.
	 *
	 * @param string $path
	 * @param int $options Possible values include STREAM_REPORT_ERRORS.
	 * @return boolean TRUE on success or FALSE on failure.
	 */
	static function rmdir ( $path, $options )
	{
		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		return rmdir($url);
	}

	/**
	 * Allow to call methods of the underlying stream wrapper: touch, chmod, chgrp, chown, ...
	 * 
	 * We cant use a magic __call() method, as it does not work for static methods!
	 *
	 * @param string $name
	 * @param array $params first param has to be the path, otherwise we can not determine the correct wrapper
	 */
	static private function _call_on_backend($name,$params)
	{
		$path = $params[0];
		
		if (!($url = self::resolve_url($params[0])))
		{
			return false;
		}
		if (($scheme = parse_url($url,PHP_URL_SCHEME)))
		{
			if (!class_exists($class = $scheme.'_stream_wrapper') || !method_exists($class,$name))
			{
				trigger_error("Can't $name for scheme $scheme!\n",E_USER_WARNING);
				return false;
			}
			$params[0] = $url;

			return call_user_func_array(array($scheme.'_stream_wrapper',$name),$params);
		}
		// call the filesystem specific function
		if (!function_exists($name))
		{
			return false;
		}
		return $name($url,$time);
	}

	/**
	 * This is not (yet) a stream-wrapper function, but it's necessary and can be used static
	 *
	 * @param string $path
	 * @param int $time=null modification time (unix timestamp), default null = current time
	 * @param int $atime=null access time (unix timestamp), default null = current time, not implemented in the vfs!
	 * @return boolean true on success, false otherwise
	 */
	static function touch($path,$time=null,$atime=null)
	{
		return self::_call_on_backend('touch',array($path,$time,$atime));
	}

	/**
	 * This is not (yet) a stream-wrapper function, but it's necessary and can be used static
	 *
	 * Requires owner or root rights!
	 *
	 * @param string $path
	 * @param string $mode mode string see egw_vfs::mode2int
	 * @return boolean true on success, false otherwise
	 */
	static function chmod($path,$mode)
	{
		return self::_call_on_backend('chmod',array($path,$mode));
	}

	/**
	 * This is not (yet) a stream-wrapper function, but it's necessary and can be used static
	 * 
	 * Requires root rights!
	 *
	 * @param string $path
	 * @param int $owner numeric user id
	 * @return boolean true on success, false otherwise
	 */
	static function chown($path,$owner)
	{
		return self::_call_on_backend('chown',array($path,$owner));
	}

	/**
	 * This is not (yet) a stream-wrapper function, but it's necessary and can be used static
	 * 
	 * Requires owner or root rights!
	 *
	 * @param string $path
	 * @param int $group numeric group id
	 * @return boolean true on success, false otherwise
	 */
	static function chgrp($path,$group)
	{
		return self::_call_on_backend('chgrp',array($path,$group));
	}

	/**
	 * This is not (yet) a stream-wrapper function, but it's necessary and can be used static
	 * 
	 * The methods use the following ways to get the mime type (in that order)
	 * - directories (is_dir()) --> self::DIR_MIME_TYPE
	 * - stream implemented by class defining the STAT_RETURN_MIME_TYPE constant --> use mime-type returned by url_stat
	 * - for regular filesystem use mime_content_type function if available
	 * - use eGW's mime-magic class
	 *
	 * @param string $path
	 * @return string mime-type (self::DIR_MIME_TYPE for directories)
	 */
	static function mime_content_type($path)
	{
		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		if (is_dir($url))
		{
			$mime = self::DIR_MIME_TYPE;
		}
		if (!$mime && ($scheme = parse_url($url,PHP_URL_SCHEME)))
		{
			// check it it's an eGW stream wrapper returning mime-type via url_stat
			if (class_exists($class = $scheme.'_stream_wrapper') && ($mime_attr = constant($class.'::STAT_RETURN_MIME_TYPE')))
			{
				$stat = call_user_func(array($scheme.'_stream_wrapper','url_stat'),parse_url($url,PHP_URL_PATH),0);
				if ($stat[$mime_attr])
				{
					$mime = $stat[$mime_attr];
				}
			}
		}
		// if we operate on the regular filesystem and the mime_content_type function is available --> use it
		if (!$mime && !$scheme && function_exists('mime_content_type'))
		{
			$mime = mime_content_type($path);
		}
		// using eGW's own mime magic
		// ToDo: rework mime_magic as all methods cound be static!
		if (!$mime)
		{
			static $mime_magic;
			if (is_null($mime_magic))
			{
				$mime_magic = mime_magic();
			}
			$mime = $mime_magic->filename2mime(parse_url($url,PHP_URL_PATH));
		}
		//error_log(__METHOD__."($path) mime=$mime");
		return $mime;
	}
	
	/**
	 * This method is called immediately when your stream object is created for examining directory contents with opendir(). 
	 * 
	 * @param string $path URL that was passed to opendir() and that this object is expected to explore.
	 * @return booelan 
	 */
	function dir_opendir ( $path, $options )
	{
		$this->opened_dir = $this->extra_dirs = null;
		$this->extra_dir_ptr = 0;
		
		if (!($this->opened_dir_url = self::resolve_url($path)))
		{
			return false;
		}
		if (!($this->opened_dir = opendir($this->opened_dir_url)))
		{
			return false;
		}
		$this->opened_dir_writable = ($stat = @stat($this->opened_dir_url)) && egw_vfs::check_access($stat,egw_vfs::WRITABLE);

		// check our fstab if we need to add some of the mountpoints
		$basepath = parse_url($this->opened_dir_url,PHP_URL_PATH);
		foreach(self::$fstab as $mounted => $nul)
		{
			if (dirname($mounted) == $basepath && $mounted != '/')
			{
				$this->extra_dirs[] = basename($mounted);
			}
		}
		return true;
	}

	/**
	 * This method is called in response to stat() calls on the URL paths associated with the wrapper.
	 * 
	 * It should return as many elements in common with the system function as possible. 
	 * Unknown or unavailable values should be set to a rational value (usually 0).
	 * 
	 * If you plan to use your wrapper in a require_once you need to define stream_stat().  
	 * If you plan to allow any other tests like is_file()/is_dir(), you have to define url_stat().
	 * stream_stat() must define the size of the file, or it will never be included.  
	 * url_stat() must define mode, or is_file()/is_dir()/is_executable(), and any of those functions affected by clearstatcache() simply won't work.
	 * It's not documented, but directories must be a mode like 040777 (octal), and files a mode like 0100666.  
	 * If you wish the file to be executable, use 7s instead of 6s.  
	 * The last 3 digits are exactly the same thing as what you pass to chmod.  
	 * 040000 defines a directory, and 0100000 defines a file.  
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
	static function url_stat ( $path, $flags )
	{
		//error_log(__METHOD__."('$path',$flags)");

		if (!($url = self::resolve_url($path)))
		{
			return false;
		}
		//error_log(__METHOD__."('$path',$flags) calling stat($url)");
		return @stat($url);	// suppressed the stat failed warnings
	}
	
	/**
	 * This method is called in response to readdir().
	 * 
	 * It should return a string representing the next filename in the location opened by dir_opendir().
	 * 
	 * Unless other filesystem, we only return files readable by the user, if the dir is not writable for him.
	 * This is done to hide files and dirs not accessible by the user (eg. other peoples home-dirs in /home).
	 * 
	 * @return string
	 */
	function dir_readdir ( )
	{
		if ($this->extra_dirs && count($this->extra_dirs) > $this->extra_dir_ptr)
		{
			return $this->extra_dirs[$this->extra_dir_ptr++];
		}
		// only return children readable by the user, if dir is not writable
		do {
			if (($file = readdir($this->opened_dir)) !== false && !$this->opened_dir_writable)
			{
				$stat = stat($this->opened_dir_url.'/'.$file);
			}
			//echo __METHOD__."() opened_dir_writable=$this->opened_dir_writable, file=$file, readable=".(int)egw_vfs::check_access($stat,egw_vfs::READABLE).", loop=".
			//	(int)($file !== false && $stat && !egw_vfs::check_access($stat,egw_vfs::READABLE))."\n";
		}
		while($file !== false && $stat && !egw_vfs::check_access($stat,egw_vfs::READABLE));
		
		return $file;
	}

	/**
	 * This method is called in response to rewinddir().
	 * 
	 * It should reset the output generated by dir_readdir(). i.e.: 
	 * The next call to dir_readdir() should return the first entry in the location returned by dir_opendir().
	 * 
	 * @return boolean
	 */
	function dir_rewinddir ( )
	{
		$this->extra_dir_ptr = 0;

		return rewinddir($this->opened_dir);
	}
	
	/**
	 * This method is called in response to closedir().
	 * 
	 * You should release any resources which were locked or allocated during the opening and use of the directory stream. 
	 * 
	 * @return boolean
	 */
	function dir_closedir ( )
	{
		$ret = closedir($this->opened_dir);
		
		$this->opened_dir = $this->extra_dirs = null;
		
		return $ret;
	}
	
	/**
	 * Load stream wrapper for a given schema
	 *
	 * @param string $scheme
	 * @return boolean
	 */
	static function load_wrapper($scheme)
	{
		if (!in_array($scheme,self::get_wrappers()))
		{
			switch($scheme)
			{
				case 'webdav':
					require_once('HTTP/WebDAV/Client.php');
					self::$wrappers[] = 'webdav';
					break;
				case 'oldvfs':
				case 'sqlfs':
					require_once(EGW_API_INC.'/class.'.$scheme.'_stream_wrapper.inc.php');
					self::$wrappers[] = $scheme;
					break;
				case '':
					return true;	// default file, always loaded
				default:
					trigger_error("Can't load stream-wrapper for scheme '$scheme'!",E_USER_WARNING);
					return false;
			}
		}
		return true;
	}
	
	/**
	 * Return already loaded stream wrappers
	 *
	 * @return array
	 */
	static function get_wrappers()
	{
		if (is_null(self::$wrappers))
		{
			self::$wrappers = stream_get_wrappers();
		}
		return self::$wrappers;
	}
	
	static function init_static()
	{
		stream_register_wrapper(self::SCHEME,__CLASS__);
		
		if ($GLOBALS['egw_info']['server']['vfs_fstab'] && 
			is_array($fstab = unserialize($GLOBALS['egw_info']['server']['vfs_fstab'])))
		{
			self::$fstab = $fstab;				
		}
	}
}

vfs_stream_wrapper::init_static();