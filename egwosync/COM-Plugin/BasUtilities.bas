Attribute VB_Name = "BasUtilities"
'#################################################################################################
'# BasUtilities.bas
'# Various multipurpose functions for the egwosync module of eGroupWare
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################
Dim egwContacts As New CeGWContacts

'***********************************************************************************************
' Gets information on the remote contacts and lists all the remote contacts and local contacts
' in listboxes.
'***********************************************************************************************
Public Sub GetContacts()
    If Master.Ready Then
        Dim xmlParms    As New XMLRPCStruct
        Dim xmlArray    As New XMLRPCArray
        Dim xmlResponse As New XMLRPCResponse
        Dim tempValue   As XMLRPCValue
        Dim INT_START   As Integer
        Dim INT_LIMIT   As Integer
        Dim query       As String
        Dim ORDER       As String
        Dim SORT        As String
        Dim oContacts   As New COutlookContacts
        Dim colFields   As Collection
        Dim strTemp     As Variant
        
        'Clears the local and remote listbox, so that we dont get double contacts.
        FrmMain.listLocal.Clear
        FrmMain.listRemote.Clear
        
        'Get Filter and search info
        query = FrmMain.txtSearch
        Set colFields = FrmMain.AdditionalFields
        
        'Some Defaults
        INT_START = 1
        INT_LIMIT = 50
        ORDER = "fn"
        SORT = "ASC"
        
        '[ > Get the contacts from the eGW server.
        '[ When I tried to grab all the contacts from the server at once I got an Overflow
        '[ XML Parse Error, so now I grab them 50 at a time.
        Do
            '[ It's not sufficient to only add the parameters that need updating, they all need to be
            '[ re-added in a specific order
            xmlParms.AddInteger "start", INT_START
            xmlParms.AddInteger "limit", INT_LIMIT
            xmlArray.AddString "fn"
            For Each strTemp In colFields
                If strTemp <> "" Then
                    xmlArray.AddString strTemp
                End If
            Next strTemp
            xmlParms.AddArray "fields", xmlArray
            xmlParms.AddString "query", query
            xmlParms.AddString "order", ORDER
            xmlParms.AddString "sort", SORT
            
            'Get the contacts from the server and temporarily store them in xmlResponse
            Set xmlResponse = BasUtilities.SimpleExec("addressbook.boaddressbook.search", xmlParms)
            
            'A touch of Ye Olde Error Handling. Aborts execution if login failed.
            If xmlResponse.Status <> XMLRPC_PARAMSRETURNED Then
                Exit Sub
            End If
            
            For Each tempValue In xmlResponse.Params(1).ArrayValue
                'Add each contact to the response collection
                egwContacts.CursoryInfo.Add tempValue.StructValue
            Next tempValue
            egwContacts.List FrmMain.listRemote
            
            'update our place in the remote list of contacts
            INT_START = INT_LIMIT + INT_START
        Loop While xmlResponse.Params(1).ArrayValue.Count = INT_LIMIT
        
        Debug.Print "Got all contacts from the server."
        
        'List the contacts from the local Outlook folders
        Dim gnspNamespace As NameSpace
        Dim fldContacts As Outlook.MAPIFolder
        Set gnspNamespace = GetNamespace("MAPI")
        Set fldContacts = gnspNamespace.GetDefaultFolder(olFolderContacts)
        oContacts.List fldContacts
    Else
        Master.Setup
    End If
End Sub

'***********************************************************************************************
' Finds which contacts have been selected by the user in each listbox. Then downloads full
' information on each selected remote contact, adds them to the local contact folder. Then
' retrieves information on the selected local contacts and writes them to the eGW server. Provides
' simple overwrite protection in each direction.
'***********************************************************************************************
Public Sub SynchronizeContacts()
    If Master.Ready Then
        Dim colSelLocal         As Collection
        Dim colSelRemote        As Collection
        Dim arrFullInformation() As XMLRPCResponse
        Dim strListItem         As Variant
        Dim ciContact           As ContactItem
        Dim tempResponse        As Variant
        Dim gnspNamespace       As NameSpace
        Dim fldContacts         As Outlook.MAPIFolder
        Dim xmlResponse         As New XMLRPCResponse
        Dim tempValue           As XMLRPCValue
        Dim oContacts           As New COutlookContacts
        
        Set gnspNamespace = GetNamespace("MAPI")
        Set fldContacts = gnspNamespace.GetDefaultFolder(olFolderContacts)
        
        'Get the full names of the selected contacts from each listbox
        With FrmMain
            Set colSelLocal = .GetSelectedListItems(.listLocal)
            Set colSelRemote = .GetSelectedListItems(.listRemote)
        End With
        
        '[ > Start synchronizing remote contacts
        'If there are remote contacts selected
        If colSelRemote.Count > 0 Then
            'reset ciContact
            Set ciContact = Nothing
            'Load all the available information on each selected contact into an array
            For Each strListItem In colSelRemote
                'Dynamically resize arrFullInformation
                ReDim Preserve arrFullInformation(GetUpper(arrFullInformation))
                '[ Pass an XMLRPCValue that holds the fullname of the contact to get the full
                '[ information on that contact from the remote server.
                Set arrFullInformation(GetUpper(arrFullInformation) - 1) = egwContacts.GetFullInfoFromServer(CStr(strListItem))
            Next strListItem
        
            'Add the selected remote contacts to the local directory
            For Each tempResponse In arrFullInformation
                Set ciContact = oContacts.Create(tempResponse.Params(1).ArrayValue(1).StructValue)
                If Not (ciContact Is Nothing) Then
                    Debug.Print "Successfully imported " & tempResponse.Params(1).ArrayValue(1).StructValue.GetValueByName("fn").StringValue & _
                                    " to the Outlook Contact List."
                    'Update the local list to show the new contact
                    With FrmMain.listLocal
                        For i = 0 To (.ListCount - 1) Step 1
                            If .List(i) = ciContact.FullName Then
                                .RemoveItem (i)
                                Exit For
                            End If
                        Next i
                        .AddItem (ciContact.FullName)
                    End With
                End If
            Next tempResponse
        End If
        '[ < End synchronizing remote contacts
        
        '[ > Start synchronizing local contacts
        'If there are local contacts selected...
        If colSelLocal.Count > 0 Then
            'Be sure everything is reset
            Set ciContact = Nothing
            Set tempResponse = Nothing
            
            'Add the selected local contacts to the remote directory
            For Each strListItem In colSelLocal
                'Find the contact in the local directory by their fullname, which was in the listbox
                Set ciContact = fldContacts.Items.Find("[FullName] = " & strListItem)
                Set tempResponse = egwContacts.Create(ciContact)
                
                'If creating the remote contact was successful...
                If Not (tempResponse Is Nothing) Then
                    Debug.Print "Successfully exported " & ciContact.FullName & " to the eGroupWare server"
    
                    'Add each contact to the response collection
                    egwContacts.CursoryInfo.Add tempResponse.Params(1).ArrayValue(1).StructValue
                    'list the contacts in the listbox
                    With FrmMain.listRemote
                        For i = 0 To (.ListCount - 1) Step 1
                            If .List(i) = egwContacts.CursoryInfo.Item(egwContacts.CursoryInfo.Count).GetValueByName("fn").StringValue Then
                                .RemoveItem (i)
                                Exit For
                            End If
                        Next i
                        .AddItem egwContacts.CursoryInfo.Item(egwContacts.CursoryInfo.Count).GetValueByName("fn").StringValue
                    End With
                'If creation of the remote contact failed...
                End If
            Next strListItem
        End If
        '[ < End synchronizing local contacts
    Else
        Master.Setup
    End If
End Sub

'***********************************************************************************************
' Provides a simplified way of running commands on the eGW XMLRPC server. No command should call
' SimpleExec unless it has first checked to see if Master.Ready is true, which means that
' egwosync has all the information it needs to log in to the server.
'***********************************************************************************************
Public Function SimpleExec(methodName As String, xmlParms As XMLRPCStruct) As XMLRPCResponse
    Dim linsUtility As New XMLRPCUtility
    Dim bLogin As Boolean
    Dim bEnabled As Boolean
    
    '[ grab the login information from the form GUI. eGW is defined in CeGWOSyncMaster
    '[ so it's always accessible to everyone.
    bEnabled = Master.Ready
    
    If bEnabled Then
        'login and put the result in a variable for testing
        bLogin = Master.eGW.Login
        
        'If we logged in successfully...
        If bLogin Then
            Master.eGW.Reset
            Master.eGW.Exec methodName, xmlParms
            
            'Error handling bonanza
            If Master.eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
                Debug.Print "Unexpected response from XML-RPC request " & Master.eGW.Response.Status
                If Master.eGW.Response.Status = 4 Then
                    Debug.Print "XML Parse Error: " & Master.eGW.Response.XMLParseError
                    'This line will show the full response string from the server in all its glory
                    Debug.Print Master.eGW.Response.xmlResponse
                End If
            ElseIf Master.eGW.Response.Params.Count <> 1 Then
                Debug.Print "Unexpected response from XML-RPC request " & Master.eGW.Response.Params.Count & " return parameters, expecting 1"
            End If
            
            'return the response from the XMLRPC server
            Set SimpleExec = Master.eGW.Response
            'it's always polite to close the door when you leave
            Master.eGW.Logout
        'If login failed...
        Else
            Dim x As VbMsgBoxResult
            'Show a message box with a warning icon and an Okay button
            x = MsgBox("An attempt to log in to the remote server failed.", vbExclamation + vbOKOnly, "Login Failed")
            If x = vbOkay Then
                Exit Function
            End If
        End If
    Else
        Dim y As VbMsgBoxResult
        y = MsgBox("You cannot use eGWOSync without first setting the " & _
                    (Chr(10)) & "server access parameters. Click okay to setup, cancel to abort.", _
                    vbExclamation + vbOKCancel, "Cannot log in to eGW server")
        If y = vbOK Then
            Master.Setup
        End If
    End If
End Function

'***********************************************************************************************
' Finds the highest subscript available in an array. Like UBound, only doesn't throw an error if
' the array is uninitialized. Note, the default return value is ONE MORE than than the last filled
' element.
'***********************************************************************************************
Public Function GetUpper(varArray As Variant) As Integer
    Dim Upper As Integer
    On Error Resume Next
    Upper = UBound(varArray)
    If Err.Number Then
        If Err.Number = 9 Then
            Upper = 0
        Else
            With Err
                MsgBox "Error:" & .Number & "-" & .Description
            End With
            Exit Function
        End If
    Else
        Upper = UBound(varArray) + 1
    End If
    On Error GoTo 0
    GetUpper = Upper
End Function
