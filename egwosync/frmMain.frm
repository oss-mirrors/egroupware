VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   9210
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5805
   OleObjectBlob   =   "FrmMain.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "frmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# BasUtilities.bas
'# Various multipurpose functions for the egwosync module of eGroupWare
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Private Sub cmdGet_Click()
    BasUtilities.GetContacts
End Sub
Private Sub cmdSynchronize_Click()
    BasUtilities.SynchronizeContacts
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
' Makes it easy to get selected items from a listBox. Returns an array of strings in a variant.
'***********************************************************************************************
Public Function GetSelectedListItems(myList As ListBox) As Variant
    Dim intListIndex    As Integer
    Dim tempArray()     As String
    
    'Loop through all the listbox items
    For intListIndex = 0 To (myList.ListCount - 1) Step 1
        'If the current item is selected...
        If myList.Selected(intListIndex) Then
            ReDim Preserve tempArray(BasUtilities.GetUpper(tempArray))
            'Add it to the array of selected items
            tempArray(BasUtilities.GetUpper(tempArray) - 1) = myList.List(intListIndex)
        End If
    Next intListIndex
    GetSelectedListItems = tempArray()
End Function
