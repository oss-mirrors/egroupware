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
    Dim xmlParms As New XMLRPCStruct
    Dim xmlArray As New XMLRPCArray
    Dim response As XMLRPCResponse
    
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
        
    'Create XMLRPCStruct for the method parameters
    xmlParms.AddInteger "start", 1
    xmlParms.AddInteger "limit", 10
    xmlArray.AddString "n_given"
    xmlArray.AddString "n_family"
    xmlParms.AddArray "fields", xmlArray
    xmlParms.AddString "query", "xkadj"
    xmlParms.AddString "order", "n_given"
    xmlParms.AddString "sort", "ASC"
    
    Set response = modEGWUtilities.SimpleExec("addressbook.boaddressbook.search", xmlParms)
    Debug.Print response.params.Count
End Sub
