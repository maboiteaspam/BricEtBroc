<?php
class ActiveModelCollection extends ArrayObject {
    public function __toString(){
        return "Collection (".$this->count().")";
    }
}

