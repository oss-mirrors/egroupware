VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   8535
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5925
   OleObjectBlob   =   "FrmMain.dsx":0000
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

'***********************************************************************************************
' Applies and saves settings
'***********************************************************************************************
Private Sub cmdApply_Click()
    SaveSettings
    PutSettings
End Sub

Private Sub cmdCancel_Click()
    Me.Hide
End Sub

'***********************************************************************************************
' Display the names of contacts in the remote and local directories
'***********************************************************************************************
Private Sub cmdGet_Click()
    BasUtilities.GetContacts
End Sub

Private Sub cmdOK_Click()
    SaveSettings
    PutSettings
    Me.Hide
End Sub

'***********************************************************************************************
' Add selected contacts to the other contact directory
'***********************************************************************************************
Private Sub cmdSynchronize_Click()
    BasUtilities.SynchronizeContacts
End Sub

'***********************************************************************************************
' Set things up for frmMain
'***********************************************************************************************
Private Sub UserForm_Initialize()
    'load previous settings
    LoadSettings
    'we get an error if we try and run the putsettings() if there is nothing in the options.
    If Me.txtHostname.Text <> "" Then
        PutSettings
    End If
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
    End If
End Sub

'***********************************************************************************************
' Puts log in info into the global CeGW instance.
'***********************************************************************************************
Public Sub PutSettings()
    Master.eGW.HostName = txtHostname
    Master.eGW.Port = txtPort
    Master.eGW.URI = txtURI
    Master.eGW.Username = txtUsername
    Master.eGW.Password = txtPassword
End Sub

'***********************************************************************************************
' Makes it easy to get selected items from a listBox. Returns a collection of strings
' This is an unbelievably messy way of doing things. I had it working where you pass a reference
' to the listbox itself, but I couldn't get it working when we went from VBA to VB6. So here's
' this, it needs to be fixed but oh well.
'***********************************************************************************************
Public Function GetSelectedListItems(List As String) As Collection
    Dim i As Integer
    Set GetSelectedListItems = New Collection
    
    If List = "local" Then
        For i = 0 To listLocal.ListCount - 1
            If listLocal.Selected(i) Then
                Debug.Print listLocal.List(i)
                GetSelectedListItems.Add listLocal.List(i)
            End If
        Next i
    ElseIf List = "remote" Then
        For i = 0 To listRemote.ListCount - 1
            If listRemote.Selected(i) Then
                Debug.Print listRemote.List(i)
                GetSelectedListItems.Add listRemote.List(i)
            End If
        Next i
    Else
        On Error Resume Next
        Err.Clear
        Err.Raise vbObjectError + 607, "FrmMain", _
                                    "invalid listbox specified to GetSelectedListItems"
        If Err.Number <> 0 Then
        MsgBox "Error # " & Str(Err.Number) & " was generated by " _
                & Err.Source & Chr(13) & Err.Description, vbOKOnly, "Error"
        End If
    End If
End Function
