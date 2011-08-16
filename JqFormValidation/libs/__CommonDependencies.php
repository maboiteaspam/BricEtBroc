<?php
namespace BricEtBroc\Form;

class CheckedDependency extends Dependency{
    public function is_confirmed( ){
        return $this->accessor->is_set();
    }
}
class SelectedDependency extends Dependency{
    public function is_confirmed( ){
        return $this->accessor->is_set();
    }
}
class UncheckedDependency extends Dependency{
    public function is_confirmed( ){
        $retour = !$this->accessor->is_set();
        return $retour;
    }
}
class NotBlankDependency extends Dependency{
    public function is_confirmed( ){
        if( ! $this->accessor->is_set() ) return false;
        if( $this->accessor->read() === "" ) return false;
        return true;
    }
}
class BlankDependency extends Dependency{
    public function is_confirmed( ){
        if( ! $this->accessor->is_set() ) return true;
        if( $this->accessor->read() === "" ) return true;
        return false;
    }
}