﻿/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.plugins.setLang( 'a11yhelp', 'he',
{
	accessibilityHelp :
	{
		title : 'הוראות נגישות',
		contents : 'הוראות נגישות. לסגירה לחץ אסקייפ (ESC).',
		legend :
		[
			{
				name : 'כללי',
				items :
						[
							{
								name : 'סרגל הכלים',
								legend:
									'לחץ על ${toolbarFocus} כדי לנווט לסרגל הכלים. עבור לכפתור הבא עם מקש הטאב (TAB) או חץ שמאלי. עבור לכפתור הקודם עם מקש השיפט (SHIFT) + טאב (TAB) או חץ ימני. לחץ רווח או אנטר (ENTER) כדי להפעיל את הכפתור הנבחר.'
							},

							{
								name : 'דיאלוגים (חלונות תשאול)',
								legend :
									'בתוך דיאלוג, לחץ טאב (TAB) כדי לנווט לשדה הבא, לחץ שיפט (SHIFT) + טאב (TAB) כדי לנווט לשדה הקודם, לחץ אנטר (ENTER) כדי לשלוח את הדיאלוג, לחץ אסקייפ (ESC) כדי לבטל. בתוך דיאלוגים בעלי מספר טאבים (לשוניות), לחץ אלט (ALT) + F10 כדי לנווט לשורת הטאבים. נווט לטאב הבא עם טאב (TAB) או חץ שמאלי. עבור לטאב הקודם עם שיפט (SHIFT) + טאב (TAB) או חץ שמאלי. לחץ רווח או אנטר (ENTER) כדי להיכנס לטאב.'
							},

							{
								name : 'תפריט ההקשר (Context Menu)',
								legend :
									'לחץ ${contextMenu} או APPLICATION KEYכדי לפתוח את תפריט ההקשר. עבור לאפשרות הבאה עם טאב (TAB) או חץ למטה. עבור לאפשרות הקודמת עם שיפט (SHIFT) + טאב (TAB) או חץ למעלה. לחץ רווח או אנטר (ENTER) כדי לבחור את האפשרות. פתח את תת התפריט (Sub-menu) של האפשרות הנוכחית עם רווח או אנטר (ENTER) או חץ שמאלי. חזור לתפריט האב עם אסקייפ (ESC) או חץ שמאלי. סגור את תפריט ההקשר עם אסקייפ (ESC).'
							},

							{
								name : 'תפריטים צפים (List boxes)',
								legend :
									'בתוך תפריט צף, עבור לפריט הבא עם טאב (TAB) או חץ למטה. עבור לתפריט הקודם עם שיפט (SHIFT) + טאב (TAB) or חץ עליון. Press SPACE or ENTER to select the list option. Press ESC to close the list-box.'
							},

							{
								name : 'עץ אלמנטים (Elements Path)',
								legend :
									'לחץ ${elementsPathFocus} כדי לנווט לעץ האלמנטים. עבור לפריט הבא עם טאב (TAB) או חץ ימני. עבור לפריט הקודם עם שיפט (SHIFT) + טאב (TAB) או חץ שמאלי. לחץ רווח או אנטר (ENTER) כדי לבחור את האלמנט בעורך.'
							}
						]
			},
			{
				name : 'פקודות',
				items :
						[
							{
								name : ' ביטול צעד אחרון',
								legend : 'לחץ ${undo}'
							},
							{
								name : ' חזרה על צעד אחרון',
								legend : 'לחץ ${redo}'
							},
							{
								name : ' הדגשה',
								legend : 'לחץ ${bold}'
							},
							{
								name : ' הטייה',
								legend : 'לחץ ${italic}'
							},
							{
								name : ' הוספת קו תחתון',
								legend : 'לחץ ${underline}'
							},
							{
								name : ' הוספת לינק',
								legend : 'לחץ ${link}'
							},
							{
								name : ' כיווץ סרגל הכלים',
								legend : 'לחץ ${toolbarCollapse}'
							},
							{
								name : ' הוראות נגישות',
								legend : 'לחץ ${a11yHelp}'
							}
						]
			}
		]
	}
});
