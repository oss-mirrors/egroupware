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

Property Get Filter() As String
    Filter = myFilter
End Property

Property Get AdditionalFields() As Collection
    Dim Control As Variant
    Set AdditionalFields = New Collection
    
    For Each Control In FilterFrame.Controls
        If TypeOf Control Is MsForms.ComboBox Then
            If Control.Value <> "" Then
                AdditionalFields.Add Control.Value
            End If
        End If
    Next Control
End Property

Property Get FieldQueries() As Collection
    Dim Control As Variant
    Set FieldQueries = New Collection
    
    For Each Control In FilterFrame.Controls
        If TypeOf Control Is MsForms.TextBox Then
            If Control.Value <> "" Then
                FieldQueries.Add Control.Text
            End If
        End If
    Next Control
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
        If TypeOf Control Is MsForms.ComboBox Then
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
Public Function GetSelectedListItems(ByRef myList As MsForms.ListBox) As Collection
    Dim i As Integer
    Set GetSelectedListItems = New Collection

    For i = 0 To myList.ListCount - 1
        If myList.Selected(i) Then
            GetSelectedListItems.Add myList.List(i)
        End If
    Next i
End Function
