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
    QUERY = "w"
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
            'Increment the size of the array, starts zero, so the first time around becomes 1
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
    Dim arrFullInformation() As XMLRPCResponse
    Dim intFullInfoSize     As Integer
    Dim tempResponse        As Variant
    Dim bAdded              As Boolean
    
    'Get the full names of the selected contacts from each listbox
    '   and put them in XMLRPC arrays
    Set arrSelLocal = modEGWUtilities.GetSelectedListItems(listLocal)
    Set arrSelRemote = modEGWUtilities.GetSelectedListItems(listRemote)
    
    For Each valListItem In arrSelRemote
        intFullInfoSize = intFullInfoSize + 1
        ReDim Preserve arrFullInformation(1 To intFullInfoSize)
        Set arrFullInformation(intFullInfoSize) = modEGWUtilities.RemoteGetFullInformation(valListItem, arrResponses)
    Next valListItem
    For Each tempResponse In arrFullInformation
        Debug.Print "Got full info on contact: " & tempResponse.params(1).ArrayValue(1).StructValue.GetValueByName("fn").StringValue
        bAdded = modEGWUtilities.CreateOutlookContact(tempResponse)
        If bAdded Then Debug.Print "Added " & tempResponse.params(1).ArrayValue(1).StructValue.GetValueByName("fn").StringValue _
        & " to the Outlook Contact List."
    Next tempResponse
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
