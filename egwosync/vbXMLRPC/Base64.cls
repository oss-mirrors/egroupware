VERSION 1.0 CLASS
BEGIN
  MultiUse = -1  'True
  Persistable = 0  'NotPersistable
  DataBindingBehavior = 0  'vbNone
  DataSourceBehavior  = 0  'vbNone
  MTSTransactionMode  = 0  'NotAnMTSObject
END
Attribute VB_Name = "XMLRPCBase64"
Attribute VB_GlobalNameSpace = False
Attribute VB_Creatable = True
Attribute VB_PredeclaredId = False
Attribute VB_Exposed = True
Attribute VB_HelpID = 11000
Attribute VB_Description = "Models a Base64 string"
'===============================================================================
'
'  Title             : Base64.cls
'  Program           : vbXMLRPC
'  Version           : 0.9
'  Copyright         : © EnAppSys Ltd
'  Date              : 14th Oct, 2002
'  Author            : Phil Hewitt
'  Contact Address   : 52 Byelands Street,
'                      Middlesbrough,
'                      Cleveland. TS4 2HP
'                      United Kingdom
'  Contact e-mail    : support@enappsys.com
'  Technical Reviewer:
'
'  Purpose           : Base64 Helper Class
'  Notes             : Provides functions for Encoding and Decoding
'                      Base64 strings. See RFC2045.
'
'===============================================================================
'
'   This library is free software; you can redistribute it and/or
'   modify it under the terms of the GNU Lesser General Public
'   License as published by the Free Software Foundation; either
'   version 2.1 of the License, or (at your option) any later version.
'
'   This library is distributed in the hope that it will be useful,
'   but WITHOUT ANY WARRANTY; without even the implied warranty of
'   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
'   Lesser General Public License for more details.
'
'   You should have received a copy of the GNU Lesser General Public
'   License along with this library; if not, write to the Free Software
'   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
'
'===============================================================================
'
'  Modification History
'
'  Version           :
'  Date              :
'  Author            :
'  Technical Reviewer:
'  Changes           :
'
'===============================================================================

Option Explicit

Private Const MODULETITLE = APPNAME & "#Base64.cls"

'---------------------------------------------------
'   Internal encoded data
Private mstrEncodedData As String

'---------------------------------------------------
'   Internal unencoded data
Private mstrUnencodedData As String

'---------------------------------------------------
'   Internal line length before terminator applied
Private mintLineLength As Integer

'---------------------------------------------------
'   Internal line terminator
Private mstrLineTerminator As String

'---------------------------------------------------
'   Internal flag to indicate that line
'   terminator should be used
Private mblnUseLineTerminator As Boolean

'---------------------------------------------------
'   Flag to indicate needs decoding
Private mblnNeedsDecoding As Boolean

'---------------------------------------------------
'   Flag to indicate needs encoding
Private mblnNeedsEncoding As Boolean

'================================================
'   P R O P E R T I E S
'================================================

'================================================
'
'   PUBLIC UseLineTerminator
'
'   Gets/Lets a flag to indicate that line
'   terminator should be used
'
Public Property Let UseLineTerminator(UseLineTerminator As Boolean)
Attribute UseLineTerminator.VB_Description = "Specifies whether to use line termination functionality."
Attribute UseLineTerminator.VB_HelpID = 11250
    mblnUseLineTerminator = UseLineTerminator
End Property
Public Property Get UseLineTerminator() As Boolean
    UseLineTerminator = mblnUseLineTerminator
End Property

'================================================
'
'   PUBLIC LineLength
'
'   Gets/Lets the line length before a
'   terminator is applied
'
Public Property Let LineLength(ByVal LineLength As Integer)
Attribute LineLength.VB_Description = "The length of lines in the encoded Base64 string, defaults to 76"
Attribute LineLength.VB_HelpID = 11100
    mintLineLength = LineLength
End Property
Public Property Get LineLength() As Integer
    LineLength = mintLineLength
End Property

'================================================
'
'   PUBLIC LineTerminator
'
'   Gets/Lets the line terminator
'
Public Property Let LineTerminator(ByVal LineTerminator As String)
Attribute LineTerminator.VB_Description = "The terminator of lines in the Base64 encoded string, defaults to CR + LF"
Attribute LineTerminator.VB_HelpID = 11150
    mstrLineTerminator = LineTerminator
End Property
Public Property Get LineTerminator() As String
    LineTerminator = mstrLineTerminator
End Property

'================================================
'
'   PUBLIC Encoded
'
'   Gets/Lets an encoded string
'
Public Property Let Encoded(Encoded As String)
Attribute Encoded.VB_Description = "Encoded Base64 String"
Attribute Encoded.VB_HelpID = 11050
    mstrEncodedData = Encoded
    mblnNeedsDecoding = True
    mblnNeedsEncoding = False
End Property
Public Property Get Encoded() As String
    If mblnNeedsEncoding Then
        Encode
        mblnNeedsEncoding = False
    End If
    Encoded = mstrEncodedData
End Property

'================================================
'
'   PUBLIC Unencoded
'
'   Gets/Lets an unencoded string
'
Public Property Let Unencoded(Unencoded As String)
Attribute Unencoded.VB_Description = "Unencoded Base64 String"
Attribute Unencoded.VB_HelpID = 11200
    mstrUnencodedData = Unencoded
    mblnNeedsEncoding = True
    mblnNeedsDecoding = False
End Property
Public Property Get Unencoded() As String
    If mblnNeedsDecoding Then
        Unencode
        mblnNeedsDecoding = False
    End If
    Unencoded = mstrUnencodedData
End Property

'================================================
'   M E T H O D S
'
'   All PRIVATE
'================================================

'================================================
'
'   PRIVATE Class_Initialize
'
'   Class constructor, sets the line length
'   and terminator to the values in RFC2045
'
Private Sub Class_Initialize()
    mblnUseLineTerminator = False
    mintLineLength = 76
    mstrLineTerminator = vbCrLf
End Sub

'================================================
'
'   PRIVATE Encode
'
'   Encodes the passed string to a Base64 string
'
Private Sub Encode()

    Dim ldblCount As Double
    Dim lintCounter As Integer
    Dim ldblLen As Double
    Dim lstrFragment As String
    Dim lstrFagEnd As String
    Dim lintLenFagEnd As Integer
    Dim lstrEncoding As String
    
    ldblLen = Len(mstrUnencodedData)
    lintLenFagEnd = ldblLen Mod 3
    lstrFagEnd = Right$(mstrUnencodedData, lintLenFagEnd)
    ldblLen = Len(mstrUnencodedData) - lintLenFagEnd
    
    For ldblCount = 1 To ldblLen Step 3

'--------------------------------------------
'       Free up the processor
        DoEvents

        lstrFragment = EncodeTriplet(Mid$(mstrUnencodedData, ldblCount, 3))
        If mblnUseLineTerminator Then _
            AddLineTerminator lstrFragment, lintCounter
        lstrEncoding = lstrEncoding & lstrFragment
    
    Next ldblCount

'------------------------------------
'   Sort the fag end out
    lstrFragment = ""
    Select Case lintLenFagEnd
    Case 1
        lstrFagEnd = lstrFagEnd & Chr$(0) & Chr$(0)
        lstrFragment = EncodeTriplet(lstrFagEnd)
        Mid$(lstrFragment, 3, 2) = "=="
    Case 2
        lstrFagEnd = lstrFagEnd & Chr$(0)
        lstrFragment = EncodeTriplet(lstrFagEnd)
        Mid$(lstrFragment, 4, 1) = "="
    End Select
    
    If lstrFragment <> "" Then
        If mblnUseLineTerminator Then _
            AddLineTerminator lstrFragment, lintCounter
        lstrEncoding = lstrEncoding & lstrFragment
    End If
    
    mstrEncodedData = lstrEncoding

End Sub

'================================================
'
'   PRIVATE Unencode
'
'   Decodes the passed string from a Base64 string
'
'   Ignores CRs or LFs
'
Private Sub Unencode()

    Const METHODTITLE = MODULETITLE & "#Unencode"
    
    Dim ldblCount As Double
    Dim lintCounter As Integer
    Dim ldblLen As Double
    Dim lstrByte As String
    Dim lstrFragment As String
    Dim lstrDecoded As String
    
    ldblLen = Len(mstrEncodedData)
    
    For ldblCount = 1 To ldblLen

'--------------------------------------------
'       Free up the processor
        DoEvents

        lstrByte = Mid$(mstrEncodedData, ldblCount, 1)
        If lstrByte <> Chr$(10) And _
           lstrByte <> Chr$(13) Then
            lstrFragment = lstrFragment & lstrByte
        End If
            
        If Len(lstrFragment) = 4 Then
            If InStr(lstrFragment, "=") <> 0 Then
                If Right$(lstrFragment, 2) = "==" Then
                    Mid$(lstrFragment, 3, 1) = GetBase64Char(0)
                    Mid$(lstrFragment, 4, 1) = GetBase64Char(0)
                    lstrDecoded = lstrDecoded & Left$(DecodeTriplet(lstrFragment), 1)
                Else
                    Mid$(lstrFragment, 4, 1) = GetBase64Char(0)
                    lstrDecoded = lstrDecoded & Left$(DecodeTriplet(lstrFragment), 2)
                End If
            Else
                lstrDecoded = lstrDecoded & DecodeTriplet(lstrFragment)
            End If
            lstrFragment = ""
        End If
        
    Next ldblCount

    mstrUnencodedData = lstrDecoded

End Sub

'================================================
'
'   PRIVATE GetBase64Char
'
'   Gets a base64 character from the passed number
'
Private Function GetBase64Char(ByVal Number As Byte)
    GetBase64Char = Mid$("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", Number + 1, 1)
End Function

'================================================
'
'   PRIVATE GetBase64Code
'
'   Gets a base64 number from the passed character
'
Private Function GetBase64Code(ByVal Char As String)
    GetBase64Code = InStr("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", Char) - 1
End Function

'================================================
'
'   PRIVATE EncodeTriplet
'
'   Takes a ASCII 3 byte triplet and converts
'   it to a 4 byte Base64 string
'
' 0 | 1 | 2 | 3 | 4  | 5  | 6  |  7  |  8
'------------------------------------------
' 1 | 2 | 4 | 8 | 16 | 32 | 64 | 128 | 256
'
'------------------------------------
' 01010101  | 01010101  | 01010101  |
'------------------------------------
' 010101 | 010101 | 010101 | 010101 |
'------------------------------------
'
Private Function EncodeTriplet(ByVal Data As String)

    Dim lbytSrcBase64(1 To 3) As Byte
    Dim lbytDesBase64(1 To 4) As Byte
    Dim lintCount As Integer
    Dim lstrReturn As String
    
    lbytSrcBase64(1) = Asc(Mid$(Data, 1, 1))
    lbytSrcBase64(2) = Asc(Mid$(Data, 2, 1))
    lbytSrcBase64(3) = Asc(Mid$(Data, 3, 1))
    lbytDesBase64(1) = lbytSrcBase64(1) \ 4
    lbytDesBase64(2) = ((lbytSrcBase64(1) Mod 4) * 16) + (lbytSrcBase64(2) \ 16)
    lbytDesBase64(3) = (lbytSrcBase64(3) \ 64) + ((lbytSrcBase64(2) Mod 16) * 4)
    lbytDesBase64(4) = lbytSrcBase64(3) Mod 64
    
    For lintCount = 1 To 4
        lstrReturn = lstrReturn & GetBase64Char(lbytDesBase64(lintCount))
    Next lintCount
    EncodeTriplet = lstrReturn
    
End Function

'================================================
'
'   PRIVATE AddLineTerminator
'
'   Adds the terminator to the passed string
'   if at the 76 byte boundary
'
Private Sub AddLineTerminator(ByRef Data As String, ByRef Counter As Integer)

    Dim lintCount As Integer
    Dim lstrEncoding As String
    
    For lintCount = 1 To 4
        Counter = Counter + IIf(mintLineLength <> 0, 1, 0)
        lstrEncoding = lstrEncoding & Mid$(Data, lintCount, 1)
        If Counter >= mintLineLength Then
            Counter = 0
            lstrEncoding = lstrEncoding & mstrLineTerminator
        End If
    Next lintCount
    
    Data = lstrEncoding
    
End Sub

'================================================
'
'   PRIVATE DecodeTriplet
'
'   Takes a Base64 4 byte string and converts
'   it to a 3 byte ASCII triplet
'
Private Function DecodeTriplet(ByVal Data As String)

    Const METHODTITLE = MODULETITLE & "#DecodeTriplet"
    
    Dim lbytSrcBase64(1 To 4) As Byte
    Dim lbytDesBase64(1 To 3) As Byte
    Dim lintCode As Integer
    Dim lintCount As Integer
    Dim lstrReturn As String
    
    For lintCount = 1 To 4
        lintCode = GetBase64Code(Mid$(Data, lintCount, 1))
        If lintCode < 0 Then
            Err.Raise vbXMLRPC_ERROR_BASE64ERROR, _
                      METHODTITLE, _
                      "Unexpected chracter found in Base64 string", _
                      App.HelpFile, _
                      ginsUtility.ErrorHelpContext(vbXMLRPC_ERROR_BASE64ERROR)
        End If
        lbytSrcBase64(lintCount) = lintCode
    Next lintCount
    
    lbytDesBase64(1) = (lbytSrcBase64(1) * 4) + (lbytSrcBase64(2) \ 16)
    lbytDesBase64(2) = ((lbytSrcBase64(2) Mod 8) * 16) + (lbytSrcBase64(3) \ 4)
    lbytDesBase64(3) = ((lbytSrcBase64(3) Mod 4) * 64) + lbytSrcBase64(4)
    
    For lintCount = 1 To 3
        lstrReturn = lstrReturn & Chr$(lbytDesBase64(lintCount))
    Next lintCount
    DecodeTriplet = lstrReturn
    
End Function

