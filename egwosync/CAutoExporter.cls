VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "CAutoExporter"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'#################################################################################################
'# CAutoExporter.cls
'# Uploads contacts to the eGroupWare server automatically upon creation.
'#
'# Please visit egroupware.org for more information
'# This software is distributed under the GPL and is provided as-is. I assume no responsibility
'# for its usage or losses of any kind that may ensue thereof or otherwise. Feedback is nice:
'# heisters[at]0x09.com
'#################################################################################################

Dim myOlApp As New Outlook.Application
Public WithEvents contactItems As Outlook.Items
Attribute contactItems.VB_VarHelpID = -1

'***********************************************************************************************
' Start by getting the default contact directory's items
'***********************************************************************************************
Public Sub Initialize_handler()
    Set contactItems = myOlApp.GetNamespace("MAPI").GetDefaultFolder(olFolderContacts).Items
End Sub

'***********************************************************************************************
' Responds to a contact getting added to the default contact directory by asking whether to add
' the contact to the server, and then, or course, doing so if the user says OK.
'***********************************************************************************************
Private Sub contactItems_ItemAdd(ByVal Item As Object)
    'This will only work if the main egwosync window isn't open!
    If Not frmMain.Visible Then
        Dim egwContacts As New CeGWContacts
        Dim msgResponse As VbMsgBoxResult
        Dim xmlResponse As XMLRPCResponse
        Dim Contact     As ContactItem
        
        Set Contact = Item
        
        msgResponse = MsgBox("Add " & Contact.FullName & " to the remote server?", vbOKCancel, _
                            "Add Contact Confirmation")
        
        If msgResponse = vbOK Then
            If ThisOutlookSession.eGW.Hostname = "" Then
                FrmLogin.Show
            End If
            Set xmlResponse = egwContacts.Create(Contact)
            If xmlResponse.Status = XMLRPC_PARAMSRETURNED Then
                Debug.Print Contact.FullName & " was added to the remote server successfully"
            Else
                Debug.Print Contact.FullName & " was NOT added to the remote server. Response Status: " & _
                            xmlResponse.Status
            End If
        End If
    End If
End Sub
