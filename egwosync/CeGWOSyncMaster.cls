VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "CeGWOSyncMaster"
Attribute VB_GlobalNameSpace = True
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = True
'#################################################################################################
'# CeGWOSyncMaster
'# Provides a centralized hub for eGWOSync. This was ThisOutlookSession when eGWOSync was a VBA
'# project.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Dim AutoMaker As New CAutoExporter
Public eGW As CeGW


Public Sub OpenMain()
    If Ready Then
        FrmMain.Show
    Else
        Setup
    End If
End Sub

'***********************************************************************************************
' Do we have enough info to log in to the server?
'***********************************************************************************************
Public Property Get Ready() As Boolean
    If _
    GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" And _
    GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Port") <> "" And _
    GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="URI") <> "" And _
    GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Username") <> "" And _
    GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Password") <> "" Then
    
        Ready = True
    Else
        Ready = False
    End If
End Property

'***********************************************************************************************
' Runs when Outlook opens
'***********************************************************************************************
Private Sub Class_Initialize()
    Set eGW = New CeGW
    'Are we ready to contact the server?
    If Ready Then
        AutoMaker.Initialize_handler
    Else
        Setup
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
                    vbOKCancel + vbApplicationModal, "eGrwoupWare not set up")
        If x = vbOK Then
            If Not FrmMain.Visible Then
                FrmMain.Show
            End If
            'Set focus to settings page
            FrmMain.MultiPage1.Value = 1
        ElseIf x = vbCancel Then
            Setup = False
        End If
End Function

