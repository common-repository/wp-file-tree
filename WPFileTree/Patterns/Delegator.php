<?php
/*
name: Delegator
type: class 
package: * Patterns

description: Implements delegator pattern
Class used as generic delegetor. 
Allows to access whole application functionality from the single point
(which delegates requests to specific objects).

author:			jimisaacs
author uri:		http://ji.dd.jimisaacs.com
*/


class Delegator {
	/*
	Singleton instance.
	Set as protected to allow extension of the class. To extend simply override the {@link getInstance()}
	var Umapper_Patterns_Delegator
	*/
	protected static $_instance;

	/*
	List of targeted objects to whom calls would be deligated.
	var array
	*/
	private $_targets = array();


	/*
	Constructor
	param  object $firstTarget Object used for default targeting
	*/
	public function __construct($firstTarget = null) {
		if (null != $firstTarget) {
			$this->_targets[] = $firstTarget;
		}
	}

	/*
	Adds yet another object to delegators list
	param  object  $newTarget Handle to the new object that should act as delegate
	return object
	*/
	public function addTarget($newTarget) {
		$this->_targets[] = $newTarget;
		return $newTarget;
	}

	/*
	Magic method that would make sure that calls are deligated to registred targets
	param  string  $name Name of invoked function
	param  array   $args List of passed arguments
	return mixed
	*/
	public function __call($name, $args) {
		$callInvoked = true;
		foreach ($this->_targets as $targetObject) {
			$r = new ReflectionClass($targetObject);
			try {
				$method = $r->getMethod($name);
			} catch (Exception $e){
				$callInvoked = false;
				continue;
			}
			return $method->invokeArgs($targetObject, $args);
		}
		return $callInvoked;
	}

	/*
	Singleton instance.
	param  object  $firstTarget If instance is created then $firstTarget is passed into constructor
	return Delegator
	*/
	public static function getInstance($firstTarget = null) {
		if (null == self::$_instance) {
			self::$_instance = new self($firstTarget);
		}
		return self::$_instance;
	}
}