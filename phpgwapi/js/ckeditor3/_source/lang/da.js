﻿/*
Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

/**
 * @fileOverview Defines the {@link CKEDITOR.lang} object, for the
 * Danish language.
 */

/**#@+
   @type String
   @example
*/

/**
 * Contains the dictionary of language entries.
 * @namespace
 */
CKEDITOR.lang['da'] =
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
	toolbars	: 'Editors værktøjslinjer',
	editor		: 'Rich Text Editor',

	// Toolbar buttons without dialogs.
	source			: 'Kilde',
	newPage			: 'Ny side',
	save			: 'Gem',
	preview			: 'Vis eksempel',
	cut				: 'Klip',
	copy			: 'Kopiér',
	paste			: 'Indsæt',
	print			: 'Udskriv',
	underline		: 'Understreget',
	bold			: 'Fed',
	italic			: 'Kursiv',
	selectAll		: 'Vælg alt',
	removeFormat	: 'Fjern formatering',
	strike			: 'Gennemstreget',
	subscript		: 'Sænket skrift',
	superscript		: 'Hævet skrift',
	horizontalrule	: 'Indsæt vandret streg',
	pagebreak		: 'Indsæt sideskift',
	pagebreakAlt		: 'Sideskift',
	unlink			: 'Fjern hyperlink',
	undo			: 'Fortryd',
	redo			: 'Annullér fortryd',

	// Common messages and labels.
	common :
	{
		browseServer	: 'Gennemse...',
		url				: 'URL',
		protocol		: 'Protokol',
		upload			: 'Upload',
		uploadSubmit	: 'Upload',
		image			: 'Indsæt billede',
		flash			: 'Indsæt Flash',
		form			: 'Indsæt formular',
		checkbox		: 'Indsæt afkrydsningsfelt',
		radio			: 'Indsæt alternativknap',
		textField		: 'Indsæt tekstfelt',
		textarea		: 'Indsæt tekstboks',
		hiddenField		: 'Indsæt skjult felt',
		button			: 'Indsæt knap',
		select			: 'Indsæt liste',
		imageButton		: 'Indsæt billedknap',
		notSet			: '<intet valgt>',
		id				: 'Id',
		name			: 'Navn',
		langDir			: 'Tekstretning',
		langDirLtr		: 'Fra venstre mod højre (LTR)',
		langDirRtl		: 'Fra højre mod venstre (RTL)',
		langCode		: 'Sprogkode',
		longDescr		: 'Udvidet beskrivelse',
		cssClass		: 'Typografiark (CSS)',
		advisoryTitle	: 'Titel',
		cssStyle		: 'Typografi (CSS)',
		ok				: 'OK',
		cancel			: 'Annullér',
		close			: 'Luk',
		preview			: 'Forhåndsvisning',
		generalTab		: 'Generelt',
		advancedTab		: 'Avanceret',
		validateNumberFailed : 'Værdien er ikke et tal.',
		confirmNewPage	: 'Alt indhold, der ikke er blevet gemt, vil gå tabt. Er du sikker på, at du vil indlæse en ny side?',
		confirmCancel	: 'Nogle af indstillingerne er blevet ændret. Er du sikker på, at du vil lukke vinduet?',
		options			: 'Vis muligheder',
		target			: 'Mål',
		targetNew		: 'Nyt vindue (_blank)',
		targetTop		: 'Øverste vindue (_top)',
		targetSelf		: 'Samme vindue (_self)',
		targetParent	: 'Samme vindue (_parent)',
		langDirLTR		: 'Venstre til højre (LTR)',
		langDirRTL		: 'Højre til venstre (RTL)',
		styles			: 'Style',
		cssClasses		: 'Stylesheetklasser',
		width			: 'Bredde',
		height			: 'Højde',
		align			: 'Justering',
		alignLeft		: 'Venstre',
		alignRight		: 'Højre',
		alignCenter		: 'Centreret',
		alignTop		: 'Øverst',
		alignMiddle		: 'Centreret',
		alignBottom		: 'Nederst',
		invalidHeight	: 'Højde skal være et tal.',
		invalidWidth	: 'Bredde skal være et tal.',
		invalidCssLength	: 'Værdien specificeret for "%1" feltet skal være et positivt nummer med eller uden en CSS måleenhed  (px, %, in, cm, mm, em, ex, pt, eller pc).',
		invalidHtmlLength	: 'Værdien specificeret for "%1" feltet skal være et positivt nummer med eller uden en CSS måleenhed  (px eller %).',
		invalidInlineStyle	: 'Værdien specificeret for inline style skal indeholde en eller flere elementer med et format som "name:value", separeret af semikoloner',
		cssLengthTooltip	: 'Indsæt en numerisk værdi i pixel eller nummer med en gyldig CSS værdi (px, %, in, cm, mm, em, ex, pt, or pc).',

		// Put the voice-only part of the label in the span.
		unavailable		: '%1<span class="cke_accessibility">, ikke tilgængelig</span>'
	},

	contextmenu :
	{
		options : 'Muligheder for hjælpemenu'
	},

	// Special char dialog.
	specialChar		:
	{
		toolbar		: 'Indsæt symbol',
		title		: 'Vælg symbol',
		options : 'Muligheder for specialkarakterer'
	},

	// Link dialog.
	link :
	{
		toolbar		: 'Indsæt/redigér hyperlink',
		other 		: '<anden>',
		menu		: 'Redigér hyperlink',
		title		: 'Egenskaber for hyperlink',
		info		: 'Generelt',
		target		: 'Mål',
		upload		: 'Upload',
		advanced	: 'Avanceret',
		type		: 'Type',
		toUrl		: 'URL',
		toAnchor	: 'Bogmærke på denne side',
		toEmail		: 'E-mail',
		targetFrame		: '<ramme>',
		targetPopup		: '<popup vindue>',
		targetFrameName	: 'Destinationsvinduets navn',
		targetPopupName	: 'Popupvinduets navn',
		popupFeatures	: 'Egenskaber for popup',
		popupResizable	: 'Justérbar',
		popupStatusBar	: 'Statuslinje',
		popupLocationBar: 'Adresselinje',
		popupToolbar	: 'Værktøjslinje',
		popupMenuBar	: 'Menulinje',
		popupFullScreen	: 'Fuld skærm (IE)',
		popupScrollBars	: 'Scrollbar',
		popupDependent	: 'Koblet/dependent (Netscape)',
		popupLeft		: 'Position fra venstre',
		popupTop		: 'Position fra toppen',
		id				: 'Id',
		langDir			: 'Tekstretning',
		langDirLTR		: 'Fra venstre mod højre (LTR)',
		langDirRTL		: 'Fra højre mod venstre (RTL)',
		acccessKey		: 'Genvejstast',
		name			: 'Navn',
		langCode			: 'Tekstretning',
		tabIndex			: 'Tabulatorindeks',
		advisoryTitle		: 'Titel',
		advisoryContentType	: 'Indholdstype',
		cssClasses		: 'Typografiark',
		charset			: 'Tegnsæt',
		styles			: 'Typografi',
		rel			: 'Relation',
		selectAnchor		: 'Vælg et anker',
		anchorName		: 'Efter ankernavn',
		anchorId			: 'Efter element-Id',
		emailAddress		: 'E-mailadresse',
		emailSubject		: 'Emne',
		emailBody		: 'Besked',
		noAnchors		: '(Ingen bogmærker i dokumentet)',
		noUrl			: 'Indtast hyperlink-URL!',
		noEmail			: 'Indtast e-mailadresse!'
	},

	// Anchor dialog
	anchor :
	{
		toolbar		: 'Indsæt/redigér bogmærke',
		menu		: 'Egenskaber for bogmærke',
		title		: 'Egenskaber for bogmærke',
		name		: 'Bogmærkenavn',
		errorName	: 'Indtast bogmærkenavn',
		remove		: 'Fjern bogmærke'
	},

	// List style dialog
	list:
	{
		numberedTitle		: 'Egenskaber for nummereret liste',
		bulletedTitle		: 'Værdier for cirkelpunktopstilling',
		type				: 'Type',
		start				: 'Start',
		validateStartNumber				:'Den nummererede liste skal starte med et rundt nummer',
		circle				: 'Cirkel',
		disc				: 'Værdier for diskpunktopstilling',
		square				: 'Firkant',
		none				: 'Ingen',
		notset				: '<ikke defineret>',
		armenian			: 'Armensk nummering',
		georgian			: 'Georgiansk nummering (an, ban, gan, etc.)',
		lowerRoman			: 'Små romerske (i, ii, iii, iv, v, etc.)',
		upperRoman			: 'Store romerske (I, II, III, IV, V, etc.)',
		lowerAlpha			: 'Små alfabet (a, b, c, d, e, etc.)',
		upperAlpha			: 'Store alfabet (A, B, C, D, E, etc.)',
		lowerGreek			: 'Små græsk (alpha, beta, gamma, etc.)',
		decimal				: 'Decimal (1, 2, 3, osv.)',
		decimalLeadingZero	: 'Decimaler med 0 først (01, 02, 03, etc.)'
	},

	// Find And Replace Dialog
	findAndReplace :
	{
		title				: 'Søg og erstat',
		find				: 'Søg',
		replace				: 'Erstat',
		findWhat			: 'Søg efter:',
		replaceWith			: 'Erstat med:',
		notFoundMsg			: 'Søgeteksten blev ikke fundet',
		findOptions			: 'Find muligheder',
		matchCase			: 'Forskel på store og små bogstaver',
		matchWord			: 'Kun hele ord',
		matchCyclic			: 'Match cyklisk',
		replaceAll			: 'Erstat alle',
		replaceSuccessMsg	: '%1 forekomst(er) erstattet.'
	},

	// Table Dialog
	table :
	{
		toolbar		: 'Tabel',
		title		: 'Egenskaber for tabel',
		menu		: 'Egenskaber for tabel',
		deleteTable	: 'Slet tabel',
		rows		: 'Rækker',
		columns		: 'Kolonner',
		border		: 'Rammebredde',
		widthPx		: 'pixels',
		widthPc		: 'procent',
		widthUnit	: 'Bredde på enhed',
		cellSpace	: 'Celleafstand',
		cellPad		: 'Cellemargen',
		caption		: 'Titel',
		summary		: 'Resumé',
		headers		: 'Hoved',
		headersNone		: 'Ingen',
		headersColumn	: 'Første kolonne',
		headersRow		: 'Første række',
		headersBoth		: 'Begge',
		invalidRows		: 'Antallet af rækker skal være større end 0.',
		invalidCols		: 'Antallet af kolonner skal være større end 0.',
		invalidBorder	: 'Rammetykkelse skal være et tal.',
		invalidWidth	: 'Tabelbredde skal være et tal.',
		invalidHeight	: 'Tabelhøjde skal være et tal.',
		invalidCellSpacing	: 'Celleafstand skal være et tal.',
		invalidCellPadding	: 'Cellemargen skal være et tal.',

		cell :
		{
			menu			: 'Celle',
			insertBefore	: 'Indsæt celle før',
			insertAfter		: 'Indsæt celle efter',
			deleteCell		: 'Slet celle',
			merge			: 'Flet celler',
			mergeRight		: 'Flet til højre',
			mergeDown		: 'Flet nedad',
			splitHorizontal	: 'Del celle vandret',
			splitVertical	: 'Del celle lodret',
			title			: 'Celleegenskaber',
			cellType		: 'Celletype',
			rowSpan			: 'Række span (rows span)',
			colSpan			: 'Kolonne span (columns span)',
			wordWrap		: 'Tekstombrydning',
			hAlign			: 'Vandret justering',
			vAlign			: 'Lodret justering',
			alignBaseline	: 'Grundlinje',
			bgColor			: 'Baggrundsfarve',
			borderColor		: 'Rammefarve',
			data			: 'Data',
			header			: 'Hoved',
			yes				: 'Ja',
			no				: 'Nej',
			invalidWidth	: 'Cellebredde skal være et tal.',
			invalidHeight	: 'Cellehøjde skal være et tal.',
			invalidRowSpan	: 'Række span skal være et heltal.',
			invalidColSpan	: 'Kolonne span skal være et heltal.',
			chooseColor		: 'Vælg'
		},

		row :
		{
			menu			: 'Række',
			insertBefore	: 'Indsæt række før',
			insertAfter		: 'Indsæt række efter',
			deleteRow		: 'Slet række'
		},

		column :
		{
			menu			: 'Kolonne',
			insertBefore	: 'Indsæt kolonne før',
			insertAfter		: 'Indsæt kolonne efter',
			deleteColumn	: 'Slet kolonne'
		}
	},

	// Button Dialog.
	button :
	{
		title		: 'Egenskaber for knap',
		text		: 'Tekst',
		type		: 'Type',
		typeBtn		: 'Knap',
		typeSbm		: 'Send',
		typeRst		: 'Nulstil'
	},

	// Checkbox and Radio Button Dialogs.
	checkboxAndRadio :
	{
		checkboxTitle : 'Egenskaber for afkrydsningsfelt',
		radioTitle	: 'Egenskaber for alternativknap',
		value		: 'Værdi',
		selected	: 'Valgt'
	},

	// Form Dialog.
	form :
	{
		title		: 'Egenskaber for formular',
		menu		: 'Egenskaber for formular',
		action		: 'Handling',
		method		: 'Metode',
		encoding	: 'Kodning (encoding)'
	},

	// Select Field Dialog.
	select :
	{
		title		: 'Egenskaber for liste',
		selectInfo	: 'Generelt',
		opAvail		: 'Valgmuligheder',
		value		: 'Værdi',
		size		: 'Størrelse',
		lines		: 'Linjer',
		chkMulti	: 'Tillad flere valg',
		opText		: 'Tekst',
		opValue		: 'Værdi',
		btnAdd		: 'Tilføj',
		btnModify	: 'Redigér',
		btnUp		: 'Op',
		btnDown		: 'Ned',
		btnSetValue : 'Sæt som valgt',
		btnDelete	: 'Slet'
	},

	// Textarea Dialog.
	textarea :
	{
		title		: 'Egenskaber for tekstboks',
		cols		: 'Kolonner',
		rows		: 'Rækker'
	},

	// Text Field Dialog.
	textfield :
	{
		title		: 'Egenskaber for tekstfelt',
		name		: 'Navn',
		value		: 'Værdi',
		charWidth	: 'Bredde (tegn)',
		maxChars	: 'Max. antal tegn',
		type		: 'Type',
		typeText	: 'Tekst',
		typePass	: 'Adgangskode'
	},

	// Hidden Field Dialog.
	hidden :
	{
		title	: 'Egenskaber for skjult felt',
		name	: 'Navn',
		value	: 'Værdi'
	},

	// Image Dialog.
	image :
	{
		title		: 'Egenskaber for billede',
		titleButton	: 'Egenskaber for billedknap',
		menu		: 'Egenskaber for billede',
		infoTab		: 'Generelt',
		btnUpload	: 'Upload fil til serveren',
		upload		: 'Upload',
		alt			: 'Alternativ tekst',
		lockRatio	: 'Lås størrelsesforhold',
		resetSize	: 'Nulstil størrelse',
		border		: 'Ramme',
		hSpace		: 'Vandret margen',
		vSpace		: 'Lodret margen',
		alertUrl	: 'Indtast stien til billedet',
		linkTab		: 'Hyperlink',
		button2Img	: 'Vil du lave billedknappen om til et almindeligt billede?',
		img2Button	: 'Vil du lave billedet om til en billedknap?',
		urlMissing	: 'Kilde på billed-URL mangler',
		validateBorder	: 'Kant skal være et helt nummer.',
		validateHSpace	: 'HSpace skal være et helt nummer.',
		validateVSpace	: 'VSpace skal være et helt nummer.'
	},

	// Flash Dialog
	flash :
	{
		properties		: 'Egenskaber for Flash',
		propertiesTab	: 'Egenskaber',
		title			: 'Egenskaber for Flash',
		chkPlay			: 'Automatisk afspilning',
		chkLoop			: 'Gentagelse',
		chkMenu			: 'Vis Flash-menu',
		chkFull			: 'Tillad fuldskærm',
 		scale			: 'Skalér',
		scaleAll		: 'Vis alt',
		scaleNoBorder	: 'Ingen ramme',
		scaleFit		: 'Tilpas størrelse',
		access			: 'Scriptadgang',
		accessAlways	: 'Altid',
		accessSameDomain: 'Samme domæne',
		accessNever		: 'Aldrig',
		alignAbsBottom	: 'Absolut nederst',
		alignAbsMiddle	: 'Absolut centreret',
		alignBaseline	: 'Grundlinje',
		alignTextTop	: 'Toppen af teksten',
		quality			: 'Kvalitet',
		qualityBest		: 'Bedste',
		qualityHigh		: 'Høj',
		qualityAutoHigh	: 'Auto høj',
		qualityMedium	: 'Medium',
		qualityAutoLow	: 'Auto lav',
		qualityLow		: 'Lav',
		windowModeWindow: 'Vindue',
		windowModeOpaque: 'Gennemsigtig (opaque)',
		windowModeTransparent : 'Transparent',
		windowMode		: 'Vinduestilstand',
		flashvars		: 'Variabler for Flash',
		bgcolor			: 'Baggrundsfarve',
		hSpace			: 'Vandret margen',
		vSpace			: 'Lodret margen',
		validateSrc		: 'Indtast hyperlink URL!',
		validateHSpace	: 'Vandret margen skal være et tal.',
		validateVSpace	: 'Lodret margen skal være et tal.'
	},

	// Speller Pages Dialog
	spellCheck :
	{
		toolbar			: 'Stavekontrol',
		title			: 'Stavekontrol',
		notAvailable	: 'Stavekontrol er desværre ikke tilgængelig.',
		errorLoading	: 'Fejl ved indlæsning af host: %s.',
		notInDic		: 'Ikke i ordbogen',
		changeTo		: 'Forslag',
		btnIgnore		: 'Ignorér',
		btnIgnoreAll	: 'Ignorér alle',
		btnReplace		: 'Erstat',
		btnReplaceAll	: 'Erstat alle',
		btnUndo			: 'Tilbage',
		noSuggestions	: '(ingen forslag)',
		progress		: 'Stavekontrollen arbejder...',
		noMispell		: 'Stavekontrol færdig: Ingen fejl fundet',
		noChanges		: 'Stavekontrol færdig: Ingen ord ændret',
		oneChange		: 'Stavekontrol færdig: Et ord ændret',
		manyChanges		: 'Stavekontrol færdig: %1 ord ændret',
		ieSpellDownload	: 'Stavekontrol ikke installeret. Vil du installere den nu?'
	},

	smiley :
	{
		toolbar	: 'Smiley',
		title	: 'Vælg smiley',
		options : 'Smileymuligheder'
	},

	elementsPath :
	{
		eleLabel : 'Sti på element',
		eleTitle : '%1 element'
	},

	numberedlist	: 'Talopstilling',
	bulletedlist	: 'Punktopstilling',
	indent			: 'Forøg indrykning',
	outdent			: 'Formindsk indrykning',

	justify :
	{
		left	: 'Venstrestillet',
		center	: 'Centreret',
		right	: 'Højrestillet',
		block	: 'Lige margener'
	},

	blockquote : 'Blokcitat',

	clipboard :
	{
		title		: 'Indsæt',
		cutError	: 'Din browsers sikkerhedsindstillinger tillader ikke editoren at få automatisk adgang til udklipsholderen.<br><br>Brug i stedet tastaturet til at klippe teksten (Ctrl/Cmd+X).',
		copyError	: 'Din browsers sikkerhedsindstillinger tillader ikke editoren at få automatisk adgang til udklipsholderen.<br><br>Brug i stedet tastaturet til at kopiere teksten (Ctrl/Cmd+C).',
		pasteMsg	: 'Indsæt i feltet herunder (<STRONG>Ctrl/Cmd+V</STRONG>) og klik på <STRONG>OK</STRONG>.',
		securityMsg	: 'Din browsers sikkerhedsindstillinger tillader ikke editoren at få automatisk adgang til udklipsholderen.<br><br>Du skal indsætte udklipsholderens indhold i dette vindue igen.',
		pasteArea	: 'Indsæt område'
	},

	pastefromword :
	{
		confirmCleanup	: 'Den tekst du forsøger at indsætte ser ud til at komme fra Word. Vil du rense teksten før den indsættes?',
		toolbar			: 'Indsæt fra Word',
		title			: 'Indsæt fra Word',
		error			: 'Det var ikke muligt at fjerne formatteringen på den indsatte tekst grundet en intern fejl'
	},

	pasteText :
	{
		button	: 'Indsæt som ikke-formateret tekst',
		title	: 'Indsæt som ikke-formateret tekst'
	},

	templates :
	{
		button			: 'Skabeloner',
		title			: 'Indholdsskabeloner',
		options : 'Skabelon muligheder',
		insertOption	: 'Erstat det faktiske indhold',
		selectPromptMsg	: 'Vælg den skabelon, som skal åbnes i editoren (nuværende indhold vil blive overskrevet):',
		emptyListMsg	: '(Der er ikke defineret nogen skabelon)'
	},

	showBlocks : 'Vis afsnitsmærker',

	stylesCombo :
	{
		label		: 'Typografi',
		panelTitle	: 'Formattering på stylesheet',
		panelTitle1	: 'Block typografi',
		panelTitle2	: 'Inline typografi',
		panelTitle3	: 'Object typografi'
	},

	format :
	{
		label		: 'Formatering',
		panelTitle	: 'Formatering',

		tag_p		: 'Normal',
		tag_pre		: 'Formateret',
		tag_address	: 'Adresse',
		tag_h1		: 'Overskrift 1',
		tag_h2		: 'Overskrift 2',
		tag_h3		: 'Overskrift 3',
		tag_h4		: 'Overskrift 4',
		tag_h5		: 'Overskrift 5',
		tag_h6		: 'Overskrift 6',
		tag_div		: 'Normal (DIV)'
	},

	div :
	{
		title				: 'Opret Div Container',
		toolbar				: 'Opret Div Container',
		cssClassInputLabel	: 'Typografiark',
		styleSelectLabel	: 'Style',
		IdInputLabel		: 'Id',
		languageCodeInputLabel	: ' Sprogkode',
		inlineStyleInputLabel	: 'Inline Style',
		advisoryTitleInputLabel	: 'Vejledende titel',
		langDirLabel		: 'Sprogretning',
		langDirLTRLabel		: 'Venstre til højre (LTR)',
		langDirRTLLabel		: 'Højre til venstre (RTL)',
		edit				: 'Rediger Div',
		remove				: 'Slet Div'
  	},

	iframe :
	{
		title		: 'Iframe egenskaber',
		toolbar		: 'Iframe',
		noUrl		: 'Venligst indsæt URL på iframen',
		scrolling	: 'Aktiver scrollbars',
		border		: 'Vis kant på rammen'
	},

	font :
	{
		label		: 'Skrifttype',
		voiceLabel	: 'Skrifttype',
		panelTitle	: 'Skrifttype'
	},

	fontSize :
	{
		label		: 'Skriftstørrelse',
		voiceLabel	: 'Skriftstørrelse',
		panelTitle	: 'Skriftstørrelse'
	},

	colorButton :
	{
		textColorTitle	: 'Tekstfarve',
		bgColorTitle	: 'Baggrundsfarve',
		panelTitle		: 'Farver',
		auto			: 'Automatisk',
		more			: 'Flere farver...'
	},

	colors :
	{
		'000' : 'Sort',
		'800000' : 'Mørkerød',
		'8B4513' : 'Mørk orange',
		'2F4F4F' : 'Dark Slate Grå',
		'008080' : 'Teal',
		'000080' : 'Navy',
		'4B0082' : 'Indigo',
		'696969' : 'Mørkegrå',
		'B22222' : 'Scarlet / Rød',
		'A52A2A' : 'Brun',
		'DAA520' : 'Guld',
		'006400' : 'Mørkegrøn',
		'40E0D0' : 'Tyrkis',
		'0000CD' : 'Mellemblå',
		'800080' : 'Lilla',
		'808080' : 'Grå',
		'F00' : 'Rød',
		'FF8C00' : 'Mørk orange',
		'FFD700' : 'Guld',
		'008000' : 'Grøn',
		'0FF' : 'Cyan',
		'00F' : 'Blå',
		'EE82EE' : 'Violet',
		'A9A9A9' : 'Matgrå',
		'FFA07A' : 'Laksefarve',
		'FFA500' : 'Orange',
		'FFFF00' : 'Gul',
		'00FF00' : 'Lime',
		'AFEEEE' : 'Mat tyrkis',
		'ADD8E6' : 'Lyseblå',
		'DDA0DD' : 'Plum',
		'D3D3D3' : 'Lysegrå',
		'FFF0F5' : 'Lavender Blush',
		'FAEBD7' : 'Antikhvid',
		'FFFFE0' : 'Lysegul',
		'F0FFF0' : 'Gul / Beige',
		'F0FFFF' : 'Himmeblå',
		'F0F8FF' : 'Alice blue',
		'E6E6FA' : 'Lavendel',
		'FFF' : 'Hvid'
	},

	scayt :
	{
		title			: 'Stavekontrol mens du skriver',
		opera_title		: 'Ikke supporteret af Opera',
		enable			: 'Aktivér SCAYT',
		disable			: 'Deaktivér SCAYT',
		about			: 'Om SCAYT',
		toggle			: 'Skift/toggle SCAYT',
		options			: 'Indstillinger',
		langs			: 'Sprog',
		moreSuggestions	: 'Flere forslag',
		ignore			: 'Ignorér',
		ignoreAll		: 'Ignorér alle',
		addWord			: 'Tilføj ord',
		emptyDic		: 'Ordbogsnavn må ikke være tom.',

		optionsTab		: 'Indstillinger',
		allCaps			: 'Ignorer alle store bogstaver',
		ignoreDomainNames : 'Ignorér domænenavne',
		mixedCase		: 'Ignorer ord med store og små bogstaver',
		mixedWithDigits	: 'Ignorér ord med numre',

		languagesTab	: 'Sprog',

		dictionariesTab	: 'Ordbøger',
		dic_field_name	: 'Navn på ordbog',
		dic_create		: 'Opret',
		dic_restore		: 'Gendan',
		dic_delete		: 'Slet',
		dic_rename		: 'Omdøb',
		dic_info		: 'Til start er brugerordbogen gemt i en Cookie. Dog har Cookies en begrænsning på størrelse. Når ordbogen når en bestemt størrelse kan den blive gemt på vores server. For at gemme din personlige ordbog på vores server skal du angive et navn for denne. Såfremt du allerede har gemt en ordbog, skriv navnet på denne og klik på Gendan knappen.',

		aboutTab		: 'Om'
	},

	about :
	{
		title		: 'Om CKEditor',
		dlgTitle	: 'Om CKEditor',
		help	: 'Se $1 for at få hjælp.',
		userGuide : 'CKEditor-brugermanual',
		moreInfo	: 'For informationer omkring licens, se venligst vores hjemmeside (på engelsk):',
		copy		: 'Copyright &copy; $1. Alle rettigheder forbeholdes.'
	},

	maximize : 'Maksimér',
	minimize : 'Minimér',

	fakeobjects :
	{
		anchor		: 'Anker',
		flash		: 'Flashanimation',
		iframe		: 'Iframe',
		hiddenfield	: 'Skjult felt',
		unknown		: 'Ukendt objekt'
	},

	resize : 'Træk for at skalere',

	colordialog :
	{
		title		: 'Vælg farve',
		options	:	'Farvemuligheder',
		highlight	: 'Markér',
		selected	: 'Valgt farve',
		clear		: 'Nulstil'
	},

	toolbarCollapse	: 'Sammenklap værktøjslinje',
	toolbarExpand	: 'Udvid værktøjslinje',

	toolbarGroups :
	{
		document : 'Dokument',
		clipboard : 'Udklipsholder/Fortryd',
		editing : 'Redigering',
		forms : 'Formularer',
		basicstyles : 'Basis styles',
		paragraph : 'Paragraf',
		links : 'Links',
		insert : 'Indsæt',
		styles : 'Typografier',
		colors : 'Farver',
		tools : 'Værktøjer'
	},

	bidi :
	{
		ltr : 'Tekstretning fra venstre til højre',
		rtl : 'Tekstretning fra højre til venstre'
	},

	docprops :
	{
		label : 'Egenskaber for dokument',
		title : 'Egenskaber for dokument',
		design : 'Design',
		meta : 'Metatags',
		chooseColor : 'Vælg',
		other : '<anden>',
		docTitle :	'Sidetitel',
		charset : 	'Tegnsætskode',
		charsetOther : 'Anden tegnsætskode',
		charsetASCII : 'ASCII',
		charsetCE : 'Centraleuropæisk',
		charsetCT : 'Traditionel kinesisk (Big5)',
		charsetCR : 'Kyrillisk',
		charsetGR : 'Græsk',
		charsetJP : 'Japansk',
		charsetKR : 'Koreansk',
		charsetTR : 'Tyrkisk',
		charsetUN : 'Unicode (UTF-8)',
		charsetWE : 'Vesteuropæisk',
		docType : 'Dokumenttype kategori',
		docTypeOther : 'Anden dokumenttype kategori',
		xhtmlDec : 'Inkludere XHTML deklartion',
		bgColor : 'Baggrundsfarve',
		bgImage : 'Baggrundsbillede URL',
		bgFixed : 'Fastlåst baggrund',
		txtColor : 'Tekstfarve',
		margin : 'Sidemargen',
		marginTop : 'Øverst',
		marginLeft : 'Venstre',
		marginRight : 'Højre',
		marginBottom : 'Nederst',
		metaKeywords : 'Dokument index nøgleord (kommasepareret)',
		metaDescription : 'Dokumentbeskrivelse',
		metaAuthor : 'Forfatter',
		metaCopyright : 'Copyright',
		previewHtml : '<p>Dette er et <strong>eksempel på noget tekst</strong>. Du benytter <a href="javascript:void(0)">CKEditor</a>.</p>'
	}
};
