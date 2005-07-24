<!-- BEGIN main -->
<table width="100%" border="0" cellpadding="0" cellspacing="1">
	<tr>
		<th width="210px" class="th">
			{lang_folder_list}
		</th>
		<th class="th">
			{lang_folder_settings}
		</th>
	</tr>
	<tr>
		<td>
			<form name="folderList" method="post" action="{form_action}">
			<!-- <div id="divFolderTree" style='width:200;height:200;'></div> -->
			<div id="divFolderTree" style="overflow:auto; width:250px; height:400px; margin-bottom: 0px;padding-left: 0px; padding-top:0px; z-index:100; border : 1px solid Silver;">
			{folder_tree}
			</form>
		</td>
		<td valign="top">
		<table width="100%" border="0" cellspacing="0" cellpading="0" bgcolor="white">
			<tr>
				<th id="tab1" class="activetab" onclick="javascript:tab.display(1);" style="width:50%;"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);" style="font-size:10px;">{lang_Overview}</a></th>
				<th id="tab2" class="activetab" onclick="javascript:tab.display(2);" style="width:50%;"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);" style="font-size:10px;">{lang_ACL}</a></th>
			</tr>
		</table>
			{settings_view}
		</td>
	</tr>
</table>

<!-- END main -->

<!-- BEGIN folder_settings -->
		<div id="tabcontent1" class="inactivetab" bgcolor="white">
			<table border="0" width="100%" cellpadding=2 cellspacing=0>
				<tr>
					<td style="width:30%;">
						Host:
					</td>
					<td style="width:50%;">
						{imap_server}
					</td>
					<td style="width:20%;">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						Foldername:
					</td>
					<td>
						<span id="folderName">{folderName}</span>
					</td>
					<td align="center">
						<button type='button' onclick='xajax_doXMLHTTP("felamimail.ajaxfelamimail.deleteFolder",tree.getSelectedItemId())'>{lang_delete}</button>
					</td>
				</tr>
				<tr>
					<td align="left">
						{lang_rename_folder}
					</td>
					<td align="left">
						<input type="text" size="30" id="newMailboxName" name="newMailboxName" value="{mailboxNameShort}" onchange="document.renameMailbox.submit()">
					</td>
					<td align="center">
						<button type='button' id="mailboxRenameButton" onclick='xajax_doXMLHTTP("felamimail.ajaxfelamimail.renameFolder",tree.getSelectedItemId(), tree.getParentId(tree.getSelectedItemId()), document.getElementById("newMailboxName").value)'>{lang_rename}</button>
					</td>
				</tr>
				<tr>
					<td align="left">
						{lang_create_subfolder}
					</td>
					<td align="left">
						<input type="text" size="30" id="newSubFolder" name="newSubFolder" onchange="document.createSubFolder.submit()">
					</td>
					<td align="center">
						<button type='button' onclick='xajax_doXMLHTTP("felamimail.ajaxfelamimail.addFolder",tree.getSelectedItemId(),document.getElementById("newSubFolder").value)'>{lang_create}</button>
					</td>
				</tr>
				<tr>
					<td align="left">
						&nbsp;
					</td>
					<td align="center" colspan="2">
						&nbsp;
					</td>
				</tr>
			</table>
		</div>
		<div id="tabcontent2" class="inactivetab">
			<table border="0" width="100%" cellpadding=2 cellspacing=0>
				<tr>
					<td align="left">
						ACL <a href="javascript:openWindow('{url_addACL}','felamiMailACL','400','200');">add ACL</a>
					</td>
					<td>
						<form id="editACL">
						<span id="aclTable"></span>
					</td>
					<td align="center">
						<button type="button" onClick="javascript:xajax_doXMLHTTP('felamimail.ajaxfelamimail.deleteACL', xajax.getFormValues('editACL'));">{lang_delete_selected}</button>
						<form>
					</td>
				</tr>
			</table>
		</div>
<!-- END folder_settings -->

<!-- BEGIN mainFolder_settings -->
			<table border="0" width="100%" cellpadding=2 cellspacing=0>
				<tr class="th">
					<td colspan="3">
						<b>Host: {imap_server}</b>
					</td>
				</tr>
				<tr>
					<td width="150"align="left">
						{lang_create_subfolder}
					</td>
					<td align="center">
						<form action="{form_action}" method="post" name="createSubFolder">
						<input type="text" size="30" name="newSubFolder" onchange="document.createSubFolder.submit()">
					</td>
					<td align="center">
						<input type="submit" value="{lang_create}" name="createSubFolder">&nbsp;
						</form>
					</td>
				</tr>
			</table>
<!-- END mainFolder_settings -->

<!-- BEGIN add_acl -->
	<form id="formAddACL" >
		<table border="0" width="100%" bgcolor="#FFFFFF">
			<tr class="th">
				<td>
					Name
				</td>
				<td>
					L
				</td>
				<td>
					R
				</td>
				<td>
					S
				</td>
				<td>
					W
				</td>
				<td>
					I
				</td>
				<td>
					P
				</td>
				<td>
					C
				</td>
				<td>
					D
				</td>
				<td>
					A
				</td>
			</tr>

			<tr class="row_off">
				<td>
					<input type="text" name="accountName" id="accountName" style="width:100%;">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="l" id="acl_l">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="r" id="acl_r">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="s" id="acl_s">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="w" id="acl_w">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="i" id="acl_i">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="p" id="acl_p">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="c" id="acl_c">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="d" id="acl_d">
				</td>
				<td>
					<input type="checkbox" name="acl[]" value="a" id="acl_a">
				</td>
			</tr>
			
			<tr>
				<td colspan="4">
					<button onClick="javascript:window.close();"> 
						{lang_cancel}
					</button>
				</td>
				<td colspan="6" align="right">
					<button type="button" ddisabled="disabled" sstyle="color:silver;" onClick="resetACLAddView();">
						{lang_add}
					</button>
				</td>
			</tr>

		<table>
	</form>
<!-- END add_acl -->
