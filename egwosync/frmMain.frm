VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} frmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   9210
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5805
   OleObjectBlob   =   "frmMain.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "frmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Dim arrResponses() As XMLRPCValue
Dim ResponseArraySize As Integer
Private Sub cmdGet_Click()
    Dim xmlParms    As New XMLRPCStruct
    Dim xmlArray    As New XMLRPCArray
    Dim xmlResponse As New XMLRPCResponse
    Dim tempValue   As XMLRPCValue
    Dim INT_START   As Integer
    Dim INT_LIMIT   As Integer
    Dim QUERY       As String
    Dim ORDER       As String
    Dim SORT        As String
    
    INT_START = 1
    INT_LIMIT = 50
    QUERY = ""
    ORDER = "fn"
    SORT = "ASC"
    
    'Save connection details to registry
    SaveSettings

    'Get the contacts from the eGW server
    '   When I tried to grab all the contacts from the server at once I got an Overflow
    '   XML Parse Error, so now I grab them 50 at a time.
    Do
        'update our place in the remote list of contacts
        INT_START = INT_LIMIT + INT_START
        
        'It's not sufficient to only add the parameters that need updating, they all need to be
        '   re-added in a specific order
        xmlParms.AddInteger "start", INT_START
        xmlParms.AddInteger "limit", INT_LIMIT
        xmlArray.AddString "fn"
        xmlArray.AddString "n_given"
        xmlArray.AddString "n_family"
        xmlParms.AddArray "fields", xmlArray
        xmlParms.AddString "query", QUERY
        xmlParms.AddString "order", ORDER
        xmlParms.AddString "sort", SORT
        
        'Get the contacts from the server and temporarily store them in xmlResponse
        Set xmlResponse = modEGWUtilities.SimpleExec("addressbook.boaddressbook.search", xmlParms)
        
        'A touch of Ye Olde Error Handling. Aborts execution if login failed.
        If xmlResponse.Status <> XMLRPC_PARAMSRETURNED Then
            Exit Sub
        End If
        
        For Each tempValue In xmlResponse.params(1).ArrayValue
            'Increment the size of the array, starts at zero, so the first time around becomes 1
            ResponseArraySize = ResponseArraySize + 1
            'Resize the response params array
            ReDim Preserve arrResponses(1 To ResponseArraySize)
            'Add each contact to the response array
            Set arrResponses(ResponseArraySize) = tempValue
            
            'list the contacts in the listbox
            listRemote.AddItem arrResponses(ResponseArraySize).StructValue.GetValueByName("fn").StringValue
        Next tempValue
        
    Loop While xmlResponse.params(1).ArrayValue.Count = INT_LIMIT
    
    Debug.Print "Got all contacts from the server."
    
    'List the contacts from the local Outlook folders
    Dim gnspNameSpace As NameSpace
    Dim fldContacts As Outlook.MAPIFolder
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    modEGWUtilities.ListOutlookContacts fldContacts
End Sub

Private Sub cmdSynchronize_Click()
    Dim arrSelLocal         As New XMLRPCArray
    Dim arrSelRemote        As New XMLRPCArray
    Dim valListItem         As XMLRPCValue
    Dim ciContact           As ContactItem
    Dim arrFullInformation() As XMLRPCResponse
    Dim intFullInfoSize     As Integer
    Dim tempResponse        As Variant
    Dim gnspNameSpace       As NameSpace
    Dim fldContacts         As Outlook.MAPIFolder
    Dim xmlResponse         As New XMLRPCResponse
    Dim tempValue           As XMLRPCValue
    
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    
    'Get the full names of the selected contacts from each listbox
    '   and put them in XMLRPC arrays
    Set arrSelLocal = modEGWUtilities.GetSelectedListItems(listLocal)
    Set arrSelRemote = modEGWUtilities.GetSelectedListItems(listRemote)
    
    'If there are remote contacts selected
    If arrSelRemote.Count > 0 Then
        'reset ciContact
        Set ciContact = Nothing
        'Get full information on all the selected remote contacts
        For Each valListItem In arrSelRemote
            'Dynamically resize arrFullInformation
            intFullInfoSize = intFullInfoSize + 1
            ReDim Preserve arrFullInformation(1 To intFullInfoSize)
            'Pass an XMLRPCValue that holds the fullname of the contact to get the full information on
            '   that contact from the remote server.
            Set arrFullInformation(intFullInfoSize) = modEGWUtilities.RemoteGetFullInformation(valListItem, arrResponses)
        Next valListItem
    
        'Add the selected remote contacts to the local directory
        For Each tempResponse In arrFullInformation
            Set ciContact = modEGWUtilities.CreateOutlookContact(tempResponse)
            If Not (ciContact Is Nothing) Then
                Debug.Print "Successfully imported " & tempResponse.params(1).ArrayValue(1).StructValue.GetValueByName("fn").StringValue & _
                                " to the Outlook Contact List."
            Else
                Debug.Print "Failed to import " & tempResponse.params(1).ArrayValue(1).StructValue.GetValueByName("fn").StringValue & _
                                " to the Outlook Contact List."
            End If
            'Update the local list to show the new contact
            frmMain.listLocal.AddItem (ciContact.FullName)
        Next tempResponse
    End If
    
    'If there are local contacts selected
    If arrSelLocal.Count > 0 Then
        Set ciContact = Nothing
        Set tempResponse = Nothing
        'Add the selected local contacts to the remote directory
        For Each valListItem In arrSelLocal
            Set ciContact = fldContacts.Items.Find("[FullName] = " & valListItem.StringValue)
            Set tempResponse = modEGWUtilities.CreateEGWContact(ciContact, arrResponses)
            If Not (tempResponse Is Nothing) Then
                Debug.Print "Successfully exported " & ciContact.FullName & " to the eGroupWare server"
                
                'Now add the new contact to both the array of contacts and the lisbox
                ResponseArraySize = ResponseArraySize + 1
                'Resize the response params array
                ReDim Preserve arrResponses(1 To ResponseArraySize)
                'Add each contact to the response array
                Set arrResponses(ResponseArraySize) = tempResponse.params(1).ArrayValue(1)
            
                'list the contacts in the listbox
                listRemote.AddItem arrResponses(ResponseArraySize).StructValue.GetValueByName("fn").StringValue
            Else
                Debug.Print "Failed to export " & ciContact.FullName & " to the eGroupWare server"
            End If
        Next valListItem
    End If
End Sub

'Saves all the settings from frmMain to the registry
Public Sub SaveSettings()
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Hostname", Setting:=txtHostname
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Port", Setting:=txtPort
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="URI", Setting:=txtURI
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Username", Setting:=txtUsername
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Password", Setting:=txtPassword
End Sub
