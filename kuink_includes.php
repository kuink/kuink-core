<?php
global $KUINK_INCLUDE_PATH;

require_once ($KUINK_INCLUDE_PATH . 'kuink_config.php');

// Kuink::Core Libraries
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Control.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Runtime.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Formatter.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Factory.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Message.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/DataSource.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Language.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Application.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/ProcessOrchestrator.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Tools.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Layout.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Parser.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/ParserFunctions.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Exception.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Reflection.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Lib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/NodeManager.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/ApplicationManager.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/DataAccess.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/DataSourceClass.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/DataSourceManager.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/DataSourceConnector.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/Instruction.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/InstructionManager.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/core/TraceManager.php');

// Kuink::libs
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/DateTimeLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/FileLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/StringLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/UtilsLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/ValidationLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/MathLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/FormatterLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/SetLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/TemplateLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/AsciiLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/ReflectionLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/MessageLib.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/lib/MetadataLib.php');

// Kuink::Tools
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/zend_libs/autoload.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/tcpdf/config/lang/eng.php');
// require_once($KUINK_INCLUDE_PATH.'lib/tools/tcpdf_min/tcpdf.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/KuinkPDF/KuinkPDF.php');
// require_once($KUINK_INCLUDE_PATH.'lib/tools/cmisClient/CMISWebService.php');
// require_once($KUINK_INCLUDE_PATH.'lib/tools/cmisClient/CMISAlfrescoSoapClient.php');
// require_once($KUINK_INCLUDE_PATH.'lib/tools/cmisClient/cmisTypeDefinitions.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/imapMailbox/ImapMailbox.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/securimage/securimage.php');
require_once ($KUINK_INCLUDE_PATH . 'lib/tools/googleClientAPI/autoload.php');
include_once ($KUINK_INCLUDE_PATH . 'lib/tools/tbs_us/tbs_class.php'); // For manupulating tempaltes odt, docx files
include_once ($KUINK_INCLUDE_PATH . 'lib/tools/tbs_us/tbs_plugin_opentbs.php'); // For manupulating templates odt, docx files
include_once ($KUINK_INCLUDE_PATH . 'lib/tools/smarty/autoloader.php'); // For manupulating templates odt, docx files
                                                                             
// Kuink::Instructions
foreach ( glob ( $KUINK_INCLUDE_PATH . 'lib/instruction/*.php' ) as $filename ) {
	include_once $filename;
}

/*Kuink******************************************/
