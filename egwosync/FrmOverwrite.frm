VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmOverwrite 
   Caption         =   "Contact Name Conflict"
   ClientHeight    =   1575
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   3675
   OleObjectBlob   =   "FrmOverwrite.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "FrmOverwrite"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# FrmOverwrite.frm & .frx
'# Provides the user with a means of controlling what is done when duplicate contacts exist.
'# Note, should be private to COverwriteManager, but VBA doesn't offer a way of doing that that
'# I know of.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Private RetVal      As Integer
Private myParent    As COverwriteManager

Public Property Get Parent() As COverwriteManager
    Set Parent = myParent
End Property

Public Property Set Parent(p As COverwriteManager)
    Set myParent = p
End Property

Public Property Get OVERWRITE() As Integer
    OVERWRITE = 1
End Property

Public Property Get SKIP() As Integer
    SKIP = 2
End Property

Public Property Get RENAME() As Integer
    RENAME = 3
End Property

Public Property Get Choice() As Integer
    Choice = RetVal
End Property

Private Sub cmdOverwrite_Click()
    RetVal = OVERWRITE
    Me.Hide
End Sub

Private Sub cmdSkip_Click()
    RetVal = SKIP
    Me.Hide
End Sub

Private Sub cmdRename_Click()
    RetVal = RENAME
    Me.Hide
    Set FrmRename.Parent = Me
    Set FrmRename.GrandParent = Me.Parent
    FrmRename.Show
End Sub

Private Sub UserForm_Activate()
    RetVal = 0
    txtName.Text = Parent.NewContactNames.Item("FullName")
End Sub

Public Sub OverwriteFormError()
    On Error Resume Next
    Err.Clear
    Err.Raise vbObjectError + 600, "FrmOverwrite", "Contact information not defined for " & _
                                    "overwrite protection"
    Dim Msg As String
    If Err.Number <> 0 Then
        Msg = "Error # " & str(Err.Number) & " was generated by " _
                & Err.Source & Chr(13) & Err.Description
        MsgBox Msg, vbOKOnly, "Error"
    End If
End Sub
