VERSION 5.00
Begin {AC0714F6-3D04-11D1-AE7D-00A0C90F26F4} FrmConnect 
   ClientHeight    =   7755
   ClientLeft      =   1740
   ClientTop       =   1545
   ClientWidth     =   4905
   _ExtentX        =   8652
   _ExtentY        =   13679
   _Version        =   393216
   Description     =   "eGWOSync Test Add-In Description...."
   DisplayName     =   "eGWOSync Test Add-In"
   AppName         =   "Microsoft Outlook"
   AppVer          =   "Microsoft Outlook 10.0"
   LoadName        =   "Startup"
   LoadBehavior    =   3
   RegLocation     =   "HKEY_CURRENT_USER\Software\Microsoft\Office\Outlook"
End
Attribute VB_Name = "FrmConnect"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
'#################################################################################################
'# FrmConnect.Dsr
'# The COM Add-In handle for Outlook.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# hyber[at]hyber.dk
'#################################################################################################


Implements IDTExtensibility2

Dim WithEvents oApp As Outlook.Application
Attribute oApp.VB_VarHelpID = -1
Dim oCB As Office.CommandBarButton
Dim oCBs As Office.CommandBars
Dim oMenuBar As Office.CommandBar
Dim oFolder As Outlook.MAPIFolder
Dim WithEvents oNS As Outlook.NameSpace
Attribute oNS.VB_VarHelpID = -1

' Not completly sure that this does but it dont work
' without it, do you know Ian??
Dim WithEvents oSettingsCB As Office.CommandBarButton
Attribute oSettingsCB.VB_VarHelpID = -1
Dim WithEvents oCloseCB As Office.CommandBarButton
Attribute oCloseCB.VB_VarHelpID = -1
Dim WithEvents oSyncCB As Office.CommandBarButton
Attribute oSyncCB.VB_VarHelpID = -1



' Use this subroutine when Add-ins are updated.
Private Sub IDTExtensibility2_OnAddInsUpdate(custom() As Variant)
    'just here so that compiler dont remove this Sub
    'It wount compile with out it.
End Sub

' Use this subroutine when the host app is shutting down.
' You should persist or destroy your objects in this
' subroutine.
Private Sub IDTExtensibility2_OnBeginShutdown(custom() As Variant)
    
'        MsgBox "OnBeginShutdown called"
    On Error Resume Next
    
    Set oApp = Nothing
    Set oCBs = Nothing
    Set oMenuBar = Nothing
    Set oMyCB = Nothing
    Set oNS = Nothing
    Set oCB = Nothing
    Set oResetCB = Nothing
    Set oFolder = Nothing
End Sub
    
' This subroutine is called when your Add-in is connected
' to by the host application.
Private Sub IDTExtensibility2_OnConnection( _
    ByVal Application As Object, ByVal ConnectMode As _
    AddInDesignerObjects.ext_ConnectMode, _
    ByVal AddInInst As Object, custom() As Variant)
   

    ' Get the Application object for Outlook.
    Set oApp = Application
    
'************************************************************************
'we dont use this right now, perhaps later, please dont delete.
    
    ' Get the Namespace.
'    Set oNS = oApp.GetNamespace("MAPI")
    ' Get a Folder to extend with the PropPage extension.
    ' Let the user pick the folder.
'      Set oFolder = oNS.PickFolder()
'************************************************************************

    'Customize the Outlook Menu structure and toolbar.
    Set oCBs = oApp.ActiveExplorer.CommandBars
    Set oMenuBar = oCBs.Add("eGWOSync", msoBarTop, , True)
    oMenuBar.Visible = True

    
    'Create the main button, and name it eGWOSync
    Set oMycontrol = oMenuBar.Controls.Add( _
        msoControlPopup, , , , False)
    oMycontrol.Caption = "&eGWOSync"
    
    'Adding and enable the "Close Menu" tab in toolbar.
    Set oCloseCB = oMycontrol.Controls.Add( _
        Type:=msoControlButton, Temporary:=True, Before:=1)
    oCloseCB.Caption = "&Close Menu"
    oCloseCB.Enabled = True
    
    'Adding and enable the "Settings" tab in toolbar.
    Set oSettingsCB = oMycontrol.Controls.Add( _
        Type:=msoControlButton, Temporary:=True, Before:=1)
    oSettingsCB.Caption = "&Settings"
    oSettingsCB.Enabled = True
    
    'Adding and enable the "Sync" tab in toolbar.
    Set oSyncCB = oMycontrol.Controls.Add( _
        Type:=msoControlButton, Temporary:=True, Before:=1)
    oSyncCB.Caption = "&Sync"
    oSyncCB.Enabled = True
    
    '************************************************************************
    'loading the eGWOSync settings. Hey Ian is this the right way or what???
    '************************************************************************
    ThisOutlookSession.Application_Startup
        
End Sub
    
' This Sub is called when your add-in is
' disconnected from the host.
Private Sub IDTExtensibility2_OnDisconnection( _
    ByVal RemoveMode As _
        AddInDesignerObjects.ext_DisconnectMode, _
    custom() As Variant)
End Sub
    
' This Sub is called when the host application has
' completed its startup routines.
Private Sub IDTExtensibility2_OnStartupComplete( _
    custom() As Variant)
    'just here so that compiler dont remove this Sub
    'It wount compile with out it.
End Sub
    
'************************************************************************
'we dont use this right now, perhaps later, please dont delete.
'It's for adding a PropertyPage to the folders, may come in handy

'Private Sub oNS_OptionsPagesAdd( _
'    ByVal Pages As Outlook.PropertyPages, _
'    ByVal Folder As Outlook.MAPIFolder)
'    If Folder.Name = oFolder.Name Then
'        ' Add in the Options page to the folder.
'        'Set oNewPage = CreateObject("TestPropPage.PropPage")
'        'Pages.Add oNewPage
'        Pages.Add "PropertyPage.options", "eGWOSync Settings"
'        'Pages.Add ctloptions.Name(), "TEST"
'    End If
'End Sub
'************************************************************************
    
Private Sub oApp_OptionsPagesAdd( _
    ByVal Pages As Outlook.PropertyPages)

    ' Add a new Prop Page to the Tools/Options prop page
    ' and set caption to "eGWOSync Settings"
    ' it uses the User Control page ctlMainOptions
    Pages.Add "PropertyPage.ctlMainOptions", "eGWOSync Settings"
    
End Sub
    
'This is what happens when Close are pressed in the Toolbar menu
Private Sub oCloseCB_Click( _
    ByVal Ctrl As Office.CommandBarButton, _
    CancelDefault As Boolean)
    
    oMenuBar.Delete
End Sub

'This is what happens when Settings are pressed in the Toolbar menu
Private Sub oSettingsCB_Click( _
    ByVal Ctrl As Office.CommandBarButton, _
    CancelDefault As Boolean)
    
    FrmMain.Show
    FrmMain.MultiPage1.Value = 1
End Sub

'This is what happens when Sync are pressed in the Toolbar menu
Private Sub oSyncCB_Click( _
    ByVal Ctrl As Office.CommandBarButton, _
    CancelDefault As Boolean)

    FrmMain.Show
    FrmMain.MultiPage1.Value = 0
End Sub

