VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmMain 
   BorderStyle     =   1  'Fixed Single
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   8520
   ClientLeft      =   30
   ClientTop       =   300
   ClientWidth     =   5880
   Icon            =   "FrmMain.dsx":0000
   MaxButton       =   0   'False
   MinButton       =   0   'False
   OleObjectBlob   =   "FrmMain.dsx":08CA
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "FrmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# FrmMain.frm & .frx
'# The main control window for mass uploading and downloading of contact information
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################
Private myFilter As String
Private myCategories As Collection

Property Get SelectedCategoryID() As String
    If cbCategories.Value <> "" Then
        SelectedCategoryID = myCategories.Item(cbCategories.Value)("idnum")
    End If
End Property

Property Get Filter() As String
    Filter = myFilter
End Property

Property Get Filters() As Collection
    Dim cbControl As Variant
    Dim strQueryName As String
    Dim strQuery As String
    Dim colTemp As Collection
    Dim Trans As New CContactTranslator
    Set Filters = New Collection
    
    For Each cbControl In FilterFrame.Controls
        If TypeOf cbControl Is MSForms.ComboBox Then
            If cbControl.Value <> "" Then
                Set colTemp = New Collection
                colTemp.Add Trans.DefaultFields(cbControl.Value)("eGWName"), "field"
                strQueryName = "txt" & Right(cbControl.Name, 6) & "Query"
                strQuery = CallByName(FilterFrame, strQueryName, VbGet)
                colTemp.Add strQuery, "query"
                Filters.Add colTemp
            End If
        End If
    Next cbControl
End Property

'***********************************************************************************************
' Applies and saves settings
'***********************************************************************************************
Private Sub cmdApply_Click()
    SaveSettings
    PutSettings
    Master.InitAuto
End Sub

Private Sub cmdCancel_Click()
    Me.Hide
End Sub

Private Sub cmdFilterSave_Click()
    SaveSettings
End Sub

'***********************************************************************************************
' Display the names of contacts in the remote and local directories
'***********************************************************************************************
Private Sub cmdGet_Click()
    Me.MousePointer = vbHourglass
    BasUtilities.GetContacts
    Me.MousePointer = vbDefault
End Sub

Private Sub cmdListCats_Click()
    Me.MousePointer = vbHourglass
    listCategories
    Me.MousePointer = vbDefault
End Sub

Private Sub cmdOK_Click()
    SaveSettings
    PutSettings
    Master.InitAuto
    Me.Hide
End Sub

'***********************************************************************************************
' Add selected contacts to the other contact directory
'***********************************************************************************************
Private Sub cmdSynchronize_Click()
    Me.MousePointer = vbHourglass
    BasUtilities.SynchronizeContacts
    Me.MousePointer = vbDefault
End Sub


'***********************************************************************************************
' Set things up for frmMain
'***********************************************************************************************
Private Sub UserForm_Initialize()
    Dim Translator As New CContactTranslator
    Dim FieldName As Variant
    Dim Control As Variant

    'load previous settings
    LoadSettings
    'we get an error if we try and run the putsettings() if there is nothing in the options.
    If Me.txtHostname.Text <> "" Then
        PutSettings
    End If
    
    'Populate the Filter comboboxes with available fields
    For Each Control In FilterFrame.Controls
        If TypeOf Control Is MSForms.ComboBox Then
            For Each FieldName In Translator.Descriptions
                Control.AddItem FieldName
            Next FieldName
            Control.AddItem ""
        End If
    Next Control
End Sub

'***********************************************************************************************
' Saves all the settings from frmMain to the registry.
'***********************************************************************************************
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
        
    SaveSetting AppName:="eGWOSync", Section:="Filters", _
        Key:="Search", Setting:=txtSearch
End Sub

'***********************************************************************************************
' Loads the settings for frmMain from the registry if they've been set previously.
'***********************************************************************************************
Public Sub LoadSettings()
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" Then
        txtHostname.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Hostname")
        txtPort.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Port")
        txtURI.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="URI")
        txtUsername.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Username")
        txtPassword.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Password")
            
        txtSearch.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Filters", Key:="Search")
    End If
End Sub

'***********************************************************************************************
' Puts log in info into the global CeGW instance.
'***********************************************************************************************
Public Sub PutSettings()
    Master.eGW.Hostname = txtHostname
    Master.eGW.Port = txtPort
    Master.eGW.URI = txtURI
    Master.eGW.Username = txtUsername
    Master.eGW.Password = txtPassword
End Sub

'***********************************************************************************************
' Makes it easy to get selected items from a listBox. Returns a collection of strings
'***********************************************************************************************
Public Function GetSelectedListItems(ByRef myList As MSForms.ListBox) As Collection
    Dim i As Integer
    Set GetSelectedListItems = New Collection

    For i = 0 To myList.ListCount - 1
        If myList.Selected(i) Then
            GetSelectedListItems.Add myList.List(i)
        End If
    Next i
End Function

Private Sub listCategories()
'[ This is a messy cut and paste job. What needs to be done is a simpler simple exec needs to be
'[ written that takes things besides XMLRPCStructs as arguents, then this function and SimpleExec
'[ can use the new function.
    Dim xmlResponse As New XMLRPCResponse
    Dim bEnabled As Boolean
    Dim bLogin As Boolean
    Dim thingy As Variant
    Dim tempCol As Collection
    
    Set myCategories = New Collection
    
    bEnabled = Master.Ready
    If bEnabled Then
        
        bLogin = Master.eGW.Login
        If bLogin Then
            Master.eGW.Reset
            
            Master.eGW.Exec "addressbook.boaddressbook.categories", False
            
            'Error handling bonanza
            If Master.eGW.Response.Status <> XMLRPC_PARAMSRETURNED Then
                Debug.Print "Unexpected response from XML-RPC request " & Master.eGW.Response.Status
                If Master.eGW.Response.Status = 4 Then
                    Debug.Print "XML Parse Error: " & Master.eGW.Response.XMLParseError
                    'This line will show the full response string from the server in all its glory
                    'Debug.Print Master.eGW.Response.xmlResponse
                End If
            ElseIf Master.eGW.Response.Params.Count <> 1 Then
                Debug.Print "Unexpected response from XML-RPC request " & Master.eGW.Response.Params.Count & " return parameters, expecting 1"
            End If
            
            'return the response from the XMLRPC server
            Set xmlResponse = Master.eGW.Response
        End If
    Else
        Dim y As VbMsgBoxResult
        y = MsgBox("You cannot use eGWOSync without first setting the " & _
                    (Chr(10)) & "server access parameters. Click okay to setup, cancel to abort.", _
                    vbExclamation + vbOKCancel, "Cannot log in to eGW server")
        If y = vbOK Then _
            Master.Setup
    End If
    
    For Each thingy In xmlResponse.Params(1).StructValue
        Set tempCol = New Collection
        tempCol.Add thingy.Name, "idnum"
        tempCol.Add thingy.Value.StringValue, "name"
        If Not ExistsInCollection(myCategories, thingy.Value.StringValue) Then
            myCategories.Add tempCol, thingy.Value.StringValue
        End If
    Next thingy
    
    If myCategories.Count > 0 Then
        For Each thingy In myCategories
            cbCategories.AddItem thingy("name")
        Next thingy
    End If
End Sub

Public Function ExistsInCollection(myCol As Collection, Key As String) As Boolean
    Dim x
    On Error Resume Next
    ExistsInCollection = False
    Set x = myCol(Key)
    If Err.Number Then
        If Err.Number = 5 Then
            ExistsInCollection = False
        Else
            With Err
                MsgBox "Error:" & .Number & "-" & .Description
            End With
            Exit Function
        End If
    Else
        ExistsInCollection = True
    End If
End Function
