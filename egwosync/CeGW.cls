VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
END
Attribute VB_Name = "CeGW"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = False
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = False
'This software is Licensed Public Domain. This software is provided 'as-is'.
'I assume no responsibility for its usage or losses of any kind that may
'ensue thereof or otherwise. Feedback would be nice.
'chris.carter[at]ebunda.com
Private linsResponse As XMLRPCResponse
Private mHostname As String
Private mPort As String
Private mURI As String
Private mDomain As String
Private mUsername As String
Private mPassword As String
Private mSessid As String
Private mKp3 As String

Private Sub Class_Initialize()
    Debug.Print "eGW Connector class initialized"
    mPort = 80
    mURI = "xmlrpc.php"
    mDomain = "default"
    Reset
End Sub

Private Sub Class_Terminate()
    Debug.Print "eGW Connector class terminated"
End Sub

Public Function Reset()
    Set linsResponse = Nothing
    Set linsResponse = New XMLRPCResponse
End Function

Public Property Let Hostname(szHostname As String)
    mHostname = szHostname
End Property

Public Property Get Hostname() As String
    Hostname = mHostname
End Property

Public Property Let Port(szPort As String)
    mPort = szPort
End Property

Public Property Get Port() As String
    Port = mPort
End Property

Public Property Let URI(szURI As String)
    mURI = szURI
End Property

Public Property Get URI() As String
    URI = mURI
End Property

Public Property Let Domain(szDomain As String)
    mDomain = szDomain
End Property

Public Property Get Domain() As String
    Domain = mDomain
End Property

Public Property Let Username(szUsername As String)
    mUsername = szUsername
End Property

Public Property Get Username() As String
    Username = mUsername
End Property

Public Property Let Password(szPass As String)
    mPassword = szPass
End Property

Public Property Get Password() As String
    Password = mPassword
End Property

Public Property Get Response() As XMLRPCResponse
    Set Response = linsResponse
End Property

Public Function Login() As Boolean
    Dim linsUtility As New XMLRPCUtility
    Dim linsStruct As XMLRPCStruct
    
    Set linsStruct = New XMLRPCStruct
    
    linsStruct.AddString "domain", mDomain
    linsStruct.AddString "username", mUsername
    linsStruct.AddString "password", mPassword
    
    Exec "system.login", linsStruct
    
    If linsResponse.Status <> XMLRPC_PARAMSRETURNED Then
        BugOut "Unexpected response from XML-RPC request " & linsResponse.Status
    ElseIf linsResponse.params.Count <> 1 Then
        BugOut "Unexpected response from XML-RPC request " & linsResponse.params.Count & " return parameters, expecting 1"
    ElseIf linsResponse.params(1).ValueType <> XMLRPC_STRUCT Then
        BugOut "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(linsResponse.params(1).ValueType) & " returned, expecting an array"
    End If
    For Each linsMember In linsResponse.params(1).StructValue
        If linsMember.Name = "sessionid" Then
            mSessid = linsMember.Value.StringValue
            Login = True
            Debug.Print "Login successful"
        ElseIf linsMember.Name = "kp3" Then
            mKp3 = linsMember.Value.StringValue
        Else
            Login = False
        End If
    Next linsMember
End Function

Public Function Logout() As Boolean
    Dim linsStruct As XMLRPCStruct
    Dim linsUtility As XMLRPCUtility
        
    Set linsStruct = New XMLRPCStruct
    
    linsStruct.AddString "sessionid", mSessid
    linsStruct.AddString "kp3", mKp3
    
    Exec "system.logout", linsStruct
    
    If linsResponse.Status <> XMLRPC_PARAMSRETURNED Then
        BugOut "Unexpected response from XML-RPC request " & linsResponse.Status
    ElseIf linsResponse.params.Count <> 1 Then
        BugOut "Unexpected response from XML-RPC request " & linsResponse.params.Count & " return parameters, expecting 1"
    ElseIf linsResponse.params(1).ValueType <> XMLRPC_STRUCT Then
        BugOut "Unexpected response from XML-RPC request " & linsUtility.GetXMLRPCType(linsResponse.params(1).ValueType) & " returned, expecting an array"
    End If
    
    For Each linsMember In linsResponse.params(1).StructValue
        If linsMember.Name = "GOODBYE" Then
            Logout = True
            Debug.Print "Logout successful"
        End If
    Next linsMember
End Function

Public Function Exec(szMethod As String, Optional xmlParams) As Boolean
    On Error GoTo Err_Exec
    
    Dim linsRequest As New XMLRPCRequest

    linsRequest.Hostname = mHostname
    linsRequest.HostPort = mPort
    linsRequest.HostURI = mURI
    
    linsRequest.methodName = szMethod
    
    If Not IsMissing(xmlParams) Then
        linsRequest.params.AddStruct xmlParams
    End If
    
    Set linsResponse = linsRequest.Submit
    Exec = True
    
Exit_Exec:
    Exit Function
    
Err_Exec:
    Exec = False
    Resume Exit_Exec
End Function

Private Sub BugOut(ByVal vstrError As String)
    MsgBox vstrError, vbOKOnly + vbCritical, App.Title
    End
End Sub


