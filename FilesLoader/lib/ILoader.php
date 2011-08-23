<?php
namespace BricEtBroc\FilesLoader;

/**
 *
 * @author clement
 */

interface ILoader{
    public function addFileToLoad( $file_to_load, $at_the_end=true );
    public function addPathToLoadDir( $path_to_config_dir, $at_the_end=true );
    public function listOfFiles( );
    public function completeListOfFiles( );
    public function createResponse( array $data );
}

?>
