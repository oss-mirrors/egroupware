/***************************************************************************
                       SimpleLogger.cs  -  description
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
    public class SimpleLogger
    {
        public SimpleLogger()
        {
            this.className = "unknown";
            this.logger = null;
        }

        public SimpleLogger(String className)
        {
            this.className = className;
            this.logger = null;
        }

        public void SetLogger(Logger logger)
        {
            this.logger = logger;
        }

        public Logger GetLogger()
        {
            return this.logger;
        }

        public void SetClassName(string className)
        {
            this.className = className;
        }

        public void LogInfo(String message)
        {
            if (logger != null)
                logger.Log(Level.Info, formatMessage(message));
        }

        public void LogWarning(String message)
        {
            if (logger != null)
                logger.Log(Level.Warning, formatMessage(message));
        }

        public void LogError(String message)
        {
            if (logger != null)
                logger.Log(Level.Exception, formatMessage(message));
        }

        private string formatMessage(string message)
        {
            return ("[" + className + "]: \"" + message + "\"");
        }


        private string className;
        private Logger logger;
    }
}
