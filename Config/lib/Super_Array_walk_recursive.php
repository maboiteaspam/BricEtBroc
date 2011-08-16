<?php
namespace BricEtBroc\Config;

/**
 * @see http://www.php.net/manual/fr/function.array-walk-recursive.php#71060
 */
Class Super_Array_walk_recursive {

    private $depth = -1;
    private $userdata, $funcname;
    public $status;
    public $input;
    
    public function __construct($input, $funcname, $userdata = "") {
        $this->input    = $input;
        $this->funcname = $funcname;
        $this->userdata = $userdata;
        $this->status   = $this->array_walk_recursive($this->input);
    }
    
    private function array_walk_recursive(&$input) {
       if (!is_callable($this->funcname)) {
           return false;
       }
    
       if (!is_array($input)) {
           return false;
       }

        $this->depth++;
    
       foreach (array_keys($input) AS $keyIdx => $key) {
            $saved_value = $input[$key];
            $saved_key = $key;
            $tomate = call_user_func_array($this->funcname, array(&$input[$saved_key], &$key, $this->userdata));
    
            if ($input[$saved_key] !== $saved_value || $saved_key !== $key) {
                $saved_value = $input[$saved_key];

                unset($input[$saved_key]);
                $input[$key] = $saved_value;
            }
           if (is_array($input[$key])) {
                if (!$this->array_walk_recursive($input[$key])) return false;
                $this->depth--;
           }
       }    
       return true;
    }

}