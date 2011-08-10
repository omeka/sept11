<?php
require_once 'Sept11/Import/Strategy/SimpleCollectionAbstract.php';

class Sept11_Import_Strategy_AriannaZinda 
    extends Sept11_Import_Strategy_SimpleCollectionAbstract
{
    const COLLECTION_ID = 35;
    
    public function getCollectionIdSept11()
    {
        return self::COLLECTION_ID;
    }
}
