<?php

namespace Kuink\Core\Instruction;

/**
 * Mail Instruction
 *
 * @author paulo.tavares
 */
class MailInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Sends an email
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		// Get all the params
		$params = $instManager->getParams ( $instructionXmlNode );

		// message object
		$message = new \Zend\Mail\Message ();

		// encoding and body
		$body = new \Zend\Mime\Part ( $params ['body'] );
		$body->type = $params ['content_type'];
		$messageBody = new \Zend\Mime\Message ();
		$messageBody->addPart ( $body );
		$message->setEncoding ( $params ['charset'] );
		
		// set body to message
		$message->setBody ( $messageBody );
		
		// subject
		$message->setSubject ( $params ['subject'] );
		
		// to, from, replyto
		$message->addTo ( $params ['to_email'], $params ['to'] );
		$message->addFrom ( $params ['from_email'], $params ['from'] );
		$message->addReplyTo ( $params ['reply_to'] );
		
		// bcc and cc
		$message->addBcc ( $params ['bcc'] );
		$message->addCc ( $params ['cc'] );
		
		// send message
		$transport = new \Zend\Mail\Transport\Sendmail ();
		$transport->send ( $message );
		
		$arrayHeaders = $message->getHeaders ()->toArray ();
		$arrayHeaders ['Charset'] = $message->getEncoding ();
		
		return json_encode ( $arrayHeaders );	
	}
}

?>
