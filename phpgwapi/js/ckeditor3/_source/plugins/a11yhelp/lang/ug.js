﻿/*
Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.plugins.setLang( 'a11yhelp', 'ug',
{
	accessibilityHelp :
	{
		title : 'قوشۇمچە چۈشەندۈرۈش',
		contents : 'ياردەم مەزمۇنى. بۇ سۆزلەشكۈنى ياپماقچى بولسىڭىز ESC نى بېسىڭ.',
		legend :
		[
			{
				name : 'ئادەتتىكى',
				items :
						[
							{
								name : 'قورال بالداق تەھرىر',
								legend:
									'${toolbarFocus} بېسىلسا قورال بالداققا يېتەكلەيدۇ، TAB ياكى SHIFT+TAB ئارقىلىق قورال بالداق گۇرۇپپىسى تاللىنىدۇ، ئوڭ سول يا ئوقتا توپچا تاللىنىدۇ، بوشلۇق ياكى Enter كۇنۇپكىسىدا تاللانغان توپچىنى قوللىنىدۇ.'
							},

							{
								name : 'تەھرىرلىگۈچ سۆزلەشكۈسى',
								legend :
									'Inside a dialog, press TAB to navigate to next dialog field, press SHIFT + TAB to move to previous field, press ENTER to submit dialog, press ESC to cancel dialog. For dialogs that have multiple tab pages, press ALT + F10 to navigate to tab-list. Then move to next tab with TAB OR RIGTH ARROW. Move to previous tab with SHIFT + TAB or LEFT ARROW. Press SPACE or ENTER to select the tab page.'  // MISSING
							},

							{
								name : 'تەھرىرلىگۈچ تىل مۇھىت تىزىملىكى',
								legend :
									'Press ${contextMenu} or APPLICATION KEY to open context-menu. Then move to next menu option with TAB or DOWN ARROW. Move to previous option with SHIFT+TAB or UP ARROW. Press SPACE or ENTER to select the menu option. Open sub-menu of current option with SPACE or ENTER or RIGHT ARROW. Go back to parent menu item with ESC or LEFT ARROW. Close context menu with ESC.'  // MISSING
							},

							{
								name : 'تەھرىرلىگۈچ تىزىمى',
								legend :
									'Inside a list-box, move to next list item with TAB OR DOWN ARROW. Move to previous list item with SHIFT + TAB or UP ARROW. Press SPACE or ENTER to select the list option. Press ESC to close the list-box.'  // MISSING
							},

							{
								name : 'تەھرىرلىگۈچ ئېلېمېنت يول بالداق',
								legend :
									'${elementsPathFocus} بېسىلسا ئېلېمېنت يول بالداققا يېتەكلەيدۇ، TAB ياكى ئوڭ يا ئوقتا كېيىنكى ئېلېمېنت تاللىنىدۇ،  SHIFT+TAB ياكى سول يا ئوقتا ئالدىنقى ئېلېمېنت تاللىنىدۇ،  بوشلۇق ياكى Enter كۇنۇپكىسىدا تەھرىرلىگۈچتىكى ئېلېمېنت تاللىنىدۇ.'
							}
						]
			},
			{
				name : 'بۇيرۇق',
				items :
						[
							{
								name : 'بۇيرۇقتىن يېنىۋال',
								legend : '${undo} نى بېسىڭ'
							},
							{
								name : 'قايتىلاش بۇيرۇقى',
								legend : '${redo} نى بېسىڭ'
							},
							{
								name : 'توملىتىش بۇيرۇقى',
								legend : '${bold} نى بېسىڭ'
							},
							{
								name : 'يانتۇ بۇيرۇقى',
								legend : '${italic} نى بېسىڭ'
							},
							{
								name : 'ئاستى سىزىق بۇيرۇقى',
								legend : '${underline} نى بېسىڭ'
							},
							{
								name : 'ئۇلانما بۇيرۇقى',
								legend : '${link} نى بېسىڭ'
							},
							{
								name : 'قورال بالداق قاتلاش بۇيرۇقى',
								legend : '${toolbarCollapse} نى بېسىڭ'
							},
							{
								name : 'توسالغۇسىز لايىھە چۈشەندۈرۈشى',
								legend : '${a11yHelp} نى بېسىڭ'
							}
						]
			}
		]
	}
});
