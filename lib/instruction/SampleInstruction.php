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


namespace Kuink\Core\Instruction;

/**
 * This is a sample file for an instruction 
 * 
 * To use it:
 *    Copy this file and paste it in the same directory with the name changed to the new instruction name
 *    Change the classname to the new instruction name
 * 		
 * Xml definition of this instruction
 * 		<Sample var="">
 * 			<Branch1 condition="">
 * 				other instructions to execute
 * 			</Branch1>
 *	 		<Branch2 condition="">
 * 				other instructions to execute 
 * 			</Branch2>
 * 		</Sample>
 *
 * Usage example:
 * 		This sample instruction will take the value of variable test and in Branch1 and Branch2 it will be compared with values PT and EN. The only branch that will be executed 
 * 		is the one where the variable test will match the condition
 * 
 * 		<Var name="test"><String>PT</String></Var>
 * 		<Sample var="test">
 * 			<Branch value="PT">
 * 				<Print>Portugal!!</Print>
 * 			</Branch>
 * 			<Branch value="EN">
 * 				<Print>England!!</Print>
 * 			</Branch>
 * 			<Default>
 * 				<Print>Any other language!!</Print>
 * 			</Default>
 * 		</Sample>
 * 
 * @author paulo.tavares
 */
class SampleInstruction extends \Kuink\Core\Instruction {
	
	/**
	 * Sample instruction
	 *
	 * @see \Kuink\Core\DataSourceConnector::connect()
	 */
	static public function execute($instManager, $instructionXmlNode) {
		//Getting an attribute directly from the root xml element, in this case the var attribute
		//Static method so use self:: instead of $this->
		//self::getAttribute ( [xml element], [attribute name], [variables array], [mandatory true or false], [default value] );
		// If the mandatory param is set to true and the attribute is not supplied then an automatic exception will be thrown
		// If the value of the attribute is a reference to another variable, then getAttribute will return the reference variable value
		$varName = self::getAttribute ( $instructionXmlNode, 'var', $instManager->variables, true, '' );
		$varValue = isset($instManager->variables[$varName]) ? $instManager->variables[$varName] : '';

		//The resulting value of this variable
		$result = null;

		//Control variable to determine if any branch was executed
		$foundMatch = false;

		//Get all branches from the xml 
		$branchsXml = $instructionXmlNode->xpath ( './/Branch' );

		foreach($branchsXml as $branchXml) {
			//Get branch value attribute
			$value = self::getAttribute ( $branchXml, 'value', $instManager->variables, true, '' );
			if ($value == $varValue) {
				//This is it! this branch the value of the branch matches the var value, so let's execute all the inner instructions on this branch
				//Execute the inner instructions
				$result = $instManager->executeInnerInstruction ( $branchXml );
				
				//A match was found
				$foundMatch = true;
				break; //Stop evaluating the other branches
			}
		}

		if (!$foundMatch) {
			//Then no match found so execute the default branch..

			//It's your turn now!
			//Get the default branch and execute the inner instructions
		}

		//Returning the result back to the caller
		return $result;
	}
}

?>
