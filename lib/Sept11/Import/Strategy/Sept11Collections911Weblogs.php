<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

// This was separated out from Sept11CollectionsComplex because the ZIP file 
// comprising every subdirectory was too large for Omeka to handle. Instead, 
// this breaks out each subdirectory as a separate file.
class Sept11_Import_Strategy_Sept11Collections911Weblogs 
    extends Sept11_Import_Strategy_StrategyAbstract
{
    const PATH_MISC_COLLECTION = '/websites/sept11/home/www/911digitalarchive.org/REPOSITORY/MISC_COLLECTIONS/911_weblogs';
    
    public function delete()
    {
        $this->_deleteCollectionOmeka();
    }
    
    public function import()
    {
        chdir(self::PATH_MISC_COLLECTION);
        
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();
        
        // Create all the ZIP archives and directory trees.
        $zipFiles = array();
        foreach (scandir($this->_collectionSept11['COLLECTION_FOLDER_NAME']) as $dirName) {
            if (in_array($dirName, array('.', '..'))) {
                continue;
            }
            
            $dirPath = $this->_collectionSept11['COLLECTION_FOLDER_NAME'] . "/$dirName";
            if (!is_dir($dirPath)) {
                continue;
            }
            
            // Create a temporary Zip archive from the 911_weblogs directory.
            $zipDirName = basename($dirPath);
            $zipArchivePath = Sept11_Import::PATH_TMP . "/$zipDirName";
            $dirTree = shell_exec("tree -a -I read_me.txt $zipDirName");
            exec("zip -r $zipArchivePath.zip ./$zipDirName");
            
            $zipFiles[] = array(
                'source' => "$zipArchivePath.zip", 
                'metadata' => array(
                    'Dublin Core' => array(
                        'Description' => array(array('text' => $dirTree, 'html' => false))
                    ),
                ), 
            );
        }
        
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
            'files' => $zipFiles, 
        );
        
        $itemId = $this->_insertItem($collectionOmekaId, $object, 
                                     $metadata, $elementTexts, $fileMetadata);
        
        // Delete the temporary Zip archive.
        foreach ($zipFiles as $zipFile) {
            unlink($zipFile['source']);
        }
    }
    
    public function getCollectionIdSept11()
    {
        return 2039;
    }
    
    protected function _getItemTypeMetadata()
    {}
    
    protected function _getItemTypeElementMetadata()
    {}
}
