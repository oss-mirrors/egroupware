/***************************************************************************
                         MemoryLog.cs  -  description
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
using System.Collections;
using System.Text;
using System.IO;

using NSpring.Logging;
using NSpring.Logging.Loggers;
using NSpring.Logging.EventFormatters;

#endregion

namespace Utilities.Logging
{
    public class MemoryLog
    {
        public MemoryLog()
        {
            seekPos = 0;

            logStream = new System.IO.MemoryStream();
            logReader = new StreamReader(logStream, new UTF8Encoding());

            logger = new StreamLogger(logStream);
            logger.EventFormatter = new PatternEventFormatter(formatString);
            logger.Open();
        }

        ~MemoryLog()
        {
            logger.Close();
            logReader.Close();
        }


        public ArrayList GetMessages()
        {
            ArrayList messageList = new ArrayList();
            String message;

            logStream.Seek(seekPos, SeekOrigin.Begin);

            while ((message = logReader.ReadLine()) != null)
                messageList.Add(message);

            seekPos = logStream.Position;

            return messageList;
        }

        public StreamLogger Logger
        {
            get
            {
                return logger;
            }
        }

        private MemoryStream logStream;
        private StreamReader logReader;
        private StreamLogger logger;


        private long seekPos;

        private const string formatString = "<{levelName}> {hour}:{minute}:{second} - {msg} \n";
    }

}
