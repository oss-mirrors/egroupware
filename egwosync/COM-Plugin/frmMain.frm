VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmMain 
   Caption         =   "eGroupWare Synchronization"
   ClientHeight    =   9285
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5925
   OleObjectBlob   =   "frmMain.dsx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "FrmMain"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# FrmMain.frm & .frx
'# The main control window for mass uploading and downloading of contact information
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Public Helper As New CFormHelper

'***********************************************************************************************
' Applies and saves settings
'***********************************************************************************************
Private Sub cmdApply_Click()
    Helper.SaveSettings
    Helper.PutSettings
    ThisOutlookSession.RefreshSettingStatus
End Sub

Private Sub cmdCancel_Click()
    Me.Hide
End Sub

'***********************************************************************************************
' Display the names of contacts in the remote and local directories
'***********************************************************************************************
Private Sub cmdGet_Click()
    BasUtilities.GetContacts
End Sub

Private Sub cmdOK_Click()
    Helper.SaveSettings
    Helper.PutSettings
    ThisOutlookSession.RefreshSettingStatus
    Me.Hide
End Sub

'***********************************************************************************************
' Add selected contacts to the other contact directory
'***********************************************************************************************
Private Sub cmdSynchronize_Click()
    BasUtilities.SynchronizeContacts
End Sub

'***********************************************************************************************
' Set things up for frmMain
'***********************************************************************************************
Private Sub UserForm_Initialize()
    Set Helper.Parent = Me
    'load previous settings
    Helper.LoadSettings
    Helper.PutSettings
    ThisOutlookSession.RefreshSettingStatus
End Sub
