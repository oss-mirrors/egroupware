VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "CFormHelper"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'#################################################################################################
'# CFormHelper.cls
'# Functions that are common to the forms in this project.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

'Having a parent property will make it easy for the helper to get properties from its creator
Private myParent As UserForm

Property Set Parent(p As UserForm)
    If myParent Is Nothing Then
        Set myParent = p
    End If
End Property

Property Get Parent() As UserForm
    Set Parent = myParent
End Property

'***********************************************************************************************
' Saves all the settings from frmMain to the registry.
'***********************************************************************************************
Public Sub SaveSettings()
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Hostname", Setting:=myParent.txtHostname
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Port", Setting:=myParent.txtPort
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="URI", Setting:=myParent.txtURI
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Username", Setting:=myParent.txtUsername
    SaveSetting AppName:="eGWOSync", Section:="Settings", _
        Key:="Password", Setting:=myParent.txtPassword
End Sub

'***********************************************************************************************
' Loads the settings for frmMain from the registry if they've been set previously.
'***********************************************************************************************
Public Sub LoadSettings()
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" Then
        myParent.txtHostname.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Hostname")
        myParent.txtPort.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Port")
        myParent.txtURI.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="URI")
        myParent.txtUsername.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Username")
        myParent.txtPassword.Text = GetSetting(AppName:="eGWOSync", _
            Section:="Settings", Key:="Password")
    End If
End Sub

'***********************************************************************************************
' Puts log in info into the global CeGW instance.
'***********************************************************************************************
Public Sub PutSettings()
    Master.eGW.Hostname = myParent.txtHostname
    Master.eGW.Port = myParent.txtPort
    Master.eGW.URI = myParent.txtURI
    Master.eGW.Username = myParent.txtUsername
    Master.eGW.Password = myParent.txtPassword
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
