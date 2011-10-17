<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_Sept11CollectionsComplex 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    const PATH_MISC_COLLECTION = '/websites/911digitalarchive.org/REPOSITORY/MISC_COLLECTIONS';
    
    // Sept11 collection IDs of all directories in REPOSITORY/MISC_COLLECTIONS 
    // that contain subdirectories and contain HTML and/or deep directory 
    // hierarchy.
    private $_collectionIdsSept11 = array(
        44, 64, 67, 158, 175, 193, 219, 283, 297, 316, 2039, 12392, 12416, 
        12421, 12444, 12453, 12546, 12551, 12563, 
    );
    
    public function delete()
    {
        foreach ($this->_collectionIdsSept11 as $collectionIdSept11) {
            $this->_collectionSept11 = $this->_fetchCollectionSept11($collectionIdSept11);
            $this->_deleteCollectionOmeka();
        }
    }
    
    public function import()
    {
        chdir(self::PATH_MISC_COLLECTION);
        
        // Iterate the collections.
        foreach ($this->_collectionIdsSept11 as $collectionIdSept11) {
            
            $this->_collectionSept11 = $this->_fetchCollectionSept11($collectionIdSept11);
            
            // Insert the Omeka collection.
            $collectionOmekaId = $this->_insertCollection();
            
            // Save read_me.txt content in the collection_notes table.
            $pathReadMe = $this->_collectionSept11['COLLECTION_FOLDER_NAME'] . '/read_me.txt';
            if (file_exists($pathReadMe)) {
                $this->_dbOmeka->insert(
                    'collection_notes', 
                    array('collection_id' => $collectionOmekaId, 
                          'note' => file_get_contents($pathReadMe))
                );
            }
            
            // Create a temporary Zip archive from the MISC_COLLECTIONS 
            // directory and remove read_me.txt from it.
            $zipDirName = basename($this->_collectionSept11['COLLECTION_FOLDER_NAME']);
            $zipArchivePath = Sept11_Import::PATH_TMP . "/$zipDirName";
            $dirTree = shell_exec("tree -a -I read_me.txt $zipDirName");
            exec("zip -r $zipArchivePath.zip ./$zipDirName");
            exec("zip -d $zipArchivePath.zip ./$zipDirName/read_me.txt");
            
            // Build the pseudo object array.
            $object = array(
                'OBJECT_ID' => 0, 
                'CONTRIBUTOR_ID' => $this->_collectionSept11['CONTRIBUTOR_ID'], 
                'STATUS_ID' => $this->_collectionSept11['STATUS_ID'], 
                'CONSENT_ID' => 5, 
                'SOURCE_ID' => 6, 
                'OBJECT_MEDIA_TYPE_ID' => 13, 
                'OBJECT_TITLE' => $this->_collectionSept11['COLLECTION_TITLE'], 
                'OBJECT_DESC' => $this->_collectionSept11['COLLECTION_DESC'], 
                'OBJECT_POSTING' => 'unknown', 
                'OBJECT_COPYRIGHT' => 'unknown', 
                'OBJECT_ORIG_NAME' => null, 
                'OBJECT_AUTHOR_CREATE' => null, 
                'OBJECT_AUTHOR_DESCRIBE' => null, 
                'OBJECT_DATE_ENTERED' => null, 
                'OBJECT_IP_SOURCE' => null, 
                'OBJECT_ANNOTATION' => null, 
                'OBJECT_NOTES' => null, 
            );
            
            // Insert the item, attaching the Zip archive.
            $metadata = array();
            $elementTexts = array();
            $fileMetadata = array(
                'file_transfer_type' => 'Filesystem', 
                'files' => array(
                    'source' => "$zipArchivePath.zip", 
                    'metadata' => array(
                        'Dublin Core' => array(
                            'Description' => array(array('text' => $dirTree, 'html' => false))
                        )
                    ), 
                ), 
            );
            
            $itemId = $this->_insertItem($collectionOmekaId, $object, 
                                         $metadata, $elementTexts, $fileMetadata);
            
            // Delete the temporary Zip archive.
            unlink("$zipArchivePath.zip");
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
