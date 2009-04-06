<?php
/**
 * eGroupWare - Filemanager - user interface
 *
 * @link http://www.egroupware.org
 * @package filemanager
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2008-9 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

class filemanager_ui
{
	/**
	 * Methods callable via menuaction
	 *
	 * @var array
	 */
	var $public_functions = array(
		'index' => true,
		'file' => true,
	);

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		// strip slashes from _GET parameters, if someone still has magic_quotes_gpc on
		if (get_magic_quotes_gpc() && $_GET)
		{
			$_GET = etemplate::array_stripslashes($_GET);
		}
		// do we have root rights
		if (egw_session::appsession('is_root','filemanager'))
		{
			egw_vfs::$is_root = true;
		}
	}

	/**
	 * Make the current user (vfs) root
	 *
	 * The user/pw is either the setup config user or a specially configured vfs_root user
	 *
	 * @param string $user='' setup config user to become root or '' to log off as root
	 * @param string $password=null setup config password to become root
	 */
	private function sudo($user='',$password=null)
	{
		if (!$user)
		{
			$is_root = false;
		}
		else
		{
			$is_root = egw_session::user_pw_hash($user,$password) === $GLOBALS['egw_info']['server']['config_hash'] ||	// config user&password
				$GLOBALS['egw_info']['server']['vfs_root_user'] && 							// vfs root user from setup >> configuration
				in_array($user,split(', *',$GLOBALS['egw_info']['server']['vfs_root_user'])) &&
				$GLOBALS['egw']->auth->authenticate($user, $password, 'text');
		}
		return egw_session::appsession('is_root','filemanager',egw_vfs::$is_root = $is_root);
	}

	/**
	 * Main filemanager page
	 *
	 * @param array $content=null
	 * @param string $msg=null
	 */
	function index(array $content=null,$msg=null)
	{
		$GLOBALS['egw_info']['flags']['include_xajax'] = true;

		$tpl = new etemplate('filemanager.index');

		if (!is_array($content))
		{
			$content = array(
				'nm' => $GLOBALS['egw']->session->appsession('index','filemanager'),
			);
			if (!is_array($content['nm']))
			{
				$content['nm'] = array(
					'get_rows'       =>	'filemanager.filemanager_ui.get_rows',	// I  method/callback to request the data for the rows eg. 'notes.bo.get_rows'
					'filter'         => '1',	// current dir only
					'no_filter2'     => True,	// I  disable the 2. filter (params are the same as for filter)
					'no_cat'         => True,	// I  disable the cat-selectbox
					'lettersearch'   => True,	// I  show a lettersearch
					'searchletter'   =>	false,	// I0 active letter of the lettersearch or false for [all]
					'start'          =>	0,		// IO position in list
					'order'          =>	'name',	// IO name of the column to sort after (optional for the sortheaders)
					'sort'           =>	'ASC',	// IO direction of the sort: 'ASC' or 'DESC'
					'default_cols'   => '!comment,ctime',	// I  columns to use if there's no user or default pref (! as first char uses all but the named columns), default all columns
					'csv_fields'     =>	false, // I  false=disable csv export, true or unset=enable it with auto-detected fieldnames,
									//or array with name=>label or name=>array('label'=>label,'type'=>type) pairs (type is a eT widget-type)
				);
				$content['nm']['path'] = self::get_home_dir();
			}
			if (isset($_GET['msg'])) $msg = $_GET['msg'];
			if (isset($_GET['path']) && ($path = $_GET['path']))
			{
				if ($path[0] == '/' && egw_vfs::stat($path,true) && egw_vfs::is_dir($path) && egw_vfs::check_access($path,egw_vfs::READABLE))
				{
					$content['nm']['path'] = $path;
				}
				else
				{
					$msg .= lang('The requested path %1 is not available.',$path);
				}
			}
		}
		// check if we have a failed upload AND upload_max_filesize >= post_max_size --> no $_POST array
		if ($_GET['post_empty'] && self::km2int(ini_get('upload_max_filesize')) >= self::km2int(ini_get('post_max_size')))
		{
			$msg = lang('Error uploading file!')."\n".self::max_upload_size_message();
		}
		$content['nm']['msg'] = $msg;

		if ($content['action'] || $content['nm']['rows'])
		{
			if ($content['action'])
			{
				$content['nm']['msg'] = self::action($content['action'],$content['nm']['rows']['checked'],$content['nm']['path']);
				unset($content['action']);
			}
			elseif($content['nm']['rows']['delete'])
			{
				$content['nm']['msg'] = self::action('delete',array_keys($content['nm']['rows']['delete']),$content['nm']['path']);
			}
			unset($content['nm']['rows']);
		}
		$clipboard_files = $GLOBALS['egw']->session->appsession('clipboard_files','filemanager');
		$clipboard_type = $GLOBALS['egw']->session->appsession('clipboard_type','filemanager');

		if ($content['button'])
		{
			if ($content['button'])
			{
				list($button) = each($content['button']);
				unset($content['button']);
			}
			switch($button)
			{
				case 'up':
					if ($content['nm']['path'] != '/')
					{
						$content['nm']['path'] = dirname($content['nm']['path']);
					}
					break;
				case 'home':
					$content['nm']['path'] = self::get_home_dir();
					break;
				case 'createdir':
					if ($content['nm']['path'][0] != '/')
					{
						$ses = $GLOBALS['egw']->session->appsession('index','filemanager');
						$old_path = $ses['path'];
						$content['nm']['path'] = egw_vfs::concat($old_path,$content['nm']['path']);
					}
					if (!@egw_vfs::mkdir($content['nm']['path'],null,STREAM_MKDIR_RECURSIVE))
					{
						$content['nm']['msg'] = !egw_vfs::is_writable(dirname($content['nm']['path'])) ?
							lang('Permission denied!') : lang('Failed to create directory!');
						if (!$old_path)
						{
							$ses = $GLOBALS['egw']->session->appsession('index','filemanager');
							$old_path = $ses['path'];
						}
						$content['nm']['path'] = $old_path;
					}
					break;
				case 'symlink':
					$target = $content['nm']['path'];
					$ses = $GLOBALS['egw']->session->appsession('index','filemanager');
					$content['nm']['path'] = $ses['path'];
					$link = egw_vfs::concat($content['nm']['path'],basename($target));
					$abs_target = $target[0] == '/' ? $target : egw_vfs::concat($content['nm']['path'],$target);
					if (!egw_vfs::stat($abs_target))
					{
						$content['nm']['msg'] = lang('Link target %1 not found!',$abs_target);
						break;
					}
					$content['nm']['msg'] = egw_vfs::symlink($target,$link) ?
						lang('Symlink to %1 created.',$target) : lang('Error creating symlink to target %1!',$target);
					break;
				case 'paste':
					$content['nm']['msg'] = self::action($clipboard_type.'_paste',$clipboard_files,$content['nm']['path']);
					break;
				case 'upload':
					if (!$content['upload'])
					{
						$content['nm']['msg'] = lang('You need to select some files first!');
						break;
					}
					$upload_success = $upload_failure = array();
					foreach(isset($content['upload'][0]) ? $content['upload'] : array($content['upload']) as $upload)
					{
						// strip '?', '/' and '#' from filenames, as they are forbidden for sqlfs / make problems
						$to = egw_vfs::concat($content['nm']['path'],str_replace(array('?','/','#'),'',$upload['name']));
						if ($upload && is_uploaded_file($upload['tmp_name']) &&
							(egw_vfs::is_writable($content['nm']['path']) || egw_vfs::is_writable($to)) &&
							copy($upload['tmp_name'],egw_vfs::PREFIX.$to))
						{
							$upload_success[] = $upload['name'];
						}
						else
						{
							$upload_failure[] = $upload['name'];
						}
					}
					$content['nm']['msg'] = '';
					if ($upload_success)
					{
						$content['nm']['msg'] = count($upload_success) == 1 && !$upload_failure ? lang('File successful uploaded.') :
							lang('%1 successful uploaded.',implode(', ',$upload_success));
					}
					if ($upload_failure)
					{
						$content['nm']['msg'] .= ($upload_success ? "\n" : '').lang('Error uploading file!')."\n".self::max_upload_size_message();
					}
					break;
			}
		}
		if (!egw_vfs::stat($content['nm']['path'],true) || !egw_vfs::is_dir($content['nm']['path']))
		{
			$content['nm']['msg'] .= ' '.lang('Directory not found or no permission to access it!');
		}
		else
		{
			$dir_is_writable = egw_vfs::is_writable($content['nm']['path']);
		}
		$content['paste_tooltip'] = $clipboard_files ? '<p><b>'.lang('%1 the following files into current directory',
			$clipboard_type=='copy'?lang('Copy'):lang('Move')).':</b><br />'.implode('<br />',$clipboard_files).'</p>' : '';
		$content['upload_size'] = self::max_upload_size_message();
		//_debug_array($content);

		$readonlys['button[paste]'] = !$clipboard_files;
		$readonlys['button[createdir]'] = !$dir_is_writable;
		$readonlys['button[symlink]'] = !$dir_is_writable;
		$readonlys['button[upload]'] = $readonlys['upload'] = !$dir_is_writable;

		if ($dir_is_writable || !$content['nm']['filter']) $sel_options['action']['delete'] = lang('Delete');
		$sel_options['action']['copy'] = lang('Copy to clipboard');
		if ($dir_is_writable || !$content['nm']['filter']) $sel_options['action']['cut'] = lang('Cut to clipboard');

		$sel_options['filter'] = array(
			'1' => 'Current directory',
			'2' => 'Directories sorted in',
			''  => 'Files from subdirectories',
		);
		$tpl->exec('filemanager.filemanager_ui.index',$content,$sel_options,$readonlys,array('nm' => $content['nm']));
	}

	/**
	 * Check if a file upload would overwrite an existing file and get a user confirmation in that case
	 *
	 * @param string $id id of the input
	 * @param string $name name (incl. client-path) of the file to upload
	 * @param string $dir current vfs directory
	 * @return string xajax output
	 */
	static function ajax_check_upload_target($id,$name,$dir)
	{
		$response = new xajaxResponse();

		//$response->addAlert(__METHOD__."('$id','$name','$dir')");

		$name = explode('/',str_replace('\\','/',$name));	// in case of win clients
		$name = array_pop($name);

		// strip '?', '/' and '#' from filenames, as they are forbidden for sqlfs / make problems
		$path = egw_vfs::concat($dir,str_replace(array('?','/','#'),'',$name));

		if(egw_vfs::deny_script($path))
		{
			$response->addAlert(lang('You are NOT allowed to upload a script!'));
			$response->addScript("document.getElementById('$id').value='';");
		}
		elseif (egw_vfs::stat($path))
		{
			if (egw_vfs::is_dir($path))
			{
				$response->addAlert(lang("There's already a directory with that name!"));
				$response->addScript("document.getElementById('$id').value='';");
			}
			else
			{
				$response->addScript("if (!confirm('".addslashes(lang('Do you want to overwrite the existing file %1?',$path))."')) document.getElementById('$id').value='';");
			}
		}
		else
		{
			// do nothing new file
		}
		return $response->getXML();
	}

	/**
	 * Get the configured start directory for the current user
	 *
	 * @return string
	 */
	static function get_home_dir()
	{
		$start = '/home/'.$GLOBALS['egw_info']['user']['account_lid'];

		// check if user specified a valid startpath in his prefs --> use it
		if (($path = $GLOBALS['egw_info']['user']['preferences']['filemanager']['startfolder']) &&
			$path[0] == '/' && egw_vfs::is_dir($path) && egw_vfs::check_access($path, egw_vfs::READABLE))
		{
			$start = $path;
		}
		return $start;
	}

	/**
	 * Convert numbers like '32M' or '512k' to integers
	 *
	 * @param string $size
	 * @return int
	 */
	private static function km2int($size)
	{
		if (!is_numeric($size))
		{
			switch(strtolower(substr($size,-1)))
			{
				case 'm':
					$size = 1024*1024*(int)$size;
					break;
				case 'k':
					$size = 1024*(int)$size;
					break;
			}
		}
		return (int)$size;
	}

	/**
	 * Message containing the max Upload size from the current php.ini settings
	 *
	 * We have to take the smaler one of upload_max_filesize AND post_max_size-2800 into account.
	 * memory_limit does NOT matter any more, because of the stream-interface of the vfs.
	 *
	 * @return string
	 */
	static function max_upload_size_message()
	{
		$upload_max_filesize = ini_get('upload_max_filesize');
		$post_max_size = ini_get('post_max_size');
		$max_upload = min(self::km2int($upload_max_filesize),self::km2int($post_max_size)-2800);

		return lang('Maximum size for uploads').': '.egw_vfs::hsize($max_upload).
			" (php.ini: upload_max_filesize=$upload_max_filesize, post_max_size=$post_max_size)";
	}

	/**
	 * Run a certain action with the selected file
	 *
	 * @param string $action
	 * @param array $selected selected pathes
	 * @param mixed $dir=null current directory
	 * @return string success or failure message displayed to the user
	 */
	static private function action($action,$selected,$dir=null)
	{
		//echo '<p>'.__METHOD__."($action,array(".implode(', ',$selected).",$dir)</p>\n";
		if (!count($selected))
		{
			return lang('You need to select some files first!');
		}
		$errs = $dirs = $files = 0;
		switch($action)
		{
			case 'delete':
				$dirs = $files = $errs = 0;
				foreach(egw_vfs::find($selected,array('depth'=>true)) as $path)
				{
					if (($is_dir = egw_vfs::is_dir($path) && !egw_vfs::is_link($path)) && egw_vfs::rmdir($path,0))
					{
						++$dirs;
					}
					elseif (!$is_dir && egw_vfs::unlink($path))
					{
						++$files;
					}
					else
					{
						++$errs;
					}
				}
				if ($errs)
				{
					return lang('%1 errors deleteting (%2 directories and %3 files deleted)!',$errs,$dirs,$files);
				}
				if ($dirs)
				{
					return lang('%1 directories and %2 files deleted.',$dirs,$files);
				}
				return $files == 1 ? lang('File deleted.') : lang('%1 files deleted.',$files);

			case 'copy':
			case 'cut':
				$GLOBALS['egw']->session->appsession('clipboard_files','filemanager',$selected);
				$GLOBALS['egw']->session->appsession('clipboard_type','filemanager',$action);
				return lang('%1 URLs %2 to clipboard.',count($selected),$action=='copy'?lang('copied'):lang('cut'));

			case 'copy_paste':
				foreach($selected as $path)
				{
					if (!egw_vfs::is_dir($path))
					{
						$to = egw_vfs::concat($dir,egw_vfs::basename($path));
						if ($path != $to && egw_vfs::copy($path,$to))
						{
							++$files;
						}
						else
						{
							++$errs;
						}
					}
					else
					{
						$len = strlen(dirname($path));
						foreach(egw_vfs::find($path) as $p)
						{
							$to = $dir.substr($p,$len);
							if ($to == $p)	// cant copy into itself!
							{
								++$errs;
								continue;
							}
							if (($is_dir = egw_vfs::is_dir($p)) && egw_vfs::mkdir($to,null,STREAM_MKDIR_RECURSIVE))
							{
								++$dirs;
							}
							elseif(!$is_dir && egw_vfs::copy($p,$to))
							{
								++$files;
							}
							else
							{
								++$errs;
							}
						}
					}
				}
				if ($errs)
				{
					return lang('%1 errors copying (%2 diretories and %3 files copied)!',$errs,$dirs,$files);
				}
				return $dirs ? lang('%1 directories and %2 files copied.',$dirs,$files) : lang('%1 files copied.',$files);

			case 'cut_paste':
				foreach($selected as $path)
				{
					$to = egw_vfs::concat($dir,egw_vfs::basename($path));
					if ($path != $to && egw_vfs::rename($path,$to))
					{
						++$files;
					}
					else
					{
						++$errs;
					}
				}
				$GLOBALS['egw']->session->appsession('clipboard_files','filemanager',false);	// cant move again
				if ($errs)
				{
					return lang('%1 errors moving (%2 files moved)!',$errs,$files);
				}
				return lang('%1 files moved.',$files);
		}
		return "Unknown action '$action'!";
	}

	/**
	 * Callback to fetch the rows for the nextmatch widget
	 *
	 * @param array $query
	 * @param array &$rows
	 * @param array &$readonlys
	 */
	function get_rows($query,&$rows,&$readonlys)
	{
		$GLOBALS['egw']->session->appsession('index','filemanager',$query);

		if (!egw_vfs::stat($query['path'],true) || !egw_vfs::is_dir($query['path']) || !egw_vfs::check_access($query['path'],egw_vfs::READABLE))
		{
			// we will leave here, since we are not allowed, or the location does not exist. Index must handle that, and give
			// an appropriate message
			egw::redirect_link('/index.php',array('menuaction'=>'filemanager.filemanager_ui.index',
				'path' => self::get_home_dir(),
				'msg' => lang('The requested path %1 is not available.',$query['path']),
			));
		}
		$rows = $dir_is_writable = array();
		if($query['searchletter'] && !empty($query['search']))
		{
			$namefilter = '/^'.$query['searchletter'].'.*'.str_replace(array('\\?','\\*'),array('.{1}','.*'),preg_quote($query['search'])).'/i';
			if ($query['searchletter'] == strtolower($query['search'][0]))
			{
				$namefilter = '/^('.$query['searchletter'].'.*'.str_replace(array('\\?','\\*'),array('.{1}','.*'),preg_quote($query['search'])).'|'.
					str_replace(array('\\?','\\*'),array('.{1}','.*'),preg_quote($query['search'])).')/i';
			}
		}
		elseif ($query['searchletter'])
		{
			$namefilter = '/^'.$query['searchletter'].'/i';
		}
		elseif(!empty($query['search']))
		{
			$namefilter = '/'.str_replace(array('\\?','\\*'),array('.{1}','.*'),preg_quote($query['search'])).'/i';
		}
		foreach(egw_vfs::find($query['path'],array(
			'mindepth' => 1,
			'maxdepth' => $query['filter'] ? (int)(boolean)$query['filter'] : null,
			'dirsontop' => $query['filter'] <= 1,
			'type' => $query['filter'] ? null : 'f',
			'order' => $query['order'], 'sort' => $query['sort'],
			'limit' => (int)$query['num_rows'].','.(int)$query['start'],
			'need_mime' => true,
			'name_preg' => $namefilter,
			'follow' => true,	// follow symlinks
		),true) as $path => $row)
		{
			//echo $path; _debug_array($row);
			$rows[++$n] = $row;
			$path2n[$path] = $n;

			$dir = dirname($path);
			if (!isset($dir_is_writable[$dir]))
			{
				$dir_is_writable[$dir] = egw_vfs::is_writable($dir);
			}
			if (!$dir_is_writable[$dir])
			{
				$readonlys["delete[$path]"] = true;	// no rights to delete the file
			}
		}
		// query comments and cf's for the displayed rows
		$cols_to_show = explode(',',$GLOBALS['egw_info']['user']['preferences']['filemanager']['nextmatch-filemanager.index.rows']);
		$cfs = config::get_customfields('filemanager');
		$all_cfs = in_array('customfields',$cols_to_show) && $cols_to_show[count($cols_to_show)-1][0] != '#';
		if ($path2n && (in_array('comment',$cols_to_show) || in_array('customfields',$cols_to_show)) &&
			($path2props = egw_vfs::propfind(array_keys($path2n))))
		{
			foreach($path2props as $path => $props)
			{
				unset($row);	// fixes a weird problem with php5.1, does NOT happen with php5.2
				$row =& $rows[$path2n[$path]];
				if ( !is_array($props) ) continue;
				foreach($props as $prop)
				{
					if (!$all_cfs && $prop['name'][0] == '#' && !in_array($prop['name'],$cols_to_show)) continue;
					$row[$prop['name']] = strlen($prop['val']) < 64 ? $prop['val'] : substr($prop['val'],0,64).' ...';
				}
			}
		}
		//_debug_array($readonlys);
		return egw_vfs::$find_total;
	}

	/**
	 * Preferences of a file/directory
	 *
	 * @param array $content=null
	 * @param string $msg=''
	 */
	function file(array $content=null,$msg='')
	{
		$tpl = new etemplate('filemanager.file');

		if (!is_array($content))
		{
			if (!($path = $_GET['path']) || !($stat = egw_vfs::lstat($path)))
			{
				$content['msg'] = lang('File or directory not found!');
			}
			else
			{
				$content = $stat;
				$content['name'] = egw_vfs::basename($path);
				$content['dir'] = dirname($path);
				$content['path'] = $path;
				$content['hsize'] = egw_vfs::hsize($stat['size']);
				$content['mime'] = egw_vfs::mime_content_type($path);
				$content['icon'] = egw_vfs::mime_icon($content['mime']);
				$content['gid'] *= -1;	// our widgets use negative gid's
				if (($props = egw_vfs::propfind($path)))
				{
					foreach($props as $prop) $content[$prop['name']] = $prop['val'];
				}
				if (($content['is_link'] = egw_vfs::is_link($path)))
				{
					$content['symlink'] = egw_vfs::readlink($path);
				}
			}
			$content['tabs'] = $_GET['tabs'];
			if (!($content['is_dir'] = egw_vfs::is_dir($path) && !egw_vfs::is_link($path)))
			{
				$content['perms']['executable'] = (int)!!($content['mode'] & 0111);
				$mask = 6;
				if (preg_match('/^text/',$content['mime']) && $content['size'] < 100000)
				{
					$content['text_content'] = file_get_contents(egw_vfs::PREFIX.$path);
				}
			}
			else
			{
				//currently not implemented in backend $content['perms']['sticky'] = (int)!!($content['mode'] & 0x201);
				$mask = 7;
			}
			foreach(array('owner' => 6,'group' => 3,'other' => 0) as $name => $shift)
			{
				$content['perms'][$name] = ($content['mode'] >> $shift) & $mask;
			}
			$content['is_owner'] = egw_vfs::has_owner_rights($path,$content);
		}
		else
		{
			//_debug_array($content);
			$path =& $content['path'];

			list($button) = @each($content['button']); unset($content['button']);
			if ($button == 'sudo' || $content['sudo']['user'])
			{
				$msg = $this->sudo($content['sudo']['user'],$content['sudo']['passwd']) ? lang('Root access granted.') :
					($content['sudo']['user'] ? lang('Wrong username or password!') : lang('Root access stopped.'));
				unset($content['sudo']);
				$content['is_owner'] = egw_vfs::has_owner_rights($path);
			}
			if (in_array($button,array('save','apply')))
			{
				$props = array();
				foreach($content['old'] as $name => $old_value)
				{
					if (isset($content[$name]) && $old_value != $content[$name])
					{
						if ($name == 'name')
						{
							$to = egw_vfs::concat($content['dir'],$content['name']);
							if (file_exists(egw_vfs::PREFIX.$to) && $content['confirm_overwrite'] !== $to)
							{
								$tpl->set_validation_error('name',lang("There's already a file with that name!").'<br />'.
									lang('To overwrite the existing file store again.',lang($button)));
								$content['confirm_overwrite'] = $to;
								if ($button == 'save') $button = 'apply';
								continue;
							}
							if (egw_vfs::rename($path,$to))
							{
								$msg .= lang('Renamed %1 to %2.',$path,$to).' ';
								$content['old']['name'] = $content[$name];
								$path = $to;
							}
							else
							{
								$msg .= lang('Rename of %1 to %2 failed!',$path,$to).' ';
								if (egw_vfs::deny_script($to))
								{
									$msg .= lang('You are NOT allowed to upload a script!').' ';
								}
							}
						}
						elseif ($name[0] == '#' || $name == 'comment')
						{
							$props[] = array('name' => $name, 'val' => $content[$name] ? $content[$name] : null);
						}
						else
						{
							static $name2cmd = array('uid' => 'chown','gid' => 'chgrp','perms' => 'chmod');
							$cmd = array('egw_vfs',$name2cmd[$name]);
							$value = $name == 'perms' ? self::perms2mode($content['perms']) : $content[$name];
							if ($content['modify_subs'])
							{
								if ($name == 'perms')
								{
									$changed = egw_vfs::find($path,array('type'=>'d'),$cmd,array($value));
									$changed += egw_vfs::find($path,array('type'=>'f'),$cmd,array($value & 0666));	// no execute for files
								}
								else
								{
									$changed = egw_vfs::find($path,null,$cmd,array($value));
								}
								$ok = $failed = 0;
								foreach($changed as $p => $r)
								{
									if ($r)
									{
										++$ok;
									}
									else
									{
										++$failed;
									}
								}
								if ($ok && !$failed)
								{
									$msg .= lang('Permissions of %1 changed.',$path.' '.lang('and all it\'s childeren'));
									$content['old'][$name] = $content[$name];
								}
								elseif($failed)
								{
									$msg .= lang('Failed to change permissions of %1!',$path.lang('and all it\'s childeren').
										($ok ? ' ('.lang('%1 failed, %2 succeded',$failed,$ok).')' : ''));
								}
							}
							elseif (call_user_func_array($cmd,array($path,$value)))
							{
								$msg .= lang('Permissions of %1 changed.',$path);
								$content['old'][$name] = $content[$name];
							}
							else
							{
								$msg .= lang('Failed to change permissions of %1!',$path);
							}
						}
					}
				}
				if ($props)
				{
					if (egw_vfs::proppatch($path,$props))
					{
						foreach($props as $prop)
						{
							$content['old'][$prop['name']] = $prop['val'];
						}
						$msg .= lang('Properties saved.');
					}
					else
					{
						$msg .= lang('Saving properties failed!');
					}
				}
			}
			elseif ($content['eacl'] && $content['is_owner'])
			{
				if ($content['eacl']['delete'])
				{
					list($ino_owner) = each($content['eacl']['delete']);
					list($ino,$owner) = explode('-',$ino_owner,2);	// $owner is a group and starts with a minus!
					$msg .= egw_vfs::eacl($path,null,$owner) ? lang('ACL deleted.') : lang('Error deleting the ACL entry!');
				}
				elseif ($button == 'eacl')
				{
					if (!$content['eacl']['owner'])
					{
						$msg .= lang('You need to select an owner!');
					}
					else
					{
						$msg .= egw_vfs::eacl($path,$content['eacl']['rights'],$content['eacl']['owner']) ?
							lang('ACL added.') : lang('Error adding the ACL!');
					}
				}
			}
			// refresh opender and close our window
			$link = egw::link('/index.php',array(
				'menuaction' => 'filemanager.filemanager_ui.index',
				'msg' => $msg,
			));
			$js = "opener.location.href='".addslashes($link)."'; ";
			if ($button == 'save') $js .= "window.close();";
			echo "<html>\n<body>\n<script>\n$js\n</script>\n</body>\n</html>\n";
			if ($button == 'save')$GLOBALS['egw']->common->egw_exit();
		}
		if ($content['is_link'] && !egw_vfs::stat($path))
		{
			$msg .= ($msg ? "\n" : '').lang('Link target %1 not found!',$content['symlink']);
		}
		$content['link'] = $GLOBALS['egw']->link(egw_vfs::download_url($path));
		$content['msg'] = $msg;

		if (($readonlys['uid'] = !egw_vfs::$is_root) && !$content['uid']) $content['ro_uid_root'] = 'root';
		// only owner can change group & perms
		if (($readonlys['gid'] = !$content['is_owner'] ||
			parse_url(egw_vfs::resolve_url($content['path']),PHP_URL_SCHEME) == 'oldvfs'))	// no uid, gid or perms in oldvfs
		{
			if (!$content['gid']) $content['ro_gid_root'] = 'root';
			foreach($content['perms'] as $name => $value)
			{
				$readonlys['perms['.$name.']'] = true;
			}
		}
		$readonlys['name'] = $path == '/' || !egw_vfs::is_writable($content['dir']);
		$readonlys['comment'] = !egw_vfs::is_writable($path);
		$readonlys['tabs']['preview'] = $readonlys['tabs']['perms'] = $content['is_link'];

		// if neither owner nor is writable --> disable save&apply
		$readonlys['button[save]'] = $readonlys['button[apply]'] = !$content['is_owner'] && !egw_vfs::is_writable($path);

		if (!($cfs = config::get_customfields('filemanager')))
		{
			$readonlys['tabs']['custom'] = true;
		}
		elseif (!egw_vfs::is_writable($path))
		{
			foreach($cfs as $name => $data)
			{
				$readonlys['#'.$name] = true;
			}
		}
		$readonlys['tabs']['eacl'] = true;	// eacl off by default
		if ($content['is_dir'])
		{
			$readonlys['tabs']['preview'] = true;	// no preview tab for dirs
			$sel_options['rights']=$sel_options['owner']=$sel_options['group']=$sel_options['other'] = array(
				7 => lang('Display and modification of content'),
				5 => lang('Display of content'),
				0 => lang('No access'),
			);
			if(($content['eacl'] = egw_vfs::get_eacl($content['path'])) !== false)	// backend supports eacl
			{
				unset($readonlys['tabs']['eacl']);	// --> switch the tab on again
				foreach($content['eacl'] as &$eacl)
				{
					$eacl['path'] = parse_url($eacl['path'],PHP_URL_PATH);
					$readonlys['delete['.$eacl['ino'].'-'.$eacl['owner'].']'] = $eacl['ino'] != $content['ino'] ||
						$eacl['path'] != $content['path'] || !$content['is_owner'];
				}
				array_unshift($content['eacl'],false);	// make the keys start with 1, not 0
				$content['eacl']['rights'] = $content['eacl']['owner'] = 0;
			}
		}
		else
		{
			$sel_options['owner']=$sel_options['group']=$sel_options['other'] = array(
				6 => lang('Read & write access'),
				4 => lang('Read access only'),
				0 => lang('No access'),
			);
		}
		$preserve = $content;
		if (!isset($preserve['old']))
		{
			$preserve['old'] = array(
				'perms' => $content['perms'],
				'name'  => $content['name'],
				'uid'   => $content['uid'],
				'gid'   => $content['gid'],
				'comment' => (string)$content['comment'],
			);
			if ($cfs) foreach($cfs as $name => $data)
			{
				$preserve['old']['#'.$name] = (string)$content['#'.$name];
			}
		}
		if (egw_vfs::$is_root)
		{
			$sudo_button =& $tpl->get_widget_by_name('sudo');
			$sudo_button = etemplate::empty_cell('button','button[sudo]',array(
				'label' => 'Logout',
				'help'  => 'Log out as superuser',
				'align' => 'right',
			));
		}
		$GLOBALS['egw_info']['flags']['java_script'] = "<script>window.focus();</script>\n";
		$GLOBALS['egw_info']['flags']['app_header'] = lang('Preferences').' '.$path;

		$tpl->exec('filemanager.filemanager_ui.file',$content,$sel_options,$readonlys,$preserve,2);
	}

	/**
	 * Convert perms array back to integer mode
	 *
	 * @param array $perms with keys owner, group, other, executable, sticky
	 * @return int
	 */
	private function perms2mode(array $perms)
	{
		$mode = $perms['owner'] << 6 | $perms['group'] << 3 | $perms['other'];
		if ($mode['executable'])
		{
			$mode |= 0111;
		}
		if ($mode['sticky'])
		{
			$mode |= 0x201;
		}
		return $mode;
	}
}
