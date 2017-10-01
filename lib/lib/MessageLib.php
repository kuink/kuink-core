<?php

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
class MessageLib {
	var $nodeconfiguration;
	var $msg_manager;
	function MessageLib($nodeconfiguration, $msg_manager) {
		$this->nodeconfiguration = $nodeconfiguration;
		$this->msg_manager = $msg_manager;
		return;
	}
	function createHeader($params) {
		$headers = new Zend\Mail\Headers ();
		$mailAux = new Zend\Mail\Message ();
		$mailAux->setHeaders ( $headers );
		
		// $headers->addHeaderLine('Date', date('r',$params['send_date']));
		$headers->addHeaderLine ( 'Message-ID', '<' . $params ['guid'] . '@' . $params ['context'] . '>' );
		$headers->addHeaderLine ( 'In-Reply-to', '<' . $params ['guid'] . '@' . $params ['context'] . '>' );
		$headers->addHeaderLine ( 'References', '<' . $params ['guid'] . '@' . $params ['context'] . '>' );
		$headers->addHeaderLine ( 'Content-type', $params ['content_type'] . ';charset=' . $params ['charset'] );
		// $headers->addHeaderLine('Content-Transfer-Encoding', '8bit');
		$headers->addHeaderLine ( 'Mime-Version', '1.0' );
		$headers->addHeaderLine ( 'Charset', $params ['charset'] );
		
		if ($params ['confirm_reading'] == '1') {
			$headers->addHeaderLine ( 'X-Confirm-Reading-To', $params ['from_email'] );
			$headers->addHeaderLine ( 'Disposition-Notification-To', $params ['from_email'] );
			$headers->addHeaderLine ( 'Disposition', 'automatic-action/MDN-sent-automatically; displayed' );
		}
		
		$mailAux->addFrom ( $params ['from_email'] );
		
		$toEmail = ($params ['to_email'] === NULL) ? '' : ( string ) $params ['to_email'];
		// print_object($toEmail);
		
		$mailAux->addTo ( $toEmail );
		$mailAux->setSubject ( '=?utf-8?B?' . base64_encode ( $params ['subject'] ) . '?=' );
		
		$content = mb_convert_encoding ( $params ['content'], $params ['charset'], "auto" );
		// var_dump(mb_detect_encoding($content));
		$mailAux->setBody ( $content );
		$mailAux->setEncoding ( $params ['charset'] );
		
		if ($params ['cc'] != '') {
			foreach ( $params ['cc'] as $name => $cc )
				$mailAux->addCc ( $cc );
		}
		
		if ($params ['bcc'] != '') {
			foreach ( $params ['bcc'] as $name => $bcc )
				$mailAux->addBcc ( $bcc );
		}
		
		if ($params ['reply_to'] != '') {
			foreach ( $params ['reply_to'] as $name => $replyTo )
				$mailAux->addReplyTo ( $replyTo );
		}
		
		// $mailAux->addReplyTo()
		
		// var_dump($mailAux->getHeaders()->toString());
		
		return json_encode ( $mailAux->getHeaders ()->toArray () );
	}
	function sendMessage($params) {
		global $KUINK_CFG;
		// print_object($params);
		$msg = new Zend\Mail\Message ();
		
		$_headers = json_decode ( $params ['headers'] );
		if (! isset ( $_headers->To ) || $_headers->To == '' || $_headers->To == null) {
			return - 1;
		}
		$charset = $_headers->Charset;
		
		$headers = new Zend\Mail\Headers ();
		$headers->setEncoding ( $params ['charset'] );
		foreach ( $_headers as $key => $header ) {
			$headers->addHeaderLine ( $key, mb_convert_encoding ( $header, $charset ) );
		}
		
		// var_dump($params);
		// var_dump($_headers);
		// print_r($_headers);
		
		$params ['body'] = mb_convert_encoding ( $params ['body'], $charset );
		$msg->setBody ( $params ['body'] );
		$msg->setHeaders ( $headers );
		if ($KUINK_CFG->enableEmailSending) {
			$transport = new Zend\Mail\Transport\Sendmail ();
			$transport->send ( $msg );
		}
		return 0;
	}
}
?>
