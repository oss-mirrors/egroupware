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
    Dim dTemp As Variant
    Dim Command As String

    For Each dTemp In Descriptions
        If DefaultFields(dTemp)("eGWName") <> "" Then
            egwContact.AddString DefaultFields(dTemp)("eGWName"), _
                                CallByName(oContact, DefaultFields(dTemp)("oName"), VbGet)
        End If
    Next dTemp
End Sub

'***********************************************************************************************
' Takes an eGW contact item and an empty Outlook contact item and maps the eGW fields to the
' Outlook fields.
'***********************************************************************************************
Public Sub eGW2Outlook(egwContact As XMLRPCStruct, oContact As ContactItem)
    Dim dTemp As Variant
    Dim Command As String
    
    For Each dTemp In Descriptions
        If DefaultFields(dTemp)("oName") <> "" Then
            CallByName oContact, DefaultFields(dTemp)("oName"), VbLet, _
                    egwContact.GetValueByName(DefaultFields(dTemp)("eGWName")).StringValue
        End If
    Next dTemp
End Sub

Private Function MakeField(ByVal Description As String, _
                            ByVal eGWName As String, _
                            ByVal oName As String) As Collection
    Set MakeField = New Collection
    
    MakeField.Add Description, "Description"
    MakeField.Add eGWName, "eGWName"
    MakeField.Add oName, "oName"
End Function

Property Get DefaultFields() As Collection
    Set DefaultFields = New Collection
    
    With DefaultFields
        .Add MakeField("full name", "fn", "FullName"), "full name"
        .Add MakeField("Sound", "sound", ""), "Sound"
        
        .Add MakeField("company name", "org_name", "CompanyName"), "company name"
        .Add MakeField("department", "org_unit", "Department"), "department"
        
        .Add MakeField("title", "title", "JobTitle"), "title"
        .Add MakeField("prefix", "n_prefix", "Title"), "prefix"
        .Add MakeField("first name", "n_given", "Firstname"), "first name"
        .Add MakeField("middle name", "n_middle", "MiddleName"), "middle name"
        .Add MakeField("last name", "n_family", "LastName"), "last name"
        .Add MakeField("suffix", "n_suffix", "Suffix"), "suffix"
        .Add MakeField("label", "label", ""), "label"
        
        .Add MakeField("business street", "adr_one_street", "BusinessAddressStreet"), "business street"
        .Add MakeField("business city", "adr_one_locality", "BusinessAddressCity"), "business city"
        .Add MakeField("business state", "adr_one_region", "BusinessAddressState"), "business state"
        .Add MakeField("business zip code", "adr_one_postalcode", "BusinessAddressPostalCode"), "business zip code"
        .Add MakeField("business country", "adr_one_countryname", "BusinessAddressCountry"), "business country"
        .Add MakeField("business address type", "adr_one_type", ""), "business address type"
        
        .Add MakeField("home street", "adr_two_street", "HomeAddressStreet"), "home street"
        .Add MakeField("home city", "adr_two_locality", "HomeAddressCity"), "home city"
        .Add MakeField("home state", "adr_two_region", "HomeAddressState"), "home state"
        .Add MakeField("home zip code", "adr_two_postalcode", "HomeAddressPostalCode"), "home zip code"
        .Add MakeField("home country", "adr_two_countryname", "HomeAddressCountry"), "home country"
        .Add MakeField("home address type", "adr_two_type", ""), "home address type"
        
        .Add MakeField("time zone", "tz", ""), "time zone"
        .Add MakeField("geo", "geo", ""), "geo"
        
        .Add MakeField("business phone", "tel_work", "BusinessTelephoneNumber"), "business phone"
        .Add MakeField("home phone", "tel_home", "HomeTelephoneNumber"), "home phone"
        .Add MakeField("voice phone", "tel_voice", ""), "voice phone"
        .Add MakeField("message phone", "tel_msg", "AssistantTelephoneNumber"), "message phone"
        .Add MakeField("fax", "tel_fax", "BusinessFaxNumber"), "fax"
        .Add MakeField("pager", "tel_pager", "PagerNumber"), "pager"
        .Add MakeField("mobile phone", "tel_cell", "MobileTelephoneNumber"), "mobile phone"
        .Add MakeField("bbs phone", "tel_bbs", ""), "bbs phone"
        .Add MakeField("modem phone", "tel_modem", ""), "modem phone"
        .Add MakeField("isdn phone", "tel_isdn", "ISDNNumber"), "isdn phone"
        .Add MakeField("car phone", "tel_car", "CarTelephoneNumber"), "car phone"
        .Add MakeField("video phone", "tel_video", ""), "video phone"
        .Add MakeField("preferred phone", "tel_prefer", "PrimaryTelephoneNumber"), "preferred phone"
        
        .Add MakeField("business email", "email", "Email1Address"), "business email"
        .Add MakeField("business email type", "email_type", "Email1AddressType"), "business email type"
        .Add MakeField("home email", "email_home", "Email2Address"), "home email"
        .Add MakeField("home email type", "email_home_type", "Email2AddressType"), "home email type"
        
        .Add MakeField("address line 2", "address2", ""), "address line 2"
        .Add MakeField("address line 3", "address3", ""), "address line 3"
        .Add MakeField("Other Phone", "ophone", ""), "Other Phone"
        .Add MakeField("birthday", "bday", ""), "birthday"
        .Add MakeField("url", "url", "WebPage"), "url"
        .Add MakeField("public key", "pubkey", ""), "public key"
        .Add MakeField("notes", "note", ""), "notes"
    End With
End Property

Property Get eGWFields() As Collection
    'From http://www.egroupware.org/index.php?page_name=&category_id=38&domain=developers&wikipage=AddressbookXmlRpc
    Set eGWFields = New Collection
    
    With eGWFields
        .Add "fn"
        .Add "Sound"
        .Add "org_name"
        .Add "org_unit"
        .Add "title"
        .Add "n_prefix"
        .Add "n_given"
        .Add "n_middle"
        .Add "n_family"
        .Add "n_suffix"
        .Add "Label"
        .Add "adr_one_street"
        .Add "adr_one_locality"
        .Add "adr_one_region"
        .Add "adr_one_postalcode"
        .Add "adr_one_countryname"
        .Add "adr_one_type"
        .Add "adr_two_street"
        .Add "adr_two_locality"
        .Add "adr_two_region"
        .Add "adr_two_postalcode"
        .Add "adr_two_countryname"
        .Add "adr_two_type"
        .Add "tz"
        .Add "geo"
        .Add "tel_work"
        .Add "tel_home"
        .Add "tel_voice"
        .Add "tel_msg"
        .Add "tel_fax"
        .Add "tel_pager"
        .Add "tel_cell"
        .Add "tel_bbs"
        .Add "tel_modem"
        .Add "tel_isdn"
        .Add "tel_car"
        .Add "tel_video"
        .Add "tel_prefer"
        .Add "email"
        .Add "email_type"
        .Add "email_home"
        .Add "email_home_type"
        .Add "address2"
        .Add "address3"
        .Add "ophone"
        .Add "bday"
        .Add "url"
        .Add "pubkey"
        .Add "note"
    End With
End Property

Property Get oFields() As Collection
    'Grabbed from http://www.cdolive.com/cdo10.htm
    Set oFields = New Collection

    With oFields
        .Add "CreationTime"
        .Add "Size"
        .Add "Sensitivity"
        .Add "MessageClass"
        .Add "Importance"
        .Add "EntryID"
        .Add "Body"
        .Add "Categories"
        .Add "FullName"
        .Add "JobTitle"
        .Add "CompanyName"
        .Add "Title"
        .Add "LastName"
        .Add "MiddleName"
        .Add "FirstName"
        .Add "Suffix"
        .Add "BusinessHomePage"
        .Add "PersonalHomePage"
        .Add "FTPSite"
        .Add "Initials"
        .Add "WebPage"
        .Add "FileAs"
        .Add "CompanyAndFullName"
        .Add "CompanyLastFirstNoSpace"
        .Add "CompanyLastFirstSpaceOnly"
        .Add "FullNameAndCompany"
        .Add "LastFirstAndSuffix"
        .Add "LastFirstNoSpace"
        .Add "LastFirstNoSpaceCompany"
        .Add "LastFirstSpaceOnly"
        .Add "LastFirstSpaceOnlyCompany"
        .Add "LastNameAndFirstName"
        .Add "Email1DisplayName"
        .Add "Email1Address"
        .Add "Email1AddressType"
        .Add "Email1EntryID"
        .Add "Email2DisplayName"
        .Add "Email2Address"
        .Add "Email2AddressType"
        .Add "Email2EntryID"
        .Add "Email3DisplayName"
        .Add "Email3Address"
        .Add "Email3AddressType"
        .Add "Email3EntryID"
        .Add "IMAddress"
        .Add "SelectedMailingAddress"
        .Add "AssistantTelephoneNumber"
        .Add "BusinessTelephoneNumber"
        .Add "Business2TelephoneNumber"
        .Add "BusinessFaxNumber"
        .Add "CallbackTelephoneNumber"
        .Add "CarTelephoneNumber"
        .Add "CompanyMainTelephoneNumber"
        .Add "HomeTelephoneNumber"
        .Add "Home2TelephoneNumber"
        .Add "HomeFaxNumber"
        .Add "ISDNNumber"
        .Add "MobileTelephoneNumber"
        .Add "OtherTelephoneNumber"
        .Add "OtherFaxNumber"
        .Add "PagerNumber"
        .Add "PrimaryTelephoneNumber"
        .Add "RadioTelephoneNumber"
        .Add "TelexNumber"
        .Add "TTYTDDTelephoneNumber"
        .Add "MailingAddress"
        .Add "MailingAddressCity"
        .Add "MailingAddressCountry"
        .Add "MailingAddressPostalCode"
        .Add "MailingAddressPostOfficeBox"
        .Add "MailingAddressState"
        .Add "MailingAddressStreet"
        .Add "HomeAddressCity"
        .Add "HomeAddressStreet"
        .Add "HomeAddressState"
        .Add "HomeAddressCountry"
        .Add "HomeAddressPostOfficeBox"
        .Add "HomeAddressPostalCode"
        .Add "OtherAddressCity"
        .Add "OtherAddressStreet"
        .Add "OtherAddressState"
        .Add "OtherAddressCountry"
        .Add "OtherAddressPostalCode"
        .Add "OtherAddressPostOfficeBox"
        .Add "HomeAddress"
        .Add "BusinessAddress"
        .Add "OtherAddress"
        .Add "BusinessAddressCity"
        .Add "BusinessAddressStreet"
        .Add "BusinessAddressState"
        .Add "BusinessAddressCountry"
        .Add "BusinessAddressPostalCode"
        .Add "BusinessAddressPostOfficeBox"
        .Add "Department"
        .Add "ManagerName"
        .Add "Location"
        .Add "AssistantName"
        .Add "Profession"
        .Add "NickName"
        .Add "Birthday"
        .Add "SpouseName"
        .Add "Anniversary"
        .Add "Account"
        .Add "ComputerNetworkName"
        .Add "Children"
        .Add "CustomerID"
        .Add "Gender"
        .Add "GovernmentIDNumber"
        .Add "Hobby"
        .Add "Language"
        .Add "OfficeLocation"
        .Add "OrganizationalIDNumber"
        .Add "ReferredBy"
        .Add "BillingInformation"
        .Add "InternetFreeBusyAddress"
        .Add "Mileage"
        .Add "UserField1"
        .Add "UserField2"
        .Add "UserField3"
        .Add "UserField4"
        .Add "YomiCompanyName"
        .Add "YomiFirstName"
        .Add "YomiLastName"
    End With
End Property

Property Get Descriptions() As Collection
    'From http://www.egroupware.org/index.php?page_name=&category_id=38&domain=developers&wikipage=AddressbookXmlRpc
    Set Descriptions = New Collection
    
    With Descriptions
        .Add "full name"
        .Add "Sound"
        .Add "company name"
        .Add "department"
        .Add "title"
        .Add "prefix"
        .Add "first name"
        .Add "middle name"
        .Add "last name"
        .Add "suffix"
        .Add "label"
        .Add "business street"
        .Add "business city"
        .Add "business state"
        .Add "business zip code"
        .Add "business country"
        .Add "business address type"
        .Add "home street"
        .Add "home city"
        .Add "home state"
        .Add "home zip code"
        .Add "home country"
        .Add "home address type"
        .Add "time zone"
        .Add "geo"
        .Add "business phone"
        .Add "home phone"
        .Add "voice phone"
        .Add "message phone"
        .Add "fax"
        .Add "pager"
        .Add "mobile phone"
        .Add "bbs phone"
        .Add "modem phone"
        .Add "isdn phone"
        .Add "car phone"
        .Add "video phone"
        .Add "preferred phone"
        .Add "business email"
        .Add "business email type"
        .Add "home email"
        .Add "home email type"
        .Add "address line 2"
        .Add "address line 3"
        .Add "Other Phone"
        .Add "birthday"
        .Add "url"
        .Add "public key"
        .Add "notes"
    End With
End Property
