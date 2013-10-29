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
