#!/usr/bin/php -d memory_limit=500M
<?php
/**
 * Confirm a user action.
 * 
 * @param string $prompt
 */
function confirm($prompt)
{
    if ('yes' != readline("$prompt (yes, no): ")) exit;
}

// Set the Sept11 library path.
set_include_path(dirname(__FILE__) . '/lib' . PATH_SEPARATOR . get_include_path());

// Load Omeka to access Zend Framework.
require_once 'Sept11/Import.php';
Sept11_Import::loadOmeka();

try {
    // Parse the command-line options.
    require_once 'Zend/Console/Getopt.php';
    $options = new Zend_Console_Getopt(array(
        'help' => 'this help screen', 
        'install' => 'install the Omeka import environment', 
        'uninstall' => 'uninstall the Omeka import environment', 
        'optimize' => 'optimize the Omeka database; usually done after uninstall/delete', 
        'status' => 'view the colletion import status', 
        'collection|c=s' => 'set a collection for the specified action', 
        'import|i' => 'import this Sept11 collection into Omeka; will resume a previously initialized import', 
        'delete|d' => 'delete a previously imported Omeka collection; do not import', 
    ));
    $options->parse();
    
} catch (Zend_Console_Getopt_Exception $e) {
     echo "Invalid options. No action taken.\n";
     exit;
}

try {
    if ($options->getOption('help')) {
        echo "\n{$options->getUsageMessage()}\n";
        echo 'Select from these collections: ' . implode(', ', Sept11_Import::$collectionFlags) . "\n\n";
        exit;
    } else if ($options->getOption('install')) {
        confirm('Install the Omeka import environment?');
        Sept11_Import::install();
        exit;
    } else if ($options->getOption('uninstall')) {
        confirm('Uninstall the Omeka import environment?');
        Sept11_Import::uninstall();
        exit;
    } else if ($options->getOption('optimize')) {
        Sept11_Import::optimize();
        exit;
    } else if ($options->getOption('status')) {
        Sept11_Import::status();
        exit;
    }
    
    // Get the collection flag.
    $collectionFlag = $options->getOption('c');
    if (!$collectionFlag) {
        echo "A collection must be specified. No action taken.\n";
        exit;
    }
    
    // Set the import strategy.
    $import = new Sept11_Import(Sept11_Import::getStrategy($collectionFlag));
    
    // Run an action on the collection.
    if ($options->getOption('d')) {
        confirm('Delete this Omeka collection?');
        $import->delete();
    } else if ($options->getOption('i')) {
        $import->import();
    } else {
        echo "An action must be specified. No action taken.\n";
        exit;
    }
    
} catch (Sept11_Import_Exception $e) {
    echo "{$e->getMessage()}\n";
    exit;
}
