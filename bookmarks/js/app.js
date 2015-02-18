/**
 * EGroupware - Bookmarks - Javascript UI
 *
 * @link http://www.egroupware.org
 * @package bookmarks
 * @author Hadi Nategh	<hn-AT-stylite.de>
 * @copyright 2015 Stylite AG
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @version $Id:$
 */

/**
 * UI for bookmarks
 *
 * @augments AppJS
 */
app.bookmarks = AppJS.extend(
{
	appname: 'bookmarks',
	/**
	 * et2 widget container
	 */
	et2: null,
	/**
	 * path widget
	 */

	/**
	 * Constructor
	 *
	 * @memberOf app.bookmarks
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
		// call parent
		this._super.apply(this, arguments);

	},

	/**
	 * Redirect the selected bookmark's leaf
	 * 
	 * @param {type} _id
	 * @param {type} _widget
	 */
	tree_onclick: function (_id, _widget)
	{
		// Get the bookmark id
		var id = _id.split('/');
		if (id) id = id[id.length-1];
		
		var url = _widget.getUserData(_id,'url');
		if (url) this.egw.open_link(this.egw.link('/index','menuaction=bookmarks.bookmarks_ui.redirect&bm_id='+id),'_blank');
	}

});
