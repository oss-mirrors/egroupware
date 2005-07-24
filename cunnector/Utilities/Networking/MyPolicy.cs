/***************************************************************************
                         MyPolicy.cs  -  description
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

using System.Net;
using System.Security.Cryptography.X509Certificates;

namespace Utilities.Networking
{

    public class AlwaysAcceptCertificatePolicy : ICertificatePolicy
    {
        public bool CheckValidationResult(ServicePoint srvPoint,
            X509Certificate certificate, WebRequest request, int certificateProblem)
        {
            return true;
        }
    }

}