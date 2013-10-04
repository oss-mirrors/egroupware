/**
 * mail - static javaScript functions
 *
 * @link http://www.egroupware.org
 * @author klaus leithoff <kl at stylite.de>
 * @package mail
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id$
 */

app.mail = AppJS.extend(
{
	appname: 'mail',

	/**
	 * et2 widget container
	 */
	et2: null,
	doStatus: null,
	mail_doTimedRefresh: false,

	mail_refreshTimeOut: 1000*60*3, // initial call
	mail_queuedFolders: [],
	mail_queuedFoldersIndex: 0,

	mail_selectedMails: [],
	mail_currentlyFocussed: '',
	mail_previewAreaActive: true, // we start with the area active

	nm_index: 'nm', // nm nome of index
	mail_fileSelectorWindow: null,

	/**
	 * Initialize javascript for this application
	 *
	 * @memberOf app.mail
	 */
	init: function() {
		this._super.apply(this,arguments);
		window.register_app_refresh("mail", this.app_refresh);

		this.mail_startTimerFolderStatusUpdate(this.mail_refreshTimeOut);
		//inital call of refresh folderstatus
		var self = this;
		window.setTimeout(function() {self.mail_refreshFolderStatus.apply(self);},1000);
	},

	/**
	 * Destructor
	 */
	destroy: function()
	{
		delete this.et2;
		// call parent
		this._super.apply(this, arguments);
	},

	/**
	 * This function is called when the etemplate2 object is loaded
	 * and ready.  If you must store a reference to the et2 object,
	 * make sure to clean it up in destroy().
	 *
	 * @param et2 etemplate2 Newly ready object
	 */
	et2_ready: function(et2)
	{
		// call parent; somehow this function is called more often. (twice on a display and compose) why?
		this._super.apply(this, arguments);
		this.et2_obj = et2;
		this.et2 = et2.widgetContainer;
		var isMainView = false;
		var isDisplay = false;
		for (var t in et2.templates)
		{
			//alert(t); // as we iterate through this more than once, ... we separate trigger and action
			switch (t) {
				case 'mail.index':
					isMainView=true;
					break;
				case 'mail.display':
					isDisplay=true;
					break;
				case 'mail.compose':

			}
		}
		//alert('action about to go down');
		if (isMainView) this.mail_disablePreviewArea(true);
		if (isDisplay)
		{
			var subject = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mail_displaysubject');
			var body = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mail_displaybody');
			body.node.parentNode.style.top=subject.node.offsetTop+40+'px';
			var app_registry = egw.link_get_registry(this.appname);
			//console.log(app_registry);
			w=870;
			if (typeof app_registry['view'] != 'undefined' && typeof app_registry['view_popup'] != 'undefined' )
			{
				var w_h =app_registry['view_popup'].split('x');
				if (w_h[1] == 'egw_getWindowOuterHeight()') w_h[1] = (screen.availHeight>egw_getWindowOuterHeight()?screen.availHeight:egw_getWindowOuterHeight());
			}
			//alert('resizing to'+(w_h[0]?w_h[0]:870)+','+(w_h[1]?w_h[1]:egw_getWindowOuterHeight()));
			window.resizeTo((w_h[0]?w_h[0]:870),(w_h[1]?w_h[1]:(screen.availHeight>egw_getWindowOuterHeight()?screen.availHeight:egw_getWindowOuterHeight())));
		}
	},

	/**
	 * mail_fetchCurrentlyFocussed - implementation to decide wich mail of all the selected ones is the current
	 *
	 * @param _selected array of the selected mails
	 * @param _reset bool - tell the function to reset the global vars used
	 */
	mail_fetchCurrentlyFocussed: function(_selected, _reset) {
		//console.log("mail_fetchCurrentlyFocussed",_selected, _reset);
		// reinitialize the buffer-info on selected mails
		if (_reset == true || typeof _selected == 'undefined')
		{
			if (_reset == true)
			{
				//var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(nm_index);
				//if (this.mail_currentlyFocussed!='') nm.refresh([this.mail_currentlyFocussed],'delete');//egw.dataDeleteUID(this.mail_currentlyFocussed);
				if (this.mail_currentlyFocussed!='') egw.dataDeleteUID(this.mail_currentlyFocussed);
				for(var k = 0; k < this.mail_selectedMails.length; k++) egw.dataDeleteUID(this.mail_selectedMails[k]);
				//nm.refresh(this.mail_selectedMails,'delete');
			}
			this.mail_selectedMails = [];
			this.mail_currentlyFocussed = '';
			return '';
		}
		for(var k = 0; k < _selected.length; k++)
		{
			if (jQuery.inArray(_selected[k],this.mail_selectedMails)==-1)
			{
				this.mail_currentlyFocussed = _selected[k];
				break;
			}
		}
		this.mail_selectedMails = _selected;
		return this.mail_currentlyFocussed;
	},

	/**
	 * mail_open - implementation of the open action
	 *
	 * @param _action
	 * @param _senders - the representation of the elements(s) the action is to be performed on
	 * @param _mode - you may pass the mode. if not given view is used (tryastext|tryashtml are supported)
	 */
	mail_open: function(_action, _senders, _mode) {
		//console.log("mail_open",_action, _senders);
		var _id = _senders[0].id;
		// reinitialize the buffer-info on selected mails
		if (!(_mode == 'tryastext' || _mode == 'tryashtml' || _mode == 'view')) _mode = 'view';
		this.mail_selectedMails = [];
		this.mail_selectedMails.push(_id);
		this.mail_currentlyFocussed = _id;

		var dataElem = egw.dataGetUIDdata(_id);
		var subject = dataElem.data.subject;
		//alert('Open Message:'+_id+' '+subject);
		var h = egw().open( _id,'mail','view',_mode+'='+_id.replace(/=/g,"_") );
		egw(h).ready(function() {
			h.document.title = subject;
		});
	},

	/**
	 * Open a single message in html mode
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_openAsHtml: function(_action, _elems)
	{
		this.mail_open(_action, _elems,'tryashtml');
	},

	/**
	 * Open a single message in plain text mode
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_openAsText: function(_action, _elems)
	{
		this.mail_open(_action, _elems,'tryastext');
	},

	/**
	 * Compose, reply or forward a message
	 *
	 * @param _action _action.id is 'compose', 'composeasnew', 'reply', 'reply_all' or 'forward' (forward can be multiple messages)
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_compose: function(_action, _elems)
	{
		var idsToProcess = '';
		var multipleIds = false;
		var url = window.egw_webserverUrl+'/index.php?';
		if (typeof _elems == 'undefined')
		{
			var h = egw().open('','mail','add');
			return true;
		}
		if (_elems.length > 1) multipleIds = true;
		//for (var i=0; i<_elems.length; i++)
		//{
		//	if (i>0) idsToProcess += ',';
		//	idsToProcess += _elems[i].id;
		//}
		//alert('mail_'+_action.id+'('+idsToProcess+')');
		if (_action.id == 'compose')
		{
			if (multipleIds == false)
			{
				if (_elems.length == 1) mail_parentRefreshListRowStyle(_elems[0].id,_elems[0].id);
				url += 'menuaction=mail.mail_compose.compose';
				this.mail_openComposeWindow(url)
			}
			else
			{
				this.mail_compose('forward',_elems);
			}
		}
		if (_action.id == 'composefromdraft')
		{
			url += 'menuaction=mail.mail_compose.composeFromDraft';
			url += '&id='+_elems[0].id;
			egw_openWindowCentered(url,'composeasnew_'+_elems[0].id,870,egw_getWindowOuterHeight());
		}
		if (_action.id == 'composeasnew')
		{
			url += 'menuaction=mail.mail_compose.composeAsNew';
			url += '&reply_id='+_elems[0].id;
			egw_openWindowCentered(url,'composeasnew_'+_elems[0].id,870,egw_getWindowOuterHeight());
		}
		if (_action.id == 'reply')
		{
			url += 'menuaction=mail.mail_compose.reply';
			url += '&reply_id='+_elems[0].id;
			egw_openWindowCentered(url,'reply_'+_elems[0].id,870,egw_getWindowOuterHeight());
		}
		if (_action.id == 'reply_all')
		{
			url += 'menuaction=mail.mail_compose.replyAll';
			url += '&reply_id='+_elems[0].id;
			egw_openWindowCentered(url,'replyAll_'+_elems[0].id,870,egw_getWindowOuterHeight());
		}
		if (_action.id == 'forward'||_action.id == 'forwardinline'||_action.id == 'forwardasattach')
		{
			if (multipleIds||_action.id == 'forwardasattach')
			{
				url += 'menuaction=mail.mail_compose.compose';
				mail_openComposeWindow(url,_action.id == 'forwardasattach');
			}
			else
			{
				url += 'menuaction=mail.mail_compose.forward';
				url += '&reply_id='+_elems[0].id;
				url += '&mode=forwardinline';
				egw_openWindowCentered(url,'forward_'+_elems[0].id,870,egw_getWindowOuterHeight());
			}
		}
	},

	/**
	 * Compose, reply or forward a message
	 *
	 * @param _action _action.id is 'compose', 'composeasnew', 'reply', 'reply_all' or 'forward' (forward can be multiple messages)
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_testhtmlarea: function(_action, _elems)
	{
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=mail.mail_compose.testhtmlarea';
		egw_openWindowCentered(url);
	},

	/**
	 * Compose, reply or forward a message
	 *
	 * @param _url url to open
	 * @param forwardByCompose boolean to decide about the method
	 */
	mail_openComposeWindow: function(_url,forwardByCompose,_elems) {
		var Check=true;
		var alreadyAsked=false;
		var _messageList;
		var sMessageList='';
		//var cbAllMessages = document.getElementById('selectAllMessagesCheckBox').checked;
		// check if mailgrid exists, before accessing it
		var cbAllVisibleMessages;
		if (typeof forwardByCompose == 'undefined') forwardByCompose = true;
		if (forwardByCompose == false)
		{
			cbAllMessages = cbAllVisibleMessages = Check = false;
		}
		if (typeof prefAskForMultipleForward == 'undefined') prefAskForMultipleForward = egw.preference('prefaskformultipleforward','mail');
		if (cbAllMessages == true || cbAllVisibleMessages == true)
		{
			Check = confirm(egw.lang('multiple forward of all mesages'));
			alreadyAsked=true;
		}

		if ((cbAllMessages == true || cbAllVisibleMessages == true ) && Check == true)
		{
			//_messageList = 'all'; // all is not supported by now, only visibly selected messages are chosen
			_messageList = this.mail_getFormData(_elems);
		}
		else
		{
			if (Check == true) _messageList = this.mail_getFormData(_elems);
		}
		if (typeof _messageList != 'undefined')
		{
			for (var i in _messageList['msg']) {
				//alert('eigenschaft:'+_messageList['msg'][i]);
				sMessageList=sMessageList+_messageList['msg'][i]+',';
				//sMessageList.concat(',');
			}
		}
		if (prefAskForMultipleForward == 1 && Check == true && alreadyAsked == false && sMessageList.length >0 && _messageList['msg'].length>1)
		{
			askme = egw.lang('multipleforward');
			//if (cbAllMessages == true || cbAllVisibleMessages == true) askme = egw_appWindow('felamimail').lang_confirm_all_messages; // not supported
			Check = confirm(askme);
		}
		//alert("Check:"+Check+" MessageList:"+sMessageList+"#");
		if (Check != true) sMessageList=''; // deny the appending off selected messages to new compose -> reset the sMessageList
		if (Check == true || sMessageList=='')
		{
			if (sMessageList.length >0) {
				sMessageList= 'AsForward&forwardmails=1&folder='+activeFolderB64+'&reply_id='+sMessageList.substring(0,sMessageList.length-1);
			}
			//alert(sMessageList);
			egw_openWindowCentered(_url+sMessageList,'compose',870,egw_getWindowOuterHeight());
		}
		//ToDo: reset message selection
	},

	/**
	 * mail_disablePreviewArea - implementation of the disablePreviewArea action
	 *
	 * @param _value
	 */
	mail_disablePreviewArea: function(_value) {
		var splitter = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailSplitter');
		if (typeof splitter == 'undefined' || splitter == null) return;
		var splitterDN = splitter.getDOMNode();
		// check if DOM Node has class that contains docked; then we assume the bar docked, whatever our class var states
		for (var i=0; i < splitterDN.childNodes[1].classList.length;i++) if (splitterDN.childNodes[1].classList[i].search(/docked/)>=0) this.mail_previewAreaActive = false;
		//if this.mail_previewAreaActive but clientHeight of childNode is 0, assume this.mail_previewAreaActive incorrect
		if ( this.mail_previewAreaActive && splitterDN.childNodes.length > 2 && splitterDN.childNodes[2].clientHeight < 15) this.mail_previewAreaActive=false;
		//etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailPreviewHeadersFrom').set_disabled(_value);
		//etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailPreviewHeadersTo').set_disabled(_value);
		//etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailPreviewHeadersDate').set_disabled(_value);
		//etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailPreviewHeadersSubject').set_disabled(_value);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('mailPreview').set_disabled(_value);
		if (_value==true)
		{
			if (this.mail_previewAreaActive) splitter.dock();
			this.mail_previewAreaActive = false;
		}
		else
		{
			if (!this.mail_previewAreaActive) splitter.undock();
			this.mail_previewAreaActive = true;
		}
	},

	/**
	 * mail_preview - implementation of the preview action
	 *
	 * @param nextmatch et2_nextmatch The widget whose row was selected
	 * @param selected Array Selected row IDs.  May be empty if user unselected all rows.
	 */
	mail_preview: function(nextmatch, selected) {
		//console.log("mail_preview",nextmatch, selected);
		// Empty values, just in case selected is empty (user cleared selection)
		var dataElem = {data:{subject:"",fromaddress:"",toaddress:"",date:"",subject:""}};
		if(typeof selected != 'undefined' && selected.length == 1)
		{
			var _id = this.mail_fetchCurrentlyFocussed(selected);
			dataElem = egw.dataGetUIDdata(_id);
		}
		//get_class does not exist yet
		//var pAAClass = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').get_class();
		//console.log(pAAClass);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').set_class('previewAttachmentArea');
		if(typeof selected == 'undefined' || selected.length == 0 || selected.length > 1 || typeof dataElem =='undefined')
		{
			this.mail_fetchCurrentlyFocussed();
			var subject ="";
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewFromAddress').set_value("");
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewToAddress').set_value("");
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewDate').set_value("");
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewSubject').set_value("");
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').set_value("");
			etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').set_class('previewAttachmentArea noContent');
			var IframeHandle = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('messageIFRAME');
			IframeHandle.set_src(egw.link('/index.php',{menuaction:'mail.mail_ui.loadEmailBody',_messageID:""}));
			this.mail_disablePreviewArea(true);
			return;
		}
		//console.log("mail_preview",dataElem);
		this.mail_selectedMails.push(_id);
		var subject =dataElem.data.subject;
		this.mail_disablePreviewArea(false);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewFromAddress').set_value(dataElem.data.fromaddress);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewToAddress').set_value(dataElem.data.toaddress);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewDate').set_value(dataElem.data.date);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewSubject').set_value(subject);
		etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').set_value((dataElem.data.attachmentsBlock.length>1?dataElem.data.attachmentsBlock:''));
		if (dataElem.data.attachmentsBlock.length<1) etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('previewAttachmentArea').set_class('previewAttachmentArea noContent');
		var IframeHandle = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('messageIFRAME');
		//console.log(IframeHandle);
		IframeHandle.set_src(egw.link('/index.php',{menuaction:'mail.mail_ui.loadEmailBody',_messageID:_id}));
		var messages = {};
		messages['msg'] = [_id];
		this.mail_removeRowClass(messages,'unseen');
	//	var request = new egw_json_request('mail.mail_ui.ajax_loadEmailBody',[_id]);
	//	request.sendRequest(false);
	},

	mail_setMailBody: function(content) {
		//console.log('mail_setMailBody',content);
		var IframeHandle = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('messageIFRAME');
		IframeHandle.set_value('');
	},

	/**
	 * mail_startTimerFolderStatusUpdate, timer functions, if the counter changes for the current folder
	 * refresh the message list
	 * @param timeout
	 */
	mail_startTimerFolderStatusUpdate: function(_refreshTimeOut) {
		if (typeof _refreshTimeOut == 'undefined')
		{
			var minutes = egw.preference('refreshTime','mail');
			this.mail_refreshTimeOut = _refreshTimeOut= 1000*60*(minutes?minutes:3); // either the prefs or 3 Minutes
		}
		if (this.mail_refreshTimeOut > _refreshTimeOut) _refreshTimeOut = this.mail_refreshTimeOut;
		if(this.mail_doTimedRefresh) {
			window.clearTimeout(this.mail_doTimedRefresh);
		}
		if(_refreshTimeOut > 9999) {//we do not set _refreshTimeOut's less than 10 seconds
			var self = this;
			this.mail_doTimedRefresh = window.setInterval(function() {
				self.mail_refreshFolderStatus.apply(self);
			}, _refreshTimeOut);
		}
	},

	/**
	 * mail_refreshFolderStatus, function to call to read the counters of a folder and apply them
	 *
	 * @param _nodeID
	 * @param mode
	 */
	mail_refreshFolderStatus: function(_nodeID,mode) {
		var nodeToRefresh = 0;
		var mode2use = "none";
		if (_nodeID) nodeToRefresh = _nodeID;
		if (mode) {
			if (mode == "forced") {mode2use = mode;}
		}
		try
		{
			var tree_wdg = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');

			var activeFolders = tree_wdg.getTreeNodeOpenItems(nodeToRefresh,mode2use);
			//alert(activeFolders.join('#,#'));
			this.mail_queueRefreshFolderList(activeFolders);
			// maybe to use the mode forced as trigger for grid reload and using the grids own autorefresh
			// would solve the refresh issue more accurately
			//if (mode == "forced") this.mail_refreshMessageGrid();
			this.mail_refreshMessageGrid();
		} catch(e) { } // ignore the error; maybe the template is not loaded yet
	},


	/**
	 * Queues a refreshFolderList request for 1ms. Actually this will just execute the
	 * code after the calling script has finished.
	 */
	mail_queueRefreshFolderList: function(_folders)
	{
		this.mail_queuedFolders.push(_folders);
		this.mail_queuedFoldersIndex++;

		// Copy idx onto the anonymous function scope
		var idx = this.mail_queuedFoldersIndex;
		var self = this;
		window.setTimeout(function() {
			if (idx == self.mail_queuedFoldersIndex)
			{
				//var folders = mail_queuedFolders.join(",");
				self.mail_queuedFoldersIndex = 0;
				self.mail_queuedFolders = [];

				egw.json('mail.mail_ui.ajax_setFolderStatus',[_folders])
					.sendRequest(true);
			}
		}, 10);
	},

	/**
	 * mail_setFolderStatus, function to set the status for the visible folders
	 */
	mail_setFolderStatus: function(_status) {
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		for (var i in _status) ftree.setLabel(i,_status[i]);//alert(i +'->'+_status[i]);
	},

	/**
	 * mail_setLeaf, function to set the id and description for the folder given by status key
	 * @param array _status status array with the required data (new id, desc, old desc)
	 *		key is the original id of the leaf to change
	 *		multiple sets can be passed to mail_setLeaf
	 */
	mail_setLeaf: function(_status) {
		//console.log('mail_setLeaf',_status);
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		var selectedNode = ftree.getSelectedNode();
		for (var i in _status)
		{
			// if olddesc is undefined or #skip# then skip the message, as we process subfolders
			if (typeof _status[i]['olddesc'] !== 'undefined' && _status[i]['olddesc'] !== '#skip-user-interaction-message#') app.mail.app_refresh(egw.lang("Renamed Folder %1 to %2",_status[i]['olddesc'],_status[i]['desc'], 'mail'));
			ftree.renameItem(i,_status[i]['id'],_status[i]['desc']);
			//alert(i +'->'+_status[i]['id']+'+'+_status[i]['desc']);
			if (_status[i]['id']==selectedNode.id)
			{
				var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
				nm.activeFilters["selectedFolder"] = _status[i]['id'];
				nm.applyFilters();
			}
		}
	},

	/**
	 * mail_removeLeaf, function to remove the leaf represented by the given ID
	 * @param array _status status array with the required data (KEY id, VALUE desc)
	 *		key is the id of the leaf to delete
	 *		multiple sets can be passed to mail_deleteLeaf
	 */
	mail_removeLeaf: function(_status) {
		//console.log('mail_removeLeaf',_status);
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		var selectedNode = ftree.getSelectedNode();
		for (var i in _status)
		{
			// if olddesc is undefined or #skip# then skip the message, as we process subfolders
			if (typeof _status[i] !== 'undefined' && _status[i] !== '#skip-user-interaction-message#') app.mail.app_refresh(egw.lang("Removed Folder %1 ",_status[i], 'mail'));
			ftree.deleteItem(i,(selectedNode.id==i));
			var selectedNodeAfter = ftree.getSelectedNode();
			//alert(i +'->'+_status[i]['id']+'+'+_status[i]['desc']);
			if (selectedNodeAfter.id!=selectedNode.id && selectedNode.id==i)
			{
				var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
				nm.activeFilters["selectedFolder"] = selectedNodeAfter.id;
				nm.applyFilters();
			}
		}
	},

	/**
	 * mail_reloadNode, function to reload the leaf represented by the given ID
	 * @param array _status status array with the required data (KEY id, VALUE desc)
	 *		key is the id of the leaf to delete
	 *		multiple sets can be passed to mail_deleteLeaf
	 */
	mail_reloadNode: function(_status) {
		//console.log('mail_reloadNode',_status);
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		var selectedNode = ftree.getSelectedNode();
		for (var i in _status)
		{
			// if olddesc is undefined or #skip# then skip the message, as we process subfolders
			if (typeof _status[i] !== 'undefined' && _status[i] !== '#skip-user-interaction-message#') app.mail.app_refresh(egw.lang("Reloaded Folder %1 ",_status[i], 'mail'));
			ftree.refreshItem(i);
			var selectedNodeAfter = ftree.getSelectedNode();
			//alert(i +'->'+_status[i]['id']+'+'+_status[i]['desc']);
			if (selectedNodeAfter.id!=selectedNode.id && selectedNode.id==i)
			{
				var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
				nm.activeFilters["selectedFolder"] = selectedNodeAfter.id;
				nm.applyFilters();
			}
		}
	},

	/**
	 * mail_refreshMessageGrid, function to call to reread ofthe current folder
	 */
	mail_refreshMessageGrid: function() {
		var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
		nm.applyFilters(); // this should refresh the active folder
	},

	/**
	 * Refresh given application _targetapp display of entry _app _id, incl. outputting _msg
	 *
	 * Default implementation here only reloads window with it's current url with an added msg=_msg attached
	 *
	 * @param string _msg message (already translated) to show, eg. 'Entry deleted'
	 * @param string _app application name
	 * @param string|int _id=null id of entry to refresh
	 * @param string _type=null either 'edit', 'delete', 'add' or null
	 */
	app_refresh: function(_msg, _app, _id, _type)
	{
		var bufferExists = false;
		window.clearInterval(app.mail.doStatus); // whatever message was up to be activated
		//alert("app_refresh(\'"+_msg+"\',\'"+_app+"\',\'"+_id+"\',\'"+_type+"\')");
		//myCurrentMsg = mail_getMsg();
		//if (myCurrentMsg.length) {
			// clear message after some time
			myMessageBuffer = ""; //myCurrentMsg;
			bufferExists = true;
		//}
		app.mail.mail_setMsg('<span style="font-weight: bold;">' +_msg+ '</span>');
		if (_app=='mail')
		{
			//we may want to trigger some actions, like modifying the grid, disable preview and stuff

			// TODO: more actions
		}
		if (bufferExists)
		{
			this.doStatus = window.setInterval("app.mail.mail_setMsg(myMessageBuffer);", 10000);
		}
	},

	/**
	 * mail_getMsg - gets the current Message
	 * @return string
	 */
	mail_getMsg: function()
	{
		var msg_wdg = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('msg');
		if (msg_wdg)
		{
			return msg_wdg.valueOf().htmlNode[0].innerHTML;
		}
		return "";
	},

	/**
	 * mail_setMsg - sets a Message, with the msg container, and controls if the container is enabled/disabled
	 * @param string myMsg - the message
	 */
	mail_setMsg: function(myMsg)
	{
		var msg_wdg = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById('msg');
		if (msg_wdg)
		{
			msg_wdg.set_value(myMsg);
			msg_wdg.set_disabled(myMsg.trim().length==0);
		}
	},

	/**
	 * Delete mails
	 * takes in all arguments
	 * @param _action
	 * @param _elems
	 */
	mail_delete: function(_action,_elems)
	{
		var msg = this.mail_getFormData(_elems);
		//alert(_action.id+','+ msg);
		app.mail.app_refresh(egw.lang('delete messages'), 'mail');
		this.mail_setRowClass(_elems,'deleted');
		this.mail_deleteMessages(msg);
	},

	/**
	 * Delete mails - actually calls the backend function for deletion
	 * takes in all arguments
	 * @param _msg - message list
	 * @param _action - optional action
	 */
	mail_deleteMessages: function(_msg,_action)
	{
		app.mail.app_refresh(egw.lang('delete messages'), 'mail');
		egw.json('mail.mail_ui.ajax_deleteMessages',[_msg,(typeof _action == 'undefined'?'no':_action)])
			.sendRequest();
		for (var i = 0; i < _msg['msg'].length; i++)  egw.dataDeleteUID(_msg['msg'][i]);
		this.mail_refreshMessageGrid();
		this.mail_preview();
	},

	/**
	 * retry to Delete mails
	 * @param responseObject ->
	 * 	 reason - reason to report
	 * 	 messageList
	 */
	mail_retryForcedDelete: function(responseObject)
	{
		var reason = responseObject['response'];
		var messageList = responseObject['messageList'];
		Check = confirm(reason);
		if (Check==true)
		{
			this.mail_deleteMessages(messageList,'remove_immediately');
		}
		else
		{
			app.mail.app_refresh(egw.lang('canceled deletion due to userinteraction'), 'mail');
			this.mail_removeRowClass(messageList,'deleted');
		}
		this.mail_refreshMessageGrid();
		this.mail_preview();
	},

	/**
	 * UnDelete mailMessages
	 *
	 * @param _messageList
	 */
	mail_undeleteMessages: function(_messageList) {
	// setting class of row, the old style
	/*
			for(var i=0;i<_messageList['msg'].length;i++) {
				_id = _messageList['msg'][i];
				var dataElem = egw_appWindow('felamimail').mailGrid.dataRoot.getElementById(_id);
				if (dataElem)
				{
					//dataElem.clearData();
					//dataElem.addClass('deleted');
					dataElem.removeClass('deleted');
				}
			}
	*/
	},

	/**
	 * mail_emptyTrash
	 */
	mail_emptyTrash: function() {
		app.mail.app_refresh(egw.lang('empty trash'), 'mail');
		egw.json('mail.mail_ui.ajax_emptyTrash')
			.sendRequest(true);
	},

	/**
	 * mail_compressFolder
	 */
	mail_compressFolder: function() {
		app.mail.app_refresh(egw.lang('compress folder'), 'mail');
		egw.json('mail.mail_ui.ajax_compressFolder')
			.sendRequest(true);
	},

	/**
	 * mail_changeProfile
	 * @param folder, the ID of the selected Node -> should be an integer
	 * @param _widget, handle to the tree widget
	 */
	mail_changeProfile: function(folder,_widget) {
	//	alert(folder);
		egw.json('mail.mail_ui.ajax_changeProfile',[folder])
			.sendRequest();
		this.mail_refreshMessageGrid();

		return true;
	},

	/**
	 * mail_changeFolder
	 * @param folder, the ID of the selected Node
	 * @param _widget, handle to the tree widget
	 */
	mail_changeFolder: function(folder,_widget) {
		//alert('change Folder called:'+folder);
		app.mail.app_refresh(egw.lang('change folder')+'...', 'mail');
		var img = _widget.getSelectedNode().images[0]; // fetch first image
		if (!(img.search(eval('/'+'NoSelect'+'/'))<0) || !(img.search(eval('/'+'thunderbird'+'/'))<0))
		{
			if (!(img.search(eval('/'+'thunderbird'+'/'))<0))
			{
				rv = this.mail_changeProfile(folder,_widget);
				if (rv)
				{
					return rv;
				}
			}
			if (_widget.event_args.length==2)
			{
				folder = _widget.event_args[1];
				_widget.set_value(folder);
			}
			else
			{
				_widget.set_value('');
			}
		}
		var nm = _widget.getRoot().getWidgetById(this.nm_index);
		nm.activeFilters["selectedFolder"] = folder;
		//nm.applyFilters();// its done in refrefreshMessageGrid
		var msg = _widget.getRoot().getWidgetById('msg');
		if (msg)
		{
			window.clearInterval(this.doStatus);
			displayname = _widget.getSelectedLabel();
			inBraket = displayname.search(/\(/);
			if (inBraket!=-1)
			{
				outBraket = displayname.search(/\)/);
				if (outBraket!=-1) displayname = displayname.replace(/\((.*?)\)/,"");
			}
			myMsg = (displayname?displayname:folder)+' '+egw.lang('selected');
			app.mail.app_refresh(myMsg, 'mail');
		}
		//mail_refreshMessageGrid();// its done in refreshFolderStatus already
		this.mail_refreshFolderStatus(folder,'forced');
		this.mail_startTimerFolderStatusUpdate(this.mail_refreshTimeOut);
		this.mail_fetchCurrentlyFocussed(null,true);
		this.mail_preview();
	},

	/**
	 * Flag mail as 'read', 'unread', 'flagged' or 'unflagged'
	 *
	 * @param _action _action.id is 'read', 'unread', 'flagged' or 'unflagged'
	 * @param _elems
	 */
	mail_flag: function(_action, _elems)
	{
		//alert(_action.id+' - '+_elems[0].id);
		//console.log(_action, _elems);
		var msg = this.mail_getFormData(_elems);
		if (_action.id.substring(0,2)=='un') {
			this.mail_removeRowClass(_elems,_action.id.substring(2));
		}
		else
		{
			this.mail_removeRowClass(_elems,'un'+_action.id);
		}
		this.mail_setRowClass(_elems,_action.id);
		this.mail_flagMessages(_action.id,msg);
	},

	/**
	 * Flag mail as 'read', 'unread', 'flagged' or 'unflagged'
	 *
	 * @param _action _action.id is 'read', 'unread', 'flagged' or 'unflagged'
	 * @param _elems
	 */
	mail_flagMessages: function(_flag, _elems)
	{
		//console.log(_flag, _elems);
		app.mail.app_refresh(egw.lang('flag messages'), 'mail');
		egw.json('mail.mail_ui.ajax_flagMessages',[_flag, _elems])
			.sendRequest();
		this.mail_refreshMessageGrid();
	},

	/**
	 * display header lines, or source of mail, depending on the url given
	 *
	 * @param _url
	 */
	mail_displayHeaderLines: function(_url) {
		// only used by right clickaction
		egw_openWindowCentered(_url,'mail_display_headerLines','870','600',window.outerWidth/2,window.outerHeight/2);
	},

	/**
	 * View header of a message
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_header: function(_action, _elems)
	{
		//alert('mail_header('+_elems[0].id+')');
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=mail.mail_ui.displayHeader';	// todo compose for Draft folder
		url += '&id='+_elems[0].id;
		this.mail_displayHeaderLines(url);
	},

	/**
	 * View message source
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_mailsource: function(_action, _elems)
	{
		//alert('mail_mailsource('+_elems[0].id+')');
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=mail.mail_ui.saveMessage';	// todo compose for Draft folder
		url += '&id='+_elems[0].id;
		url += '&location=display';
		this.mail_displayHeaderLines(url);
	},

	/**
	 * Save a message
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_save: function(_action, _elems)
	{
		//alert('mail_save('+_elems[0].id+')');
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=mail.mail_ui.saveMessage';	// todo compose for Draft folder
		url += '&id='+_elems[0].id;
		//window.open(url,'_blank','dependent=yes,width=100,height=100,scrollbars=yes,status=yes');
		document.location = url;
	},

	/**
	 * User clicked an address (FROM, TO, etc)
	 */
	address_click: function(DOMNode, tag_info, widget)
	{
		console.log(this, arguments);
	},

	/**
	 * Save a message to filemanager
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_save2fm: function(_action, _elems)
	{
		var _id = _elems[0].id;
		var dataElem = egw.dataGetUIDdata(_id);
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=filemanager.filemanager_select.select';	// todo compose for Draft folder
		url += '&mode=saveas';
		var filename =dataElem.data.subject.replace(/[\f\n\t\v/\\:*#?<>\|]/g,"_");
		url += '&name='+encodeURIComponent(filename+'.eml');
		url += '&mime=message'+encodeURIComponent('/')+'rfc822';
		url += '&method=mail.mail_ui.vfsSaveMessage'
		url += '&id='+_elems[0].id;
		url += '&label=Save';
		//window.open(url,'_blank','dependent=yes,width=100,height=100,scrollbars=yes,status=yes')
		//document.location = url;
		egw_openWindowCentered(url,'vfs_save_message_'+_elems[0].id,'640','570',window.outerWidth/2,window.outerHeight/2);

	},

	/**
	 * Save message as InfoLog
	 *
	 * @param _action
	 * @param _elems _elems[0].id is the row-id
	 */
	mail_infolog: function(_action, _elems)
	{
		//alert('mail_infolog('+_elems[0].id+')');return;
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=infolog.infolog_ui.import_mail';	// todo compose for Draft folder
		url += '&rowid='+_elems[0].id;
		egw_openWindowCentered(url,'import_mail_'+_elems[0].id,_action.data.width,_action.data.height);
	},

	/**
	 * Save message as ticket
	 *
	 * @param _action _action.id is 'read', 'unread', 'flagged' or 'unflagged'
	 * @param _elems
	 */
	mail_tracker: function(_action, _elems)
	{
		//alert('mail_tracker('+_elems[0].id+')');
		var url = window.egw_webserverUrl+'/index.php?';
		url += 'menuaction=tracker.tracker_ui.import_mail';	// todo compose for Draft folder
		url += '&rowid='+_elems[0].id;
		egw_openWindowCentered(url,'import_tracker_'+_elems[0].id,_action.data.width,_action.data.height);
	},


	/**
	 * mail_getFormData
	 *
	 * @param _actionObjects, the senders
	 * @return structured array of message ids: array(msg=>message-ids)
	 */
	mail_getFormData: function(_actionObjects) {
		var messages = {};
		if (_actionObjects.length>0)
		{
			messages['msg'] = [];
		}

		for (var i = 0; i < _actionObjects.length; i++)
		{
			if (_actionObjects[i].id.length>0)
			{
				messages['msg'][i] = _actionObjects[i].id;
			}
		}

		return messages;
	},

	/**
	 * mail_setRowClass
	 *
	 * @param _actionObjects, the senders
	 */
	mail_setRowClass: function(_actionObjects,_class) {
		if (typeof _class == 'undefined') return false;

		for (var i = 0; i < _actionObjects.length; i++)
		{
			if (_actionObjects[i].id.length>0)
			{
				var dataElem = $j(_actionObjects[i].iface.getDOMNode());
				dataElem.addClass(_class);

			}
		}
	},

	/**
	 * mail_removeRowClass
	 *
	 * @param _actionObjects, the senders, or a messages object
	 * @param _class, the class to be removed
	 */
	mail_removeRowClass: function(_actionObjects,_class) {
		if (typeof _class == 'undefined') return false;

		if (typeof _actionObjects['msg'] == 'undefined')
		{
			for (var i = 0; i < _actionObjects.length; i++)
			{
				if (_actionObjects[i].id.length>0)
				{
					var dataElem = $j(_actionObjects[i].iface.getDOMNode());
					dataElem.removeClass(_class);

				}
			}
		}
		else
		{
			var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
			var aO = nm.controller._objectManager.selectedChildren;
			for (var i = 0; i < _actionObjects['msg'].length; i++)
			{
				for (var i = 0; i < aO.length; i++)
				{
					if (aO[i].id==_actionObjects['msg'][i])
					{
						var dataElem = $j(aO[i].iface.getDOMNode());
						dataElem.removeClass(_class);

					}
				}
			}
		}
	},

	// Tree widget stubs
	/**
	 * mail_dragStart - displays information while dragging
	 *
	 * @param action
	 * @param _senders - the representation of the elements dragged
	 * @return the ddhelper
	 */
	mail_dragStart: function(action,_senders) {
		//console.log(action,_senders);
		return $j("<div class=\"ddhelper\">" + _senders.length + " Mails selected </div>");
	},
	/**
	 * mail_move - implementation of the move action from drag n drop
	 *
	 * @param _action
	 * @param _senders - the representation of the elements dragged
	 * @param _target - the representation of the target
	 */
	mail_move: function(_action,_senders,_target) {
		//console.log(_action,_senders,_target);
		var target = _action.id == 'drop_move_mail' ? _target.iface.id : _action.id.substr(5);
		var messages = this.mail_getFormData(_senders);
		//alert('mail_move('+messages.msg.join(',')+' --> '+target+')');
		// TODO: Write move/copy function which cares about doing the same stuff
		// as the "onNodeSelect" function!
		egw.json('mail.mail_ui.ajax_moveMessages',[target, messages])
			.sendRequest();
		var nm = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index);
		this.mail_setRowClass(_senders,'deleted');
		nm.refresh(messages['msg'],'delete')
		//for (var i = 0; i < messages['msg'].length; i++) egw.dataDeleteUID(messages['msg'][i]);
		this.mail_refreshMessageGrid();
	},
	/**
	 * mail_copy - implementation of the copy action from drag n drop
	 *
	 * @param _action
	 * @param _senders - the representation of the elements dragged
	 * @param _target - the representation of the target
	 */
	mail_copy: function(_action,_senders,_target) {
		//console.log(_action,_senders,_target);
		var target = _action.id == 'drop_copy_mail' ? _target.iface.id : _action.id.substr(5);
		var messages = this.mail_getFormData(_senders);
		//alert('mail_copy('+messages.msg.join(',')+' --> '+target+')');
		// TODO: Write move/copy function which cares about doing the same stuff
		// as the "onNodeSelect" function!
		egw.json('mail.mail_ui.ajax_copyMessages',[target, messages])
			.sendRequest();
		this.mail_refreshMessageGrid();
	},

	/**
	 * mail_AddFolder - implementation of the AddFolder action of right click options on the tree
	 *
	 * @param _action
	 * @param _senders - the representation of the tree leaf to be manipulated
	 */
	mail_AddFolder: function(action,_senders) {
		//console.log(action,_senders);
		//action.id == 'add'
		//_senders.iface.id == target leaf / leaf to edit
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		OldFolderName = ftree.getLabel(_senders[0].iface.id);
		if (jQuery(OldFolderName).text().length>0) OldFolderName = jQuery(OldFolderName).text();
		OldFolderName = OldFolderName.trim();
		OldFolderName = OldFolderName.replace(/\([0-9]*\)/g,'').trim();
		//console.log(OldFolderName);
		NewFolderName = prompt(egw.lang("Add a new Folder to %1:",OldFolderName));
		if (jQuery(NewFolderName).text().length>0) NewFolderName = jQuery(NewFolderName).text();
		//alert(NewFolderName);
		if (NewFolderName && NewFolderName.length>0)
		{
			app.mail.app_refresh(egw.lang("Adding Folder %1 to %2",NewFolderName, OldFolderName, 'mail'));
			egw.json('mail.mail_ui.ajax_addFolder',[_senders[0].iface.id, NewFolderName])
				.sendRequest(true);
		}
	},

	/**
	 * mail_RenameFolder - implementation of the RenameFolder action of right click options on the tree
	 *
	 * @param _action
	 * @param _senders - the representation of the tree leaf to be manipulated
	 */
	mail_RenameFolder: function(action,_senders) {
		//console.log(action,_senders);
		//action.id == 'rename'
		//_senders.iface.id == target leaf / leaf to edit
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		OldFolderName = ftree.getLabel(_senders[0].iface.id);
		if (jQuery(OldFolderName).text().length>0) OldFolderName = jQuery(OldFolderName).text();
		OldFolderName = OldFolderName.trim();
		OldFolderName = OldFolderName.replace(/\([0-9]*\)/g,'').trim();
		//console.log(OldFolderName);
		NewFolderName = prompt(egw.lang("Rename Folder %1 to:",OldFolderName));
		if (jQuery(NewFolderName).text().length>0) NewFolderName = jQuery(NewFolderName).text();
		//alert(NewFolderName);
		if (NewFolderName && NewFolderName.length>0)
		{
			app.mail.app_refresh(egw.lang("Renaming Folder %1 to %2",OldFolderName,NewFolderName, 'mail'));
			egw.json('mail.mail_ui.ajax_renameFolder',[_senders[0].iface.id, NewFolderName])
				.sendRequest(true);
		}
	},

	/**
	 * mail_DeleteFolder - implementation of the DeleteFolder action of right click options on the tree
	 *
	 * @param _action
	 * @param _senders - the representation of the tree leaf to be manipulated
	 */
	mail_DeleteFolder: function(action,_senders) {
		//console.log(action,_senders);
		//action.id == 'delete'
		//_senders.iface.id == target leaf / leaf to edit
		var ftree = etemplate2.getByApplication('mail')[0].widgetContainer.getWidgetById(this.nm_index+'[foldertree]');
		OldFolderName = ftree.getLabel(_senders[0].iface.id);
		if (jQuery(OldFolderName).text().length>0) OldFolderName = jQuery(OldFolderName).text();
		OldFolderName = OldFolderName.trim();
		OldFolderName = OldFolderName.replace(/\([0-9]*\)/g,'').trim();
		//console.log(OldFolderName);
		reallyDelete = confirm(egw.lang("Do you really want to DELETE Folder %1 ? \r\nAll messages in the folder will be lost",OldFolderName));
		if (reallyDelete)
		{
			app.mail.app_refresh(egw.lang("Deleting Folder %1",OldFolderName, 'mail'));
			egw.json('mail.mail_ui.ajax_deleteFolder',[_senders[0].iface.id])
				.sendRequest(true);
		}
	},

	/**
	 * Send names of uploaded files (again) to server, to process them: either copy to vfs or ask overwrite/rename
	 * 
	 * @param _event
	 * @param _file_count
	 * @param {string} [_path=current directory] Where the file is uploaded to.
	 */
	uploadForImport: function(_event, _file_count, _path)
	{
		//console.log(_event,_file_count,_path);
		// path is probably not needed when uploading for file; maybe it is when from vfs
		if(typeof _path == 'undefined')
		{
			//_path = this.get_path();
		}
		if (_file_count && !jQuery.isEmptyObject(_event.data.getValue()))
		{
			var widget = _event.data;
			//console.log(widget.getValue());
//			var request = new egw_json_request('mail_ui::ajax_importMessage', ['upload', widget.getValue(), _path], this);
//			widget.set_value('');
//			request.sendRequest();//false, this._upload_callback, this);
			this.et2_obj.submit();
		}
	},

	/**
	 * Send names of uploaded files (again) to server, to process them: either copy to vfs or ask overwrite/rename
	 * 
	 * @param _event
	 * @param _file_count
	 * @param {string} [_path=current directory] Where the file is uploaded to.
	 */
	uploadForCompose: function(_event, _file_count, _path)
	{
		//console.log(_event,_file_count,_path);
		// path is probably not needed when uploading for file; maybe it is when from vfs
		if(typeof _path == 'undefined')
		{
			//_path = this.get_path();
		}
		if (_file_count && !jQuery.isEmptyObject(_event.data.getValue()))
		{
			var widget = _event.data;
			//console.log(widget.getValue());
//			var request = new egw_json_request('mail_ui::ajax_importMessage', ['upload', widget.getValue(), _path], this);
//			widget.set_value('');
//			request.sendRequest();//false, this._upload_callback, this);
			this.et2_obj.submit();
		}
	},

	vfsUploadForImport: function(_egw, _widget, _window) {
		//console.log(_egw, _widget, _window);
		if (!jQuery.isEmptyObject(_widget.getValue()))
		{
			this.et2_obj.submit();
		}
	},

	vfsUploadForCompose: function(_egw, _widget, _window)
	{
		//console.log(_egw, _widget, _window);
		if (!jQuery.isEmptyObject(_widget.getValue()))
		{
			this.et2_obj.submit();
		}
	},

	submitOnChange: function(_egw, _widget, _window) {
		//console.log(_egw, _widget, _window);
		if (!jQuery.isEmptyObject(_widget.getValue()))
		{
			this.et2_obj.submit();
		}
	},

	saveAsDraft: function(_egw, _widget, _window)
	{
		this.et2_obj.submit();
	},

	sieve_editRules_radiobtn: function()
	{
		console.log("hi i am radiobtn");
	},
	/**
	 *
	 */
	sieve_vac_all_aliases: function()
	{

		var addr = this.et2.getWidgetById('addresses');

		console.log('say something');
		addr.select_all();
		//addr.set_autocomplete_url('mail.mail_compose.ajax_searchAddress');
	},

	/**
	 * action - handling actions on sieve rules
	 *
	 * @param _type - action name
	 * @param _selected - selected row from the sieve rule list
	 * @param _msg - messages
	 */
	action: function(_type, _selected)
	{
		var  actionData ;
		var that = this;
		var typeId = _type.id;
		var ruleID = ((_selected[0].id.split("_").pop()) - 1); // subtract the row id from 1 because the first row id is reserved by grid header
		if (_type)
		{

			switch (_type.id)
			{
				case 'delete':

					var callbackDeleteDialog = function (button_id)
					{
						if (button_id == et2_dialog.YES_BUTTON )
						{
							actionData = _type.parent.data.widget.getArrayMgr('content');
							that._do_action(typeId, actionData['data'],ruleID);
						}
					}
					var confirmDeleteDialog = et2_dialog.show_dialog(callbackDeleteDialog, egw.lang("Do you really want to DELETE this Rule"),egw.lang("Delete"), {},et2_dialog.BUTTONS_YES_NO_CANCEL, et2_dialog.WARNING_MESSAGE);

					break;
				case 'add'	:
					$linkData = "mail.mail_sieve.edit";
					egw.open_link($linkData,'',"600x480");
					break;
				case 'edit'	:
					$linkData = "mail.mail_sieve.edit&ruleID="+ruleID;
					egw.open_link($linkData,'',"600x480");
					break;
				case 'enable':
					actionData = _type.parent.data.widget.getArrayMgr('content');
					this._do_action(typeId,actionData['data'],ruleID);
					//window.egw_refresh('refreshed',this.appname);
					break;
				case 'disable':
					actionData = _type.parent.data.widget.getArrayMgr('content');
					this._do_action(typeId,actionData['data'],ruleID);
					break;

			}
		}

	},

	_do_action: function(_typeID, _data,_selectedID,_msg)
	{
		if (_typeID && _data)
		{
			var request = egw().json('mail.mail_sieve.ajax_action', [_typeID,_selectedID,_msg],null,null,true);
			console.log(request);
			request.sendRequest();
		}
	},

	sieve_egw_refresh: function(_execid,_msg)
	{
		var request = egw().json('mail.mail_sieve.ajax_sieve_egw_refresh', [this.et2_obj.etemplate_exec_id,_msg],null,this.et2_obj,true);
		console.log(request);
		request.sendRequest();
	},

});
