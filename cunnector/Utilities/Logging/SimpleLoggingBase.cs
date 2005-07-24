/***************************************************************************
                     SimpleLoggingBase.cs  -  description
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

using NSpring.Logging;

#endregion

namespace Utilities.Logging
{
    public class SimpleLoggingBase
    {
        public SimpleLoggingBase(String className)
        {
            this.log = new SimpleLogger(className);
        }


        public void SetLogger(Logger logger)
        {
            log.SetLogger(logger);
        }



        protected SimpleLogger log;


    }
}
