<?php
/**
 * Super Closure Class
 *
 * The SuperClosure class encapsulates a PHP Closure and adds new capabilities
 * like serialization and code retrieval. It uses the ReflectionFunction class
 * heavily to acquire information about the closure.
 * @author		Jeremy Lindblom
 * @copyright	(c) 2010 Synapse Studios, LLC.
 */
class SuperClosure {

	protected $closure = NULL;
	protected $reflection = NULL;
	protected $code = NULL;
	protected $used_variables = array();
	protected $to_unserialize = array();
	protected $start_line = array();


	public function __construct($function)
	{
		// Check if parameter is an actual closure
		if ( ! $function instanceOf Closure)
			throw new InvalidArgumentException();

		// Set the member variable values
		$this->closure          = $function;
		$this->reflection       = new ReflectionFunction($function);
		$this->start_line       = $this->reflection->getStartLine();
		$this->code             = $this->_fetchCode();
		$this->used_variables   = $this->_fetchUsedVariables();
		$this->to_unserialize   = $this->_fetchUnserializable();
	}

    public function startLine(){
        return $this->start_line;
    }


	public function __invoke()
	{
		$args = func_get_args();
		return $this->reflection->invokeArgs($args);
	}


	public function getClosure()
	{
		return $this->closure;
	}


	protected function _fetchCode()
	{
		// Open file and seek to the first line of the closure
		$file = new SplFileObject($this->reflection->getFileName());
		$file->seek($this->reflection->getStartLine()-1);

		// Retrieve all of the lines that contain code for the closure
		$code = '';
		while ($file->key() < $this->reflection->getEndLine())
		{
			$code .= $file->current();
			$file->next();
		}

		// Only keep the code defining that closure
		$begin = strpos($code, 'function');
		$end = strrpos($code, '}');
		$code = substr($code, $begin, $end - $begin + 1);

		return $code;
	}


	public function getCode()
	{
		return $this->code;
	}


	public function getParameters()
	{
		return $this->reflection->getParameters();
	}


	protected function _fetchUsedVariables()
	{
		// Make sure the use construct is actually used
		$use_index = stripos($this->code, 'use');
		if ( ! $use_index)
			return array();

		// Get the names of the variables inside the use statement
		$begin = strpos($this->code, '(', $use_index) + 1;
		$end = strpos($this->code, ')', $begin);
		$vars = explode(',', substr($this->code, $begin, $end - $begin));

		// Get the static variables of the function via reflection
		$static_vars = $this->reflection->getStaticVariables();

		// Only keep the variables that appeared in both sets
		$used_vars = array();
		foreach ($vars as $var)
		{
			$var = trim($var, ' $&');
            $v = $static_vars[$var];
			$used_vars[$var] = $v;
		}

		return $used_vars;
	}


	protected function _fetchUnserializable()
	{
        $retour = array();
		foreach ($this->used_variables as $var=>$v)
		{
            if( $v instanceof Closure ){
                $v = serialize( new SuperClosure($v) );
                $this->used_variables[$var] = $v;
                $retour[] = $var;
            }
		}
        return $retour;
    }


	public function getUsedVariables()
	{
		return $this->used_variables;
	}


	public function __sleep()
	{
		// Only serialize the code and used_variables, the closure or reflection members with cause a fatal error
		return array('code', 'used_variables', 'to_unserialize');
	}


	public function __wakeup()
	{
        $superclosure= null;
        foreach( $this->to_unserialize as $k=>$v ){
            $superclosure = unserialize($this->used_variables[$v]);
            $this->used_variables[$v] = $superclosure->closure;
        }
        unset($k);unset($v);unset($superclosure);
		// Import the used variables so they can again be inherited by the closure
		extract($this->used_variables);

		// Eval the closure's code to recreate the closure
		eval('$_function = '.$this->code.';');

		// If the eval succeeded create the reflection object as well
		if (isset($_function) AND $_function instanceOf Closure)
		{
			$this->closure = $_function;
			$this->reflection = new ReflectionFunction($_function);
		}
		else
			throw new Exception();
	}

    public function __toString() // Depuis PHP 5.1.0
    {
        return serialize($this);
    }

}