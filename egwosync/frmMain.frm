VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} frmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   3390
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   8415
   OleObjectBlob   =   "frmMain.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "frmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Private Sub cmdTest_Click()
    Dim eGW As CeGW 'eGW is the object that handles the connection to eGW
    Dim linsUtility As XMLRPCUtility
    Dim bLogin As Boolean
    Dim xmlParms As XMLRPCStruct
    Dim xmlArray As XMLRPCArray
   
    Set eGW = New CeGW
    Set linsUtility = New XMLRPCUtility
    Set xmlParms = New XMLRPCStruct
    Set xmlArray = New XMLRPCArray
   
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
    
    'Load parameters to login
    eGW.Hostname = txtHostname
    eGW.Port = txtPort
    eGW.URI = txtURI
    eGW.Username = txtUsername
    eGW.Password = txtPassword
    bLogin = eGW.Login
    If bLogin Then
        xmlParms.AddInteger "id", 14
        xmlArray.AddString "n_family"
        xmlArray.AddString "n_given"
        xmlArray.AddString "email"
        xmlParms.AddArray "fields", xmlArray
        
        eGW.Reset
        eGW.Exec "addressbook.boaddressbook.read", xmlParms
           
        If eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Status
            If eGW.Response.Status = 4 Then
                Debug.Print "XML Parse Error:" & eGW.Response.XMLParseError
            End If
        ElseIf eGW.Response.Params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Params.Count & " return parameters, expecting 1"
        ElseIf eGW.Response.Params(1).ValueType <> XMLRPC_ARRAY Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.Response.Params(1).ValueType) & " returned, expecting an array"
        End If
        
        'Extract response items
        For Each responseItem In eGW.Response.Params(1).ArrayValue(1).StructValue
            If responseItem.Value.ValueType = 3 Then
                List1.AddItem responseItem.Name & ": " & responseItem.Value.StringValue
            ElseIf responseItem.Value.ValueType = 1 Then
                List1.AddItem responseItem.Name & ": " & responseItem.Value.IntegerValue
            End If
        Next
        'End of List eGW methods
        eGW.Logout
    End If
End Sub
