<?php
require_once 'Sept11/Import/Strategy/Abstract.php';

class Sept11_Import_Strategy_NmahCards extends Sept11_Import_Strategy_Abstract
{
    const COLLECTION_ID = 31;
    
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
            
            echo $object['OBJECT_ABSOLUTE_PATH'] . "\n";
            
            $xml = new SimpleXMLElement($object['OBJECT_ABSOLUTE_PATH'], null, true);
            $files = array();
            foreach ($xml->FILEPATH as $filepath) {
                if (preg_match('/^tifs\//', $filepath)) {
                    $files[] = '/websites/911digitalarchive.org/REPOSITORY/SMITHCARDS/' . $filepath; 
                }
            }
            natsort($files);
            
            $metadata = array();
            $elementTexts = array();
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => $files, 
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, $metadata, $elementTexts, $fileMetadata);
        }
    }
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
    
    protected function _getItemTypeMetadata()
    {}
    
    protected function _getItemTypeElementMetadata()
    {}
}
