/***************************************************************************
                 CommandBarPictureConverter.cs  -  description
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
using System.Drawing;
using System.Windows.Forms;
using stdole;


#endregion

namespace Utilities.OfficeExtension
{
    public class CommandBarPictureConverter : AxHost
    {
        public CommandBarPictureConverter() : base(null)
        {
        }

        public new static IPictureDisp GetIPictureDispFromPicture(Image image)
        {
            return (IPictureDisp)AxHost.GetIPictureDispFromPicture(image);
        }

    }
}
