VERSION 5.00
Begin VB.UserControl ctlMainOptions 
   ClientHeight    =   5250
   ClientLeft      =   0
   ClientTop       =   0
   ClientWidth     =   6900
   ScaleHeight     =   5250
   ScaleWidth      =   6900
   Begin VB.Frame Frame1 
      Caption         =   "Settings - Dont work yet :-)"
      Height          =   2055
      Left            =   240
      TabIndex        =   0
      Top             =   240
      Width           =   6375
      Begin VB.TextBox txtURI 
         Height          =   375
         Left            =   1080
         TabIndex        =   10
         Text            =   "/egroupware/xmlrpc.php"
         Top             =   1320
         Width           =   5055
      End
      Begin VB.TextBox txtHostname 
         Height          =   375
         Left            =   3000
         TabIndex        =   9
         Top             =   840
         Width           =   3135
      End
      Begin VB.TextBox txtPasswd 
         Height          =   375
         Left            =   3840
         TabIndex        =   8
         Top             =   360
         Width           =   2295
      End
      Begin VB.TextBox txtPort 
         Height          =   375
         Left            =   1080
         TabIndex        =   7
         Text            =   "80"
         Top             =   840
         Width           =   735
      End
      Begin VB.TextBox txtUserName 
         Height          =   405
         Left            =   1080
         TabIndex        =   6
         Top             =   360
         Width           =   1695
      End
      Begin VB.Label Label5 
         Caption         =   "Hostname"
         Height          =   255
         Left            =   2040
         TabIndex        =   5
         Top             =   840
         Width           =   1095
      End
      Begin VB.Label Label4 
         Caption         =   "Password"
         Height          =   255
         Left            =   2880
         TabIndex        =   4
         Top             =   360
         Width           =   975
      End
      Begin VB.Label Label3 
         Caption         =   "URI"
         Height          =   255
         Left            =   240
         TabIndex        =   3
         Top             =   1320
         Width           =   975
      End
      Begin VB.Label Label2 
         Caption         =   "Port"
         Height          =   255
         Left            =   240
         TabIndex        =   2
         Top             =   840
         Width           =   855
      End
      Begin VB.Label Label1 
         Caption         =   "Username"
         Height          =   255
         Left            =   240
         TabIndex        =   1
         Top             =   360
         Width           =   1215
      End
   End
End
Attribute VB_Name = "ctlMainOptions"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
'#################################################################################################
'# ctlMainOptions.ctl
'# This is the code for the PropertyPage in Settings
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# hyber[at]hyber.dk
'#################################################################################################


Implements Outlook.PropertyPage

Private oSite As Outlook.PropertyPageSite
Private boolInitializing As Boolean
Dim m_fDirty As Boolean
Dim m_AdminDLL As Object

Private Sub UserControl_InitProperties()
    On Error Resume Next
    Set oSite = Parent
End Sub

'if we changed anything in eGWOSync Settings, we set the for dirty
'so and therefor enable the apply button.
Private Sub SetDirty()
    If Not oSite Is Nothing Then
        m_fDirty = True
        oSite.OnStatusChange
    End If
End Sub

'This happens when we press the OK or apply button.
Private Sub PropertyPage_Apply()
    On Error GoTo PropertyPageApply_Err
    m_fDirty = False
    MsgBox ("didn't it say that this don't work???")
    Exit Sub
'Some debug error code for the user, I didn't write it and have
'never seen it in function...
PropertyPageApply_Err:
    MsgBox "Error in PropertyPage_Apply.    Err# " & _
        Err.Number & " and Err Description: " & Err.Description
End Sub

Private Property Get PropertyPage_Dirty() As Boolean
    PropertyPage_Dirty = m_fDirty
End Property

'This one we need, it wount compile without it. It's the help file
'for this property page. You know when press the little "?" next
'to the "x" and then clicks on somethings. It just says "No help here"
Private Sub PropertyPage_GetPageInfo(HelpFile As String, _
    HelpContext As Long)

    HelpFile = "nothing.hlp"
    HelpContext = 102
End Sub

'On change we set the propertypage dirty
Private Sub txtHostname_Change()
SetDirty
End Sub

'On change we set the propertypage dirty
Private Sub txtPasswd_Change()
SetDirty
End Sub

'On change we set the propertypage dirty
Private Sub txtPort_Change()
SetDirty
End Sub

'On change we set the propertypage dirty
Private Sub txtURI_Change()
SetDirty
End Sub

'On change we set the propertypage dirty
Private Sub txtUserName_Change()
SetDirty
End Sub

Private Sub UserControl_EnterFocus()
    boolInitializing = False
End Sub

Private Sub UserControl_Initialize()
    m_fDirty = False
    boolInitializing = True
End Sub



