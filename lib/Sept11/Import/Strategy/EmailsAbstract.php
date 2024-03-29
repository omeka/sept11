<?php
require_once 'Sept11/Import/Strategy/StrategyAbstract.php';

/**
 * Extend email import strategies off this class.
 */
abstract class Sept11_Import_Strategy_EmailsAbstract extends Sept11_Import_Strategy_StrategyAbstract
{
    private $_itemTypeMetadata = array(
        'name' => 'September 11 Email', 
        'description' => '',
    );
    
    private $_itemTypeElementMetadata = array(
        array(
            'name' => 'September 11 Email: Body', 
            'description' => 'The basic content, as unstructured text; sometimes containing a signature block at the end.',
        ), 
        array(
            'name' => 'September 11 Email: Date', 
            'description' => 'The local time and date when the message was written.',
        ), 
        array(
            'name' => 'September 11 Email: To', 
            'description' => 'The email addresses, and optionally names of the message\'s recipients',
        ), 
        array(
            'name' => 'September 11 Email: From', 
            'description' => 'The email address, and optionally the name of the author.',
        ), 
        array(
            'name' => 'September 11 Email: CC', 
            'description' => 'The email addresses of those who received the message addressed primarily to another.',
        ), 
        array(
            'name' => 'September 11 Email: Subject', 
            'description' => 'A brief summary of the topic of the message.',
        ), 
    );
    
    protected function _getItemTypeMetadata()
    {
        return $this->_itemTypeMetadata;
    }
    
    protected function _getItemTypeElementMetadata()
    {
        return $this->_itemTypeElementMetadata;
    }
}
