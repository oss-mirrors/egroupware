/**
 * EGroupware: Stylite Pixelegg template: hiding/showing header
 *
 * @link http://www.egroupware.org
 * @license http://opensource.org/licenses/gpl-license.php GPL - GNU General Public License
 * @author Wolfgang Ott <wolfgang.ott@pixelegg.de>
 * @package pixelegg
 * @version $Id: class.pixelegg_framework.inc.php 2741 2013-11-14 13:53:24Z ralfbecker $
 */

function show_pixelegg_header(_toggle, _delay)
{
	$j("#egw_fw_header").slideToggle();
        
	$j("#egw_fw_topmenu_info_items").animate({"margin-right": "20px","bottom": "","padding-right" : "0"},_delay);
	$j("#egw_fw_topmenu_info_items").css("position", "relative");
	
    $j("#egw_fw_sidebar").animate({'top':'57px'},_delay);
        $j("#egw_fw_tabs").animate({'margin-top':'0px'},_delay);
        $j(".egw_fw_ui_sidemenu_entry_header_active").css("background-position","95% -3000px");
	$j(_toggle).parent().removeClass("slidedown");
	$j(_toggle).parent().addClass("slideup");
}

function hide_pixelegg_header(_toggle, _delay)
{
	$j("#egw_fw_header").slideToggle();
	$j("#egw_fw_sidebar").animate({'top':'0px'},_delay);
	$j("#egw_fw_topmenu_info_items").show();
	$j("#egw_fw_logout").show();
	$j("#egw_fw_print").show();
//        $j("#egw_fw_tabs").animate({'margin-top':'-13px'},_delay);
	$j("#egw_fw_topmenu_info_items").animate({
			"bottom": "3px",
                        "right": "5px",
			"display": "flex",
			"padding-right" : "20px",
			"text-align": "right",
			"white-space": "nowrap",
			},_delay);
	$j(".egw_fw_ui_sidemenu_entry_header_active").css("background-position","95% 50%");
      
	$j("#egw_fw_topmenu_info_items").css("position", "fixed");
	$j("#egw_fw_topmenu_info_items").css("z-index", "1000");
	$j(_toggle).parent().removeClass("slideup");
	$j(_toggle).parent().addClass("slidedown");
}

egw_LAB.wait(function() {
	$j(document).ready(function() {

		$j('#slidetoggle').click(function(){
			if ($j('#egw_fw_header').css('display') === 'none') {
				show_pixelegg_header(this, 1000);
				egw.set_preference('common', 'pixelegg_header_hidden', '');
			}
			else {
				hide_pixelegg_header(this, 1000);
				egw.set_preference('common', 'pixelegg_header_hidden', 'true');
			}
		});

		// hide header, if pref says it is not shown
		if (egw.preference('pixelegg_header_hidden')) {
			hide_pixelegg_header($j('#slidetoggle'),0);
		}
         
	});

	// Override jdots height calcluation
	egw_fw.prototype.getIFrameHeight = function()
	{
		$header = $j(this.tabsUi.appHeaderContainer);
		var content = $j(this.tabsUi.activeTab.contentDiv);
		//var height = $j(this.sidemenuDiv).height()-this.tabsUi.appHeaderContainer.outerHeight() - this.tabsUi.appHeader.outerHeight();
		var height = $j(this.sidemenuDiv).height()
			- $header.outerHeight() - $j(this.tabsUi.contHeaderDiv).outerHeight() - (content.outerHeight(true) - content.height())
			// Not sure where this comes from...
			+ 5;
		return height;
	};
});



/* #egw_fw_topmenu_info_items {
    bottom: 0;
    display: flex;
    float: right;
    padding-right: 20px;
    position: fixed;
    text-align: right;
    white-space: nowrap;
    z-index: 1000;
} */