VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} eGWForm 
   Caption         =   "MerceNet eGroupWare Synchronization"
   ClientHeight    =   1530
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   6840
   OleObjectBlob   =   "eGWForm.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "eGWForm"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
Private Sub cmdTest_Click()
    Dim eGW As CeGW 'eGW is the object that handles the connection to eGW
    Dim linsUtility As XMLRPCUtility
    Dim bLogin As Boolean
    
    'Create the connection objects
    Set eGW = New CeGW
    Set linsUtility = New XMLRPCUtility
    
    'Save connection details to the registry
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
        'List eGW methods
        eGW.Reset
        eGW.Exec "system.listMethods"
        If eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Status
        ElseIf eGW.Response.Params.Count <> 1 Then
            Debug.Print "Unexpected response from XML-RPC request " & eGW.Response.Params.Count & " return parameters, expecting 1"
        ElseIf eGW.Response.Params(1).ValueType <> XMLRPC_ARRAY Then
            Debug.Print "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(eGW.Response.Params(1).ValueType) & " returned, expecting an array"
        End If
        For Each linsValue In eGW.Response.Params(1).ArrayValue
            Debug.Print linsValue.StringValue
            List1.AddItem linsValue.StringValue
            DoEvents
        Next linsValue
        'End of List eGW methods
        
        'Try pushing Contacts to eGW
        eGWOSync.PushContacts
        eGW.Logout
    End If
End Sub
