VERSION 5.00
Begin {C62A69F0-16DC-11CE-9E98-00AA00574A4F} FrmLogin 
   Caption         =   "Login"
   ClientHeight    =   2055
   ClientLeft      =   45
   ClientTop       =   315
   ClientWidth     =   5970
   OleObjectBlob   =   "FrmLogin.frx":0000
   StartUpPosition =   1  'CenterOwner
End
Attribute VB_Name = "FrmLogin"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = True
Attribute VB_Exposed = False
'#################################################################################################
'# FrmLogin.frm & .frx
'# A form that can be shown if login information is missing.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Public Helper As New CFormHelper

Private Sub cmdLogin_Click()
    Helper.PutSettings
    Helper.SaveSettings
    Me.Hide
End Sub

Private Sub UserForm_Initialize()
    Set Helper.Parent = Me
    'load previous settings
    Helper.LoadSettings
End Sub
