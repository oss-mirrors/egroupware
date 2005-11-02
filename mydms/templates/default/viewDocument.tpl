<!-- BEGIN main -->
<script type='text/javascript'>
	var folderChooserURL ='{folderChooserURL}';
</script>

<table width="100%" border="0" cellspacing="0" cellpading="0" bgcolor="white" hheight="100px">
	<tr class="topRow">
		<th width="33%">{delete}</th>
		<th width="33%">{view_online}</th>
		<th width="33%">{download}</th>
	</tr>
</table>
<br>
<table width="100%" border="0" cellspacing="0" cellpading="0">
	<tr>
		<th width="20%" id="tab1" class="activetab" onclick="javascript:tab.display(1);"><a href="#" tabindex="0" accesskey="1" onfocus="tab.display(1);" onclick="tab.display(1); return(false);" style="font-size:10px;">{lang_informations}</a></th>
		<th width="20%" id="tab2" class="activetab" onclick="javascript:tab.display(2);"><a href="#" tabindex="0" accesskey="2" onfocus="tab.display(2);" onclick="tab.display(2); return(false);" style="font-size:10px;">{lang_all_versions}</a></th>
		<th width="20%" id="tab3" class="activetab" onclick="javascript:tab.display(3);"><a href="#" tabindex="0" accesskey="3" onfocus="tab.display(3);" onclick="tab.display(3); return(false);" style="font-size:10px;">{lang_linked_documents}</a></th>
		<th width="20%" id="tab4" class="activetab" onclick="javascript:tab.display(4);"><a href="#" tabindex="0" accesskey="4" onfocus="tab.display(4);" onclick="tab.display(4); return(false);" style="font-size:10px;">{lang_notifications}</a></th>
		<th width="20%" id="tab5" class="activetab" onclick="javascript:tab.display(5);"><a href="#" tabindex="0" accesskey="5" onfocus="tab.display(5);" onclick="tab.display(5); return(false);" style="font-size:10px;">{lang_acl}</a></th>
	</tr>
</table>
<div style="border-left: dotted 1px grey; border-bottom: dotted 1px grey; border-right: dotted 1px grey; background: white;">
<div id="tabcontent1" class="inactivetab" bgcolor="white">
	{informations}
</div>

<div id="tabcontent2" class="inactivetab">

	<table cellspacing="5" cellpadding="0" border="0" width="100%">
		<tr>
			<td></td>
			<td></td>
			<td class="filelist" style="border-bottom: 1pt solid #000080;"><i>{lang_version}</i></td>
			<td rowspan="{rownum}" style="border-left: 1pt solid #000080;">&nbsp;</td>
			<td class="filelist" style="border-bottom: 1pt solid #000080;"><i>{lang_upload_date}</i></td>
			<td rowspan="{rownum}" style="border-left: 1pt solid #000080;">&nbsp;</td>
			<td class="filelist" style="border-bottom: 1pt solid #000080;"><i>{lang_comment}</i></td>
			<td rowspan="{rownum}" style="border-left: 1pt solid #000080;">&nbsp;</td>
			<td class="filelist" style="border-bottom: 1pt solid #000080;"><i>{lang_uploaded_by}</i></td>
			<td></td>
		</tr>
		{versions}
	</table>
</div>

<div id="tabcontent3" class="inactivetab">
<!--
<table cellspacing="5" cellpadding="0" border="1">
	<?
	if ($rownum > 1)
	{
		?>
		<tr>
		<td></td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?printMLText("name");?></i></td>
		<td rowspan="<?print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?printMLText("comment");?></i></td>
		<td rowspan="<?print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?printMLText("document_link_by");?></i></td>
		<td rowspan="<?print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td>
		<td class="filelist" style="border-bottom: 1pt solid #000080;"><i><?printMLText("document_link_public");?></i></td>
		<td></td>
		</tr>
		<?
		foreach($links as $link)
		{
			$responsibleUser = $link->getUser();
			$targetDoc = $link->getTarget();
			
			print "<tr>";
			print "<td><img src=\"images/file.gif\" width=18 height=18 border=0></td>";
			print "<td class=\"linklist\"><a href=\"out.ViewDocument.php?documentid=".$targetDoc->getID()."\" class=\"linklist\">".$targetDoc->getName()."</a></td>";
			print "<td class=\"linklist\">".$targetDoc->getComment()."</td>";
			print "<td class=\"linklist\">".$responsibleUser->getFullName()."</td>";
			print "<td class=\"linklist\">" . (($link->isPublic()) ? getMLText("yes") : getMLText("no")) . "</td>";
			print "<td>";
			if (($user->getID() == $responsibleUser->getID()) || ($user->getID() == $settings->_adminID) || ($link->isPublic() && ($document->getAccessMode($user) >= M_READWRITE )))
				print "<a href=\"../op/op.RemoveDocumentLink.php?documentid=".$documentid."&linkid=".$link->getID()."\"><img src=\"images/del.gif\" border=0></a>";
			print "</td>";
			print "</tr>";
		}
	}
	else
		print "<tr><td class=\"filelist\">".getMLText("no_document_links")."</td></tr>";
	?>
</table>


if ($user->getID() != $settings->_guestID)

	<form action="../op/op.AddDocumentLink.php" name="form1">
	<input type="Hidden" name="documentid" value="<?print $documentid;?>">
	<table>
		<tr>
			<td class="inputDescription"><?printMLText("choose_target_document");?>:</td>
			<td><?printDocumentChooser("form1");?></td>
		</tr>
		<?
			if ($document->getAccessMode($user) >= M_READWRITE)
			{
				print "<tr><td class=\"inputDescription\">".getMLText("document_link_public")."</td><td class=\"inputDescription\">";
				print "<input type=\"Radio\" name=\"public\" value=\"true\" checked>" . getMLText("yes") . "&nbsp;&nbsp;";
				print "<input type=\"Radio\" name=\"public\" value=\"false\">" . getMLText("no");
				print "</td></tr>";
			}
		?>
		<tr>
			<td colspan="2"><br><input type="Submit" value="<?printMLText("add_document_link");?>"></td>
		</tr>
	</table>
	</form>-->
</div>

<div id="tabcontent4" class="inactivetab">
	<table cellspacing="0" cellpadding="5" border="0" width="100%">
		<tr>
			<td style="border-bottom: 1pt solid #000080;" width="50px">&nbsp;</td>
			<td style="border-bottom: 1pt solid #000080;" class="notifylist"><i>{lang_name}</i></td>
			<td style="border-bottom: 1pt solid #000080;" width="50px">&nbsp;</td>
		</tr>
		{notifications}
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
	</table>

	<form action="{notify_form_action}" method="post" name="notify_form">
		<fieldset><legend>{lang_add_notification}</legend>
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td class="inputDescription">{lang_user}:</td>
				<td>
					{select_userid}
				</td>
			</tr>
			<tr>
				<td class="inputDescription">{lang_group}:</td>
				<td>
					{select_groupid}
				</td>
			</tr>
		</table>
		</fieldset>
	</form>
</div>

<div id="tabcontent5" class="inactivetab">
	{change_owner}
	{display_acl}
</div>

<div id="tabcontent6" class="inactivetab">
	<form enctype="multipart/form-data" name="update_file_form" method="post" action="{action_update_file}">
	<table cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr class="row_on">
			<td class="description" valign="top" width="150px">{lang_filename}:</td>
			<td class="infos" colspan="2"><input type="file" name="userfile"></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top">{lang_comment}:</td>
			<td class="infos" colspan="2"><textarea name="comment" style="width: 100%;"></textarea></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top">{lang_expires}:</td>
			<td class="infos" valign="top" colspan="2">
				{select_expire_update}
				<span id="jscalspan_update" class="{expire_class_update}">{expire_date_update}</span>
			</td>
		</tr>
		<tr>
			<td colspan="3" valign="top">
				<input type="submit" name="save" value="{lang_update}">
				<button type="button" name="cancel" value="{lang_cancel}" onClick="javascript:tab.display(1);">
					{lang_cancel}
				</button>
			</td>
		</tr>
	</table>
	</form>
</div>



</div>
<!-- END main -->

<!-- BEGIN lock_row -->
	<tr class="row_on">
		<td class="description" valign="top">{lang_lock_status}:</td>
		<td class="infos" colspan="3">
			<input id="lockStatus" type="hidden" name="lockStatus" value="unchanged">
			<input type="checkbox" onClick="javascript:toggleLock(this);" {checked_lock_status}>
			<div id="currentLockStatus" class="active">
				{lang_current_status}
			</div>
			<div id="lockFile" class="inactive">
				{lang_file_gets_unlocked}
			</div>
			<div id="unlockFile" class="inactive">
				{lang_file_gets_locked}
			</div>
		</td>
	</tr>
<!-- END lock_row -->

<!-- BEGIN version_row -->
	<tr>
		<td>
			{url_view_online}
		</td>
		<td>
			<a href="{url_download_file}" class="oldcontent"><img src="{download_image}" width=22 height=22 border=0 title="{lang_download}"></a>
		</td>
		<td class="filelist" align="center">{version_version}</td>
		<td class="filelist">{version_date}</td>
		<td class="filelist">{version_comment}</td>
		<td class="filelist">{version_uploadingUser}</td>
		<td>{url_delete_file}</td>
	</tr>
<!-- END version_row -->

<!-- BEGIN information_ro -->
	<table cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr>
			<td class="infos" valign="top" width="150px">{lang_filename}:</td>
			<td style="border-left: 1pt solid #000080;" rowspan="14" width="1px">&nbsp;</td>
			<td class="infos">{filename}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_owner}:</td>
			<td class="infos">
				<a class="infos" href="mailto:{owner_email}">{owner_fullname}</a>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_comment}:</td>
			<td class="infos">{comment}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_creation_date}:</td>
			<td class="infos">{creation_date}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_keywords}:</td>
			<td class="infos">{keywords}</td>
		</tr>
		{locking}
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_last_update}</td>
			<td class="infos">{last_update}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_current_version}:</td>
			<td class="infos">{current_version}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_comment_for_current_version}:</td>
			<td class="infos" valign="top">{current_comment}</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_uploaded_by}:</td>
			<td class="infos">
				<a class="infos" href="mailto:{updater_email}">{updater_fullname}</a>
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_file_size}:</td>
			<td class="infos">{file_size} bytes</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_mime_type}:</td>
			<td class="infos">
				<img align="absmiddle" src="images/icons/<?print getMimeIcon($latestContent->getFileType());?>"> 
				{mime_type}
			</td>
		</tr>
		<tr>
			<td class="infos" valign="top">{lang_expires}:</td>
			<td class="infos" valign="top">
				{expire_date_ro}
			</td>
		</tr>
	</table>
<!-- END information_ro -->

<!-- BEGIN information_rw -->
	<form name="info_form" method="post" action="{action_informations}">
	<fieldset><legend>{lang_general_information}</legend>
	<table cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr class="row_on">
			<td class="description" valign="top" width="150px">{lang_filename}:</td>
			<td class="infos" colspan="2"><input style="width: 100%;" type="text" name="fname" value="{filename}"></td>
			<td align="center" width="150px"><a href="#" tabindex="0" onfocus="tab.display(6);" onclick="tab.display(6); return(false);" style="font-size:10px;">{lang_update_document}</a></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top" width="150px">{lang_folder}:</td>
			<td class="infos" colspan="2">
				<input id="targetname" style="width: 100%;" type="text" name="targetname" value="{foldername}" disabled>
				<input type="hidden" id="targetid" name="targetid" value="unchanged">
			</td>
			<td align="center" width="150px"><a href="#" onclick="selectFolder({current_folder_id},'info_form'); return(false);" style="font-size:10px;">{lang_move_document}</a></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top">{lang_comment}:</td>
			<td class="infos" colspan="3"><textarea name="comment" style="width: 100%; height: 80px;">{comment}</textarea></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top">{lang_keywords}:</td>
			<td class="infos" colspan="3"><textarea name="keywords" style="width: 100%; height: 30px;">{keywords}</textarea></td>
		</tr>
		<tr class="row_on">
			<td class="description" valign="top">{lang_expires}:</td>
			<td class="infos" valign="top" colspan="3">
				{select_expire}
				<span id="jscalspan" class="{expire_class}">{expire_date}</span>
			</td>
		</tr>
		{locking}
		<tr class="row_on">
			<td class="description_small" valign="top">{lang_owner}:</td>
			<td class="infos" colspan="1">
				<a class="infos" href="mailto:{owner_email}">{owner_fullname}</a>
			</td>
			<td class="description_small" valign="top">{lang_creation_date}:</td>
			<td class="infos" colspan="1">{creation_date}</td>
		</tr>
		<tr class="row_on">
			<td colspan="4" valign="top">
				<button type="submit" name="save" value="save">
					{lang_save}
				</button>
				<button style="margin-left: 10px;" type="button" name="cancel" value="cancel" onClick="window.close()">
					{lang_cancel}
				</button>
			</td>
		</tr>
	</table>
	</fieldset>
	<p>
	<fieldset style="border: 1px solid silver;"><legend>{lang_information_about_last_update}</legend>
	<table cellpadding="5" cellspacing="0" border="0" width="100%">
		<tr class="row_off">
			<td class="description_small" valign="top" width="150px">{lang_last_update}</td>
			<td class="infos">{last_update}</td>
			<td class="description_small" valign="top" width="150px">{lang_current_version}:</td>
			<td class="infos">{current_version}</td>
		</tr>
		<tr class="row_off">
			<td class="description_small" valign="top">{lang_uploaded_by}:</td>
			<td class="infos">
				<a class="infos" href="mailto:{updater_email}">{updater_fullname}</a>
			</td>
			<td class="description_small" valign="top">{lang_file_size}:</td>
			<td class="infos">{file_size} bytes</td>
		</tr>
		<tr class="row_off">
			<td class="description_small" valign="top">{lang_comment_for_current_version}:</td>
			<td class="infos" valign="top" colspan="3">{current_comment}</td>
		</tr>
		<tr>
			<td class="description_small" valign="top">{lang_mime_type}:</td>
			<td class="infos" colspan="3">
				<img align="absmiddle" src="images/icons/<?print getMimeIcon($latestContent->getFileType());?>"> 
				{mime_type}
			</td>
		</tr>
	</table>
	</fieldset>
	</form>
<!-- END information_rw -->

<!-- BEGIN block_download -->
<a href="{download_link}"><img src="{download_image}" align="middle" border="0">{lang_download}</a>
<!-- END block_download -->

<!-- BEGIN block_view_online -->
<a target="_blank" href="{view_link}"><img src="{view_image}" align="middle" border="0">{lang_view_online}</a>
<!-- END block_view_online -->

<!-- BEGIN block_delete -->
<a href="{delete_link}" onClick="return confirm('{lang_confirm_delete}');"><img src="{delete_image}" align="middle" border="0">{lang_delete}</a>
<!-- END block_delete -->

<!-- BEGIN notification_row -->
	<tr>
		<td><img src="{notify_image}" width=16 height=16></td>
		<td class="notifylist">{notify_username}</td>
		<td align="right"><a href="{link_notify_delete}"><img title="{lang_delete_this_notification}" src="{delete_image}" width=15 height=15 border=0></a>
	</tr>
<!-- END notification_row -->

<!-- BEGIN block_change_owner -->
	<form name="change_owner" action="{action_change_owner}" method="post">
		<fieldset><legend>{lang_owner}</legend>
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td>
					{select_ownerid}
				</td>
				<td><input type="Submit"></td>
			</tr>
		</table>
		</fieldset>
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td>&nbsp;</td>
			</tr>
		</table>
	</form>
<!-- END block_change_owner -->

<!-- BEGIN block_acl_inherite -->
	<fieldset><legend>{lang_acl}</legend>
	<div>{lang_acl_get_inherited}</div>
	<p>
	<a href="{link_acl_copy}">{lang_copy_acl}</a><br>
	<a href="{link_acl_empty}">{lang_create_empty_acl}</a><br>
	</fieldset>
<!-- END block_acl_inherite -->

<!-- BEGIN block_acl_notinherite -->
	<fieldset><legend>{lang_acl}</legend>
	<div>{lang_inherite_acl_again}</div>
	<p>
	<a href="{link_acl_inherit_again}">{lang_inherit_acl_again}</a><br>

	<fieldset><legend>{lang_default_access}</legend>
	<form name="change_default_access" action="{action_change_default_access}" method="post">
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td>
					{select_default_access}
				</td>
			</tr>
		</table>
	</form>
	</fieldset>

	<fieldset><legend>{lang_current_acl}</legend>
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td></td>
				<td class="accessList" style="border-bottom: 1pt solid #000080;"><i>{lang_name}</i></td>
				<!-- <td rowspan="<?print $rownum;?>" style="border-left: 1pt solid #000080;">&nbsp;</td> -->
				<td class="accessList" style="border-bottom: 1pt solid #000080;"><i>{lang_access_mode}</i></td>
				<td></td>
				<td></td>
			</tr>
			{acls}
		</table>
	</fieldset>

	<fieldset><legend>{lang_add_acl}</legend>
	<form name="add_acl" action="{action_add_acl}" method="post">
		<table cellspacing="0" cellpadding="5" border="0" width="100%">
			<tr>
				<td class="inputDescription">{lang_user}:</td>
				<td>
					{select_add_acl_userid}
				</td>
				<td rowspan="3">
					<input type="Submit">
				</td>
			</tr>
			<tr>
				<td class="inputDescription">{lang_group}:</td>
				<td>
					{select_add_acl_groupid}
				</td>
			</tr>
			<tr>
				<td class="inputDescription">{lang_permission}:</td>
				<td>
					{select_add_acl_permission}
				</td>
			</tr>
		</table>
	</form>
	</fieldset>

	</fieldset>
<!-- END block_acl_notinherite -->

<!-- BEGIN acl_row -->
	<form method="post" action="{action_acl_row}">
	<tr>
		<td width="20px"><img src="{acl_image}" width=16 height=16></td>
		<td class="acllist">{acl_username}</td>
		<td class="acllist" width="310px">{acl_selectbox}</td>
		<td width="20px"><input type="Image" src="{save_image}" alt="{lang_save}"></td>
		<td width="20px" align="right"><a href="{link_acl_delete}" onClick="return confirm('{lang_confirm_acl_delete}');"><img title="{lang_delete_this_acl}" alt="{lang_delete}" src="{delete_image}" width=15 height=15 border=0></a>
	</tr>
	</form>
<!-- END acl_row -->

