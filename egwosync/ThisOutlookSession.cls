VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "ThisOutlookSession"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
'#################################################################################################
'# ThisOutlookSession
'# Default to all VBA projects. In this project it provides a place for centralized access to
'# eGroupWare and starts processes that respond to user actions, such as creation of a local
'# contact.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Dim AutoMaker As New CAutoExporter
Public eGW As New CeGW
Private beGWOSyncEnabled As Boolean

'***********************************************************************************************
' Specifies whether egwosync has all the information it needs to run. Functions should not run
' unless this is true.
'***********************************************************************************************
Public Property Get eGWOSyncEnabled() As Boolean
    eGWOSyncEnabled = beGWOSyncEnabled
End Property

'***********************************************************************************************
' Refresh the status of whether egwosync has all the login information it needs
'***********************************************************************************************
Public Function RefreshSettingStatus() As Boolean
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" And _
        GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Port") <> "" And _
        GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="URI") <> "" And _
        GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Username") <> "" And _
        GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Password") <> "" Then
            beGWOSyncEnabled = True
            RefreshSettingStatus = True
    Else
        beGWOSyncEnabled = False
        RefreshSettingStatus = False
    End If
End Function

'***********************************************************************************************
' Runs when Outlook opens
'***********************************************************************************************
Private Sub Application_Startup()
    'Are we ready to contact the server?
    If Not RefreshSettingStatus Then
        Setup
    End If
    
    'If so, initialize the process that will allow contacts to be added automatically
    If beGWOSyncEnabled Then
        AutoMaker.Initialize_handler
    End If
End Sub

'***********************************************************************************************
' To be run if all login the information is not present. First asks if the user wants to set
' egwosync up at the moment, then opens setup if the user says yes.
'***********************************************************************************************
Public Function Setup() As Boolean
        Dim x As VbMsgBoxResult
        
        x = MsgBox("eGroupWare Synchornization has not been set up to access the eGroupWare " & _
                    "Server. Would you like to set it up now?", _
                    vbOKCancel, "eGrwoupWare not set up")
        If x = vbOK Then
            If Not frmMain.Visible Then
                frmMain.Show
            End If
            'Set focus to settings page
            frmMain.MultiPage1.Value = 1
        ElseIf x = vbCancel Then
            beGWOSyncEnabled = False
        End If
        Setup = beGWOSyncEnabled
End Function

