<?php
namespace BricEtBroc\Form;

class CheckedDependency extends Dependency{
    public function is_confirmed( InputValueAccessor $valueAccessor ){
        return $valueAccessor->is_set();
    }
}
class SelectedDependency extends Dependency{
    public function is_confirmed( InputValueAccessor $valueAccessor ){
        return $valueAccessor->is_set();
    }
}
class UncheckedDependency extends Dependency{
    public function is_confirmed( InputValueAccessor $valueAccessor ){
        $retour = !$valueAccessor->is_set();
        return $retour;
    }
}
class NotBlankDependency extends Dependency{
    public function is_confirmed( InputValueAccessor $valueAccessor ){
        if( ! $valueAccessor->is_set() ) return false;
        if( $valueAccessor->read() === "" ) return false;
        return true;
    }
}
class BlankDependency extends Dependency{
    public function is_confirmed( InputValueAccessor $valueAccessor ){
        if( ! $valueAccessor->is_set() ) return true;
        if( $valueAccessor->read() === "" ) return true;
        return false;
    }
}