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
                    $this->_dbOmeka->insert(
                        'sept11_collection_notes', 
                        array('collection_id' => $collectionOmekaId, 
                              'note' => file_get_contents($object['OBJECT_ABSOLUTE_PATH']))
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
            
            // Import child collections.
            $childCollectionsSept11 = $this->_fetchChildCollectionsSept11($this->_collectionSept11['COLLECTION_ID']);
            
            foreach ($childCollectionsSept11 as $childCollectionsSept11) {
                
                $this->_collectionSept11 = $this->_fetchCollectionSept11($childCollectionsSept11['COLLECTION_ID']);
                
                // Insert the Omeka collection.
                $childCollectionOmekaId = $this->_insertCollection();
                
                // Save the collection parent/child relationship.
                $collectionTree = new CollectionTree;
                $collectionTree->collection_id = $childCollectionOmekaId;
                $collectionTree->parent_collection_id = $collectionOmekaId;
                $collectionTree->save();
                
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
                    
                    $itemId = $this->_insertItem($childCollectionOmekaId, $object, $metadata, 
                                                 $elementTexts, $fileMetadata);
                }
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
