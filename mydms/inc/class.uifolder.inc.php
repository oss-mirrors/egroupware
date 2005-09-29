<?php
	/***************************************************************************\
	* eGroupWare - mydms                                                        *
	* http://www.linux-at-work.de                                               *
	* http://www.phpgw.de                                                       *
	* http://www.egroupware.org                                                 *
	* Written by : Lars Kneschke [lkneschke@linux-at-work.de]                   *
	* -------------------------------------------------                         *
	* This program is free software; you can redistribute it and/or modify it   *
	* under the terms of the GNU General Public License as published by the     *
	* Free Software Foundation; version 2 of the License.                       *
	\***************************************************************************/
	
	/* $Id$ */

	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.Settings.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.DBAccess.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.AccessUtils.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassAccess.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassUser.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassGroup.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassFolder.php');
	require_once(EGW_SERVER_ROOT.'/mydms/inc/inc.ClassDocument.php');

	class uifolder
	{
		var $public_functions = array
		(
			'getInitialFolderView'	=> 'true',
			'getSubFolder'		=> 'true',
		);
		
		function generateXML($_folder, $_childID, $_targetID, $_childContent)
		{
			$subFolders = $_folder->getSubFolders();
			
			#$retValue = "<tree id='".$folderID."'>";

			foreach((array)$subFolders as $subFolderObject)
			{
				$subFolderID	= $subFolderObject->getID();
				$subFolderName	= $subFolderObject->getName();
				$hasSubfolder	= ($subFolderObject->getSubFolders() ? 1 : 0);
				$childContent	= ($subFolderID == $_childID ? $_childContent : '');
				$selectedNode	= ($subFolderID == $_targetID ? " select='1'" : '');
				$openNode	= ($subFolderID == $_childID ? " open='1'" : '');
				$retValue .="<item child='$hasSubfolder' id='$subFolderID' text='$subFolderName' im0='folderClosed.gif'$selectedNode$openNode>$childContent</item>";
			}

			#$retValue .= "</tree>";
			
			return $retValue;
		}
		
		function getInitialFolderView()
		{
			header("Content-type:text/xml"); print("<?xml version=\"1.0\"?>");
			if (isset($_GET["id"]))
				$folderID=$_GET["id"];
			else
				$folderID=1;

			$folderObject = getFolder($folderID);
			
			$path = $folderObject->getPathNew();
			
			$xmlContent	= '';
			$clientID	= -1;
			
			while($subFolder = array_pop($path))
			{
				$xmlContent	= $this->generateXML($subFolder, $clientID, $folderID, $xmlContent);
				$clientID	= $subFolder->getID();
			}

			print "<tree id='$clientID'>$xmlContent</tree>";
			
			$GLOBALS['egw']->common->egw_exit();
		}
			
		function getSubFolder()
		{
			header("Content-type:text/xml"); print("<?xml version=\"1.0\"?>");
			if (isset($_GET["id"]))
				$folderID=$_GET["id"];
			else
				$folderID=1;

			$folderObject = getFolder($folderID);
			
			$subFolders = $folderObject->getSubFolders();
			
			print("<tree id='".$folderID."'>");

			foreach((array)$subFolders as $subFolderObject)
			{
				$subFolderID	= $subFolderObject->getID();
				$subFolderName	= $subFolderObject->getName();
				$hasSubfolder	= ($subFolderObject->getSubFolders() ? 1 : 0);
				print("<item child='$hasSubfolder' id='$subFolderID' text='$subFolderName' im0='folderClosed.gif'></item>");
			}

			print("</tree>");
			
			$GLOBALS['egw']->common->egw_exit();
		}
	}
?>