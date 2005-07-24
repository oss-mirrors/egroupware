/***************************************************************************
                       SystemProxy.cs  -  description
                             -------------------
    copyright            : (C) 2005 by credativ GmbH, Germany
    email                : cunnector-dev@credativ.org
 ***************************************************************************/

/***************************************************************************
 *                                                                         *
 *   This program is free software; you can redistribute it and/or modify  *
 *   it under the terms of the GNU General Public License as published by  *
 *   the Free Software Foundation; either version 2 of the License, or     *
 *   (at your option) any later version.                                   *
 *                                                                         *
 ***************************************************************************/

#region Using directives

using System;
using System.Collections.Generic;
using System.Text;
using CookComputing.XmlRpc;

#endregion

namespace OutlookSync.EGW
{

    public struct SessionType
    {
        public string sessionid;
        public string kp3;
    }


    public struct LoginType
    {
        public string domain;
        public string username;
        public string password;
    }

    public struct LogoutType
    {
        public string GOODBYE;
    }



    public class SystemProxy : XmlRpcClientProtocol
    {
        public static string LOGOUT_MESSAGE = "XOXO";

        public SystemProxy(string url)
        {
            this.KeepAlive = false;

            this.Url = url;
        }

        [XmlRpcMethod("system.login")]
        public SessionType Login(LoginType param)
        {
            return (SessionType)Invoke("Login", param);
        }

        [XmlRpcMethod("system.logout")]
        public LogoutType Logout(SessionType session)
        {
            return (LogoutType)Invoke("Logout", session);
        }
    }
}
