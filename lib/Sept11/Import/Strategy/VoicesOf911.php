<?php
class Sept11_Import_Strategy_VoicesOf911 extends Sept11_Import_Strategy_StrategyAbstract
{
    const COLLECTION_ID = 1000008;

    const REPOSITORY_PATH = '/websites/sept11/home/www/voicesof911';

    public function __construct()
    {
        parent::__construct();

        $this->_collectionSept11 = array(
            'COLLECTION_ID'              => self::COLLECTION_ID, 
            'COLLECTION_TITLE'           => 'Voices of 9.11', 
            'COLLECTION_AUTHOR_DESCRIBE' => 'yes', 
            'COLLECTION_DESC'            => null, 
            'COLLECTION_FOLDER_NAME'     => null, 
            'COLLECTION_ANNOTATION'      => null, 
            'COLLECTION_NOTES'           => null, 
            'COLLECTION_PARENT_ID'       => null, 
            'STATUS_ID'                  => 2, 
            'CONTRIBUTOR_ID'             => 0, 
            'INGEST_ID'                  => null, 
        );

        $this->_collectionSept11['COLLECTION_DESC'] = <<<DESCRIPTION
Voices of 9.11 is a unique collection of personal video testimonies recorded in 2002 and 2003. At a time when language to describe the experience was still being formed, we traveled from New York City, to Shanksville, PA, Washington DC and the Pentagon to record over 500 video narratives.

Voices of 9.11 was explicitly designed to give each individual control of their own story. Inside a private video booth participants started and stopped their own recording. Absolutely no restrictions were placed on what could or could not be said. Participants could speak for any length of time and in whatever language they felt most comfortable.

Voices of 9.11 was created by Ruth Sergel and a dedicated team at here is new york: a democracy of photographs. In 2011 many of the people who worked on the original project came together to bring the entire collection online and accessible to all. As each recording was posted, the original participant was offered the opportunity to add a written update and to receive a DVD of their testimony. We never edit or alter the testimonies. Instead we provide the viewer with search tools to locate the narratives they might find compelling.

Today Voices of 9.11 is jointly held by the New-York Historical Society and the September 11 Digital Archive which has initiated a long term plan to donate its entire collection to the Library of Congress for permanent preservation.
DESCRIPTION;
    }

    public function delete()
    {}

    public function import()
    {
        // Insert the Omeka collection.
        $collectionOmekaId = $this->_insertCollection();

        // Iterate all /websites/sept11/home/www/voicesof911
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::REPOSITORY_PATH)
        );
        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }
            
            // Build the pseudo Sept11 object array.
            $object = array(
                'OBJECT_ID'              => 0, 
                'CONTRIBUTOR_ID'         => 0, 
                'STATUS_ID'              => Sept11_Import::STATUS_APPROVED, 
                'CONSENT_ID'             => Sept11_Import::CONSENT_UNKNOWN, 
                'SOURCE_ID'              => Sept11_Import::SOURCE_DIGITALLY_RECORDED, 
                'OBJECT_MEDIA_TYPE_ID'   => Sept11_Import::MEDIA_TYPE_MOVING_IMAGE, 
                'OBJECT_TITLE'           => $file->getFilename(), 
                'OBJECT_DESC'            => null, 
                'OBJECT_POSTING'         => 'unknown', 
                'OBJECT_COPYRIGHT'       => 'unknown', 
                'OBJECT_ORIG_NAME'       => null, 
                'OBJECT_AUTHOR_CREATE'   => null, 
                'OBJECT_AUTHOR_DESCRIBE' => null, 
                'OBJECT_DATE_ENTERED'    => null, 
                'OBJECT_IP_SOURCE'       => null, 
                'OBJECT_ANNOTATION'      => null, 
                'OBJECT_NOTES'           => null, 
            );

            $this->_insertItem(
                $collectionOmekaId, $object, array(), array(),
                array(
                    'file_transfer_type' => 'Filesystem', 
                    'files' => $file->getPathname(),
                    'file_ingest_options' => array(),
                )
            );
        }
    }

    public function getCollectionIdSept11()
    {}

    protected function _getItemTypeMetadata()
    {}

    protected function _getItemTypeElementMetadata()
    {}
}
