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
namespace Kuink\Core;

/*
 * ProcessOrchestrator functionality:
 * - Control the execution stack of nodes and processes
 * - Get the next node to execute given the event or action
 *
 * STACK
 *
 * The stack will register the nodes that the user has gone through.
 * There can be more than one context.
 * So, by contextId (guid) the stack will be a collection of execution nodes with the following properties:
 *
 * By contextId
 * +--------------+ +--------------+
 * | app | | app |
 * | proc | | proc |
 * | node | | node |
 * | procGuid | | procGuid |
 * | nodeGuid | | nodeGuid |
 * | nodeUrl | | nodeUrl |
 * +--------------+ +--------------+
 *
 *
 *
 */
class QueryStringParam {
	const BASE_APPLICATION = 'baseApplication'; // cannot be set by url...
	const PROCESS = 'startuc';
	const EVENT = 'event';
	const ACTION = 'action';
	const ACTION_VALUE = 'actionvalue';
	const FORM = 'form';
	const TRACE = 'trace';
	const ID = 'id';
	const ID_CONTEXT = 'idcontext';
	const PREVIOUS_ID_CONTEXT = 'previousidcontext';
	const NODE_GUID = 'nodeguid';
	const RESET = 'reset';
	const MODAL = 'modal';
}
class Node {
	var $application;
	var $process;
	var $node;
	function __construct($application, $process, $node) {
		$this->application = $application;
		$this->process = $process;
		$this->node = $node;
	}
}
class Flow {
	var $application;
	var $process;
	var $node;
	var $event;
	var $action;
	var $action_value;
	function __construct($application, $process, $node, $event, $action = '', $action_value = '') {
		$this->application = $application;
		$this->process = $process;
		$this->node = $node;
		$this->event = $event;
		$this->action = $action;
		$this->action_value = $action_value;
	}
}
class ProcessOrchestrator {

	static function generateNewContextId() {
		$n = (string)uniqid();
		
		return 'k'.$n;
	}

	static function getContextId() {
		$contextId = (isset ( $_GET [QueryStringParam::ID_CONTEXT] ) && ($_GET [QueryStringParam::ID_CONTEXT] != '')) ? $_GET [QueryStringParam::ID_CONTEXT] : self::generateNewContextId(); // The stack context, generate one if not in url
		//if (!isset($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] ))
		//	self::addContext($contextId);
		//var_dump($_GET);
		//var_dump($_GET [QueryStringParam::ID_CONTEXT]);
		/*
		 * if (isset($_GET[QueryStringParam::ID_CONTEXT]))
		 * $contextId = $_GET[QueryStringParam::ID_CONTEXT];
		 * else {
		 * $contextId = uniqid();
		 * $_GET[QueryStringParam::ID_CONTEXT] = $contextId;
		 * }
		 */
		$_GET [QueryStringParam::ID_CONTEXT] = $contextId;
		return $contextId;
	}
	
	// Copy the current context to a new one
	static function duplicateContext($previousContextId) {
		$newContextId = self::generateNewContextId();
		$oldContext = self::getContext ( $previousContextId );
		$currentNode = self::getCurrentNode ( $previousContextId );
		$newContext = self::addContext ( $oldContext->baseApplication, $newContextId );
		
		$addFlow = new Flow ( $currentNode->application, $currentNode->process, $currentNode->node, $currentNode->event, $currentNode->action, $currentNode->action_value );
		self::addNode ( $addFlow, $newContextId );
		
		$newContext->eventParams = $oldContext->eventParams;
		$newContext->sessionVars = $oldContext->sessionVars;
		$newContext->processVars = $oldContext->processVars;
		
		// $newContext->idContext = $newContextId
		// var_dump($newContext);
		return $newContext;
	}
	
	// If the context does not exists, create one
	static function prepareContext($baseApplicationName, $contextId = null) {
		
		// Copy the current context if not exists
		if (isset ( $_GET ['previousidcontext'] )) {
			// kuink_mydebug('id::'.$contextId, 'Copying the old context...');
			$context = self::duplicateContext ( $_GET ['previousidcontext'] );
			$_GET ['idcontext'] = $context->id;
			unset ( $_GET ['previousidcontext'] );
		} else {
			$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
			
			$context = self::getContext ( $contextId );
			
			if (! $context)
				$context = self::addContext ( $baseApplicationName, $contextId );
		}
		return $context;
	}
	static function addContext($baseApplicationName, $contextId = null) {
		global $KUINK_CFG;
		
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		$currentContext = self::getContext ( $contextId );
		if ($currentContext == null) {
			if (! $KUINK_CFG->allowMultipleContexts)
				self::clearContexts ();
		}
		
		$numContexts = isset($_SESSION ['KUINK_CONTEXT']) ? count ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] ) : 0;
		if ($numContexts < $KUINK_CFG->maxProcessOrchestratorContexts) {
			$context = new \stdClass ();
			$context->id = $contextId;
			$context->baseApplication = $baseApplicationName;
			$context->stack = array (); // init stack array
			$context->processVars = array (); // init process variables array
			$context->eventParams = array (); // last event params
			$context->sessionVars = array (); // last event params
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] = $context; // add the context
			// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]);
		} else {
			if (isset($_GET [QueryStringParam::RESET]) && $_GET [QueryStringParam::RESET])
				self::clearContexts ();
			else
				throw new \Exception ( 'Kuink::Cannot create a new context. Close this window and use the others.' );
		}
		
		return $context;
	}
	static function clearContexts() {
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] );
	}
	static function clearContext($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack );
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars );
		// unset($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]);
	}
	static function deleteContext($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack );
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars );
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] );
	}
	static function getContext($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS']);
		$context = isset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] ) ? $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] : null;
		
		return $context;
	}
	static function setContext($context, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$contextExists = self::getContext ( $contextId );
		$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] = $context;
		
		return $context;
	}
	static function getContextStackInfo($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS']);
		$context = isset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] ) ? $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId] : null;
		
		$info = '';
		foreach ( $context->stack as $stack ) {
			$info .= '>' . $stack->application . ',' . $stack->process . ',' . $stack->node . '(action:' . $stack->action . ',value:' . $stack->actionValue . ')::' . $stack->idUser . PHP_EOL;
		}
		
		return $info;
	}
	static function getEventParams($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$context = self::getContext ( $contextId );
		return $context->eventParams;
	}
	static function setEventParams($eventParams, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$context = self::getContext ( $contextId );
		$context->eventParams = $eventParams;
		self::setContext ( $context, $contextId );
	}
	static function clearEventParams($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$context = self::getContext ( $contextId );
		$context->eventParams = null;
		self::setContext ( $context, $contextId );
	}
	static function setEventParamsByUrl($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$ignoreKeys = array (
				QueryStringParam::ID => 1,
				QueryStringParam::FORM => 1,
				QueryStringParam::ACTION => 1,
				QueryStringParam::TRACE => 1,
				QueryStringParam::PROCESS => 1,
				QueryStringParam::ACTION_VALUE => 1,
				QueryStringParam::EVENT => 1,
				QueryStringParam::BASE_APPLICATION => 1,
				QueryStringParam::ID_CONTEXT => 1 
		);
		
		// Get event params from url
		$currentEventParams = self::getEventParams ( $contextId );
		$eventParams = isset ( $currentEventParams ) ? $currentEventParams : array ();
		foreach ( $_GET as $key => $value )
			if (! array_key_exists ( $key, $ignoreKeys ))
				$eventParams [$key] = $value;
		self::setEventParams ( $eventParams, $contextId );
	}
	static function getCurrentNode($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$context = self::getContext ( $contextId ); // end($_SESSION['KUINK_CONTEXT']['KUINK_PROCESS_STACK'][$contextId]);
		$currentNode = isset($context->stack) ? end($context->stack) : null;
		//$c = self::getContextId();
		//var_dump($c);
		return $currentNode;
	}
	static function updateCurrentNodeAction($action, $actionvalue) {
		$lastNode = self::getCurrentNode ( $contextId );
	}
	static function addNode($node, $contextId = null) {
		global $KUINK_CFG;
		
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		// Check if the node is already in the stack with the same RWX or IMPERSONATE
		$found = - 1;
    $foundNode = null;
    if (isset($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack))
      foreach ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack as $item => $stackNode ) {
        if ($stackNode->application == $node->application && $stackNode->process == $node->process && $stackNode->node == $node->node) {
          $found = $item;
          break;
        }
      }
		if ($found != - 1) {
			// Remove all the nodes from the found
			$count = count ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack );
			for($index = $found + 1; $index < $count; $index ++)
				unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$index] );
				
				// Update the event, action and actionvalue
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$found]->event = isset ( $_GET [QueryStringParam::EVENT] ) ? $_GET [QueryStringParam::EVENT] : '';
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$found]->action = isset ( $_GET [QueryStringParam::ACTION] ) ? $_GET [QueryStringParam::ACTION] : '';
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$found]->actionValue = isset ( $_GET [QueryStringParam::ACTION_VALUE] ) ? $_GET [QueryStringParam::ACTION_VALUE] : '';
			
			// Updating params with the new event
			// $eventParams = self::getEventParams();
			// foreach ($eventParams as $key=>$value)
			// $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->stack[$found]->params[$key] = $value;
		} else {
			// Add the node
			$lastNode = self::getCurrentNode ( $contextId );
			// var_dump($lastNode);
			// var_dump($node);
			
			$context = self::getContext ( $contextId );
			
			$newNode = new \stdClass ();
			$newNode->application = $node->application;
			$newNode->process = $node->process;
			$newNode->node = $node->node;
			$newNode->event = $node->event;
			$newNode->action = $node->action;
      $newNode->actionValue = isset($node->actionValue) ? $node->actionValue : null;
			$newNode->processGuid = (isset($lastNode) && $lastNode->application == $node->application && $lastNode->process == $node->process) ? $lastNode->processGuid : uniqid ();
			$newNode->nodeGuid = uniqid ();
			$newNode->rwx = 7; // TODO
			$newNode->idUserImpersonate = null; // TODO
      $newNode->idUser = ($KUINK_CFG->auth->user->id) ? $KUINK_CFG->auth->user->id : 0; // TODO
      $qstrId = isset($_GET [QueryStringParam::ID]) ? $_GET [QueryStringParam::ID] : '';
			$newNode->url = $KUINK_CFG->wwwRoot . '/' . $KUINK_CFG->kuinkRoot . '/view.php?id=' . $qstrId . '&' . QueryStringParam::ID_CONTEXT . '=' . $_GET [QueryStringParam::ID_CONTEXT] . '&' . QueryStringParam::NODE_GUID . '=' . $newNode->nodeGuid;
			$newNode->params = self::getEventParams ( $contextId );
			$newNode->roles = isset($lastNode) ? $lastNode->roles : null;
			
			$context->stack [] = $newNode;
			self::setContext ( $context, $contextId );
		}
	}
	static function setNode($node, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		foreach ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack as $key => $stackNode ) {
			if ($stackNode->nodeGuid == $node->nodeGuid)
				$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$key] = $node;
		}
	}
	static function setNodeRoles($roles, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$currentNode = self::getCurrentNode ( $contextId );
		$currentNode->roles = $roles;
		self::setNode ( $currentNode );
	}
	static function getNodeRoles($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$currentNode = self::getCurrentNode ( $contextId );
		return isset($currentNode->roles) ? $currentNode->roles : null;
	}
	static function setSessionVariable($variable, $key = '', $value, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		if ($key == '')
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->sessionVars [$variable] = $value;
		else
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->sessionVars [$variable] [$key] = $value;
		return;
	}
	static function unsetSessionVariable($variable, $key = '', $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		if ($key == '')
			unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->sessionVars [$variable] );
		else
			unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->sessionVars [$variable] [$key] );
		
		return;
	}
	static function getSessionVariable($variable, $key = '', $contextId = null) {
		$contextId = (isset($contextId)) ? $contextId : self::getContextId();
		$value = '';
		if ($key=='') {
			if (isset($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->sessionVars[$variable]))
				$value = $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->sessionVars[$variable];
		}
		else
			if (isset($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->sessionVars[$variable][$key]))
				$value = $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->sessionVars[$variable][$key];
	
		return $value;
					

		return $value;
	}
	static function registerAPI($api, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$currentNode = self::getCurrentNode ( $contextId );
		
		$currentNode->registeredAPI [] = $api;
		self::setNode ( $currentNode );
		return;
	}
	static function validRegisteredAPI($api, $contextId = null, $bypass = false) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		$currentNode = self::getCurrentNode ( $contextId );
		
		return ($bypass) ? true : (in_array ( $api, $currentNode->registeredAPI ));
	}
	static function getRegisteredAPIs($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		$currentNode = self::getCurrentNode ( $contextId );
		return $currentNode->registeredAPI;
	}
	static function setProcessVariable($variable, $key = '', $value, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$currentNode = self::getCurrentNode ( $contextId );
		$currentProcessGuid = $currentNode->processGuid;
		
		if ($key == '') {
			// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable]);
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable] = $value;
		} else {
			//Converts into ana array if variable had a previous value not array
			if (!isset($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable]) || !is_array($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable]))
				$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable] = array();
			$_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable] [$key] = $value;
		}
		return;
	}
	static function unsetProcessVariable($variable, $key = '', $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$currentNode = self::getCurrentNode ( $contextId );
		$currentProcessGuid = $currentNode->processGuid;
		
		if (isset($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId])) {
			if ($key == '')
				unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable] );
			else {
				
				unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] [$variable] [$key] );
				// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable]);
			}
			
			if (!empty($_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid]))
			if (count ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] ) == 0)
				unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->processVars [$currentProcessGuid] );
		}
			// var_dump("CLEAR");
			// var_dump($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable]);
		return;
	}
	static function getProcessVariable($variable, $key = '', $contextId = null) {

		$contextId = (isset($contextId)) ? $contextId : self::getContextId();
		$currentNode =self::getCurrentNode($contextId);
		$currentProcessGuid = $currentNode->processGuid;
	
		if ($key==''){
			if (isset($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable]))
				$value = $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable];
			else
				$value = null;
		}
		else
		 if (isset($_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable][$key]))
		  	$value = $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable][$key];
		else
			$value = null;
		
		/**
		 * Ugly workaround for unset process variables with arrays
		 * Joao Patricio | 25-09-2014
		 */
		if (is_array ( $value )) {
			
			$copy = array ();
			foreach ( $value as $k => $val ) {
				if ($val !== '' && $val !== null)
					$copy [$k] = $val;
			}
			// $_SESSION['KUINK_CONTEXT']['CONTEXTS'][$contextId]->processVars[$currentProcessGuid][$variable] = $copy;
			return $copy;
		} else {
			return $value;
		}
	}
	static function numberOfProcessesInStack($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		$currentProcessGuid = (isset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [0] )) ? $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [0]->processGuid : null;
		if (! isset ( $currentProcessGuid ))
			return 0;
		
		$numProcesses = 1;
		$context = self::getContext ( $contextId );
		foreach ( $context->stack as $stackNode ) {
			if ($stackNode->processGuid != $currentProcessGuid) {
				$currentProcessGuid = $stackNode->processGuid;
				$numProcesses += 1;
			}
		}
		return $numProcesses;
	}
	static function popCurrentNode($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$index = count ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack ) - 1;
		unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$index] );
	}
	static function setCurrentNode($nodeGuid, $contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		
		$context = self::getContext ( $contextId );
		$count = count ( $context->stack );
		$found = FALSE;
		for($index = 0; $index < $count; $index ++) {
			$currentNode = self::getCurrentNode ();
			if ($currentNode->nodeGuid != $nodeGuid)
				self::popCurrentNode ( $contextId );
			else
				break;
		}
		
		$currentNode = self::getCurrentNode ( $contextId );
		$currentNode->event = '';
		/*
		 * $currentNode->action = $_GET['action'];
		 * $currentNode->actionValue = $_GET['actionvalue'];
		 * $currentNode->form = $_GET['form'];
		 * $currentNode->postData = $_POST;
		 */
		$currentNode->action = '';
		$currentNode->actionValue = '';
		// var_dump($currentNode);
		self::setNode ( $currentNode, $contextId );
		
		return $currentNode;
	}
	
	// Return 0 if cannot exit process or stack will be empty
	static function exitProcess($contextId = null) {
		$numProcesses = self::numberOfProcessesInStack ();
		if ($numProcesses <= 1) {
			// cannot exit process
			return (0);
		} else {
			
			$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
			// Remove all the nodes of the current process (with the same processGuid)
			$currentNode = self::getCurrentNode ( $contextId );
			
			$processGuid = $currentNode->processGuid;
			
			$count = count ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack );
			while ( $count > 0 ) {
				if ($currentNode->processGuid == $processGuid)
					unset ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] [$contextId]->stack [$count] );
				else
					break;
				$currentNode = self::getCurrentNode ( $contextId );
				$count --;
			}
			
			// Now update the current node params with the even params
			$eventParams = self::getEventParams ();
			foreach ( $eventParams as $key => $value )
				$currentNode->params [$key] = $value;
				
				// Params updated
			self::setNode ( $currentNode );
		}
		
		return (1);
	}
	static function getContextStackHtml($showAll = FALSE) {
		$html = '';
		if ($showAll) {
			foreach ( $_SESSION ['KUINK_CONTEXT'] ['CONTEXTS'] as $contextId => $context ) {
				$html .= self::getStackHtml ( $contextId );
			}
		} else {
			$contextId = self::getContextId ();
			$html = self::getStackHtml ( $contextId );
		}
		
		return $html;
	}
	static function getStackHtml($contextId = null) {
		$contextId = (isset ( $contextId )) ? $contextId : self::getContextId ();
		$context = self::getContext ( $contextId );
		
		$html = '
      <div class="herounit">
            <ul class="thumbnails well-small">';
		
		foreach ( $context->stack as $stackNode ) {
			$app = $stackNode->application;
			$proc = $stackNode->process;
			$vars = isset($context->processVars [$stackNode->processGuid]) ? var_export ( $context->processVars [$stackNode->processGuid], true ) : '';
			$sessionVars = ''; // var_export($context->sessionVars, true);
			$roles = var_export ( $stackNode->roles, true );
      $stackProcessStack = isset($stackNode->stack) ? $stackNode->stack : null;
      $idCompany = isset($context->idCompany) ? $context->idCompany : '';
			$html .= '
          <li class="span2">
            <span class="thumbnail" style="display: inline-table">
            <p><strong>' . $contextId . '::' . $context->baseApplication . '(' . $idCompany . ')' . '</strong></p>
            <p><strong>' . $stackNode->processGuid . '</strong></p>
            <p>' . $app . ',' . $proc . '</p>
            <p><a href="' . $stackNode->url . '">' . $stackNode->node . '</a></p>
            <p> API::' . implode ( ',', $stackNode->registeredAPI ) . '</p>
            <p>Event:' . $stackNode->event . ' Action:' . $stackNode->action . ' Value:' . $stackNode->actionValue . '</p>
            <p>Roles:' . $roles . '</p>
            <p>Vars:' . $vars . '</p>
            <p>SessionVars:' . $sessionVars . '</p>
            <p>Params:' . var_export ( $stackNode->params, true ) . '</p>
                    <!-- <p>rwx: ' . $stackNode->rwx . '</p>
                    <p>idUserImp:: ' . $stackNode->idUserImpersonate . '</p>
                    <p>Params:' . var_export ( $stackNode->params, true ) . '</p> -->
            </span>
          </li>
        ';
		}
		$html .= '
        </ul>
      </div>
    ';
		return $html;
	}
	static function getNodeToExecute($roles, $defaultFlow, $setEvent = null, $forceFlow = null) {
		// This is the most important
		if (isset ( $_GET [QueryStringParam::NODE_GUID] )) {
			$nodeGuid = $_GET [QueryStringParam::NODE_GUID];
			$nodeToExecute = self::setCurrentNode ( $nodeGuid );
			return $nodeToExecute;
		}
		
		$currentFlow = (isset ( $forceFlow )) ? $forceFlow : self::getCurrentFlow ( $setEvent );
		//print_object($currentFlow);		
		// The process is set is flow? else get the default flow
		
		if (! isset ( $currentFlow->process )) {
			$currentFlow = $defaultFlow;
			self::clearContext ();
		}
		// Setting the event params by url
		self::setEventParamsByUrl ();
		
		// var_dump($defaultFlow);
		//print('<br/>');
		//var_dump($currentFlow);
		
		// if an event is set in url or by parameter than the node to execute could be different from the current one
		$nodeToExecute = self::getCurrentNode ();
		if (isset ( $currentFlow->event ))
			$nodeToExecute = self::processEvent ( $roles, $currentFlow );
		else
			$nodeToExecute = self::getCurrentNode ();
			
			// $context = self::getContext();
			// var_dump($context);
			
		// Exiting Process?
		if ($nodeToExecute->process == '' && $nodeToExecute->node == '' && $currentFlow->event != '') {
			// Exiting this process
			
			$canExit = self::exitProcess ();
			
			$currentNode = self::getCurrentNode ();
			
			if ($canExit)
				$newFlow = new Flow ( $currentNode->application, $currentNode->process, $currentNode->node, $currentFlow->event );
			else
				$newFlow = new Flow ( $currentNode->application, $currentNode->process, $currentNode->node, '' ); // empty event will lead to init action
			
			$nodeToExecute = self::getNodeToExecute ( $roles, null, null, $newFlow );
		}
		// var_dump($currentFlow);
		$addFlow = new Flow ( $nodeToExecute->application, $nodeToExecute->process, $nodeToExecute->node, $currentFlow->event, $currentFlow->action, $currentFlow->action_value );
		self::addNode ( $addFlow );
		//print_object($nodeToExecute);
		return $nodeToExecute;
	}
	static function processEvent($roles, $flow) {
		global $KUINK_CFG;
		global $KUINK_APPLICATION;

		$appBase = $KUINK_APPLICATION->appManager->getApplicationBase ( $flow->application );
		
		// var_dump($flow); //DEBUG
		// die();
		/* Load the flow definition xml file */
		$flowFile = $KUINK_CFG->appRoot . 'apps/' . $appBase . '/' . $flow->application . '/process/' . $flow->process . '/process.xml';
		
		// var_dump($_SESSION['KUINK_CONTEXT']);
		// die($flowFile);
		
		if (! file_exists ( $flowFile )) {
			die ( 'Process xml definition not found: ' . $flowFile );
			throw new \Exception ( 'Process xml definition not found: ' . $flowFile );
		}
		$processXml = simplexml_load_file ( $flowFile );
		if ($processXml == null)
			throw new \Exception ( 'Cannot open flow file: ' . $flowFile );
			
			// Get the correct flow	
		$flows = array ();
		foreach ( $roles as $role => $value ) {
			$xpath = '//Process/Transitions/Flow[contains(@role, \'' . $role . '\') and @startnode="' . $flow->node . '" and @event="' . $flow->event . '"]';
			$roleFlow = $processXml->xpath ( $xpath );
			if (! empty ( $roleFlow ))
				$flows [] = $roleFlow;
		}

		//if (! isset ( $flows [0] [0] ))
		//	throw new \Exception ( 'Kuink::ProcessOrchestrator::Invalid Transition.' );
	
		$processFlow = isset($flows [0]) && isset($flows [0] [0]) ? $flows [0] [0] : null;
		$nodeToExecute = new Node ( $flow->application, $flow->process, ( string ) $processFlow ['endnode'] );
		
		// Check to see if we are exiting a process
		if ($nodeToExecute->node == '') {
			// Exiting a process, so apply the event to the previous node in stack
			// Rebuilding the flow
			$canExit = self::exitProcess ();
			$currentNode = self::getCurrentNode ();
			
			if ($canExit) {
				$exitProcessFlow = new Flow ( $currentNode->application, $currentNode->process, $currentNode->node, $flow->event, '', '' );
				
				$nodeToExecute = self::processEvent ( $roles, $exitProcessFlow );
			} else {
				// redirect to the current node
				redirect ( $currentNode->url.'&modal=embed', '', 0 );
			}
		}
		
		return ($nodeToExecute);
	}
	static function getCurrentFlow($setEvent) {
		if ($setEvent) {
			// The event is set by parameter, so we are in the middle of a process -> context allready set
			unset ( $_GET [QueryStringParam::ACTION] );
			unset ( $_GET [QueryStringParam::ACTION_VALUE] );
		} else if (isset ( $_GET [QueryStringParam::PROCESS] )) {
			// Comming from a menu... or directly from url -> start context
			self::clearContext ();
		}
		
		$context = self::getContext ();
		$currentStackNode = self::getCurrentNode ();
    // Initialize data with the current node values in stack
		$application = isset ( $currentStackNode->application ) ? $currentStackNode->application : $context->baseApplication;
		$process = isset ( $currentStackNode->process ) ? $currentStackNode->process : (isset($_GET[QueryStringParam::PROCESS]) ? $_GET[QueryStringParam::PROCESS] : null);
		$node = isset ( $currentStackNode->node ) ? $currentStackNode->node : null;
		$event = isset ( $setEvent ) ? $setEvent : (isset($_GET[QueryStringParam::EVENT]) ? $_GET[QueryStringParam::EVENT] : null);
		
		// Update action values
		if (! $setEvent) {
			$action = isset ( $_GET [QueryStringParam::ACTION] ) ? $_GET [QueryStringParam::ACTION] : '';
			$actionValue = isset ( $_GET [QueryStringParam::ACTION_VALUE] ) ? $_GET [QueryStringParam::ACTION_VALUE] : '';
			;
		} else {
			// If an event is raised, clean action data
			$action = '';
			$actionValue = '';
		}
		
		$currentFlow = new Flow ( $application, $process, $node, $event, $action, $actionValue );
		// var_dump($currentFlow);
		return $currentFlow;
	}
	static function getCurrentRWX() {
		$currentNode = self::getCurrentNode ();
		return $currentNode->rwx;
	}
	static function getCurrentImpersonate() {
		$currentNode = self::getCurrentNode ();
		return $currentNode->idUserImpersonate;
	}
	
	/**
	 * *
	 * sets the company id in session
	 */
	static function setCompany() {
		global $KUINK_CFG;
		// unset( $_SESSION['KUINK_CONTEXT']['idCompany'] );
		
		$context = self::getContext ();
		// check if there's a company set
		if (isset ( $_GET ['idCompany'] )) {
			// check if the user belongs to this new company
			$idCompany = $_GET ['idCompany'];
			
			// Set the company id
			$companies = $_SESSION ['KUINK_CONTEXT'] ['companies'];
			foreach ( $companies as $company )
				if ($company->id == $idCompany)
					$context->idCompany = $idCompany;
			
			self::setContext ( $context );
			// Redirect to home page...
			redirect ( $KUINK_CFG->wwwRoot );
		} else if (! isset ( $context->idCompany )) {
			// Load the user companies
			$datasource = new \Kuink\Core\DataSource ( null, 'framework/framework,user,user.getCompanies', 'framework', 'user' );
			$idNumber = ($KUINK_CFG->auth->user->id) ? $KUINK_CFG->auth->user->id : 0;
			$pars = array (
					'id_person' => $idNumber 
			);
			$companies = $datasource->execute ( $pars );
			
			$_SESSION ['KUINK_CONTEXT'] ['companies'] = $companies;
			
			// Get the default company and set it
			foreach ( $companies as $company ) {
				if ($KUINK_CFG->useNewDataAccessInfrastructure) {
					if ($company ['is_default'] == 1)
						$context->idCompany = $company ['id'];
				} else {
					if ($company->is_default == 1)
						$context->idCompany = $company->id;
				}
			}
		}
		self::setContext ( $context );
	}
	static function getCompany() {
		$context = self::getContext ();
		return (isset($context->idCompany) ? $context->idCompany : null);
	}
	static function getCompanies() {
		return $_SESSION ['KUINK_CONTEXT'] ['companies'];
	}
}

?>
