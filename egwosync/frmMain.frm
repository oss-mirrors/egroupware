VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} frmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   9240
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5865
   OleObjectBlob   =   "frmMain.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "frmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Private Sub cmdTest_Click()
    Dim xmlParms As New XMLRPCStruct
    Dim xmlArray As New XMLRPCArray
    Dim Response As XMLRPCResponse
    Dim intStart As Integer
    Dim intLimit As Integer
    
    intStart = 1
    intLimit = 50
    
    'Save connection details to registry
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

    'Get the contacts from the eGW server
    '   When I tried to grab all the contacts from the server at once I got an Overflow
    '   XML Parse Error, so now I grab them 50 at a time.
    Do
        'update our place in the remote list of contacts
        intStart = intLimit + intStart
        'It's not sufficient to only add the parameters that need updating, they all need to be
        '   re-added in a specific order
        xmlParms.AddInteger "start", intStart
        xmlParms.AddInteger "limit", intLimit
        xmlArray.AddString "fn"
        xmlParms.AddArray "fields", xmlArray
        'xmlParms.AddString "query", "And"
        xmlParms.AddString "order", "n_given"
        xmlParms.AddString "sort", "ASC"
        
        Set Response = modEGWUtilities.SimpleExec("addressbook.boaddressbook.search", xmlParms)
        
        Debug.Print "Attempting to print the response values..."
        Dim tempValue As XMLRPCValue
        Dim tempValue2 As XMLRPCMember
        For Each tempValue In Response.params(1).ArrayValue
            listRemote.AddItem tempValue.StructValue.GetValueByName("fn").StringValue
        Next tempValue
    Loop While Response.params(1).ArrayValue.Count = intLimit
    
    'List the contacts from the local Outlook folders
    Dim gnspNameSpace As NameSpace
    Dim fldContacts As Outlook.MAPIFolder
    Set gnspNameSpace = GetNamespace("MAPI")
    Set fldContacts = gnspNameSpace.GetDefaultFolder(olFolderContacts)
    modEGWUtilities.GetOutlookContacts fldContacts
End Sub
