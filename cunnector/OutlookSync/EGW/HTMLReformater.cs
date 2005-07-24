/***************************************************************************
                      HTMLReformater.cs  -  description
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

#endregion

namespace OutlookSync.EGW
{
    public class HTMLReformater
    {
        public HTMLReformater()
        {
        }


        public static string HtmlToPlain(string htmlString)
        {
            if (htmlString == null) return null;

            string temp = htmlString;

            temp = temp.Replace("&", "");
            temp = temp.Replace("quot;", "\"");
            temp = temp.Replace("amp;", "&");
            temp = temp.Replace("lt;", "<");
            temp = temp.Replace("gt;", ">");

            return temp;
        }
    }
}
