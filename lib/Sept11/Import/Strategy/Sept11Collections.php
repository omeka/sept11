<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

/**
 * Represents all directories in REPOSITORY/MISC_COLLECTIONS that contain no 
 * subdirectories.
 */
class Sept11_Import_Strategy_Sept11Collections 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    // Sept11 collection IDs of all directories in REPOSITORY/MISC_COLLECTIONS 
    // that contain no subdirectories.
    private $_collectionIdsSept11 = array(
        34, 35, 36, 37, 38, 39, 40, 41, 42, 43, 49, 50, 51, 52, 55, 61, 62, 63, 
        66, 97, 98, 99, 100, 101, 102, 103, 109, 110, 111, 112, 113, 114, 115, 
        116, 117, 118, 119, 120, 124, 125, 126, 127, 128, 129, 130, 131, 132, 
        133, 134, 135, 136, 137, 138, 143, 144, 145, 146, 147, 148, 149, 150, 
        151, 155, 156, 157, 166, 167, 168, 169, 173, 174, 178, 179, 180, 181, 
        182, 183, 192, 211, 212, 213, 214, 215, 216, 217, 218, 273, 274, 275, 
        276, 277, 278, 281, 282, 285, 286, 290, 296, 314, 315, 2034, 2035, 2036, 
        2037, 2038, 12386, 12387, 12388, 12391, 12408, 12409, 12412, 12413, 
        12419, 12420, 12427, 12431, 12432, 12435, 12436, 12437, 12438, 12439, 
        12440, 12441, 12442, 12443, 12451, 12452, 12533, 12537, 12538, 12541, 
        12542, 12545, 12549, 12550, 
    );
    
    public function delete()
    {
        // Iterate the collections.
        foreach ($this->_collectionIdsSept11 as $collectionIdSept11) {
            $this->_collectionSept11 = $this->_fetchCollectionSept11($collectionIdSept11);
            $this->_deleteCollectionOmeka();
        }
    }
    
    public function import()
    {
        // Iterate the collections.
        foreach ($this->_collectionIdsSept11 as $collectionIdSept11) {
            
            $this->_collectionSept11 = $this->_fetchCollectionSept11($collectionIdSept11);
            
            // Insert the Omeka collection.
            $collectionOmekaId = $this->_insertCollection();
            
            // Insert an Omeka item for every Sept11 object.
            foreach ($this->_fetchCollectionObjectsSept11() as $object) {
                
                // read_me.txt files should not be imported as items. Rather, 
                // their contents should be saved in the collection_notes table.
                if ('read_me.txt' == $object['OBJECT_TITLE']) {
                    $sql = 'INSERT INTO collection_notes (note) VALUES (?)';
                    $this->_dbOmeka->insert(
                        'collection_notes', 
                        array('note' => file_get_contents($object['OBJECT_ABSOLUTE_PATH']))
                    );
                    continue;
                }
                
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
