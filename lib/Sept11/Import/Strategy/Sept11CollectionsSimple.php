<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_Sept11CollectionsSimple 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    const PATH_MISC_COLLECTION = '/websites/911digitalarchive.org/REPOSITORY/MISC_COLLECTIONS';
    
    // Sept11 collection IDs of all directories in REPOSITORY/MISC_COLLECTIONS 
    // that contain subdirectories and no not contain HTML or deep directory 
    // hierarchy.
    private $_collectionIdsSept11 = array(
        53, 56, 190, 104, 121, 139, 152, 170, 184, 279, 287, 291, 294, 12389, 
        12403, 12406, 12410, 12414, 12428, 12433, 12448, 12534, 12539, 12543, 
    );
    
    public function delete()
    {
        
    }
    
    public function import()
    {
        
    }
    
    public function getCollectionIdSept11()
    {
        return null;
    }
    
    protected function _getItemTypeMetadata()
    {}
    
    protected function _getItemTypeElementMetadata()
    {}
}
