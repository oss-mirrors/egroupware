VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmRename 
   Caption         =   "Rename"
   ClientHeight    =   2940
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   8175
   Icon            =   "FrmRename.dsx":0000
   OleObjectBlob   =   "FrmRename.dsx":08CA
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "FrmRename"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# FrmRename.frm & .frx
'# Control for renaming contacts in the case of duplicates.
'# NOTE: this form should only be created from FrmOverwrite. It is a kind of sub-form, but VBA
'#       doesn't provide an official way of doing that.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Private myParent                As UserForm
Private myGrandParent           As COverwriteManager
Private myNewContactNames       As New Collection
Private myOriginalContactNames  As New Collection

Public Property Get Parent() As UserForm
    Set Parent = myParent
End Property

Public Property Set Parent(p As UserForm)
    Set myParent = p
End Property

Public Property Get GrandParent() As COverwriteManager
    Set GrandParent = myGrandParent
End Property

Public Property Set GrandParent(p As COverwriteManager)
        Set myGrandParent = p
End Property

Property Get NewContactNames() As Collection
    Set NewContactNames = myNewContactNames
End Property

Property Get OriginalContactNames() As Collection
    Set OriginalContactNames = myOriginalContactNames
End Property

Private Sub cmdCancel_Click()
    Set FrmOverwrite.Parent = GrandParent
    Me.Visible = False
End Sub

Private Sub cmdOK_Click()
    Dim str As String
    
    Set myOriginalContactNames = New Collection
    Set myNewContactNames = New Collection
    
    str = txtOPrefix.Text & " " & txtOFirst.Text & " " & txtOMiddle.Text & " " & txtOLast.Text & " " & txtOSuffix.Text
    With myOriginalContactNames
        .Add txtOPrefix.Text, "Prefix"
        .Add txtOFirst.Text, "FirstName"
        .Add txtOMiddle.Text, "MiddleName"
        .Add txtOLast.Text, "LastName"
        .Add txtOSuffix.Text, "Suffix"
        .Add str, "FullName"
    End With
    
    str = txtNPrefix.Text & " " & txtNFirst.Text & " " & txtNMiddle.Text & " " & txtNLast.Text & " " & txtNSuffix.Text
    With myNewContactNames
        .Add txtNPrefix.Text, "Prefix"
        .Add txtNFirst.Text, "FirstName"
        .Add txtNMiddle.Text, "MiddleName"
        .Add txtNLast.Text, "LastName"
        .Add txtNSuffix.Text, "Suffix"
        .Add str, "FullName"
    End With
    Me.Visible = False
    FrmOverwrite.Visible = False
End Sub

Private Sub UserForm_Activate()
    Set myOriginalContactNames = Nothing
    Set myNewContactNames = Nothing
    Set myOriginalContactNames = GrandParent.OriginalContactNames
    Set myNewContactNames = GrandParent.NewContactNames
    
    txtNPrefix.Text = myNewContactNames.Item("Prefix")
    txtNFirst.Text = myNewContactNames.Item("FirstName")
    txtNMiddle.Text = myNewContactNames.Item("MiddleName")
    txtNLast.Text = myNewContactNames.Item("LastName")
    txtNSuffix.Text = myNewContactNames.Item("Suffix")
    
    txtOPrefix.Text = myOriginalContactNames.Item("Prefix")
    txtOFirst.Text = myOriginalContactNames.Item("FirstName")
    txtOMiddle.Text = myOriginalContactNames.Item("MiddleName")
    txtOLast.Text = myOriginalContactNames.Item("LastName")
    txtOSuffix.Text = myOriginalContactNames.Item("Suffix")
End Sub

'***************************************************************************************
' If the user closes the form without using my beautiful buttons, it's the same as
' choosing cancel.
'***************************************************************************************
Private Sub UserForm_QueryClose(Cancel As Integer, CloseMode As Integer)
    If CloseMode = vbFormControlMenu Then
        Me.Visible = False
        Set FrmOverwrite.Parent = GrandParent
        FrmOverwrite.Show
    End If
End Sub
