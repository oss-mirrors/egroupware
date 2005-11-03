function onNodeSelect(_nodeID)
{
	targetName.value = tree.getSelectedItemText();
	targetID.value = _nodeID;
	window.close();
	return true;
}