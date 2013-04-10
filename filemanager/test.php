<?php
/**
 * eGroupWare - Filemanager - test script
 *
 * @link http://www.egroupware.org
 * @package filemanager
 * @author Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @copyright (c) 2009-13 by Ralf Becker <RalfBecker-AT-outdoor-training.de>
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

$GLOBALS['egw_info']['flags'] = array(
	'currentapp' => 'filemanager'
);
include('../header.inc.php');

if (!($path = egw_cache::getSession('filemanger','test')))
{
	$path = '/home/'.$GLOBALS['egw_info']['user']['account_lid'];
}
if (isset($_REQUEST['path'])) $path = $_REQUEST['path'];
echo html::form("<p>Path: ".html::input('path',$path,'text','size="40"').
	html::submit_button('',lang('Submit'))."</p>\n",array(),'','','','','GET');

if (isset($path) && !empty($path))
{
	if ($path[0] != '/')
	{
		throw new egw_exception_wrong_userinput('Not an absolute path!');
	}
	egw_cache::setSession('filemanger','test',$path);

	echo "<h2>";
	foreach(explode('/',$path) as $n => $part)
	{
		$p .= ($p != '/' ? '/' : '').$part;
		echo ($n > 1 ? ' / ' : '').html::a_href($n ? $part : ' / ','/filemanager/test.php',array('path'=>$p));
	}
	echo "</h2>\n";

	echo "<p><b>egw_vfs::propfind('$path')</b>=".array2string(egw_vfs::propfind($path))."</p>\n";
	echo "<p><b>egw_vfs::resolve_url('$path')</b>=".array2string(egw_vfs::resolve_url($path))."</p>\n";

	$is_dir = egw_vfs::is_dir($path);
	echo "<p><b>is_dir('$path')</b>=".array2string($is_dir)."</p>\n";

	$time = microtime(true);
	$stat = egw_vfs::stat($path);
	$stime = number_format(1000*(microtime(true)-$time),1);

	$time = microtime(true);
	if ($is_dir)// && ($d = egw_vfs::opendir($path)))
	{
		$files = array();
		//while(($file = readdir($d)))
		foreach(egw_vfs::scandir($path) as $file)
		{
			if (egw_vfs::is_readable($fpath=egw_vfs::concat($path,$file)))
			{
				$file = html::a_href($file,'/filemanager/test.php',array('path'=>$fpath));
			}
			$file .= ' ('.egw_vfs::mime_content_type($fpath).')';
			$files[] = $file;
		}
		//closedir($d);
		$time = number_format(1000*(microtime(true)-$time),1);
		echo "<p>".($files ? 'Directory' : 'Empty directory')." took $time ms</p>\n";
		if($files) echo '<ol><li>'.implode("</li>\n<li>",$files).'</ol>'."\n";
	}

	echo "<p><b>stat('$path')</b> took $stime ms (mode = ".(isset($stat['mode'])?sprintf('%o',$stat['mode']).' = '.egw_vfs::int2mode($stat['mode']):'NULL').')';
	if (is_array($stat))
	{
		_debug_array($stat);
	}
	else
	{
		echo "<p>".array2string($stat)."</p>\n";
	}

	echo "<p><b>egw_vfs::is_readable('$path')</b>=".array2string(egw_vfs::is_readable($path))."</p>\n";
	echo "<p><b>egw_vfs::is_writable('$path')</b>=".array2string(egw_vfs::is_writable($path))."</p>\n";

	echo "<p><b>is_link('$path')</b>=".array2string(egw_vfs::is_link($path))."</p>\n";
	echo "<p><b>readlink('$path')</b>=".array2string(egw_vfs::readlink($path))."</p>\n";
	$time = microtime(true);
	$lstat = egw_vfs::lstat($path);
	$time = number_format(1000*(microtime(true)-$time),1);
	echo "<p><b>lstat('$path')</b> took $time ms (mode = ".(isset($lstat['mode'])?sprintf('%o',$lstat['mode']).' = '.egw_vfs::int2mode($lstat['mode']):'NULL').')';
	if (is_array($lstat))
	{
		_debug_array($lstat);
	}
	else
	{
		echo "<p>".array2string($lstat)."</p>\n";
	}
	if (!$is_dir && $stat)
	{
		echo "<p><b>egw_vfs::mime_content_type('$path')</b>=".array2string(egw_vfs::mime_content_type($path))."</p>\n";
		echo "<p><b>filesize(egw_vfs::PREFIX.'$path')</b>=".array2string(filesize(egw_vfs::PREFIX.$path))."</p>\n";
		echo "<p><b>bytes(file_get_contents(egw_vfs::PREFIX.'$path'))</b>=".array2string(bytes(file_get_contents(egw_vfs::PREFIX.$path)))."</p>\n";
	}
}
$GLOBALS['egw']->common->egw_footer();
