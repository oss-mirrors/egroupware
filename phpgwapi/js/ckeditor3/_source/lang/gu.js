﻿/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview Defines the {@link CKEDITOR.lang} object, for the
 * Gujarati language.
 */

/**#@+
   @type String
   @example
*/

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKEDITOR.lang['gu'] =
{
	/**
	 * The language reading direction. Possible values are "rtl" for
	 * Right-To-Left languages (like Arabic) and "ltr" for Left-To-Right
	 * languages (like English).
	 * @default 'ltr'
	 */
	dir : 'ltr',

	/*
	 * Screenreader titles. Please note that screenreaders are not always capable
	 * of reading non-English words. So be careful while translating it.
	 */
	editorTitle : 'Rich text editor, %1', // MISSING
	editorHelp : 'Press ALT 0 for help', // MISSING

	// ARIA descriptions.
	toolbars	: 'એડીટર ટૂલ બાર',
	editor		: 'રીચ ટેક્ષ્ત્ એડીટર',

	// Toolbar buttons without dialogs.
	source			: 'મૂળ કે પ્રાથમિક દસ્તાવેજ',
	newPage			: 'નવુ પાનું',
	save			: 'સેવ',
	preview			: 'પૂર્વદર્શન',
	cut				: 'કાપવું',
	copy			: 'નકલ',
	paste			: 'પેસ્ટ',
	print			: 'પ્રિન્ટ',
	underline		: 'અન્ડર્લાઇન, નીચે લીટી',
	bold			: 'બોલ્ડ/સ્પષ્ટ',
	italic			: 'ઇટેલિક, ત્રાંસા',
	selectAll		: 'બઘું પસંદ કરવું',
	removeFormat	: 'ફૉર્મટ કાઢવું',
	strike			: 'છેકી નાખવું',
	subscript		: 'એક ચિહ્નની નીચે કરેલું બીજું ચિહ્ન',
	superscript		: 'એક ચિહ્ન ઉપર કરેલું બીજું ચિહ્ન.',
	horizontalrule	: 'સમસ્તરીય રેખા ઇન્સર્ટ/દાખલ કરવી',
	pagebreak		: 'ઇન્સર્ટ પેજબ્રેક/પાનાને અલગ કરવું/દાખલ કરવું',
	pagebreakAlt		: 'નવું પાનું',
	unlink			: 'લિંક કાઢવી',
	undo			: 'રદ કરવું; પહેલાં હતી એવી સ્થિતિ પાછી લાવવી',
	redo			: 'રિડૂ; પછી હતી એવી સ્થિતિ પાછી લાવવી',

	// Common messages and labels.
	common :
	{
		browseServer	: 'સર્વર બ્રાઉઝ કરો',
		url				: 'URL',
		protocol		: 'પ્રોટોકૉલ',
		upload			: 'અપલોડ',
		uploadSubmit	: 'આ સર્વરને મોકલવું',
		image			: 'ચિત્ર',
		flash			: 'ફ્લૅશ',
		form			: 'ફૉર્મ/પત્રક',
		checkbox		: 'ચેક બોક્સ',
		radio			: 'રેડિઓ બટન',
		textField		: 'ટેક્સ્ટ ફીલ્ડ, શબ્દ ક્ષેત્ર',
		textarea		: 'ટેક્સ્ટ એરિઆ, શબ્દ વિસ્તાર',
		hiddenField		: 'ગુપ્ત ક્ષેત્ર',
		button			: 'બટન',
		select			: 'પસંદગી ક્ષેત્ર',
		imageButton		: 'ચિત્ર બટન',
		notSet			: '<સેટ નથી>',
		id				: 'Id',
		name			: 'નામ',
		langDir			: 'ભાષા લેખવાની પદ્ધતિ',
		langDirLtr		: 'ડાબે થી જમણે (LTR)',
		langDirRtl		: 'જમણે થી ડાબે (RTL)',
		langCode		: 'ભાષા કોડ',
		longDescr		: 'વધારે માહિતી માટે URL',
		cssClass		: 'સ્ટાઇલ-શીટ ક્લાસ',
		advisoryTitle	: 'મુખ્ય મથાળું',
		cssStyle		: 'સ્ટાઇલ',
		ok				: 'ઠીક છે',
		cancel			: 'રદ કરવું',
		close			: 'બંધ કરવું',
		preview			: 'જોવું',
		generalTab		: 'જનરલ',
		advancedTab		: 'અડ્વાન્સડ',
		validateNumberFailed : 'આ રકમ આકડો નથી.',
		confirmNewPage	: 'સવે કાર્ય વગરનું ફકરો ખોવાઈ જશે. તમને ખાતરી છે કે તમને નવું પાનું ખોલવું છે?',
		confirmCancel	: 'ઘણા વિકલ્પો બદલાયા છે. તમારે આ બોક્ષ્ બંધ કરવું છે?',
		options			: 'વિકલ્પો',
		target			: 'લક્ષ્ય',
		targetNew		: 'નવી વિન્ડો (_blank)',
		targetTop		: 'ઉપરની વિન્ડો (_top)',
		targetSelf		: 'એજ વિન્ડો (_self)',
		targetParent	: 'પેરનટ વિન્ડો (_parent)',
		langDirLTR		: 'ડાબે થી જમણે (LTR)',
		langDirRTL		: 'જમણે થી ડાબે (RTL)',
		styles			: 'શૈલી',
		cssClasses		: 'શૈલી કલાસીસ',
		width			: 'પહોળાઈ',
		height			: 'ઊંચાઈ',
		align			: 'લાઇનદોરીમાં ગોઠવવું',
		alignLeft		: 'ડાબી બાજુ ગોઠવવું',
		alignRight		: 'જમણી',
		alignCenter		: 'મધ્ય સેન્ટર',
		alignTop		: 'ઉપર',
		alignMiddle		: 'વચ્ચે',
		alignBottom		: 'નીચે',
		invalidHeight	: 'ઉંચાઈ એક આંકડો હોવો જોઈએ.',
		invalidWidth	: 'પોહળ ઈ એક આંકડો હોવો જોઈએ.',
		invalidCssLength	: '"%1" ની વેલ્યુ એક પોસીટીવ આંકડો હોવો જોઈએ અથવા CSS measurement unit (px, %, in, cm, mm, em, ex, pt, or pc) વગર.',
		invalidHtmlLength	: '"%1" ની વેલ્યુ એક પોસીટીવ આંકડો હોવો જોઈએ અથવા HTML measurement unit (px or %) વગર.',
		invalidInlineStyle	: 'ઈનલાઈન  સ્ટાઈલ ની વેલ્યુ  "name : value" ના ફોર્મેટ માં હોવી જોઈએ, વચ્ચે સેમી-કોલોન જોઈએ.',
		cssLengthTooltip	: 'પિક્ષ્લ્ નો આંકડો CSS unit (px, %, in, cm, mm, em, ex, pt, or pc) માં નાખો.',

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, નથી મળતું</span>'
	},

	contextmenu :
	{
		options : 'કોન્તેક્ષ્ત્ મેનુના વિકલ્પો'
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: 'વિશિષ્ટ અક્ષર ઇન્સર્ટ/દાખલ કરવું',
		title		: 'સ્પેશિઅલ વિશિષ્ટ અક્ષર પસંદ કરો',
		options : 'સ્પેશિઅલ કરેક્ટરના વિકલ્પો'
	},

	// Link dialog.
	link :
	{
		toolbar		: 'લિંક ઇન્સર્ટ/દાખલ કરવી',
		other 		: '<other> <અન્ય>',
		menu		: ' લિંક એડિટ/માં ફેરફાર કરવો',
		title		: 'લિંક',
		info		: 'લિંક ઇન્ફૉ ટૅબ',
		target		: 'ટાર્ગેટ/લક્ષ્ય',
		upload		: 'અપલોડ',
		advanced	: 'અડ્વાન્સડ',
		type		: 'લિંક પ્રકાર',
		toUrl		: 'URL',
		toAnchor	: 'આ પેજનો ઍંકર',
		toEmail		: 'ઈ-મેલ',
		targetFrame		: '<ફ્રેમ>',
		targetPopup		: '<પૉપ-અપ વિન્ડો>',
		targetFrameName	: 'ટાર્ગેટ ફ્રેમ નું નામ',
		targetPopupName	: 'પૉપ-અપ વિન્ડો નું નામ',
		popupFeatures	: 'પૉપ-અપ વિન્ડો ફીચરસૅ',
		popupResizable	: 'રીસાઈઝએબલ',
		popupStatusBar	: 'સ્ટૅટસ બાર',
		popupLocationBar: 'લોકેશન બાર',
		popupToolbar	: 'ટૂલ બાર',
		popupMenuBar	: 'મેન્યૂ બાર',
		popupFullScreen	: 'ફુલ સ્ક્રીન (IE)',
		popupScrollBars	: 'સ્ક્રોલ બાર',
		popupDependent	: 'ડિપેન્ડન્ટ (Netscape)',
		popupLeft		: 'ડાબી બાજુ',
		popupTop		: 'જમણી બાજુ',
		id				: 'Id',
		langDir			: 'ભાષા લેખવાની પદ્ધતિ',
		langDirLTR		: 'ડાબે થી જમણે (LTR)',
		langDirRTL		: 'જમણે થી ડાબે (RTL)',
		acccessKey		: 'ઍક્સેસ કી',
		name			: 'નામ',
		langCode			: 'ભાષા લેખવાની પદ્ધતિ',
		tabIndex			: 'ટૅબ ઇન્ડેક્સ',
		advisoryTitle		: 'મુખ્ય મથાળું',
		advisoryContentType	: 'મુખ્ય કન્ટેન્ટ પ્રકાર',
		cssClasses		: 'સ્ટાઇલ-શીટ ક્લાસ',
		charset			: 'લિંક રિસૉર્સ કૅરિક્ટર સેટ',
		styles			: 'સ્ટાઇલ',
		rel			: 'સંબંધની સ્થિતિ',
		selectAnchor		: 'ઍંકર પસંદ કરો',
		anchorName		: 'ઍંકર નામથી પસંદ કરો',
		anchorId			: 'ઍંકર એલિમન્ટ Id થી પસંદ કરો',
		emailAddress		: 'ઈ-મેલ સરનામું',
		emailSubject		: 'ઈ-મેલ વિષય',
		emailBody		: 'સંદેશ',
		noAnchors		: '(ડૉક્યુમન્ટમાં ઍંકરની સંખ્યા)',
		noUrl			: 'લિંક  URL ટાઇપ કરો',
		noEmail			: 'ઈ-મેલ સરનામું ટાઇપ કરો'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: 'ઍંકર ઇન્સર્ટ/દાખલ કરવી',
		menu		: 'ઍંકરના ગુણ',
		title		: 'ઍંકરના ગુણ',
		name		: 'ઍંકરનું નામ',
		errorName	: 'ઍંકરનું નામ ટાઈપ કરો',
		remove		: 'સ્થિર નકરવું'
	},

	// List style dialog
	list:
	{
		numberedTitle		: 'આંકડાના લીસ્ટના ગુણ',
		bulletedTitle		: 'બુલેટેડ લીસ્ટના ગુણ',
		type				: 'પ્રકાર',
		start				: 'શરુ કરવું',
		validateStartNumber				:'લીસ્ટના સરુઆતનો આંકડો પુરો હોવો જોઈએ.',
		circle				: 'વર્તુળ',
		disc				: 'ડિસ્ક',
		square				: 'ચોરસ',
		none				: 'કસુ ',
		notset				: '<સેટ નથી>',
		armenian			: 'અરમેનિયન આંકડા પદ્ધતિ',
		georgian			: 'ગેઓર્ગિયન આંકડા પદ્ધતિ (an, ban, gan, etc.)',
		lowerRoman			: 'રોમન નાના (i, ii, iii, iv, v, etc.)',
		upperRoman			: 'રોમન મોટા (I, II, III, IV, V, etc.)',
		lowerAlpha			: 'આલ્ફા નાના (a, b, c, d, e, etc.)',
		upperAlpha			: 'આલ્ફા મોટા (A, B, C, D, E, etc.)',
		lowerGreek			: 'ગ્રીક નાના (alpha, beta, gamma, etc.)',
		decimal				: 'આંકડા (1, 2, 3, etc.)',
		decimalLeadingZero	: 'સુન્ય આગળ આંકડા (01, 02, 03, etc.)'
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: 'શોધવું અને બદલવું',
		find				: 'શોધવું',
		replace				: 'રિપ્લેસ/બદલવું',
		findWhat			: 'આ શોધો',
		replaceWith			: 'આનાથી બદલો',
		notFoundMsg			: 'તમે શોધેલી ટેક્સ્ટ નથી મળી',
		findOptions			: 'વીકલ્પ શોધો',
		matchCase			: 'કેસ સરખા રાખો',
		matchWord			: 'બઘા શબ્દ સરખા રાખો',
		matchCyclic			: 'સરખાવવા બધા',
		replaceAll			: 'બઘા બદલી ',
		replaceSuccessMsg	: '%1 ફેરફારો બાદલાયા છે.'
	},

	// Table Dialog
	table :
	{
		toolbar		: 'ટેબલ, કોઠો',
		title		: 'ટેબલ, કોઠાનું મથાળું',
		menu		: 'ટેબલ, કોઠાનું મથાળું',
		deleteTable	: 'કોઠો ડિલીટ/કાઢી નાખવું',
		rows		: 'પંક્તિના ખાના',
		columns		: 'કૉલમ/ઊભી કટાર',
		border		: 'કોઠાની બાજુ(બોર્ડર) સાઇઝ',
		widthPx		: 'પિકસલ',
		widthPc		: 'પ્રતિશત',
		widthUnit	: 'પોહાલાઈ એકમ',
		cellSpace	: 'સેલ અંતર',
		cellPad		: 'સેલ પૅડિંગ',
		caption		: 'મથાળું/કૅપ્શન ',
		summary		: 'ટૂંકો એહેવાલ',
		headers		: 'મથાળા',
		headersNone		: 'નથી ',
		headersColumn	: 'પહેલી ઊભી કટાર',
		headersRow		: 'પહેલી  કટાર',
		headersBoth		: 'બેવું',
		invalidRows		: 'આડી કટાર, 0 કરતા વધારે હોવી જોઈએ.',
		invalidCols		: 'ઉભી કટાર, 0 કરતા વધારે હોવી જોઈએ.',
		invalidBorder	: 'બોર્ડર એક આંકડો હોવો જોઈએ',
		invalidWidth	: 'ટેબલની પોહલાઈ આંકડો હોવો જોઈએ.',
		invalidHeight	: 'ટેબલની ઊંચાઈ આંકડો હોવો જોઈએ.',
		invalidCellSpacing	: 'સેલ વચ્ચેની જગ્યા સુન્ય કરતા વધારે હોવી જોઈએ.',
		invalidCellPadding	: 'સેલની અંદરની જગ્યા સુન્ય કરતા વધારે હોવી જોઈએ.',

		cell :
		{
			menu			: 'કોષના ખાના',
			insertBefore	: 'પહેલાં કોષ ઉમેરવો',
			insertAfter		: 'પછી કોષ ઉમેરવો',
			deleteCell		: 'કોષ ડિલીટ/કાઢી નાખવો',
			merge			: 'કોષ ભેગા કરવા',
			mergeRight		: 'જમણી બાજુ ભેગા કરવા',
			mergeDown		: 'નીચે ભેગા કરવા',
			splitHorizontal	: 'કોષને સમસ્તરીય વિભાજન કરવું',
			splitVertical	: 'કોષને સીધું ને ઊભું વિભાજન કરવું',
			title			: 'સેલના ગુણ',
			cellType		: 'સેલનો પ્રકાર',
			rowSpan			: 'આડી કટારની જગ્યા',
			colSpan			: 'ઊભી કતારની જગ્યા',
			wordWrap		: 'વર્ડ રેપ',
			hAlign			: 'સપાટ લાઈનદોરી',
			vAlign			: 'ઊભી લાઈનદોરી',
			alignBaseline	: 'બસે લાઈન',
			bgColor			: 'પાછાળનો રંગ',
			borderColor		: 'બોર્ડેર રંગ',
			data			: 'સ્વીકૃત માહિતી',
			header			: 'મથાળું',
			yes				: 'હા',
			no				: 'ના',
			invalidWidth	: 'સેલની પોહલાઈ આંકડો હોવો જોઈએ.',
			invalidHeight	: 'સેલની ઊંચાઈ આંકડો હોવો જોઈએ.',
			invalidRowSpan	: 'રો સ્પાન આંકડો હોવો જોઈએ.',
			invalidColSpan	: 'કોલમ સ્પાન આંકડો હોવો જોઈએ.',
			chooseColor		: 'પસંદ કરવું'
		},

		row :
		{
			menu			: 'પંક્તિના ખાના',
			insertBefore	: 'પહેલાં પંક્તિ ઉમેરવી',
			insertAfter		: 'પછી પંક્તિ ઉમેરવી',
			deleteRow		: 'પંક્તિઓ ડિલીટ/કાઢી નાખવી'
		},

		column :
		{
			menu			: 'કૉલમ/ઊભી કટાર',
			insertBefore	: 'પહેલાં કૉલમ/ઊભી કટાર ઉમેરવી',
			insertAfter		: 'પછી કૉલમ/ઊભી કટાર ઉમેરવી',
			deleteColumn	: 'કૉલમ/ઊભી કટાર ડિલીટ/કાઢી નાખવી'
		}
	},

	// Button Dialog.
	button :
	{
		title		: 'બટનના ગુણ',
		text		: 'ટેક્સ્ટ (વૅલ્યૂ)',
		type		: 'પ્રકાર',
		typeBtn		: 'બટન',
		typeSbm		: 'સબ્મિટ',
		typeRst		: 'રિસેટ'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : 'ચેક બોક્સ ગુણ',
		radioTitle	: 'રેડિઓ બટનના ગુણ',
		value		: 'વૅલ્યૂ',
		selected	: 'સિલેક્ટેડ'
	},

	// Form Dialog.
	form :
	{
		title		: 'ફૉર્મ/પત્રકના ગુણ',
		menu		: 'ફૉર્મ/પત્રકના ગુણ',
		action		: 'ક્રિયા',
		method		: 'પદ્ધતિ',
		encoding	: 'અન્કોડીન્ગ'
	},

	// Select Field Dialog.
	select :
	{
		title		: 'પસંદગી ક્ષેત્રના ગુણ',
		selectInfo	: 'સૂચના',
		opAvail		: 'ઉપલબ્ધ વિકલ્પ',
		value		: 'વૅલ્યૂ',
		size		: 'સાઇઝ',
		lines		: 'લીટીઓ',
		chkMulti	: 'એકથી વધારે પસંદ કરી શકો',
		opText		: 'ટેક્સ્ટ',
		opValue		: 'વૅલ્યૂ',
		btnAdd		: 'ઉમેરવું',
		btnModify	: 'બદલવું',
		btnUp		: 'ઉપર',
		btnDown		: 'નીચે',
		btnSetValue : 'પસંદ કરલી વૅલ્યૂ સેટ કરો',
		btnDelete	: 'રદ કરવું'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: 'ટેક્સ્ટ એઅરિઆ, શબ્દ વિસ્તારના ગુણ',
		cols		: 'કૉલમ/ઊભી કટાર',
		rows		: 'પંક્તિઓ'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: 'ટેક્સ્ટ ફીલ્ડ, શબ્દ ક્ષેત્રના ગુણ',
		name		: 'નામ',
		value		: 'વૅલ્યૂ',
		charWidth	: 'કેરેક્ટરની પહોળાઈ',
		maxChars	: 'અધિકતમ કેરેક્ટર',
		type		: 'ટાઇપ',
		typeText	: 'ટેક્સ્ટ',
		typePass	: 'પાસવર્ડ'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: 'ગુપ્ત ક્ષેત્રના ગુણ',
		name	: 'નામ',
		value	: 'વૅલ્યૂ'
	},

	// Image Dialog.
	image :
	{
		title		: 'ચિત્રના ગુણ',
		titleButton	: 'ચિત્ર બટનના ગુણ',
		menu		: 'ચિત્રના ગુણ',
		infoTab		: 'ચિત્ર ની જાણકારી',
		btnUpload	: 'આ સર્વરને મોકલવું',
		upload		: 'અપલોડ',
		alt			: 'ઑલ્ટર્નટ ટેક્સ્ટ',
		lockRatio	: 'લૉક ગુણોત્તર',
		resetSize	: 'રીસેટ સાઇઝ',
		border		: 'બોર્ડર',
		hSpace		: 'સમસ્તરીય જગ્યા',
		vSpace		: 'લંબરૂપ જગ્યા',
		alertUrl	: 'ચિત્રની URL ટાઇપ કરો',
		linkTab		: 'લિંક',
		button2Img	: 'તમારે ઈમેજ બટનને સાદી ઈમેજમાં બદલવું છે.',
		img2Button	: 'તમારે સાદી ઈમેજને ઈમેજ બટનમાં બદલવું છે.',
		urlMissing	: 'ઈમેજની મૂળ URL છે નહી.',
		validateBorder	: 'બોર્ડેર આંકડો હોવો જોઈએ.',
		validateHSpace	: 'HSpaceઆંકડો હોવો જોઈએ.',
		validateVSpace	: 'VSpace આંકડો હોવો જોઈએ. '
	},

	// Flash Dialog
	flash :
	{
		properties		: 'ફ્લૅશના ગુણ',
		propertiesTab	: 'ગુણ',
		title			: 'ફ્લૅશ ગુણ',
		chkPlay			: 'ઑટો/સ્વયં પ્લે',
		chkLoop			: 'લૂપ',
		chkMenu			: 'ફ્લૅશ મેન્યૂ નો પ્રયોગ કરો',
		chkFull			: 'ફૂલ સ્ક્રીન કરવું',
 		scale			: 'સ્કેલ',
		scaleAll		: 'સ્કેલ ઓલ/બધુ બતાવો',
		scaleNoBorder	: 'સ્કેલ બોર્ડર વગર',
		scaleFit		: 'સ્કેલ એકદમ ફીટ',
		access			: 'સ્ક્રીપ્ટ એક્સેસ',
		accessAlways	: 'હમેશાં',
		accessSameDomain: 'એજ ડોમેન',
		accessNever		: 'નહી',
		alignAbsBottom	: 'Abs નીચે',
		alignAbsMiddle	: 'Abs ઉપર',
		alignBaseline	: 'આધાર લીટી',
		alignTextTop	: 'ટેક્સ્ટ ઉપર',
		quality			: 'ગુણધર્મ',
		qualityBest		: 'શ્રેષ્ઠ',
		qualityHigh		: 'ઊંચું',
		qualityAutoHigh	: 'ઓટો ઊંચું',
		qualityMedium	: 'મધ્યમ',
		qualityAutoLow	: 'ઓટો નીચું',
		qualityLow		: 'નીચું',
		windowModeWindow: 'વિન્ડો',
		windowModeOpaque: 'અપારદર્શક',
		windowModeTransparent : 'પારદર્શક',
		windowMode		: 'વિન્ડો મોડ',
		flashvars		: 'ફલેશ ના વિકલ્પો',
		bgcolor			: 'બૅકગ્રાઉન્ડ રંગ,',
		hSpace			: 'સમસ્તરીય જગ્યા',
		vSpace			: 'લંબરૂપ જગ્યા',
		validateSrc		: 'લિંક  URL ટાઇપ કરો',
		validateHSpace	: 'HSpace આંકડો હોવો જોઈએ.',
		validateVSpace	: 'VSpace આંકડો હોવો જોઈએ.'
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: 'જોડણી (સ્પેલિંગ) તપાસવી',
		title			: 'સ્પેલ ',
		notAvailable	: 'માફ કરશો, આ સુવિધા ઉપલબ્ધ નથી',
		errorLoading	: 'સર્વિસ એપ્લીકેશન લોડ નથી થ: %s.',
		notInDic		: 'શબ્દકોશમાં નથી',
		changeTo		: 'આનાથી બદલવું',
		btnIgnore		: 'ઇગ્નોર/અવગણના કરવી',
		btnIgnoreAll	: 'બધાની ઇગ્નોર/અવગણના કરવી',
		btnReplace		: 'બદલવું',
		btnReplaceAll	: 'બધા બદલી કરો',
		btnUndo			: 'અન્ડૂ',
		noSuggestions	: '- કઇ સજેશન નથી -',
		progress		: 'શબ્દની જોડણી/સ્પેલ ચેક ચાલુ છે...',
		noMispell		: 'શબ્દની જોડણી/સ્પેલ ચેક પૂર્ણ: ખોટી જોડણી મળી નથી',
		noChanges		: 'શબ્દની જોડણી/સ્પેલ ચેક પૂર્ણ: એકપણ શબ્દ બદલયો નથી',
		oneChange		: 'શબ્દની જોડણી/સ્પેલ ચેક પૂર્ણ: એક શબ્દ બદલયો છે',
		manyChanges		: 'શબ્દની જોડણી/સ્પેલ ચેક પૂર્ણ: %1 શબ્દ બદલયા છે',
		ieSpellDownload	: 'સ્પેલ-ચેકર ઇન્સ્ટોલ નથી. શું તમે ડાઉનલોડ કરવા માંગો છો?'
	},

	smiley :
	{
		toolbar	: 'સ્માઇલી',
		title	: 'સ્માઇલી  પસંદ કરો',
		options : 'સમ્ય્લી વિકલ્પો'
	},

	elementsPath :
	{
		eleLabel : 'એલીમેન્ટ્સ નો ',
		eleTitle : 'એલીમેન્ટ %1'
	},

	numberedlist	: 'સંખ્યાંકન સૂચિ',
	bulletedlist	: 'બુલેટ સૂચિ',
	indent			: 'ઇન્ડેન્ટ, લીટીના આરંભમાં જગ્યા વધારવી',
	outdent			: 'ઇન્ડેન્ટ લીટીના આરંભમાં જગ્યા ઘટાડવી',

	justify :
	{
		left	: 'ડાબી બાજુએ/બાજુ તરફ',
		center	: 'સંકેંદ્રણ/સેંટરિંગ',
		right	: 'જમણી બાજુએ/બાજુ તરફ',
		block	: 'બ્લૉક, અંતરાય જસ્ટિફાઇ'
	},

	blockquote : 'બ્લૉક-કોટ, અવતરણચિહ્નો',

	clipboard :
	{
		title		: 'પેસ્ટ',
		cutError	: 'તમારા બ્રાઉઝર ની સુરક્ષિત સેટિંગસ કટ કરવાની પરવાનગી નથી આપતી. (Ctrl/Cmd+X) નો ઉપયોગ કરો.',
		copyError	: 'તમારા બ્રાઉઝર ની સુરક્ષિત સેટિંગસ કોપી કરવાની પરવાનગી નથી આપતી.  (Ctrl/Cmd+C) का प्रयोग करें।',
		pasteMsg	: 'Ctrl/Cmd+V નો પ્રયોગ કરી પેસ્ટ કરો',
		securityMsg	: 'તમારા બ્રાઉઝર ની સુરક્ષિત સેટિંગસના કારણે,એડિટર તમારા કિલ્પબોર્ડ ડેટા ને કોપી નથી કરી શકતો. તમારે આ વિન્ડોમાં ફરીથી પેસ્ટ કરવું પડશે.',
		pasteArea	: 'પેસ્ટ કરવાની જગ્યા'
	},

	pastefromword :
	{
		confirmCleanup	: 'તમે જે ટેક્ષ્ત્ કોપી કરી રહ્યા છો ટે વર્ડ ની છે. કોપી કરતા પેહલા સાફ કરવી છે?',
		toolbar			: 'પેસ્ટ (વડૅ ટેક્સ્ટ)',
		title			: 'પેસ્ટ (વડૅ ટેક્સ્ટ)',
		error			: 'પેસ્ટ કરેલો ડેટા ઇન્ટરનલ એરર ના લીથે સાફ કરી શકાયો નથી.'
	},

	pasteText :
	{
		button	: 'પેસ્ટ (ટેક્સ્ટ)',
		title	: 'પેસ્ટ (ટેક્સ્ટ)'
	},

	templates :
	{
		button			: 'ટેમ્પ્લેટ',
		title			: 'કન્ટેન્ટ ટેમ્પ્લેટ',
		options : 'ટેમ્પ્લેટના વિકલ્પો',
		insertOption	: 'મૂળ શબ્દને બદલો',
		selectPromptMsg	: 'એડિટરમાં ઓપન કરવા ટેમ્પ્લેટ પસંદ કરો (વર્તમાન કન્ટેન્ટ સેવ નહીં થાય):',
		emptyListMsg	: '(કોઈ ટેમ્પ્લેટ ડિફાઇન નથી)'
	},

	showBlocks : 'બ્લૉક બતાવવું',

	stylesCombo :
	{
		label		: 'શૈલી/રીત',
		panelTitle	: 'ફોર્મેટ ',
		panelTitle1	: 'બ્લોક ',
		panelTitle2	: 'ઈનલાઈન ',
		panelTitle3	: 'ઓબ્જેક્ટ પદ્ધતિ'
	},

	format :
	{
		label		: 'ફૉન્ટ ફૉર્મટ, રચનાની શૈલી',
		panelTitle	: 'ફૉન્ટ ફૉર્મટ, રચનાની શૈલી',

		tag_p		: 'સામાન્ય',
		tag_pre		: 'ફૉર્મટેડ',
		tag_address	: 'સરનામું',
		tag_h1		: 'શીર્ષક 1',
		tag_h2		: 'શીર્ષક 2',
		tag_h3		: 'શીર્ષક 3',
		tag_h4		: 'શીર્ષક 4',
		tag_h5		: 'શીર્ષક 5',
		tag_h6		: 'શીર્ષક 6',
		tag_div		: 'શીર્ષક (DIV)'
	},

	div :
	{
		title				: 'Div કન્ટેનર બનાવુંવું',
		toolbar				: 'Div કન્ટેનર બનાવુંવું',
		cssClassInputLabel	: 'સ્ટાઈલશીટ કલાસીસ',
		styleSelectLabel	: 'સ્ટાઈલ',
		IdInputLabel		: 'Id',
		languageCodeInputLabel	: 'ભાષાનો કોડ',
		inlineStyleInputLabel	: 'ઈનલાઈન પદ્ધતિ',
		advisoryTitleInputLabel	: 'એડવાઈઝર શીર્ષક',
		langDirLabel		: 'ભાષાની દિશા',
		langDirLTRLabel		: 'ડાબે થી જમણે (LTR)',
		langDirRTLLabel		: 'જમણે થી ડાબે (RTL)',
		edit				: 'ડીવીમાં ફેરફાર કરવો',
		remove				: 'ડીવી કાઢી કાઢવું'
  	},

	iframe :
	{
		title		: 'IFrame વિકલ્પો',
		toolbar		: 'IFrame',
		noUrl		: 'iframe URL ટાઈપ્ કરો',
		scrolling	: 'સ્ક્રોલબાર ચાલુ કરવા',
		border		: 'ફ્રેમ બોર્ડેર બતાવવી'
	},

	font :
	{
		label		: 'ફૉન્ટ',
		voiceLabel	: 'ફોન્ટ',
		panelTitle	: 'ફૉન્ટ'
	},

	fontSize :
	{
		label		: 'ફૉન્ટ સાઇઝ/કદ',
		voiceLabel	: 'ફોન્ટ સાઈઝ',
		panelTitle	: 'ફૉન્ટ સાઇઝ/કદ'
	},

	colorButton :
	{
		textColorTitle	: 'શબ્દનો રંગ',
		bgColorTitle	: 'બૅકગ્રાઉન્ડ રંગ,',
		panelTitle		: 'રંગ',
		auto			: 'સ્વચાલિત',
		more			: 'ઔર રંગ...'
	},

	colors :
	{
		'000' : 'કાળો',
		'800000' : 'મરુન',
		'8B4513' : 'છીક',
		'2F4F4F' : 'ડાર્ક સ્લેટ ગ્રે ',
		'008080' : 'ટીલ',
		'000080' : 'નેવી',
		'4B0082' : 'જામલી',
		'696969' : 'ડાર્ક ગ્રે',
		'B22222' : 'ઈટ',
		'A52A2A' : 'બ્રાઉન',
		'DAA520' : 'ગોલ્ડન રોડ',
		'006400' : 'ડાર્ક લીલો',
		'40E0D0' : 'ટ્રકોઈસ',
		'0000CD' : 'મધ્યમ વાદળી',
		'800080' : 'પર્પલ',
		'808080' : 'ગ્રે',
		'F00' : 'લાલ',
		'FF8C00' : 'ડાર્ક ઓરંજ',
		'FFD700' : 'ગોલ્ડ',
		'008000' : 'ગ્રીન',
		'0FF' : 'સાયન',
		'00F' : 'વાદળી',
		'EE82EE' : 'વાયોલેટ',
		'A9A9A9' : 'ડીમ ',
		'FFA07A' : 'લાઈટ સાલમન',
		'FFA500' : 'ઓરંજ',
		'FFFF00' : 'પીળો',
		'00FF00' : 'લાઈમ',
		'AFEEEE' : 'પેલ કોઈસ',
		'ADD8E6' : 'લાઈટ બ્લુ',
		'DDA0DD' : 'પલ્મ',
		'D3D3D3' : 'લાઈટ ગ્રે',
		'FFF0F5' : 'લવંડર ',
		'FAEBD7' : 'એન્ટીક સફેદ',
		'FFFFE0' : 'લાઈટ પીળો',
		'F0FFF0' : 'હનીડઉય',
		'F0FFFF' : 'અઝુરે',
		'F0F8FF' : 'એલીસ બ્લુ',
		'E6E6FA' : 'લવંડર',
		'FFF' : 'સફેદ'
	},

	scayt :
	{
		title			: 'ટાઈપ કરતા સ્પેલ તપાસો',
		opera_title		: 'ઓપેરામાં સપોર્ટ નથી',
		enable			: 'SCAYT એનેબલ કરવું',
		disable			: 'SCAYT ડિસેબલ કરવું',
		about			: 'SCAYT વિષે',
		toggle			: 'SCAYT ટોગલ',
		options			: 'વિકલ્પો',
		langs			: 'ભાષાઓ',
		moreSuggestions	: 'વધારે વિકલ્પો',
		ignore			: 'ઇગ્નોર',
		ignoreAll		: 'બધા ઇગ્નોર ',
		addWord			: 'શબ્દ ઉમેરવો',
		emptyDic		: 'ડિક્સનરીનું નામ ખાલી ના હોય.',

		optionsTab		: 'વિકલ્પો',
		allCaps			: 'ઓલ-કેપ્સ વર્ડ છોડી દો.',
		ignoreDomainNames : 'ડોમેન નામ છોડી દો.',
		mixedCase		: 'મિક્સ કેસ વર્ડ છોડી દો.',
		mixedWithDigits	: 'આંકડા વાળા શબ્દ છોડી દો.',

		languagesTab	: 'ભાષા',

		dictionariesTab	: 'શબ્દકોશ',
		dic_field_name	: 'શબ્દકોશ નામ',
		dic_create		: 'બનાવવું',
		dic_restore		: 'પાછું ',
		dic_delete		: 'કાઢી નાખવું',
		dic_rename		: 'નવું નામ આપવું',
		dic_info		: 'પેહલા User Dictionary, Cookie તરીકે સ્ટોર થાય છે. પણ Cookie ની સમતા ઓછી છે. જયારે User Dictionary, Cookie તરીકે સ્ટોર ના કરી શકાય, ત્યારે તે અમારા સર્વર પર સ્ટોર થાય છે. તમારી વ્યતિગત ડીકસ્નરી ને સર્વર પર સ્ટોર કરવા માટે તમારે તેનું નામ આપવું પડશે. જો તમે તમારી ડીકસ્નરી નું નામ આપેલું હોય તો તમે રિસ્ટોર બટન ક્લીક કરી શકો.',

		aboutTab		: 'વિષે'
	},

	about :
	{
		title		: 'CKEditor વિષે',
		dlgTitle	: 'CKEditor વિષે',
		help	: 'મદદ માટે $1 તપાસો',
		userGuide : 'CKEditor યુઝર્સ ગાઈડ',
		moreInfo	: 'લાયસનસની માહિતી માટે અમારી વેબ સાઈટ',
		copy		: 'કોપીરાઈટ &copy; $1. ઓલ રાઈટ્સ '
	},

	maximize : 'મોટું કરવું',
	minimize : 'નાનું કરવું',

	fakeobjects :
	{
		anchor		: 'અનકર',
		flash		: 'ફ્લેશ ',
		iframe		: 'IFrame',
		hiddenfield	: 'હિડન ',
		unknown		: 'અનનોન ઓબ્જેક્ટ'
	},

	resize : 'ખેંચી ને યોગ્ય કરવું',

	colordialog :
	{
		title		: 'રંગ પસંદ કરો',
		options	:	'રંગના વિકલ્પ',
		highlight	: 'હાઈઈટ',
		selected	: 'પસંદ કરેલો રંગ',
		clear		: 'સાફ કરવું'
	},

	toolbarCollapse	: 'ટૂલબાર નાનું કરવું',
	toolbarExpand	: 'ટૂલબાર મોટું કરવું',

	toolbarGroups :
	{
		document : 'દસ્તાવેજ',
		clipboard : 'ક્લિપબોર્ડ/અન',
		editing : 'એડીટ કરવું',
		forms : 'ફોર્મ',
		basicstyles : 'બેસિક્ સ્ટાઇલ',
		paragraph : 'ફકરો',
		links : 'લીંક',
		insert : 'ઉમેરવું',
		styles : 'સ્ટાઇલ',
		colors : 'રંગ',
		tools : 'ટૂલ્સ'
	},

	bidi :
	{
		ltr : 'ટેક્ષ્ત્ ની દિશા ડાબે થી જમણે',
		rtl : 'ટેક્ષ્ત્ ની દિશા જમણે થી ડાબે'
	},

	docprops :
	{
		label : 'ડૉક્યુમન્ટ ગુણ/પ્રૉપર્ટિઝ',
		title : 'ડૉક્યુમન્ટ ગુણ/પ્રૉપર્ટિઝ',
		design : 'ડીસા',
		meta : 'મેટાડૅટા',
		chooseColor : 'વિકલ્પ',
		other : '<other>',
		docTitle :	'પેજ મથાળું/ટાઇટલ',
		charset : 	'કેરેક્ટર સેટ એન્કોડિંગ',
		charsetOther : 'અન્ય કેરેક્ટર સેટ એન્કોડિંગ',
		charsetASCII : 'ASCII',
		charsetCE : 'મધ્ય યુરોપિઅન (Central European)',
		charsetCT : 'ચાઇનીઝ (Chinese Traditional Big5)',
		charsetCR : 'સિરીલિક (Cyrillic)',
		charsetGR : 'ગ્રીક (Greek)',
		charsetJP : 'જાપાનિઝ (Japanese)',
		charsetKR : 'કોરીયન (Korean)',
		charsetTR : 'ટર્કિ (Turkish)',
		charsetUN : 'યૂનિકોડ (UTF-8)',
		charsetWE : 'પશ્ચિમ યુરોપિઅન (Western European)',
		docType : 'ડૉક્યુમન્ટ પ્રકાર શીર્ષક',
		docTypeOther : 'અન્ય ડૉક્યુમન્ટ પ્રકાર શીર્ષક',
		xhtmlDec : 'XHTML સૂચના સમાવિષ્ટ કરવી',
		bgColor : 'બૅકગ્રાઉન્ડ રંગ',
		bgImage : 'બૅકગ્રાઉન્ડ ચિત્ર URL',
		bgFixed : 'સ્ક્રોલ ન થાય તેવું બૅકગ્રાઉન્ડ',
		txtColor : 'શબ્દનો રંગ',
		margin : 'પેજ માર્જિન',
		marginTop : 'ઉપર',
		marginLeft : 'ડાબી',
		marginRight : 'જમણી',
		marginBottom : 'નીચે',
		metaKeywords : 'ડૉક્યુમન્ટ ઇન્ડેક્સ સંકેતશબ્દ (અલ્પવિરામ (,) થી અલગ કરો)',
		metaDescription : 'ડૉક્યુમન્ટ વર્ણન',
		metaAuthor : 'લેખક',
		metaCopyright : 'કૉપિરાઇટ',
		previewHtml : '<p>આ એક <strong>સેમ્પલ ટેક્ષ્ત્</strong> છે. તમે <a href="javascript:void(0)">CKEditor</a> વાપરો છો.</p>'
	}
};
