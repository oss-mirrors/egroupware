function setMoveToFolderName(_folderName)
{
	opener.document.thisRule.folderName.value = _folderName;
	opener.document.thisRule.action_folder.checked = true;
	self.close();
}
