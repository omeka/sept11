<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

abstract class Sept11_Import_Strategy_SimpleCollectionAbstract 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    public function delete()
    {
        $this->_deleteCollectionOmeka();
    }
    
    public function import()
    {
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Insert an Omeka item for every Sept11 object.
        foreach ($this->_fetchCollectionObjectsSept11() as $object) {
            
            $metadata = array();
            $elementTexts = array();
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => array(
                    'source' => $object['OBJECT_ABSOLUTE_PATH'], 
                    'name' => $object['OBJECT_ORIG_NAME'], 
                )
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, 
                                         $elementTexts, $fileMetadata);
        }
    }
    
    protected function _getItemTypeMetadata()
    {}
    
    protected function _getItemTypeElementMetadata()
    {}
}
