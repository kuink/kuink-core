<?php

namespace Kuink\Core\Instruction;

/**
 * Description of LexoRank Implementation based on
 * https://github.com/alexcrawford/lexorank-php
 *
 * @author paulo.tavares
 */
class RankInstruction extends \Kuink\Core\Instruction {
	
	
	static public function execute($instManager, $instructionXmlNode) {
		$params = $instManager->getParams ( $instructionXmlNode, true );
		$before = isset($params[0]) ? (string)$params[0] : '';
		$after = isset($params[1]) ? (string)$params[1] : '';
		$rank = (new \Kuink\Core\Rank($before, $after))->get();

		return $rank;
	}

}

?>
