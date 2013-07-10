<?php namespace Laravel\Database;

class TransformResult implements \JsonSerializable { 
	private $transform; 
	private $callableVals; 
	private $row; 

	public function __construct(&$row, $transform) { 
		$this->transform = $transform; 
		$this->row = $row;

		$this->callableVals = array();   
	}

	private function evalXformProperty($name) { 
		$xformSpec = $this->transform[$name]; 

		if(is_string($xformSpec)) { 
			return $this->row[$xformSpec];
		} else if(is_callable($xformSpec)) { 
			if(array_key_exists($name, $this->callableVals)) { 
				return $this->callableVals[$name]; 
			} else { 
				$val = $xformSpec($this->row);
				$this->callableVals[$name] = $val; 
				return $val; 
			}
		}

		$trace = debug_backtrace();
        trigger_error(
            'Unrecognized transform instruction recognized: ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
	}

	public function __get($name) { 
		// if(array_key_exists($name, $this->transform)) { 
		if(isset($this->transform[$name])) {
			return $this->evalXformProperty($name); 		
		}

		$trace = debug_backtrace();
        trigger_error(
            'Undefined TransformResult property: ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return null; 
	}

	// public function __set($name, $args) { 
	// }

	// public function __isset($name) { 

	// }

	// public function __unset($name) { 

	// }

	public function jsonSerialize() {
		$jsonArr = array(); 

		foreach ($this->transform as $field => $value) {
			$jsonArr[$field] = $this->evalXformProperty($field); 
		}

		return $jsonArr;  
	}
}