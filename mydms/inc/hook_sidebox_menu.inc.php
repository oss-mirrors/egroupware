<?php
{ 
	$folderID	= ((int)$_GET[folderid] ? (int)$_GET[folderid] : 1);
	
	if(function_exists(getFolder))
	{
		$folder		= getFolder($folderID);
		$accessMode	= $folder->getAccessMode(getUser($GLOBALS['egw_info']['user']['account_id']));
	}
	$menu_title = lang('mydms');
	$file = Array(
		'Content' => $GLOBALS['egw']->link('/mydms/out/out.ViewFolder.php', array('folderid' => 1)),
		'Search'  => $GLOBALS['egw']->link('/mydms/out/out.SearchForm.php', array('folderid' => 1)),
	);
	display_sidebox($appname,$menu_title,$file); 
	
	if ($accessMode >= M_READWRITE)
	{
		$linkData = array
		(
			'folderid'	=> $folderID,
		);
	
		$file = array();
		$menu_title = lang('folder');
		$file['add subfolder']	= $GLOBALS['egw']->link('/mydms/out/out.AddSubFolder.php',$linkData);
		$file['add document']	= $GLOBALS['egw']->link('/mydms/out/out.AddDocument.php',$linkData);
		$file[]			= '_NewLine_';
		$file['edit folder']	= $GLOBALS['egw']->link('/mydms/out/out.EditFolder.php',$linkData);
		$file['move folder']	= $GLOBALS['egw']->link('/mydms/out/out.MoveFolder.php',$linkData);
		$file['copy folder']	= $GLOBALS['egw']->link('/index.php', array('menuaction' => 'mydms.uifolder.copyFolder')+$linkData);
		if($accessMode == M_ALL)
		{
			$file['delete folder']	= $GLOBALS['egw']->link('/mydms/out/out.RemoveFolder.php',$linkData);
		}
		$file[]			= '_NewLine_';
		$file['notifications']	= $GLOBALS['egw']->link('/mydms/out/out.FolderNotify.php',$linkData);
		if($accessMode == M_ALL)
		{
			$file['access rights']	= $GLOBALS['egw']->link('/mydms/out/out.FolderAccess.php',$linkData);
		}
		display_sidebox($appname,$menu_title,$file); 
	}
	
	if ($GLOBALS['phpgw_info']['user']['apps']['admin'])
	{
		$menu_title = 'Administration';
		$file = Array(
			'Admin-Tools'			=>  $settings->_httpRoot . "out.AdminTools.php"
		);
		display_sidebox($appname,$menu_title,$file);
	}
}
?>
