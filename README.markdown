September 11 Digital Archive Import
=============

A command-line utility used to import September 11 Digital Archive collections 
into Omeka. For available options execute the following:

    $ ./import.php --help

Todo
-------------

* Describe all item types and elements.
* Should DC:Date be 911DA Item:Date Entered on certain collections?
* Should DC:Creator be 911DA Contributor:Name on certain collections?
* Should DC:Title not be the legacy file name on certain collections; e.g. 
  story6712.xml, nmah4366.xml. If not, must check Sept11:OBJECT_TITLE against 
  filename pattern for that specific collection.
