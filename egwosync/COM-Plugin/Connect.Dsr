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
Option Explicit

Private oXL As Outlook.Application
Private WithEvents btSync As Office.CommandBarButton
Attribute btSync.VB_VarHelpID = -1

Private Sub AddinInstance_OnConnection(ByVal Application As Object, _
    ByVal ConnectMode As AddInDesignerObjects.ext_ConnectMode, _
    ByVal AddInInst As Object, custom() As Variant)
    
    On Error Resume Next
    Set oXL = Application
    
    'Create a button only if there wasn't one there already.
    If oXL.ActiveExplorer.CommandBars("Standard").Controls.Item("eGroupWare Sync Main") Is Nothing Then
        Set btSync = oXL.ActiveExplorer.CommandBars("Standard").Controls.Add(1)
    
        With btSync
            .Caption = "eGroupWare Sync Main"
            .Style = msoButtonCaption

            'this helps to keep track of the button
            .Tag = "eGroupWare Sync Main"
            
            .OnAction = "!<" & AddInInst.ProgId & ">"
            .Visible = True
        End With
    Else
        Set btSync = oXL.ActiveExplorer.CommandBars("Standard").Controls.Item("eGroupWare Sync Main")
    End If
End Sub

Private Sub AddinInstance_OnBeginShutdown(custom() As Variant)
    btSync.Delete
    Set btSync = Nothing
    Set oXL = Nothing
End Sub

'I've found that putting as many processes as possible here speeds startup.
Private Sub AddinInstance_OnStartupComplete(custom() As Variant)
    Set Master = New CeGWOSyncMaster
End Sub

Private Sub AddinInstance_OnDisconnection(ByVal RemoveMode As _
    AddInDesignerObjects.ext_DisconnectMode, custom() As Variant)
    On Error Resume Next
    
    btSync.Delete
    Set btSync = Nothing
    Set oXL = Nothing
End Sub

Private Sub btSync_Click(ByVal Ctrl As Office.CommandBarButton, _
    CancelDefault As Boolean)
    Master.OpenMain
End Sub

Private Sub oApp_OptionsPagesAdd( _
    ByVal Pages As Outlook.PropertyPage)

    ' Add a new Prop Page to the Tools/Options prop page
    ' and set caption to "eGWOSync Settings"
    ' it uses the User Control page ctlMainOptions
    Pages.Add "PropertyPage.ctlMainOptions", "eGWOSync Settings"
    
End Sub

