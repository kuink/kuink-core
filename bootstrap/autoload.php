<?php
global $KUINK_INCLUDE_PATH, $KUINK_CFG;
//var_dump($KUINK_INCLUDE_PATH);
require_once ($KUINK_INCLUDE_PATH . 'kuink_config.php');
//require_once ('locallib.php');

spl_autoload_register(function ($class) {
    // project-specific namespace prefix
    global $KUINK_INCLUDE_PATH;

    require_once ($KUINK_INCLUDE_PATH . 'kuink_config.php');

    // Kuink::Core Adapters
    foreach ( glob ( $KUINK_INCLUDE_PATH . 'lib/adapter/layout/*.php' ) as $filename ) {
      include_once $filename;
    }
  
    // Kuink::Core Libraries
    foreach ( glob ( $KUINK_INCLUDE_PATH . 'lib/core/*.php' ) as $filename ) {
      include_once $filename;
    }
    
    // Kuink::libs
    foreach ( glob ( $KUINK_INCLUDE_PATH . 'lib/lib/*.php' ) as $filename ) {
      include_once $filename;
    }
    
    // Kuink::Tools
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/zend_libs/autoload.php');
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/tcpdf/config/lang/eng.php');
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/KuinkPDF/KuinkPDF.php');
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/imapMailbox/ImapMailbox.php');
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/securimage/securimage.php');
    require_once ($KUINK_INCLUDE_PATH . 'lib/tools/googleClientAPI/vendor/autoload.php');
    include_once ($KUINK_INCLUDE_PATH . 'lib/tools/tbs_us/tbs_class.php'); // For manupulating tempaltes odt, docx files
    include_once ($KUINK_INCLUDE_PATH . 'lib/tools/tbs_us/tbs_plugin_opentbs.php'); // For manupulating templates odt, docx files
    //include_once ($KUINK_INCLUDE_PATH . 'lib/tools/smarty/autoloader.php'); // For manupulating templates
                                                                                 
    // Kuink::Instructions
    foreach ( glob ( $KUINK_INCLUDE_PATH . 'lib/instruction/*.php' ) as $filename ) {
      include_once $filename;
    }    
});

?>