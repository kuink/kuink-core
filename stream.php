<?php

/*KUINK******************************************/
global $KUINK_INCLUDE_PATH;
global $KUINK_CFG;
require_once($KUINK_INCLUDE_PATH."kuink_includes.php");

  $type = $_GET['type'];
  $guid = $_GET['guid'];

  switch ($type) {
    case "photo":
      $file = $guid.'.jpg';
      $base = $KUINK_CFG->imageRoot.$type.'/';
      $baseRP = realpath($base);
      $path = realpath($base.$file);
			$pos = strpos($path.'/', $baseRP);
      if ($pos === FALSE) {
        $file = 'default.jpg';
      }
      $size = getimagesize($base.$file);
      if ($size)
      {
        ob_clean();
        header('Content-Type: '.$size['mime']);
        header('Content-Length: '.filesize($base.$file));
        readfile($base.$file);
      }
      break;
      
    case "bpmn":
    	$split = explode(',', $guid);
    	$application = $split[0];
    	$process = $split[1];
    	$bpmn = $split[2];
    	$KUINK_DATASOURCES 	= array();
    	$KUINK_APPLICATION = new Kuink\Core\Application('framework', $USER->lang, null);
 
    	$base=$KUINK_APPLICATION->appManager->getApplicationBase('framework');
    	
    	$filename=$KUINK_CFG->appRoot.'apps/'.$base.'/'.$application.'/process/'.$process.'/bpmn/'.$bpmn.'.png';
    	ob_clean();
    	header('Content-Type: image/png');
    	header('Content-Length: '.filesize($filename));
    	readfile($filename);
    	 
    	break;

    case "file":
      $KUINK_DATASOURCES  = array();
      $KUINK_APPLICATION = new Kuink\Core\Application('framework', 'pt', null);
      
      if (!empty($guid))
      {
        $dataAccess = new \Kuink\Core\DataAccess('load', 'framework', 'config');
        $params['_entity'] = 'fw_file';
        $params['guid'] = $guid;
        $file_record = $dataAccess->execute($params);

        $file = (string)$file_record['name'];
        $path = (string)$file_record['path'];

      } else {
        //Without guid
        header('HTTP/1.0 404 not found');
        print_error('Not Allowed', 'error'); 

      }
       
      $pathName = $KUINK_CFG->dataRoot.'/'.$path.'/'.$file;

      if (file_exists($pathName) and !is_dir($pathName)) {
          ob_clean();
          header('Content-Type: '.$file_record['mimetype']);
          header('Content-Length: '.filesize($pathName.$file));
          
          readfile($pathName);
          //send_file($pathName, $file, 0);
      } else {
          header('HTTP/1.0 404 not found');
          print_error('filenotfound', 'error'); 
      }      
      break;      

    case "tmp":
      $pathName = $KUINK_CFG->dataRoot.'/neon/files/tmp/'.$guid;

      if (file_exists($pathName) and !is_dir($pathName)) {
          ob_clean();
          header('Content-Type: text/csv');
          header('Content-Length: '.filesize($pathName));        
          readfile($pathName);

      } else {
          header('HTTP/1.0 404 not found');
          print_error('filenotfound', 'error'); 
      }


  }

?>
