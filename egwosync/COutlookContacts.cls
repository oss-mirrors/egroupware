VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "COutlookContacts"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False


'This software is Licensed Public Domain. This software is provided 'as-is'.
'I assume no responsibility for its usage or losses of any kind that may
'ensue thereof or otherwise. Feedback would be nice.
'chris.carter[at]ebunda.com
Option Explicit

Public Enum FolderType
    clFolderDeletedItems = 3
    clFolderOutbox = 4
    clFolderSentMail = 5
    clFolderInbox = 6
    clFolderCalendar = 9
    clFolderContacts = 10
    clFolderJournal = 11
    clFolderNotes = 12
    clFolderTasks = 13
    clFolderDrafts = 16
End Enum

Public Enum FieldType
    LastName = 1
    FirstName = 2
    JobTitle = 3
    'FileAs = 4
    Company = 5
    BusinessStreet = 6
    BusinessStreet2 = 7
    BusinessCity = 8
    BusinessState = 9
    BusinessCountry = 10
    'CompanyMainPhone = 11
    BusinessPhone = 12
    BusinessPhone2 = 13
    BusinessFax = 14
    OtherFax = 15
    HomePhone = 16
    HomePhone2 = 17
    PrimaryPhone = 18
    HomeFax = 19
    MobilePhone = 20
    WebPage = 21
    Email1Address = 22
    'Email2Address = 23
    'Email3Address = 24
End Enum

Dim olApp As Outlook.Application
Dim olNs As Outlook.NameSpace
Dim olFolders As Outlook.MAPIFolder 'Points to Parent root folder
Dim olFolder As Outlook.MAPIFolder 'Your folder to operate on
Dim iFolderItems As Integer 'RecordCount
Dim iFolderItemIdx As Integer 'Cursor location
Dim olItems As Outlook.Items 'Items of a folder
Dim bConnected As Boolean 'True if connected
Dim m As ContactItem 'The selected contact record
Dim mvarFldIdx As Long

Public Function Connect() As Boolean
    On Error GoTo Err_Connect
    
    Set olApp = CreateObject("Outlook.Application")
    Set olNs = olApp.GetNamespace("MAPI")
    Set olFolders = olNs.GetDefaultFolder(olFolderInbox).Parent
    iFolderItems = 0
    iFolderItemIdx = 0
    mvarFldIdx = 0
    bConnected = True
    Connect = True
    
Exit_Connect:
    Exit Function

Err_Connect:
    Connect = False
    Resume Exit_Connect
End Function

Public Function CreateFolder(szFolderName As String, Optional lFolderType As FolderType = clFolderInbox) As Boolean
    On Error GoTo Err_CreateFolder
    
    CreateFolder = False
    If bConnected Then
        If Not DoesFolderExist(szFolderName) Then
            Set olFolder = olFolders.Folders.Add(szFolderName, lFolderType)
            CreateFolder = True
        End If
    End If
    
Exit_CreateFolder:
    Exit Function
    
Err_CreateFolder:
    CreateFolder = False
    Resume Exit_CreateFolder
End Function

Public Function DoesFolderExist(szFolder As String) As Boolean
    On Error GoTo Err_DoesFolderExist
    
    If bConnected Then
        Set olFolder = olFolders.Folders(szFolder)
        DoesFolderExist = True
    Else
        DoesFolderExist = False
    End If
    
Exit_DoesFolderExist:
    Exit Function
    
Err_DoesFolderExist:
    DoesFolderExist = False
    Resume Exit_DoesFolderExist
End Function

Public Function DeleteFolder(szFolder As String) As Boolean
    On Error GoTo Err_DeleteFolder
    
    DeleteFolder = False
    
    If bConnected Then
        If DoesFolderExist(szFolder) Then
            Set olFolder = olFolders.Folders(szFolder)
            olFolder.Delete
            DeleteFolder = True
        End If
    End If
    
Exit_DeleteFolder:
    Exit Function
    
Err_DeleteFolder:
    DeleteFolder = False
    Resume Exit_DeleteFolder
End Function

Public Function OpenFolder(szFolder As String) As Boolean
    On Error GoTo Err_OpenFolder
    
    OpenFolder = False
    If bConnected Then
        If DoesFolderExist(szFolder) Then
            Set olFolder = olFolders.Folders(szFolder)
            Set olItems = olFolder.Items
            iFolderItems = olItems.Count
            iFolderItemIdx = 1
            OpenFolder = True
        End If
    End If
    
Exit_OpenFolder:
    Exit Function
    
Err_OpenFolder:
    OpenFolder = False
    Resume Exit_OpenFolder
End Function

Public Property Get RecordCount() As Integer
    RecordCount = iFolderItems
End Property

Public Property Get BOF() As Boolean
    BOF = False
    If iFolderItemIdx = 1 Then
        BOF = True
    End If
End Property

Public Property Get EOF() As Boolean
    EOF = False
    If iFolderItemIdx > iFolderItems Then
        iFolderItemIdx = iFolderItems
        EOF = True
    End If
End Property

Public Function MoveFirst()
    'Move cursor to first record in array
    iFolderItemIdx = 1
End Function

Public Function MoveLast()
    'Move cursor to last record in array
    iFolderItemIdx = iFolderItems
End Function

Public Function MoveNext()
    'Move cursor to next record in array
    If iFolderItemIdx <= iFolderItems Then
        iFolderItemIdx = iFolderItemIdx + 1
    End If
End Function

Public Function MovePrevious()
    'move cursor to previous record in array
    If iFolderItemIdx > 1 Then
        iFolderItemIdx = iFolderItemIdx - 1
    End If
End Function

Public Function MoveAbs(lPos As Long)
    'Move cursor to absolute record number
    If ((lPos >= 1) And (lPos <= iFolderItems)) Then
        iFolderItemIdx = lPos
    End If
End Function

'Sets/Return the value of the field requested
Public Function Field(fld As FieldType, Optional idx As Variant) As Variant
    
    Dim szFileName As String
    
    Set m = olItems(iFolderItemIdx)
    
    If IsMissing(idx) Then
        'Return the field
        Select Case fld
            Case LastName
                Field = m.LastName
            Case FirstName
                Field = m.FirstName
            Case JobTitle
                Field = m.JobTitle
            Case Company
                Field = m.CompanyName
            Case BusinessStreet
                Field = m.BusinessAddress
            Case BusinessStreet2
                Field = m.BusinessAddressStreet
            Case BusinessCity
                Field = m.BusinessAddressCity
            Case BusinessState
                Field = m.BusinessAddressState
            Case BusinessCountry
                Field = m.BusinessAddressCountry
            Case BusinessPhone
                Field = m.BusinessTelephoneNumber
            Case BusinessPhone2
                Field = m.Business2TelephoneNumber
            Case BusinessFax
                Field = m.BusinessFaxNumber
            Case Email1Address
                Field = m.Email1Address
        End Select
    Else
        'Set the field
        Select Case fld
            Case LastName
                m.LastName = idx
            Case FirstName
                m.FirstName = idx
            Case JobTitle
                m.JobTitle = idx
        End Select
        m.Save
    End If
End Function
