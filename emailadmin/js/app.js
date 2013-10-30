/**
 * EGroupware emailadmin static javascript code
 *
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @package emailadmin
 * @link http://www.egroupware.org
 * @author Klaus Leithoff <kl@stylite.de>
 * @author Ralf Becker <rb@stylite.de>
 * @version $Id$
 */

/**
 * UI for emailadmin
 *
 * @augments AppJS
 */
app.emailadmin = AppJS.extend(
{
	appname: 'emailadmin',

	/**
	 * Constructor
	 *
	 * @memberOf app.filemanager
	 */
	init: function()
	{
		// call parent
		this._super.apply(this, arguments);
	},

	/**
	 * Destructor
	 */
	destroy: function()
	{
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
		// call parent
		this._super.apply(this, arguments);
	},

	/**
	 * Switch account wizard to manual entry
	 */
	wizard_manual: function()
	{
		jQuery('tr.emailadmin_manual').fadeToggle();// not sure how to to this et2-isch
	},

	/**
	 * onclick for continue button to show progress animation
	 */
	wizard_detect: function(_event, _widget)
	{
		// we need to do a manual asynchronious submit to show progress animation
		// default synchronious submit stops animation!
		if (this.et2._inst.submit('button[continue]', true))	// true = async submit
		{
			jQuery('td.emailadmin_progress').show();
		}
		return false;
	},

	/**
	 * Set default port, if imap ssl-type changes
	 */
	wizard_imap_ssl_onchange: function(_event, _widget)
	{
		var ssl_type = _widget.get_value();
		this.et2.getWidgetById('acc_imap_port').set_value(ssl_type == 1 || ssl_type == 2 ? 993 : 143);
	},

	/**
	 * Set default port, if imap ssl-type changes
	 */
	wizard_smtp_ssl_onchange: function(_event, _widget)
	{
		var ssl_type = _widget.get_value();
		this.et2.getWidgetById('acc_smtp_port').set_value(ssl_type == 'no' ? 25 :
			(ssl_type == 1 || ssl_type == 2 ? 465 : 597));
	}
});

function disableGroupSelector()
{
	//alert('Group'+document.getElementById('exec[ea_group]').value+' User'+document.getElementById('eT_accountsel_exec_ea_user').value);
	if (document.getElementById('eT_accountsel_exec_ea_user').value != '')
	{
		if (document.getElementById('exec[ea_group]').value != '') document.getElementById('exec[ea_group]').value = '';
		document.getElementById('exec[ea_group]').disabled = true;
	}
	else
	{
		document.getElementById('exec[ea_group]').disabled = false;
	}
}

function addRow(_selectBoxName, _prompt) {
	result = prompt(_prompt, '');

	if((result == '') || (result == null)) {
		return false;
	}

	var newOption = new Option(result, result);

	selectBox = document.getElementById(_selectBoxName);
	var length      = selectBox.length;

	selectBox.options[length] = newOption;
	selectBox.selectedIndex = length;
}

function editRow(_selectBoxName, _prompt) {
	selectBox = document.getElementById(_selectBoxName);

	selectedItem = selectBox.selectedIndex;

	if(selectedItem != null && selectedItem != -1) {
		value = selectBox.options[selectedItem].text;
		result = prompt(_prompt, value);

		if((result == '') || (result == null)) {
			return false;
		}

		var newOption = new Option(result, result);

		selectBox.options[selectedItem] = newOption;
		selectBox.selectedIndex = selectedItem;
	}
}

function removeRow(_selectBoxName) {
	selectBox = document.getElementById(_selectBoxName);

	selectedItem = selectBox.selectedIndex;
	if(selectedItem != null) {
		selectBox.options[selectedItem] = null;
	}
	selectedItem--;
	if(selectedItem >= 0) {
		selectBox.selectedIndex = selectedItem;
	} else if (selectBox.length > 0) {
		selectBox.selectedIndex = 0;
	}
}

function selectAllOptions(_selectBoxName) {
	selectBox = document.getElementById(_selectBoxName);

	for(var i=0;i<selectBox.length;i++) {
		selectBox[i].selected=true;
	}

}
