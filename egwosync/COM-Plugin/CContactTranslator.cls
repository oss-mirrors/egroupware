VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "CContactTranslator"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'#################################################################################################
'# CContactTranslator.cls
'# Maps Outlook Contact fields to eGroupWare Contact fields and vice versa
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

'***********************************************************************************************
' Takes an Outlook Contact item and maps its fields to the fields of an eGW Contact item. The
' eGW Contact item is represented by an XMLRPCStruct that is the same as the xmlParams Struct
' needed by phpgwapi's addressbook.boaddressbook.write
'***********************************************************************************************
Public Sub Outlook2eGW(oContact As ContactItem, egwContact As XMLRPCStruct)

    egwContact.AddString "fn", oContact.FullName 'full name
    'egwContact.AddString "sound", oContact 'Sound
    
    egwContact.AddString "org_name", oContact.CompanyName 'company name
    egwContact.AddString "org_unit", oContact.Department 'department
    
    egwContact.AddString "Title", oContact.JobTitle 'title
    egwContact.AddString "n_prefix", oContact.Title 'prefix
    egwContact.AddString "n_given", oContact.FirstName 'first name
    egwContact.AddString "n_middle", oContact.MiddleName 'middle name
    egwContact.AddString "n_family", oContact.LastName 'last name
    egwContact.AddString "n_suffix", oContact.Suffix 'suffix
    'egwContact.AddString "label", oContact.FileAs 'label
    
    egwContact.AddString "adr_one_street", oContact.BusinessAddressStreet 'business street
    egwContact.AddString "adr_one_locality", oContact.BusinessAddressCity 'business city
    egwContact.AddString "adr_one_region", oContact.BusinessAddressState 'business state
    egwContact.AddString "adr_one_postalcode", oContact.BusinessAddressPostalCode 'business zip code
    egwContact.AddString "adr_one_countryname", oContact.BusinessAddressCountry 'business country
    'egwContact.AddString "adr_one_type", oContact 'business address type
    
    egwContact.AddString "adr_two_street", oContact.HomeAddressStreet 'home street
    egwContact.AddString "adr_two_locality", oContact.HomeAddressCity 'home city
    egwContact.AddString "adr_two_region", oContact.HomeAddressState 'home state
    egwContact.AddString "adr_two_postalcode", oContact.HomeAddressPostalCode 'home zip code
    egwContact.AddString "adr_two_countryname", oContact.HomeAddressCountry 'home country
    'egwContact.AddString "adr_two_type", oContact 'home address type
    
    'egwContact.AddString "tz", oContact 'time zone
    'egwContact.AddString "geo", oContact 'geo
    
    egwContact.AddString "tel_work", oContact.BusinessTelephoneNumber 'business phone
    egwContact.AddString "tel_home", oContact.HomeTelephoneNumber 'home phone
    'egwContact.AddString "tel_voice", oContact 'voice phone
    egwContact.AddString "tel_msg", oContact.AssistantTelephoneNumber 'message phone
    egwContact.AddString "tel_fax", oContact.BusinessFaxNumber 'fax
    egwContact.AddString "tel_pager", oContact.PagerNumber 'pager
    egwContact.AddString "tel_cell", oContact.MobileTelephoneNumber 'mobile phone
    'egwContact.AddString "tel_bbs", oContact 'bbs phone
    'egwContact.AddString "tel_modem", oContact 'modem phone
    egwContact.AddString "tel_isdn", oContact.ISDNNumber 'isdn phone
    egwContact.AddString "tel_car", oContact.CarTelephoneNumber 'car phone
    'egwContact.AddString "tel_video", oContact 'video phone
    egwContact.AddString "tel_prefer", oContact.PrimaryTelephoneNumber 'preferred phone
    
    egwContact.AddString "Email", oContact.Email1Address 'business email
    egwContact.AddString "email_type", oContact.Email1AddressType 'business email type
    egwContact.AddString "email_home", oContact.Email2Address 'home email
    egwContact.AddString "email_home_type", oContact.Email2AddressType 'home email type
    
    'egwContact.AddString "address2", oContact 'address line 2
    'egwContact.AddString "address3", oContact 'address line 3
    'egwContact.AddString "ophone", oContact.Business2TelephoneNumber 'Other Phone
    'egwContact.AddString "bday", oContact 'birthday
    egwContact.AddString "URL", oContact.WebPage 'url"
    'egwContact.AddString "pubkey", oContact 'public key
    'egwContact.AddString "note", oContact 'notes
End Sub

'***********************************************************************************************
' Takes an eGW contact item and an empty Outlook contact item and maps the eGW fields to the
' Outlook fields.
'***********************************************************************************************
Public Sub eGW2Outlook(egwContact As XMLRPCStruct, oContact As ContactItem)

    'The following field names for the eGW side of things were gotten from
    '   http://www.egroupware.org/index.php?page_name=&category_id=38&domain=developers&wikipage=AddressbookXmlRpc
    '   I've included the full eGW list for sake of completeness
    oContact.FullName = egwContact.GetValueByName("fn").StringValue 'full name
    'oContact. = egwContact.GetValueByName("sound").StringValue 'Sound
    
    oContact.CompanyName = egwContact.GetValueByName("org_name").StringValue 'company name
    oContact.Department = egwContact.GetValueByName("org_unit").StringValue 'department
    
    oContact.JobTitle = egwContact.GetValueByName("title").StringValue 'title
    oContact.Title = egwContact.GetValueByName("n_prefix").StringValue 'prefix
    oContact.FirstName = egwContact.GetValueByName("n_given").StringValue 'first name
    oContact.MiddleName = egwContact.GetValueByName("n_middle").StringValue 'middle name
    oContact.LastName = egwContact.GetValueByName("n_family").StringValue 'last name
    oContact.Suffix = egwContact.GetValueByName("n_suffix").StringValue 'suffix
    'oContact.FileAs = egwContact.GetValueByName("label").StringValue 'label
    
    oContact.BusinessAddressStreet = egwContact.GetValueByName("adr_one_street").StringValue 'business street
    oContact.BusinessAddressCity = egwContact.GetValueByName("adr_one_locality").StringValue 'business city
    oContact.BusinessAddressState = egwContact.GetValueByName("adr_one_region").StringValue 'business state
    oContact.BusinessAddressPostalCode = egwContact.GetValueByName("adr_one_postalcode").StringValue 'business zip code
    oContact.BusinessAddressCountry = egwContact.GetValueByName("adr_one_countryname").StringValue 'business country
    'oContact. = egwContact.GetValueByName("adr_one_type").StringValue 'business address type
    
    oContact.HomeAddressStreet = egwContact.GetValueByName("adr_two_street").StringValue 'home street
    oContact.HomeAddressCity = egwContact.GetValueByName("adr_two_locality").StringValue 'home city
    oContact.HomeAddressState = egwContact.GetValueByName("adr_two_region").StringValue 'home state
    oContact.HomeAddressPostalCode = egwContact.GetValueByName("adr_two_postalcode").StringValue 'home zip code
    oContact.HomeAddressCountry = egwContact.GetValueByName("adr_two_countryname").StringValue 'home country
    'oContact. = egwContact.GetValueByName("adr_two_type").StringValue 'home address type
    
    'oContact. = egwContact.GetValueByName("tz").StringValue 'time zone
    'oContact. = egwContact.GetValueByName("geo").StringValue 'geo
    
    oContact.BusinessTelephoneNumber = egwContact.GetValueByName("tel_work").StringValue 'business phone
    oContact.HomeTelephoneNumber = egwContact.GetValueByName("tel_home").StringValue 'home phone
    'oContact. = egwContact.GetValueByName("tel_voice").StringValue 'voice phone
    oContact.AssistantTelephoneNumber = egwContact.GetValueByName("tel_msg").StringValue 'message phone
    oContact.BusinessFaxNumber = egwContact.GetValueByName("tel_fax").StringValue 'fax
    oContact.PagerNumber = egwContact.GetValueByName("tel_pager").StringValue 'pager
    oContact.MobileTelephoneNumber = egwContact.GetValueByName("tel_cell").StringValue 'mobile phone
    'oContact. = egwContact.GetValueByName("tel_bbs").StringValue 'bbs phone
    'oContact. = egwContact.GetValueByName("tel_modem").StringValue 'modem phone
    oContact.ISDNNumber = egwContact.GetValueByName("tel_isdn").StringValue 'isdn phone
    oContact.CarTelephoneNumber = egwContact.GetValueByName("tel_car").StringValue 'car phone
    'oContact. = egwContact.GetValueByName("tel_video").StringValue 'video phone
    oContact.PrimaryTelephoneNumber = egwContact.GetValueByName("tel_prefer").StringValue 'preferred phone
    
    oContact.Email1Address = egwContact.GetValueByName("email").StringValue 'business email
    oContact.Email1AddressType = egwContact.GetValueByName("email_type").StringValue 'business email type
    oContact.Email2Address = egwContact.GetValueByName("email_home").StringValue 'home email
    oContact.Email2AddressType = egwContact.GetValueByName("email_home_type").StringValue 'home email type
    
    'oContact. = egwContact.GetValueByName("address2").StringValue 'address line 2
    'oContact. = egwContact.GetValueByName("address3").StringValue 'address line 3
    'oContact.Business2TelephoneNumber = egwContact.GetValueByName("ophone").StringValue 'Other Phone
    'oContact.Birthday = egwContact.GetValueByName("bday").StringValue 'birthday
    oContact.WebPage = egwContact.GetValueByName("url").StringValue 'url
    'oContact. = egwContact.GetValueByName("pubkey").StringValue 'public key
    'oContact. = egwContact.GetValueByName("note").StringValue 'notes
End Sub
