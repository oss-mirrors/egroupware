Attribute VB_Name = "modEGWUtilities"
'Provides a simplified way of running commands on the eGW XMLRPC server
Public Function SimpleExec(methodName As String, xmlParms As XMLRPCStruct) As XMLRPCResponse
    Dim linsUtility As New XMLRPCUtility
    Dim bLogin As Boolean
    Dim eGW As New CeGW
    
    'grab the login information from the form GUI
    eGW.Hostname = frmMain.txtHostname
    eGW.Port = frmMain.txtPort
    eGW.URI = frmMain.txtURI
    eGW.Username = frmMain.txtUsername
    eGW.Password = frmMain.txtPassword
    
    'login and put the result in a variable for testing
    bLogin = eGW.Login
    
    'If we logged in successfully...
    If bLogin Then
        eGW.Reset
        eGW.Exec methodName, xmlParms
        
        'Error handling bonanza
        If eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Status
            If eGW.Response.Status = 4 Then
                Debug.Print "XML Parse Error: " & eGW.Response.XMLParseError
                'Debug.Print eGW.Response.XMLResponse
            End If
        ElseIf eGW.Response.params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.params.Count & " return parameters, expecting 1"
        ElseIf eGW.Response.params(1).ValueType <> XMLRPC_ARRAY Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.Response.params(1).ValueType) & " returned, expecting an array"
            PrintXMLRPCValue eGW.Response.params(1)
        End If
        
        'return the response from the XMLRPC server
        Set SimpleExec = eGW.Response
        'it's always polite to close the door when you leave
        eGW.Logout
    Else
        Dim x As VbMsgBoxResult
        x = MsgBox("An attempt to log in to the remote server failed.", vbExclamation + vbOKOnly, "Login Failed")
        If x = vbOkay Then
            Exit Function
        End If
    End If
End Function
'Put all the contacts' fullnames from a folder into the listbox
Public Function ListOutlookContacts(fldFolder As Outlook.MAPIFolder)
    Dim objItem As Object
    
    If fldFolder.Folders.Count > 0 Then
        For Each objItem In fldFolder.Folders
            ListOutlookContacts (objItem)
        Next objItem
    End If
    
    For Each objItem In fldFolder.Items
        frmMain.listLocal.AddItem (objItem.FullName)
    Next objItem
End Function

'This is useful as a debug subroutine: it will print any XMLRPC Value without the developer
'   knowing what type the value is.
Public Sub PrintXMLRPCValue(vItem As XMLRPCValue)
    'linsValue holds each element of an array for recursive calling
    Dim linsValue As XMLRPCValue
    'linsMember holds each member of a structure for recursive calling
    Dim linsMember As XMLRPCMember
    
    'Define a printing process for each XMLRPC data type
    Select Case vItem.ValueType
        
        'Arrays
        Case XMLRPC_ARRAY
            Debug.Print "--Array Start: "
            For Each linsValue In vItem.ArrayValue
                PrintXMLRPCValue linsValue
            Next
            Debug.Print "--Array End"
        
        'Base 64
        Case XMLRPC_BASE64
            Debug.Print "Base 64: " & vItem.Base64Value
        
        'Booleans
        Case XMLRPC_BOOLEAN
            Debug.Print "Boolean: " & vItem.BooleanValue
        
        'Dates and times
        Case XMLRPC_DATETIME
            Debug.Print "Date/time: " & vItem.DateTimeValue
        
        'Double numbers
        Case XMLRPC_DOUBLE
            Debug.Print "Double: " & vItem.DoubleValue
            'Debug.Print vItem.DoubleValue
        
        'Integers
        Case XMLRPC_INT_I4
            Debug.Print "XMLRPC Integer: " & vItem.IntegerValue
        
        'Nil values
        Case XMLRPC_NIL
            Debug.Print "Nil value"
        
        'Strings
        Case XMLRPC_STRING
            Debug.Print "String: " & vItem.StringValue
        
        'Structures
        Case XMLRPC_STRUCT
            Debug.Print "--Structure Start: "
            For Each linsMember In vItem.StructValue
                PrintXMLRPCValue linsMember.Value
            Next
            Debug.Print "--Structure End"
        
        'If the value type is unrecognized
        Case Else
            Debug.Print "Attempt to print XMLRPCValue failed: ValueType not recognized."
        End Select
End Sub

'Makes it easy to get the selected items from a listBox. Returns an XMLRPCArray filled with
'   strings.
Public Function GetSelectedListItems(myList As ListBox) As XMLRPCArray
    Dim intListIndex As Integer
    Set GetSelectedListItems = New XMLRPCArray
    
    'Loop through all the listbox items
    For intListIndex = 0 To (myList.ListCount - 1) Step 1
        'If the current item is selected...
        If myList.Selected(intListIndex) Then
            'Add it to the array of selected items
            GetSelectedListItems.AddString (myList.List(intListIndex))
        End If
    Next intListIndex
End Function


Public Function RemoteGetFullInformation(vSelected As XMLRPCValue, aResponses() As XMLRPCValue) As XMLRPCResponse
    Dim xmlParms    As New XMLRPCStruct
    Dim varTemp     As Variant
    'Now we need to go back and get the full information on the selected contacts.
    '   aResponses holds the id of each contact which we can use in a
    '   boaddressbook.read call, we just need to get the id out of aResponses.
    '   I can't think of any other way to do this than to do a loop search. It
    '   seems inefficient though.
    For Each varTemp In aResponses
    
        'For every selected item from the listbox, cycle through the responses from the
        '   server. If a fullname property is the same as the selected item...
        If (vSelected.StringValue = varTemp.StructValue.GetValueByName("fn").StringValue) Then

            'Get the full information on this contact from the server. The ID needs to be
            '   converted to an Integer.
            xmlParms.AddInteger "id", Val(varTemp.StructValue.GetValueByName("id").StringValue)
            Set RemoteGetFullInformation = modEGWUtilities.SimpleExec("addressbook.boaddressbook.read", xmlParms)
                
            'Break out of the loop since we've found our man.
            GoTo NextSelection
                
        End If
    Next varTemp
    
NextSelection:
End Function

Public Function CreateOutlookContact(ByVal rContact As XMLRPCResponse) As Boolean
    Dim objContact          As Outlook.ContactItem
    Dim EC                  As XMLRPCStruct
    Dim gnspNameSpace       As NameSpace
    Dim fldContacts         As Outlook.MAPIFolder
    
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    Set objContact = fldContacts.Items.Add
    'EC is about to become an XMLRPCStruct
    Set EC = rContact.params(1).ArrayValue(1).StructValue
    
    'If a contact with the same full name doesn't exist, create the new contact.
    '   Perhaps there should be a more sophisticated method of finding similar contacts as well,
    '   but I'll leave that for the future.
    Dim vFirstName As Variant
    Dim vLastName As Variant
    Set vFirstName = fldContacts.Items.Find("[FirstName] = " & EC.GetValueByName("n_given").StringValue)
    Set vLastName = fldContacts.Items.Find("[LastName] = " & EC.GetValueByName("n_family").StringValue)
    If Not (vFirstName Is Nothing) And Not (vLastName Is Nothing) Then
    
        'Display a message box to confirm an overwrite.
        Dim x As VbMsgBoxResult
        x = MsgBox("Trying to import " & (Chr(10)) & (EC.GetValueByName("fn").StringValue) & (Chr(10)) & _
                    "from the eGroupWare server, but a contact by that name already" & _
                    " exists in the local directory." & (Chr(10)) & (Chr(10)) & _
                    "Press okay to overwrite the local contact, cancel to skip", _
                    vbOKCancel, "Confirm Overwrite of " & (EC.GetValueByName("fn").StringValue))

        'If the user chose to overwrite then go ahead and do the writing over
        If x = vbOK Then
            'First delete the current copy to make space for the new one
            vFirstName.Delete
            'then go to where the writing is done.
            GoTo WriteContact
        'If the user chose to skip, exit this function
        ElseIf x = vbCancel Then
            CreateOutlookContact = False
            Exit Function
        End If
        
    'If there wasn't an identical contact already, or the user chose to overwrite
    Else
WriteContact:
        'The following field names for the eGW side of things were gotten from
        '   http://www.egroupware.org/index.php?page_name=&category_id=38&domain=developers&wikipage=AddressbookXmlRpc
        '   I've included the full eGW list for sake of completeness
        objContact.FullName = EC.GetValueByName("fn").StringValue 'full name
        'objContact. = EC.GetValueByName("sound").StringValue 'Sound
        objContact.CompanyName = EC.GetValueByName("org_name").StringValue 'company name
        'objContact. = EC.GetValueByName("org_unit").StringValue 'department
        objContact.Title = EC.GetValueByName("title").StringValue 'title
        'objContact. = EC.GetValueByName("n_prefix").StringValue 'prefix
        objContact.FirstName = EC.GetValueByName("n_given").StringValue 'first name
        objContact.MiddleName = EC.GetValueByName("n_middle").StringValue 'middle name
        objContact.LastName = EC.GetValueByName("n_family").StringValue 'last name
        objContact.Suffix = EC.GetValueByName("n_suffix").StringValue 'suffix
        objContact.Email1DisplayName = EC.GetValueByName("label").StringValue 'label
        objContact.HomeAddress = EC.GetValueByName("adr_one_street").StringValue 'business street
        objContact.HomeAddressCity = EC.GetValueByName("adr_one_locality").StringValue 'business city
        objContact.HomeAddressState = EC.GetValueByName("adr_one_region").StringValue 'business state
        objContact.HomeAddressPostalCode = EC.GetValueByName("adr_one_postalcode").StringValue 'business zip code
        objContact.HomeAddressCountry = EC.GetValueByName("adr_one_countryname").StringValue 'business country
        'objContact. = EC.GetValueByName("adr_one_type").StringValue 'business address type
        objContact.BusinessAddress = EC.GetValueByName("adr_two_street").StringValue 'home street
        objContact.BusinessAddressCity = EC.GetValueByName("adr_two_locality").StringValue 'home city
        objContact.BusinessAddressState = EC.GetValueByName("adr_two_region").StringValue 'home state
        objContact.BusinessAddressPostalCode = EC.GetValueByName("adr_two_postalcode").StringValue 'home zip code
        objContact.BusinessAddressCountry = EC.GetValueByName("adr_two_countryname").StringValue 'home country
        'objContact. = EC.GetValueByName("adr_two_type").StringValue 'home address type
        'objContact. = EC.GetValueByName("tz").StringValue 'time zone
        'objContact. = EC.GetValueByName("geo").StringValue 'geo
        objContact.BusinessTelephoneNumber = EC.GetValueByName("tel_work").StringValue 'business phone
        objContact.HomeTelephoneNumber = EC.GetValueByName("tel_home").StringValue 'home phone
        'objContact. = EC.GetValueByName("tel_voice").StringValue 'voice phone
        'objContact. = EC.GetValueByName("tel_msg").StringValue 'message phone
        objContact.BusinessFaxNumber = EC.GetValueByName("tel_fax").StringValue 'fax
        objContact.PagerNumber = EC.GetValueByName("tel_pager").StringValue 'pager
        objContact.MobileTelephoneNumber = EC.GetValueByName("tel_cell").StringValue 'mobile phone
        'objContact. = EC.GetValueByName("tel_bbs").StringValue 'bbs phone
        'objContact. = EC.GetValueByName("tel_modem").StringValue 'modem phone
        objContact.ISDNNumber = EC.GetValueByName("tel_isdn").StringValue 'isdn phone
        objContact.CarTelephoneNumber = EC.GetValueByName("tel_car").StringValue 'car phone
        'objContact. = EC.GetValueByName("tel_video").StringValue 'video phone
        objContact.PrimaryTelephoneNumber = EC.GetValueByName("tel_prefer").StringValue 'preferred phone
        objContact.Email1Address = EC.GetValueByName("email").StringValue 'business email
        objContact.Email1AddressType = EC.GetValueByName("email_type").StringValue 'business email type
        objContact.Email2Address = EC.GetValueByName("email_home").StringValue 'home email
        objContact.Email2AddressType = EC.GetValueByName("email_home_type").StringValue 'home email type
        'objContact. = EC.GetValueByName("address2").StringValue 'address line 2
        'objContact. = EC.GetValueByName("address3").StringValue 'address line 3
        'objContact. = EC.GetValueByName("ophone").StringValue 'Other Phone
        'objContact.Birthday = EC.GetValueByName("bday").StringValue 'birthday
        objContact.WebPage = EC.GetValueByName("url").StringValue 'url
        'objContact. = EC.GetValueByName("pubkey").StringValue 'public key
        'objContact. = EC.GetValueByName("note").StringValue 'notes
        
        'Finalize the changes.
        objContact.Close (olSave)
        CreateOutlookContact = True
    End If
End Function
