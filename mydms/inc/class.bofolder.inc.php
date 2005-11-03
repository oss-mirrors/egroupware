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

	class bofolder
	{
		function copyFolder($_folderID, $_targetID, $_newFolderName, $_copySubFolder, $_copyDocuments)
		{
			//print "$_folderID, $_targetID, $_copySubFolder, $_copyDocuments";
			$folder		= getFolder($_folderID);
			$targetFolder	= getFolder($_targetID);
			
			$this->user		= getUser($GLOBALS['egw_info']['user']['account_id']);
			
			if (($folder->getAccessMode($this->user) < M_READWRITE) || ($targetFolder->getAccessMode($this->user) < M_READWRITE))
			{
				return false;
			}
			
			$newFolder = $targetFolder->addSubFolder($_newFolderName, $folder->getComment(), $this->user, $folder->getSequence());
			
			if($_copySubFolder)
			{
				$this->copySubFolder($folder, $newFolder, $_copyDocuments);
			}
			
			return $newFolder;
		}
		
		function copySubFolder($_sourceFolder, $_newParentFolder, $_copyDocuments)
		{
			$subFolders = $_sourceFolder->getSubFolders();
			
			foreach($subFolders as $sourceSubFolder)
			{
				$newFolder = $_newParentFolder->addSubFolder($sourceSubFolder->getName(), $sourceSubFolder->getComment(), $this->user, $sourceSubFolder->getSequence());
				$this->copySubFolder($sourceSubFolder, $newFolder, $_copyDocuments);
			}
		}
	}
?>