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

Private Sub Application_Startup()
    If GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname") <> "" Then
        eGW.Hostname = GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Hostname")
        eGW.Port = GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Port")
        eGW.URI = GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="URI")
        eGW.Username = GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Username")
        eGW.Password = GetSetting(AppName:="eGWOSync", Section:="Settings", Key:="Password")
    End If
    AutoMaker.Initialize_handler
End Sub
