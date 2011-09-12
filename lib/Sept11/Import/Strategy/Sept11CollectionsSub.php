<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

class Sept11_Import_Strategy_Sept11CollectionsSub 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    const PATH_MISC_COLLECTION = '/websites/911digitalarchive.org/REPOSITORY/MISC_COLLECTIONS';
    
    // Sept11 collection IDs of all directories in REPOSITORY/MISC_COLLECTIONS 
    // that contain subdirectories.
    private $_collectionIdsSept11 = array(
        44, 53, 56, 64, 67, 190, 104, 121, 139, 152, 158, 170, 175, 184, 193, 
        219, 279, 283, 287, 291, 294, 297, 316, 2039, 12389, 12392, 12403, 
        12406, 12410, 12414, 12416, 12421, 12428, 12433, 12444, 12448, 12453, 
        12534, 12539, 12543, 12546, 12551, 12563, 
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
            
            // Create a temporary Zip archive from the MISC_COLLECTIONS 
            // directory and remove read_me.txt from it.
            $zipDirName = substr(strrchr($this->_collectionSept11['COLLECTION_FOLDER_NAME'], '/'), 1);
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
