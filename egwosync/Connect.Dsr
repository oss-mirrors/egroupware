VERSION 5.00
Begin {AC0714F6-3D04-11D1-AE7D-00A0C90F26F4} Connect 
   ClientHeight    =   10320
   ClientLeft      =   1740
   ClientTop       =   1545
   ClientWidth     =   12840
   _ExtentX        =   22648
   _ExtentY        =   18203
   _Version        =   393216
   Description     =   "Add-In Project Template"
   DisplayName     =   "eGWOSync"
   AppName         =   "Microsoft Outlook"
   AppVer          =   "Microsoft Outlook 10.0"
   LoadName        =   "Startup"
   LoadBehavior    =   3
   RegLocation     =   "HKEY_CURRENT_USER\Software\Microsoft\Office\Outlook"
End
Attribute VB_Name = "Connect"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
'#################################################################################################
'# Connect.Dsr
'# The COM Add-In handle for Outlook.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# ian[at]merce.net
'# hyber[at]hyber.dk
'#################################################################################################

Option Explicit

Implements IDTExtensibility2
Private AddinInstance As COMAddIn
Private oXL As Object 'Outlook.Application is not compatible with Outlook 2000
Private WithEvents cbb As Office.CommandBarButton
Attribute cbb.VB_VarHelpID = -1


Private Sub IDTExtensibility2_OnConnection(ByVal Application As Object, _
    ByVal ConnectMode As AddInDesignerObjects.ext_ConnectMode, _
    ByVal AddInInst As Object, custom() As Variant)
    
    On Error Resume Next
    Set AddinInstance = AddInInst
    Set oXL = Application
    If oXL Is Nothing Then
        BasUtilities.BugOut 614, "Connect.OnConnection", "Error in OnConnection. Application = " & _
            Application.Name & " ConnectMode = " & ConnectMode
    End If
    If (ConnectMode <> ext_cm_Startup) Then _
        Call IDTExtensibility2_OnStartupComplete(custom)
End Sub

Private Sub IDTExtensibility2_OnBeginShutdown(custom() As Variant)
    On Error Resume Next
    cbb.Delete
    Set cbb = Nothing
End Sub

'I've found that putting as many processes as possible here speeds startup.
Private Sub IDTExtensibility2_OnStartupComplete(custom() As Variant)
    Dim oCBs As Office.CommandBars
    Dim oSB As Office.CommandBar
    
    On Error Resume Next
    If oXL Is Nothing Then
        BasUtilities.BugOut 614, "Connect.OnStartupComplete", "Application reference not received"
    End If
    
    Set oCBs = oXL.CommandBars
    If oCBs Is Nothing Then
        Set oCBs = oXL.ActiveExplorer.CommandBars
    End If
    
    Set oSB = oCBs.Item("Standard")
    
    Set cbb = oSB.Controls.Item("eGWOSync")
    If cbb Is Nothing Then
        Set cbb = oSB.Controls.Add(1)
        With cbb
            .Caption = "eGWOSync"
            .Style = msoButtonCaption
            .Tag = "eGWOSync"
            .OnAction = "!<" & AddinInstance.ProgId & ">"
            .Visible = True
        End With
    End If
    If cbb Is Nothing Then
        BasUtilities.BugOut 614, "Connect.OnStartupComplete", "Button could not be found or added"
    End If
    
    Set oCBs = Nothing
    Set oSB = Nothing

    Set Master = New CeGWOSyncMaster
End Sub

Private Sub IDTExtensibility2_OnDisconnection(ByVal RemoveMode As _
    AddInDesignerObjects.ext_DisconnectMode, custom() As Variant)
    On Error Resume Next
    
    If RemoveMode <> ext_dm_HostShutdown Then _
        Call IDTExtensibility2_OnBeginShutdown(custom)
    Set oXL = Nothing
End Sub

Private Sub IDTExtensibility2_OnAddInsUpdate(custom() As Variant)
    'No need to do anything but hold a place to satisfy implementation requirements :)
End Sub

Private Sub cbb_Click(ByVal Ctrl As Office.CommandBarButton, _
    CancelDefault As Boolean)
    Master.OpenMain
End Sub

' Add a new Prop Page to the Tools/Options prop page
' and set caption to "eGWOSync Settings"
' it uses the User Control page ctlMainOptions

Private Sub oXL_OptionsPagesAdd(ByVal pages As Outlook.PropertyPages)
        
    pages.Add "PropertyPage.ctlMainOptions", "eGWOSync Settings"
End Sub


