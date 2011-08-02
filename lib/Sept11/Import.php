<?php
require_once 'Sept11/Import/Exception.php';
require_once 'Sept11/Db/Sept11.php';

class Sept11_Import
{
    /** Paths */
    const PATH_SEPT11 = '/websites/911digitalarchive.org/omeka/sept11/lib';
    const PATH_OMEKA_PATHS = '/websites/911digitalarchive.org/omeka/paths.php';
    
    /** Sept11 item element set name */
    const SEPT11_ITEM_ELEMENT_SET = '911DA Item';
    
    /** Sept11 contributor element set name */
    const SEPT11_CONTRIBUTOR_ELEMENT_SET = '911DA Contributor';
    
    /** Sept11 sources */
    const SOURCE_BORN_DIGITAL = 1;
    const SOURCE_DIGITAL_IMAGE = 2;
    const SOURCE_SCANNED_IMAGE = 3;
    const SOURCE_DIGITALLY_RECORDED = 4;
    const SOURCE_TRANSCRIPTION = 5;
    const SOURCE_UNKNOWN = 6;
    
    /** Sept11 media types */
    const MEDIA_TYPE_AUDIO = 1;
    const MEDIA_TYPE_DOCUMENT = 3;
    const MEDIA_TYPE_STILL_IMAGE = 4;
    const MEDIA_TYPE_MOVING_IMAGE = 5;
    const MEDIA_TYPE_EMAIL = 6;
    const MEDIA_TYPE_WEBPAGE = 7;
    const MEDIA_TYPE_STORY = 8;
    const MEDIA_TYPE_ARTICLE = 9;
    const MEDIA_TYPE_MIXED_MEDIA = 10;
    const MEDIA_TYPE_DATA = 11;
    const MEDIA_TYPE_INTERVIEW = 12;
    const MEDIA_TYPE_UNKNOWN = 13;
    
    /** Sept11 consents */
    const CONSENT_FULL = 1;
    const CONSENT_NO = 2;
    const CONSENT_IMPLIED = 3;
    const CONSENT_CONDITIONAL = 4;
    const CONSENT_UNKNOWN = 5;
    
    /** Sept11 statuses */
    const STATUS_REVIEW = 1;
    const STATUS_APPROVED = 2;
    const STATUS_REJECTED = 3;
    const STATUS_FEATURED = 4;
    const STATUS_PRIVATE = 5;
    
    /** Collection flags */
    public static $collectionFlags = array(
        'LcArt', // Library of Congress Art
        'LcEmails', // Library of Congress Emails
        'LcStories', // Library of Congress Stories
        'RcStories', // American Red Cross Museum Stories
        'CairStories', // Council on American-Islamic Relations Stories
        'SeiuStories', // Service Employees International Union Stories
        'TyrImages', // Thank You Rescuers Still Images
        'TomPaineStories', // TomPaine.com Stories
        'MapcEmails', // Madison Area Peace Coalition E-mails
        'VtmbhArticles', // "Voices That Must Be Heard" Articles
        'NycfdIaps', // New York City Fire Department Incident Action Plans
        'RagsdaleFlyers', // Michael Ragsdale Flyer Collection
        'Sept11Interviews', // September 11 Digital Archive Interviews
        'MemeacInterviews', // Middle East and Middle Eastern American Center Interviews
        'Sept11Animations', // September 11 Digital Archive Collected Digital Animations and Creations
        'Seiu1199', // 1199 Service Employees International Union Collection
        'FioreAnimations', // Mark Fiore Animations
        'Tepeyac', // Asociación Tepeyac de New York Collection
        'Sept11Reports', // September 11 Digital Archive Collected Reports
        'ApInterviews', // Acting Patriotic Interviews
        'Mssm', // mtsinai
        'SatanEmails', // "Satan in the Smoke" Emails
        'Sept11Stories', // September 11 Digital Archive Stories
        'DojEmails', // Department of Justice Emails
        'Sept11Images', // September 11 Digital Archive Images
        'Sept11Photos', // September 11 Digital Archive Images Photos
        'Sept11Art', // September 11 Digital Archive Images Photos Art
        'Sept11DigitalArt', // September 11 Digital Archive Still Images
        'Sept11Emails', // September 11 Digital Archive Emails
        'NmahStories', // "September 11: Bearing Witness to History" Stories
        'NmahCards', // "September 11: Bearing Witness to History" Stories from exhibit Visitors
        'NmahCardsUncategorized', // "September 11: Bearing Witness to History" Stories from exhibit Visitors UNCATEGORIZED COLLECTION
        'Sept11Collections', // September 11 Collections
        'Sept11Uploads', // Uploaded files
    );
    
    /** Element sets to install */
    public static $elementSets = array(
        array(
            'metadata' => array(
                'name' => self::SEPT11_ITEM_ELEMENT_SET, 
                'description' => 'Elements describing a September 11 Digital Archive item.', 
            ), 
            'element_metadata' => array(
                array('name' => 'Status', // STATUS_ID
                      'description' => 'The process status of this item.'), 
                array('name' => 'Consent', // CONSENT_ID
                      'description' => 'Whether September 11 Digital Archive has permission to possess this item.'), 
                array('name' => 'Posting', // OBJECT_POSTING
                      'description' => 'Whether the contributor gave permission to post this item.'), 
                array('name' => 'Copyright', // OBJECT_COPYRIGHT
                      'description' => 'Whether the contributor holds copyright to this item.'), 
                array('name' => 'Source', // SOURCE_ID
                      'description' => 'The source of this item.'), 
                array('name' => 'Media Type', // OBJECT_MEDIA_TYPE_ID
                      'description' => 'The media type of this item.'), 
                array('name' => 'Original Name', // OBJECT_ORIG_NAME
                      'description' => 'The original name of this item.'), 
                array('name' => 'Created by Author', // OBJECT_AUTHOR_CREATE
                      'description' => 'Whether the author created this item.'), 
                array('name' => 'Described by Author', // OBJECT_AUTHOR_DESCRIBE
                      'description' => 'Whether the description of this item was submitted by the author.'), 
                array('name' => 'Date Entered', // OBJECT_DATE_ENTERED
                      'description' => 'The date this item was entered into the archive.'), 
                array('name' => 'IP Address', // OBJECT_IP_SOURCE
                      'description' => 'The IP address of the device used to submit the item.'), 
                array('name' => 'Annotation', // OBJECT_ANNOTATION
                      'description' => 'Annotations to this item.'), 
                array('name' => 'Notes', // OBJECT_NOTES
                      'description' => 'Notes about this item.'), 
            ), 
        ), 
        array(
            'metadata' => array(
                'name' => self::SEPT11_CONTRIBUTOR_ELEMENT_SET, 
                'description' => 'Elements describing the contributor of a September 11 Digital Archive item.', 
            ), 
            'element_metadata' => array(
                array('name' => 'Name', // CONTRIBUTOR_NAME
                      'description' => 'The contributor\'s name.'), 
                array('name' => 'Phone', // CONTRIBUTOR_PHONE
                      'description' => 'The contributor\'s phone number.'), 
                array('name' => 'Email', // CONTRIBUTOR_EMAIL
                      'description' => 'The contributor\'s email address.'), 
                array('name' => 'Location', // CONTRIBUTOR_LOCATION
                      'description' => 'The contributor\'s location at the time of the attacks on September 11, 2001.'), 
                array('name' => 'Residence', // CONTRIBUTOR_RESIDENCE
                      'description' => 'The contributor\'s residence at the time of contribution.'), 
                array('name' => 'Zipcode', // CONTRIBUTOR_ZIPCODE
                      'description' => 'The contributor\'s zipcode at the time of contribution.'), 
                array('name' => 'Age', // CONTRIBUTOR_AGE
                      'description' => 'The contributor\'s age at the time of contribution.'), 
                array('name' => 'Gender', // CONTRIBUTOR_GENDER
                      'description' => 'The contributor\'s gender.'), 
                array('name' => 'Race', // CONTRIBUTOR_RACE
                      'description' => 'The contributor\'s race.'), 
                array('name' => 'Occupation', // CONTRIBUTOR_OCCUPATION
                      'description' => 'The contributor\'s occupation at the time of contribution.'), 
                array('name' => 'Leads', // CONTRIBUTOR_LEADS
                      'description' => 'Emails of people whom the contributor thinks is interested in the September 11 Digital Archive.'), 
                array('name' => 'Contact', // CONTRIBUTOR_CONTACT
                      'description' => 'Whether the contributor gives the September 11 Digital Archive permission to contact him/her.'), 
                array('name' => 'How Hear', // CONTRIBUTOR_HOWHEAR
                      'description' => 'How did the contributor hear about the September 11 Digital Archive?'), 
                array('name' => 'Notes', // CONTRIBUTOR_NOTES
                      'description' => 'Notes about this contributor.'), 
                array('name' => 'Posting', // CONTRIBUTOR_POSTING
                      'description' => 'Whether the contributor gave permission to post his/her name.'), 
                array('name' => 'Annotation', // CONTRIBUTOR_ANNOTATION
                      'description' => 'Annotations to this contributor.'), 
            ), 
        ), 
    );
    
    /** Sept11_Import_Strategy_Interface */
    private $_strategy;
    
    /**
     * Construct the import object.
     */
    public function __construct(Sept11_Import_Strategy_Interface $strategy)
    {
        // Load Omeka if not already loaded.
        self::loadOmeka();
        $this->_strategy = $strategy;
    }
    
    /**
     * Delete a previously imported Omeka collection.
     */
    public function delete()
    {
        $this->_strategy->delete();
    }
    
    /**
     * Import the Sept11 collection into Omeka.
     * 
     * Must delete a previously imported Omeka collection to ensure that there 
     * is only one Omeka collection to every Sept11 collection.
     */
    public function import()
    {
        $this->_strategy->delete();
        $this->_strategy->import();
    }
    
    /**
     * Resume a previously initialized import.
     */
    public function resume()
    {
        $this->_strategy->import();
    }
    
    /**
     * Install the Omeka import environment.
     */
    public static function install()
    {
        // Load Omeka if not already loaded.
        self::loadOmeka();
        
        // Uninstall the import environment in not already.
        self::uninstall();
        
        // Create import log tables.
        $sql = '
        CREATE TABLE `sept11_import_collections_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `collection_id_sept11` int(10) unsigned NOT NULL,
            `collection_id_omeka` int(10) unsigned NOT NULL,
            `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        self::getDbOmeka()->query($sql);
        
        $sql = '
        CREATE TABLE `sept11_import_items_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `object_id` int(10) unsigned NOT NULL,
            `item_id` int(10) unsigned NOT NULL,
            `collection_id_omeka` int(10) unsigned NOT NULL,
            `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        self::getDbOmeka()->query($sql);
        
        $sql = '
        CREATE TABLE `sept11_import_error_log` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `object_id` int(10) unsigned NOT NULL,
            `collection_id_omeka` int(10) unsigned NOT NULL,
            `exception` text collate utf8_unicode_ci,
            `message` text collate utf8_unicode_ci,
            `code` text collate utf8_unicode_ci,
            `file` text collate utf8_unicode_ci,
            `line` text collate utf8_unicode_ci,
            `trace` text collate utf8_unicode_ci,
            `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';
        self::getDbOmeka()->query($sql);
        
        // Insert element sets.
        foreach (self::$elementSets as $elementSet) {
            insert_element_set($elementSet['metadata'], $elementSet['element_metadata']);
        }
        
        // Disable file validation.
        set_option('disable_default_file_validation', '1');
    }
    
    /**
     * Uninstall the Omeka import environment.
     * 
     * This will return the Omeka database and archive directory to their 
     * original state.
     */
    public static function uninstall()
    {
        // Load Omeka if not already loaded.
        self::loadOmeka();
        $db = self::getDbOmeka();
        
        // Reset the Omeka tables to their original state.
        $sql = "TRUNCATE `{$db->prefix}element_texts`";
        $db->query($sql);
        $sql = "TRUNCATE `{$db->prefix}files`";
        $db->query($sql);
        $sql = "TRUNCATE `{$db->prefix}items`";
        $db->query($sql);
        $sql = "TRUNCATE `{$db->prefix}collections`";
        $db->query($sql);
        $sql = "DELETE FROM `{$db->prefix}item_types` WHERE `id` > 13";
        $db->query($sql);
        $sql = "DELETE FROM `{$db->prefix}item_types_elements` WHERE `id` > 47";
        $db->query($sql);
        $sql = "DELETE FROM `{$db->prefix}element_sets` WHERE `id` > 6";
        $db->query($sql);
        $sql = "DELETE FROM `{$db->prefix}elements` WHERE `id` > 85";
        $db->query($sql);
        
        // Delete import log tables.
        $sql = 'DROP TABLE IF EXISTS `sept11_import_collections_log`';
        $db->query($sql);
        $sql = 'DROP TABLE IF EXISTS `sept11_import_items_log`';
        $db->query($sql);
        $sql = 'DROP TABLE IF EXISTS `sept11_import_error_log`';
        $db->query($sql);
        
        // Optimize the database.
        Sept11_Import::optimize();
        
        // Unlink all archive files. Directory constants from Omeka's paths.php.
        $paths = array(FILES_DIR, FULLSIZE_DIR, THUMBNAIL_DIR, SQUARE_THUMBNAIL_DIR);
        foreach ($paths as $path) {
            $dir = new DirectoryIterator($path);
            foreach ($dir as $fileInfo) {
                // Do not unlink index.php.
                if ($fileInfo->isFile() 
                    && !$fileInfo->isDot()
                    && 'index.html' != $fileInfo->getFilename()) {
                    unlink($fileInfo->getPathname());
                }
            }
        }
    }
    
    /**
     * Optimize the Omeka database
     */
    public static function optimize()
    {
        // Load Omeka if not already loaded.
        self::loadOmeka();
        $db = self::getDbOmeka();
        
        // Get all the tables and optimize them.
        $tables = $db->fetchCol('SHOW TABLES');
        $sql = 'OPTIMIZE TABLE ' . implode(', ', $tables);
        $db->query($sql);
    }
    
    /**
     * View collection import status.
     */
    public static function status()
    {
        // Get the imported collections. This will get the count of all items 
        // that were imported without errors. Sometimes an item is saved to 
        // Omeka but certain errors (e.g. file ingest errors) prevent it from 
        // being logged.
        $sql = '
        SELECT sicl.*, COUNT(*) item_count 
        FROM `sept11_import_collections_log` sicl
        JOIN `sept11_import_items_log` siil
        ON sicl.`collection_id_omeka` = siil.`collection_id_omeka` 
        GROUP BY siil.`collection_id_omeka`';
        $collectionsImported = array();
        foreach (self::getDbOmeka()->fetchAll($sql) as $collectionImported) {
            $collectionsImported[$collectionImported['collection_id_sept11']] = $collectionImported;
        }
        
        echo "\n";
        printf("%-25s%-18s%-8s%-8s%-8s%-14s%s\n", 
               'Collection Flag', 'Status', '911ID', '911#', 'OID', 'O#', 'Timestamp');
        echo str_repeat('-', 100) . "\n";
        foreach (self::$collectionFlags as $collectionFlag) {
            try {
                // Get the collection ID from the import strategy.
                $collectionIdSept11 = self::getStrategy($collectionFlag)->getCollectionIdSept11();
                
                // Get the Sept11 object count for this collection.
                $sql = '
                SELECT COUNT(*) 
                FROM `COLLECTIONS_OBJECTS` 
                WHERE `COLLECTION_ID` = ? 
                GROUP BY `COLLECTION_ID`';
                $countSept11 = self::getDbSept11()->fetchOne($sql, $collectionIdSept11);
                
                // The collection has been implemented and imported.
                if (array_key_exists($collectionIdSept11, $collectionsImported)) {
                    $collectionIdOmeka = $collectionsImported[$collectionIdSept11]['collection_id_omeka'];
                    // Get the total item count for this collection, including 
                    // items imported with errors.
                    $countItem = self::getDbOmeka()->getTable('Item')->count(array('collection' => $collectionIdOmeka));
                    $countOmeka = $countItem . '/' . $collectionsImported[$collectionIdSept11]['item_count'];
                    $status = 'Imported';
                    $timestamp = $collectionsImported[$collectionIdSept11]['timestamp'];
                // The collection has been implemented but not imported.
                } else {
                    $collectionIdOmeka = null;
                    $countOmeka = null;
                    $status = 'Implemented';
                    $timestamp = null;
                }
                
            // The collection has not been implemented.
            } catch (Sept11_Import_Exception $e) {
                $collectionIdSept11 = null;
                $collectionIdOmeka = null;
                $countSept11 = null;
                $countOmeka = null;
                $status = 'Not implemented';
                $timestamp = null;
            }
            printf("%-25s%-18s%-8s%-8s%-8s%-14s%s\n", 
                   $collectionFlag, $status, $collectionIdSept11, $countSept11, 
                   $collectionIdOmeka, $countOmeka, $timestamp);
        }
        echo str_repeat('-', 100) . "\n";
        echo "\n" 
           . "Key:\n" 
           . "Collection Flag: the name of this collection import strategy\n" 
           . "911ID: the 911DA collection ID\n" 
           . "911#: the object count for this 911DA collection\n" 
           . "OID: the Omeka collection ID\n" 
           . "O#: the item count for this Omeka collection, [total]/[without errors]\n" 
           . "Status: the status of this collection import strategy\n\n";
    }
    
    /**
     * Load the Omeka application to access its API.
     */
    public static function loadOmeka()
    {
        // Do not reload Omeka.
        if (!class_exists('Omeka_Context')) {
            require_once self::PATH_OMEKA_PATHS;
            $app = new Omeka_Core;
            
            // Bootstrap only those resources that are required.
            $app->getBootstrap()->bootstrap('Plugins');
            $app->getBootstrap()->bootstrap('Jobs');
            $app->getBootstrap()->bootstrap('Storage');
        }
    }
    
    /**
     * Return the import strategy object derived from the specified collection 
     * flag.
     * 
     * @param string $collectionFlag
     * @return Sept11_Import_Strategy_Interface
     */
    public static function getStrategy($collectionFlag)
    {
        if (!in_array($collectionFlag, self::$collectionFlags)) {
            throw new Sept11_Import_Exception("\"$collectionFlag\" is an invalid collection.");
        }
        
        // Check if the strategy class exists.
        if (!@include "Sept11/Import/Strategy/$collectionFlag.php") {
            throw new Sept11_Import_Exception("\"$collectionFlag\" import is not yet implemented.");
        }
        $strategyClass = "Sept11_Import_Strategy_$collectionFlag";
        if (!class_exists($strategyClass)) {
            throw new Sept11_Import_Exception("\"$collectionFlag\" import is not yet implemented.");
        }
        
        return new $strategyClass;
    }
    
    /**
     * Convenience method. Return the Omeka database object.
     * 
     * @return Omeka_Db
     */
    public static function getDbOmeka()
    {
        // Load Omeka if not already loaded.
        self::loadOmeka();
        return Omeka_Context::getInstance()->getDb();
    }
    
    /**
     * Convenience method. Return the Sept11 database object.
     * 
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getDbSept11()
    {
        return Sept11_Db_Sept11::getInstance();
    }
}
