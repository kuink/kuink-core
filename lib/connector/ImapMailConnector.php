<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Kuink\Core\DataSourceConnector;

/**
 * Description of ImapMailConnector
 *
 * @author paulo.tavares
 */
class ImapMailConnector extends \Kuink\Core\DataSourceConnector {
	var $mbox;
	var $server;
	var $user;
	var $passwd;
	var $mailbox;
	function connect() {
		$this->mailbox = isset ( $this->mailbox ) ? $this->mailbox : '';
		if (! $this->mbox) {
			$this->server = $this->dataSource->getParam ( 'server', true ) . $this->mailbox;
			$this->user = $this->dataSource->getParam ( 'user', true );
			$this->passwd = $this->dataSource->getParam ( 'passwd', true );
			$this->mbox = new \ImapMailbox ( $this->server, $this->user, $this->passwd, null, 'utf-8' );
		}
	}
	function insert($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
	}
	function update($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
	}
	function delete($params) {
		kuink_mydebug ( __CLASS__, __METHOD__ );
		
		$this->connect ();
	}
	function load($params) {
		$id = ( string ) $this->getParam ( $params, 'id', true );
		$this->connect ();
		
		$mail = ( array ) $this->mbox->getMail ( $id );
		
		return $mail;
	}
	function getAll($params) {
		$entity = ( string ) $this->getParam ( $params, '_entity', true );
		$filter = ( string ) $this->getParam ( $params, '_filter', false, 'ALL' );
		$this->mailbox = ( string ) $entity;
		$this->connect ();
		
		$mailsIds = $this->mbox->searchMailBox ( $filter );
		
		$mails = ( array ) $this->mbox->getMailsInfo ( $mailsIds );
		
		return $mails;
	}
	function move($params) {
		$mailId = ( string ) $this->getParam ( $params, 'id', true );
		$mailBox = ( string ) $this->getParam ( $params, '_entity', true );
		$this->connect ();
		
		$result = $this->mbox->moveMail ( $mailId, $mailBox );
		return $result;
	}
	function export($params) {
		$mailId = ( string ) $this->getParam ( $params, 'id', true );
		$filename = ( string ) $this->getParam ( $params, 'filename', true );
		$this->connect ();
		
		$result = $this->mbox->saveMail ( $mailId, $filename );
		// var_dump($result);
		return $result;
	}
}

?>
