/***************************************************************************
                          Program.cs  -  description
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

using System;
using System.Collections.Generic;
using System.Text;

namespace ASimpleTest
{
    class Program
    {
        static void Main(string[] args)
        {
            const string registryKey = @"Software\credativ\cUnnector";

            OutlookSync.SyncSessionData sd1 = new OutlookSync.SyncSessionData();

            System.Console.WriteLine("Hello");

            sd1.UserName = "Test";

            sd1.StoreInRegistry(registryKey);


            OutlookSync.SyncSessionData sd2 = new OutlookSync.SyncSessionData();

            sd2.RetrieveFromRegistry(registryKey);

            System.Console.WriteLine(sd2.UserName);

            System.Console.ReadLine();

        }
    }
}
