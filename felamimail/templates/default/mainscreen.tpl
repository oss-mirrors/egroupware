<!-- BEGIN main -->
<script language="JavaScript1.2">
var sURL = unescape(window.location.pathname);

// some translations needed for javascript functions

var movingMessages		= '{lang_moving_messages_to}';
var copyingMessages		= '{lang_copying_messages_to}';
var lang_askformove			= '{lang_askformove}';
var prefAskForMove			= '{prefaskformove}';
var prefAskForMultipleForward = '{prefaskformultipleforward}';
var CopyOrMove				= true;
var lang_MoveCopyTitle = '{lang_MoveCopyTitle}';
var lang_copy = '{lang_copy}';
var lang_move = '{lang_move}';
var lang_multipleforward = '{lang_multipleforward}';
var lang_cancel = '{lang_cancel}';

var lang_emptyTrashFolder	= '{lang_empty_trash}';
var lang_compressingFolder	= '{lang_compress_folder}';
var lang_select_target_folder	= '{lang_select_target_folder}';
var lang_updating_message_status = '{lang_updating_message_status}';
var lang_loading 		= '{lang_loading}';
var lang_deleting_messages 	= '{lang_deleting_messages}';
var lang_skipping_forward 	= '{lang_skipping_forward}';
var lang_skipping_previous 	= '{lang_skipping_previous}';
var lang_jumping_to_end 	= '{lang_jumping_to_end}';
var lang_jumping_to_start 	= '{lang_jumping_to_start}';
var lang_updating_view 		= '{lang_updating_view}';
var lang_mark_all_messages 	= '{lang_mark_all_messages}';
var lang_confirm_all_messages = '{lang_confirm_all_messages}';
var lang_sendnotify = "{lang_sendnotify}";

var MessageBuffer;
// global vars to store server and active folder info
//var activeServerID			= '{activeServerID}';
var activeFolder			= '{activeFolder}';
var activeFolderB64			= '{activeFolderB64}';
var activityImagePath		= '{ajax-loader}';
var test = '';

var objectManager = null;
var actionManager = null;
var mailGrid = null;
// how many row are selected currently
var checkedCounter=0;

// the refreshtimer objects
var aktiv;
var fm_timerFolderStatus;
var fm_previewMessageID;
var fm_previewMessageFolderType;

// refresh time for mailboxview
var refreshTimeOut = {refreshTime};
//refreshTimeOut = 105001;
//document.title=refreshTimeOut;

fm_startTimerFolderStatusUpdate(refreshTimeOut);
fm_startTimerMessageListUpdate(refreshTimeOut);

</script>

<TABLE WIDTH="100%" CELLPADDING="0" CELLSPACING="0" style="border: solid #aaaaaa 1px; border-right: solid black 1px; ">
	<tr class="navbarBackground">
		<td align="left" width="180px">
			<div class="parentDIV">
				<label><input style="width:12px; height:12px; border:none; margin: 1px; margin-left: 3px;" type="checkbox" id="selectAllMessagesCheckBox" onclick="selectFolderContent(this, refreshTimeOut)"> {lang_mark_all_messages}</label>
			</div>
		</td>
		<td align="right" width="90px">
			{select_search}
		</td>
		<td align="right">
			<input class="input_text" type="text" name="quickSearch" id="quickSearch" value="{quicksearch}" onChange="javascript:quickSearch();" onFocus="this.select();" style="font-size:11px; width:100%;">
		</td>
		<td align="left" width="40px" valign="middle">
			{img_clear_left}
		</td>
		<td align="left" width="40px" valign="middle">
			{lang_status}
		</td>
		<td align="center" width="100px">
			{select_status}
		</td>
		<td align="center" width="40px">
			{select_messagecount}
		</td>
		<td width="120px" style="white-space:nowrap; align:right; text-align:right;">
			<div class="parentDIV" style="text-align:right; align:right;">
				{navbarButtonsRight}
			</div>
		</td>
	</TR>
</table>
<form method="post" name="mainView" id="mainView" action="{reloadView}">
</form>

<input type="hidden" name="folderAction" id="folderAction" value="changeFolder">
<INPUT TYPE=hidden NAME="oldMailbox" value="{oldMailbox}">
<INPUT TYPE=hidden NAME="mailbox">
<TABLE  width="100%" cellpadding="0" cellspacing="0" border="0" style="height:100px;">
	<tr style="height: 20px;">
		<td nowrap>

			<span id="folderFunction" align="left" style="font-size:11px;"></span>
		</td>
		<td nowrap align="left" style="font-size:11px; width:auto;" colspan="2">
			<span id="messageCounter">{message}</span>
		</td>
		<td align="center" style="font-size:11px; color:red; width:180px;">
			<span id="vacationWarning">{vacation_warning}</span>
		</td>
		<td id="quotaDisplay" align="right" style="font-size:11px; width:180px;">
			{quota_display}
		</td>
	</tr>
	<TR>

		<TD valign="top" colspan="5">

			<!-- Start MessageList -->

			<form name="formMessageList" id="formMessageList">
			<div id="divMessageList" style="margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom: 0px; z-index:90; border : 1px solid Silver;">
				<!-- <table BORDER="0" style="width:98%; ppadding-left:2; table-layout: fixed;" cellspacing="100" cellpadding="100"> -->
					{header_rows}
				<!-- </table> -->
			</div>
			</form>

			<!-- End MessageList -->
		</TD>
	</TR>
</table>
<!-- END main -->

<!-- BEGIN message_table -->
<div id="divMessageTableList" style="height:{messagelist_height}; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom: 0px; z-index:90; border : 1px solid Silver;">

</div>
<span id="spanMessagePreview" style="margin-left:5px; margin-right:5px;">
	{IFrameForPreview}
</span>
<script type="text/javascript">
	var felamimail_iframe_height = parseInt("{messagelist_height}".replace(/px/g,""));

	function handleResize()
	{
		if (navigator.appName == 'Microsoft Internet Explorer') return true; // dont resize when IE
		/* Constant values */
		var MIN_TABLE_HEIGHT = 100;
		var MAX_TABLE_WHITESPACE = 25;

		var divMessageTableList = document.getElementById('divMessageTableList');
		var iframe = document.getElementById('messageIFRAME');
		var tdiframe = document.getElementById('tdmessageIFRAME');
		//var tableMessageTableList = document.getElementById('tableMessageTableList');
		var iframeheight = felamimail_iframe_height;
		if (isNaN(iframeheight)) iframeheight = 0;
		if (typeof divMessageTableList != 'undefined' && divMessageTableList != null)
		{
			/* The height parameter specifies how many height is left for the message list
			   if the iframe stays maximized. */
			if (window.parent && typeof window.parent.framework != 'undefined')
			{
				var height = $(document).height() - iframeheight - $(divMessageTableList).offset().top - 70;
			}
			else
			{
				var height = $(document).height() - iframeheight - $(divMessageTableList).offset().top - 90;
			}
			//alert('available height for messagelist:'+height);
			/* Tableheight specifies the rendered size of the table,
			   iframeheight the height of the message preview,
			   divheight the end size height of the table outer div */
			var tabnotavail = false;
			var tableheight = 300; //$(tableMessageTableList).height();
			if (mailGrid != null) tableheight = mailGrid.getDataHeight()+5;
			if (tableheight == 0 || isNaN(tableheight)) // set defaults to compute with, if the property is not set
			{
				tabnotavail = true;
				if (iframeheight<=0) tableheight = $(document).height() - 200; // full height butballow space for header and footer
				if (iframeheight>0) tableheight = iframeheight; // iframe and table are set to the same size
			}
			//alert('tableheight:'+tableheight);
			var divheight = height;
			if (typeof divheight == 'undefined' || isNaN(divheight)) divheight=0;
			/* If the remaining height is smaller than MIN_TABLE_HEIGHT, the iframe
			   will be scaled to a smaller size */
			if (height < MIN_TABLE_HEIGHT)
			{
				//alert('height smaller than minheight');
				divheight = MIN_TABLE_HEIGHT;
				iframeheight = iframeheight + (height - MIN_TABLE_HEIGHT);
			}

			/* If the divheight is grater than the actual size of the message table,
			   scale the divheight smaller and increase the size of the iframe */
			if (divheight > tableheight + MAX_TABLE_WHITESPACE)
			{
				//alert('divheight is greater than tableheight');
				var oh = divheight;
				divheight = tableheight + MAX_TABLE_WHITESPACE;
				if (tabnotavail==false) 
				{
					iframeheight = iframeheight + (oh - divheight);
				}
				else
				{
					divheight = divheight - MAX_TABLE_WHITESPACE;
				}
			}

			/* Set the sizes */
			//alert('divMessageList Height:'+divheight);
			//alert('iframe height:'+iframeheight);
			divMessageTableList.style.height = divheight + 'px';
			if (mailGrid != null) mailGrid.resize($(divMessageTableList).outerWidth(), divheight-10);

			if (typeof iframe != 'undefined' && typeof tdiframe != 'undefined' && iframe != null && tdiframe != null)
			{
				tdiframe.height = iframeheight;
				iframe.height = iframeheight;
			}
		}
		return true;
	}

	//Resize the elements
	handleResize();

	//Assign the handle resize
	$(window).resize(handleResize);
</script>
<!-- END message_table -->

<!-- BEGIN status_row_tpl -->
<table WIDTH="100%" BORDER="0" CELLPADDING="1" CELLSPACING="2">
				<tr BGCOLOR="{row_off}" class="text_small">
					<td width="18%">
						{link_previous}
					</td>
					<td width="10%">&nbsp;

					</td>
					<TD align="center" width="36%">
						{message}
					</td>
					<td width="18%">
						{trash_link}
					</td>
					<td align="right" width="18%">
						{link_next}
					</td>
				</tr>
			</table>


<!-- END status_row_tpl -->

<!-- BEGIN error_message -->
        <table style="width:100%;">
                <tr>
                        <td bgcolor="#FFFFCC" align="center" colspan="6">
                                <font color="red"><b>{lang_connection_failed}</b></font><br>
                                <br>{connection_error_message}<br><br>
                        </td>
                </tr>
        </table>
<!-- END error_message -->

<!-- BEGIN quota_block -->
	<table cellpadding="0" cellspacing="0" style="border:1px solid silver;width:150px;">
		<tr valign="middle">
			<td bgcolor="{quotaBG}" align="center" valign="middle" style="width:{leftWidth}%;height:9px;font-size:9px;">
				&nbsp;{quotaUsage_left}
			</td>
			<td align="center" valign="middle" style="height:9px;font-size:9px;">
				&nbsp;{quotaUsage_right}
			</td>
		</tr>
	</table>
<!-- END quota_block -->

<!-- BEGIN subject_same_window -->
	<td bgcolor="#FFFFFF">
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
<!-- END subject_same_window -->

<!-- BEGIN subject_new_window -->
	<td bgcolor="#FFFFFF">
		<a class="{row_css_class}" name="subject_url" href="{url_read_message}" title="{full_subject}">{header_subject}</a>
	</td>
<!-- END subject_new_window -->

