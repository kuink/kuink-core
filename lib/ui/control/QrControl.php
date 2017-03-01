<?

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework.  If not, see <http://www.gnu.org/licenses/>.


class QrControl
{
    function QrControl($nodeconfiguration) {

        return;
    }

    function load($nodeconfiguration, $nodexml, $tablename)
    {
        $qr = new Qr();
        return $qr;
    }

    function display($qr)
    {
        //print('Hello Calendar');
        print( $qr->getHtml() );
        return;
    }

    function addDataSource($params)
    {
    }

    function bind($params)
    {
        if (count($params) <> 2)
                throw new Exception("QrManager bind needs 1 param");

        //var_dump($params);

        $qr = $params[0];
        $data = $params[1];

        if ($qr == null)
            throw new Exception('QrManager bind: form not found.');

        $qr->bind( $data );
    }


}

// PHP Calendar Class Version 1.4 (5th March 2001)
//
// Copyright David Wilkinson 2000 - 2001. All Rights reserved.
//
// This software may be used, modified and distributed freely
// providing this copyright notice remains intact at the head
// of the file.
//
// This software is freeware. The author accepts no liability for
// any loss or damages whatsoever incurred directly or indirectly
// from the use of this script. The author of this software makes
// no claims as to its fitness for any purpose whatsoever. If you
// wish to use this software you should first satisfy yourself that
// it meets your requirements.
//
// URL:   http://www.cascade.org.uk/software/php/calendar/
// Email: davidw@cascade.org.uk


class Qr
{
    var $data;
    /*
        Constructor for the Calendar class
    */
    function Qr()
    {
        return;
    }

    function bind($data)
    {
        $this->data = $data;
        return;
    }

    function getHtml()
    {
        print('<div style="text-align:center"><img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl='.$this->data.'&choe=UTF-8"/></div>');
    }

}

?>
