<?php
/**
 * Filemanager: mounting GUI
 *
 * @link http://www.egroupware.org/
 * @package filemanager
 * @author Ralf Becker <rb-AT-stylite.de>
 * @copyright (c) 2010-15 by Ralf Becker <rb-AT-stylite.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

use EGroupware\Stylite\Vfs\Versioning;

/**
 * Filemanager: mounting GUI
 */
class filemanager_admin extends filemanager_ui
{
	/**
	 * Functions callable via menuaction
	 *
	 * @var array
	 */
	public $public_functions = array(
		'index' => true,
		'fsck' => true,
	);

	/**
	 * Autheticated user is setup config user
	 *
	 * @var boolean
	 */
	static protected $is_setup = false;

	/**
	 * Do we have versioning (Versioning\StreamWrapper class) available and with which schema
	 *
	 * @var string
	 */
	protected $versioning;

	/**
	 * Do not allow to (un)mount these
	 *
	 * @var array
	 */
	protected static $protected_path = array('/apps', '/templates');

	/**
	 * Constructor
	 */
	function __construct()
	{
		// make sure user has admin rights
		if (!isset($GLOBALS['egw_info']['user']['apps']['admin']))
		{
			throw new egw_exception_no_permission_admin();
		}
		// sudo handling
		parent::__construct();
		self::$is_setup = egw_session::appsession('is_setup','filemanager');

		if (class_exists('EGroupware\Stylite\Vfs\Versioning\StreamWrapper'))
		{
			$this->versioning = Versioning\StreamWrapper::SCHEME;
		}
	}

	/**
	 * Mount GUI
	 *
	 * @param array $content=null
	 * @param string $msg=''
	 */
	public function index(array $content=null, $msg='', $msg_type=null)
	{
		if (is_array($content))
		{
			//_debug_array($content);
			if ($content['sudo'])
			{
				$msg = $this->sudo($content['user'],$content['password'],self::$is_setup) ?
					lang('Root access granted.') : lang('Wrong username or password!');
				$msg_type = egw_vfs::$is_root ? 'success' : 'error';
			}
			elseif ($content['etemplates'] && $GLOBALS['egw_info']['user']['apps']['admin'])
			{
				$path = '/etemplates';
				$url = 'stylite.merge://default/etemplates?merge=.&lang=0&level=1&extension=xet&url=egw';
				$backup = egw_vfs::$is_root;
				egw_vfs::$is_root = true;
				$msg = egw_vfs::mount($url, $path) ?
					lang('Successful mounted %1 on %2.',$url,$path) : lang('Error mounting %1 on %2!',$url,$path);
				egw_vfs::$is_root = $backup;
			}
			elseif (egw_vfs::$is_root)
			{
				if ($content['logout'])
				{
					$msg = $this->sudo('','',self::$is_setup) ? 'Logout failed!' : lang('Root access stopped.');
					$msg_type = !egw_vfs::$is_root ? 'success' : 'error';
				}
				if ($content['mounts']['disable'] || self::$is_setup && $content['mounts']['umount'])
				{
					if (($unmount = $content['mounts']['umount']))
					{
						list($path) = @each($content['mounts']['umount']);
					}
					else
					{
						list($path) = @each($content['mounts']['disable']);
					}
					if (!in_array($path, self::$protected_path) && $path != '/')
					{
						$msg = egw_vfs::umount($path) ?
							lang('%1 successful unmounted.',$path) : lang('Error unmounting %1!',$path);
					}
					else	// re-mount / with sqlFS, to disable versioning
					{
						$msg = egw_vfs::mount($url=sqlfs_stream_wrapper::SCHEME.'://default'.$path,$path) ?
							lang('Successful mounted %1 on %2.',$url,$path) : lang('Error mounting %1 on %2!',$url,$path);
					}
				}
				if (($path = $content['mounts']['path']) &&
					($content['mounts']['enable'] || self::$is_setup && $content['mounts']['mount']))
				{
					$url = str_replace('$path',$path,$content['mounts']['url']);
					if (empty($url) && $this->versioning) $url = Versioning\StreamWrapper::PREFIX.$path;

					if ($content['mounts']['enable'] && !$this->versioning)
					{
						$msg = lang('Versioning requires <a href="http://www.egroupware.org/products">Stylite EGroupware Enterprise Line (EPL)</a>!');
						$msg_type = 'info';
					}
					elseif (!egw_vfs::file_exists($path) || !egw_vfs::is_dir($path))
					{
						$msg = lang('Path %1 not found or not a directory!',$path);
						$msg_type = 'error';
					}
					// dont allow to change mount of /apps or /templates (eg. switching on versioning)
					elseif (in_array($path, self::$protected_path))
					{
						$msg = lang('Permission denied!');
						$msg_type = 'error';
					}
					else
					{
						$msg = egw_vfs::mount($url,$path) ?
							lang('Successful mounted %1 on %2.',$url,$path) : lang('Error mounting %1 on %2!',$url,$path);
					}
				}
				if ($content['allow_delete_versions'] != $GLOBALS['egw_info']['server']['allow_delete_versions'])
				{
					config::save_value('allow_delete_versions', $content['allow_delete_versions'], 'phpgwapi');
					$GLOBALS['egw_info']['server']['allow_delete_versions'] = $content['allow_delete_versions'];
					$msg = lang('Configuration changed.');
				}
			}
		}
		if (true) $content = array();
		if ($this->versioning)
		{
			// statistical information
			$content = Versioning\StreamWrapper::summary();
			if ($content['total_files']) $content['percent_files'] = number_format(100.0*$content['version_files']/$content['total_files'],1).'%';
			if ($content['total_size']) $content['percent_size'] = number_format(100.0*$content['version_size']/$content['total_size'],1).'%';
		}
		if (!($content['is_root']=egw_vfs::$is_root))
		{
			if (empty($msg))
			{
				$msg = lang('You need to become root, to enable or disable versioning on a directory!');
				$msg_type = 'info';
			}
			$readonlys['logout'] = $readonlys['enable'] = $readonlys['allow_delete_versions'] = true;
		}
		$content['is_setup'] = self::$is_setup;
		$content['versioning'] = $this->versioning;
		$content['allow_delete_versions'] = $GLOBALS['egw_info']['server']['allow_delete_versions'];
		egw_framework::message($msg, $msg_type);

		$n = 2;
		$content['mounts'] = array();
		foreach(egw_vfs::mount() as $path => $url)
		{
			$content['mounts'][$n++] = array(
				'path' => $path,
				'url'  => $url,
			);
			$readonlys["disable[$path]"] = !$this->versioning || !egw_vfs::$is_root ||
				egw_vfs::parse_url($url,PHP_URL_SCHEME) != $this->versioning;
		}
		$readonlys['umount[/]'] = $readonlys['umount[/apps]'] = true;	// do not allow to unmount / or /apps
		$readonlys['url'] = !self::$is_setup;

		$sel_options['allow_delete_versions'] = array(
			''         => lang('Noone'),
			'root'     => lang('Superuser (root)'),
			'admins'   => lang('Administrators'),
			'everyone' => lang('Everyone'),
		);
		// show [Mount /etemplates] button for admin, if not already mounted and available
		$readonlys['etemplates'] = !class_exists('\EGroupware\Stylite\Vfs\Merge\StreamWrapper') ||
			($fs_tab=egw_vfs::mount($url)) && isset($fs_tab['/etemplates']) ||
			!isset($GLOBALS['egw_info']['user']['apps']['admin']);
		//_debug_array($content);

		$tpl = new etemplate_new('filemanager.admin');
		$GLOBALS['egw_info']['flags']['app_header'] = lang('VFS mounts and versioning');
		$tpl->exec('filemanager.filemanager_admin.index',$content,$sel_options,$readonlys);
	}

	/**
	 * Run fsck on sqlfs
	 */
	function fsck()
	{
		if ($_POST['cancel'])
		{
			egw_framework::redirect_link('/admin/index.php', null, 'admin');
		}
		$check_only = !isset($_POST['fix']);

		if (!($msgs = sqlfs_utils::fsck($check_only)))
		{
			$msgs = lang('Filesystem check reported no problems.');
		}
		$content = '<p>'.implode("</p>\n<p>", (array)$msgs)."</p>\n";

		$content .= html::form('<p>'.($check_only&&is_array($msgs)?html::submit_button('fix', lang('Fix reported problems')):'').
			html::submit_button('cancel', lang('Cancel')).'</p>',
			'','/index.php',array('menuaction'=>'filemanager.filemanager_admin.fsck'));

		$GLOBALS['egw']->framework->render($content, lang('Admin').' - '.lang('Check virtual filesystem'), true);
	}
}