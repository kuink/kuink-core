<?php

use Kuink\UI\Formatter\Dump;

// This file is part of Kuink Application Framework
//
// Kuink Application Framework is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Kuink Application Framework is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Kuink Application Framework. If not, see <http://www.gnu.org/licenses/>.
include_once (__DIR__ . '/UtilsLib.php');
class FileLib {
	var $msg_manager;
	var $nodeconfiguration;
	function __construct($nodeconfiguration, $msg_manager) {
		$this->msg_manager = $msg_manager;
		$this->nodeconfiguration = $nodeconfiguration;
		return;
	}
	
	// Param: componentName - the name of the component (uuid)
	// Param: fileName - the name of the file in the unziped fileName
	// Param: fileContents - the actual file binary
	// Param: unzip - true (1) if to retrieve the file uncompressed, false (0) if compressed
	function uploadFromCompressedDownload($params) {
		global $KUINK_CFG;
		
		$compressedFileName = $params ['compressedFileName'];
		$compressedFileExtension = $params ['compressedFileExtension'];
		$download = $params ['download'];
		$fileName = $params ['fileName'];
		$filePath = $params ['filePath'];
		$unzip = $params ['unzip'];
		$result = array ();
		
		$config = $this->nodeconfiguration ['config'];
		$base_upload = $KUINK_CFG->uploadRoot;
		$upload_dir = $base_upload . $upload_folder;
		$path = $upload_dir . 'tmp/';
		$compressedFilePath = $path . $compressedFileName . '.' . $compressedFileExtension;
		
		$file = fopen ( $compressedFilePath, 'wb' ) or $this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'Couldn\'t open file' );
		fwrite ( $file, $download );
		
		if ($file) {
			if (! $unzip) {
				// open file for writing
				$result ['path'] = $upload_dir . 'tmp';
				$result ['name'] = $compressedFileName . '.' . $compressedFileExtension;
			} else {
				
				// TODO::STI - unzip to tmp folder
				/*
				 * $phar = new \PharData($compressedFilePath);
				 * $phar->extractTo($path.$compressedFileName,null,true);
				 *
				 * //shell_exec("tar -xvf ".escapeshellcmd($compressedFilePath)." -C ".escapeshellcmd($path));
				 *
				 * $result['path'] = $upload_dir.'tmp/'.$filePath;
				 * $result['name'] = $fileName;
				 */
			}
		}
		if ($file) {
			fclose ( $file );
		}
		return $result;
	}
	
	// Param: UploadFolder - The folder under moodledata where the file is to be copied
	// Param: Filename - Force the name of the file. Generally a GUID
	// Param: MaxUploadSize - Maximum upload size of the file
	// Param: ValidExtensions - comma separated list of valid extensions
	function upload($params) {
		// global $msg_manager;
		global $KUINK_CFG;
		global $KUINK_TRACE;
		// global $_Files;
		
		// var_dump($_FILES);
		
		// var_dump($params);
		$record = 0;
		$upload_folder = ($params ['UploadFolder']) ? $params ['UploadFolder'] : 'temp';
		$param_filename = (isset ( $params ['Filename'] )) ? $params ['Filename'] : '';
		$maxUploadSize = ($params ['MaxUploadFileSize']) ? $params ['MaxUploadFileSize'] : 0;
		$valid_extensions = ($params ['AllowedExtensions']) ? $params ['AllowedExtensions'] : '';
		$original_name = (isset($params ['OriginalName'])) ? $params ['OriginalName'] : '';
		$iduser = (isset($params ['id_user'])) ? $params ['id_user'] : '';
		$desc = (isset($params [5])) ? $params [5] : '';
		$mandatory = (isset($params [6])) ? $params [6] : null;
		$showErrors = (isset($params ['showErrors'])) ? $params ['showErrors'] : 1; //default shows all errors

		//kuink_mydebug('FILENAME:'.$param_filename );
		//kuink_mydebug('MAXUPLOAD SIZE:'.$maxUploadSize );
		//kuink_mydebug('UPLOAD FOLDER:'.$upload_folder );
		$config = $this->nodeconfiguration ['config'];
		
		$base_upload = $KUINK_CFG->uploadRoot;
		$upload_dir = $base_upload . $upload_folder;
		//kuink_mydebug('uploaddir', $upload_dir);
		//kuink_mydebugObj('FILES', $_FILES);

		// normalizar os ficheiros
		foreach ( $_FILES as $tipo => $file ) {
			//ignore if filename and filetype are empty
			$filename = $file ['name'];
			$filesize = $file ['size'];
			$filetype = $file ['type'];
			
			//kuink_mydebug('filename', $filename);			
			if ($filename != '' && $filetype != '') {
				$valid_extensions_arr = explode ( ',', $valid_extensions );
				$error = false;
				if ($file ['error'] != 0 && $showErrors) {
					$KUINK_TRACE[] = 'FileLib::Upload ERROR - '.$file['error'];
					$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'Erro no upload do ficheiro.' );
				}
				
				if ($file ['error'] == 4  && $showErrors) {
					if ($mandatory == 'true')
						$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'O ficheiro é obrigatório.' );
					return null;
				}
				
				if ($filename != '') {
					// Extract file extension
					$file_ext = explode ( '.', $filename );
					$file_ext = strtolower ( $file_ext [count ( $file_ext ) - 1] );
					
					// nome normalizado para guardar na base de dados {tipo}_{num_aluno}_{increment}.{ext}
					$full_path_original = $upload_dir . '/' . $filename;
					
					$i = 0; // incremento. O primeiro ficheiro é 0
								
					// nome do ficheiro normalizado
					$db_filename = $param_filename . '.' . $file_ext;
					
					// caminho completo do ficheiro com o nome normalizado
					$full_path_normalizado = $upload_dir . "/" . $db_filename;
					
					if (! in_array ( $file_ext, $valid_extensions_arr ) && ($valid_extensions != '')) {
						unlink ( $full_path_original );
						$error = true;
						// print('Ficheiro com a extensão errada. Extensões válidas: '.$valid_extensions);
						$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'Ficheiro com a extensão errada. Extensões válidas: ' . $valid_extensions );
						continue;
					}
					
					if ((($filesize > $maxUploadSize) && ($maxUploadSize != 0)) || $file ['error'] == 2) {
						$error = true;
						unlink ( $full_path_original );
						if ($showErrors)
							$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'Ficheiro com tamanho superior ao permitido.' );
						// print('Ficheiro com tamanho superior ao permitido.');
						// ERRO: Ficheiro com tamanho superior
						continue;
					}
					
					if ($file ['error'] != 0 && $showErrors) {
						$this->msg_manager->add ( \Kuink\Core\MessageType::ERROR, 'Erro a fazer upload do ficheiro (' . $file ['error'] . ').' );
						continue;
					}
					
					//var_dump($db_filename);

					//kuink_mydebug('filename', $filename);			
					//kuink_mydebug('Error', $error);
					if (! $error) {
						//kuink_mydebug('dest filename', $upload_dir);

						//create the directory if not exists
						if (!file_exists( $upload_dir))
							mkdir( $upload_dir, 0777, true);
						
						//move the file
						move_uploaded_file($file['tmp_name'], $full_path_normalizado);
						$KUINK_TRACE[] = 'Uploading file to: '.$full_path_normalizado;
						
						// Altera o nome para o nome normalizado GUID
						$full_path_original = str_replace ( " ", "\ ", $full_path_original );

						//kuink_mydebug('$full_path_normalizado',$full_path_normalizado);
						//kuink_mydebug('$full_path_original',$full_path_original);
						//This rename is unecessary, because the file was uploaded with the $full_path_normalizado
						//rename ( $full_path_original, $full_path_normalizado );
						
						//var_dump($full_path_normalizado);
						// Grava o ficheiro na base de dados na tabela ficheiro
						
						$original_name = ($original_name == '') ? $filename : $original_name;
						$path = $upload_dir;
						$name = $param_filename . '.' . $file_ext;
						$size = $filesize;
						$ext = $file_ext;
						$mime = ( string ) $file ['type'];
						$id_user = $iduser;
						//$record = $this->register ( $original_name, $path, $name, $size, $ext, $mime, $id_user, $desc );
						$record = $this->register ( $original_name, $upload_folder, $name, $size, $ext, $mime, $id_user, $desc );
					}
				} else {
					$KUINK_TRACE[] = 'FileLib::Upload empty filename revceived. Doing nothing.';					
				}
			}
		}
		/**
		 * ***** termina o upload dos ficheiros ************
		 */
		
		return $record;
	}
	
	/**
	 * This function will register a file, which allready is in filesystem in file
	 */
	function register($original_name, $path, $name, $size, $ext, $mime, $id_user, $desc, $guid = '') {
		global $KUINK_CFG;
		// Grava o ficheiro na base de dados na tabela ficheiro
		$config = $this->nodeconfiguration ['config'];
		$path = $KUINK_CFG->uploadVirtualPrefix .$path;

		$utils = new UtilsLib ( $this->nodeconfiguration, $this->msg_manager );
		$guid = ($guid == '') ? $utils->GuidClean () : $guid;
		
		$insert = array ();
		$insert ['table'] = 'fw_file';
		$insert ['original_name'] = $original_name;
		$insert ['path'] = $path;
		$insert ['name'] = $name;
		$insert ['size'] = $size;
		$insert ['ext'] = $ext;
		$insert ['mimetype'] = $mime;
		$insert ['id_user'] = $id_user;
		$insert ['creation_ts'] = time ();
		$insert ['guid'] = $guid;
		$insert ['description'] = $desc;
		$insert ['application'] = $this->nodeconfiguration ['customappname'];
		$insert ['process'] = $this->nodeconfiguration ['master_process_name'];
		$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,insert', 'framework', 'generic' );
		$record = $datasource->execute ( $insert );
		return $record;
	}
	
	/**
	 *
	 * @param $params (id,
	 *        	delete_file_record=true)
	 * @throws Exception
	 */
	function unlink($params) {
		global $KUINK_CFG;
		if (! isset ( $params [0] ))
			throw new Exception ( 'unlink needs the id of the file.' );
		$id = ( string ) $params [0];
		$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
		$file = $datasource->execute ( array (
				'table' => 'fw_file',
				'id' => $id 
		) );
		// var_dump($file);
		// print($CFG->dataRoot.'/'.$file->path.'/'.$file->name);
		$filename = $KUINK_CFG->uploadRoot . '/' . $file ['path'] . '/' . $file ['name'];
		// kuink_mydebug('file',$filename);
		if (file_exists($filename))
			unlink ( $filename );
		
		$delete_file_record = isset ( $params [1] ) ? ( string ) $params [1] : 'true';
		
		if ($delete_file_record == 'true') {
			$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,delete', 'framework', 'generic' );
			$file = $datasource->execute ( array (
					'table' => 'fw_file',
					'id' => $id 
			) );
		} else {
			$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,update', 'framework', 'generic' );
			$file = $datasource->execute ( array (
					'table' => 'fw_file',
					'id' => $id,
					'unlinked' => 1 
			) );
		}
		// var_dump($file);
		return;
	}
		
	function download($params) {
		global $KUINK_CFG;
		// disable moodle specific debug messages and any errors in output
		// define('NO_DEBUG_DISPLAY', true);
		
		// require_once('../../config.php');
		// require_once('../../lib/filelib.php');
		
		$path = ($params [0]) ? $params [0] : '';
		$file = ($params [1]) ? $params [1] : '';
		
		/* corect the file path based on $KUINK_CFG->uploadVirtualPrefix temporary key*/
		$path = str_replace($KUINK_CFG->uploadVirtualPrefix, '', $path);		
		
		// ========================================
		// send the file
		// ========================================
		$pathName = $KUINK_CFG->uploadRoot . '/' . $path . '/' . $file;

		// print('pathname:'.$pathname);
		if (file_exists ( $pathName ) and ! is_dir ( $pathName )) {
			ob_clean ();
			header('Accept-Ranges: bytes');
			header('Content-Disposition: attachment; filename=' . $file);			
			header('Content-Type: application/octet-stream');
			readfile($pathName);
			die();
		} else {
			header ( 'HTTP/1.0 404 not found' );
			print_error ( 'filenotfound', 'error' ); // this is not displayed on IIS??
		}
	}
	
	function downloadTmp($params)
	{
		global $KUINK_CFG;

		$file = ($params[0]) ? (string)$params[0] : '';

		// ========================================
		// send the file
		// ========================================
		$pathName = $KUINK_CFG->tmpRoot.'/'.$file;
		//var_dump($pathName);
		//die();

		if (file_exists($pathName) and !is_dir($pathName)) {
			ob_clean();
			header('Accept-Ranges: bytes');
			header('Content-Disposition: attachment; filename=' . $file);
			header('Content-Type: application/octet-stream');
			//send_file($pathName, $file);
			readfile($pathName);
			die();
			//print_object($pathName.'::'.$file);
		} else {
			header('HTTP/1.0 404 not found');
			print_error('filenotfound', 'error'); //this is not displayed on IIS??
		}
	}

	function downloadContent($params) {
		$content = ($params[0]) ? (string)$params[0] : '';
		$contentType = ($params[1]) ? (string)$params[1] : 'application/pdf';
		$fileName = ($params[2]) ? (string)$params[2] : uniqid();
		ob_clean();
		$data = base64_decode($content);
		header('Content-Type: '.$contentType);
		header('Content-Disposition: attachment; filename='.$fileName);		
		echo $data;		
		die();
	}

	/**
	* Copy a folder to another destination
	* @param source : path to original folder under uploadBase/files/
	* @param destination : path to destination under uploadBase/files/ where to copy the folder to
	* @return 1 if successfuly copied, 0 otherwise
	* @author André Bittencourt
	* @since 2016-02-04
	**/
	function copyFolder($params) {
			global $KUINK_CFG;

			$config = $this->nodeconfiguration['config'];

			$baseUploadDir = $KUINK_CFG->uploadRoot;

			$source = isset($params['source']) ? $params['source'] : false;
			$source = $KUINK_CFG->dataRoot.'/'.$baseUploadDir.$source;

			$result = 0;
			if($source != false) {
				$destination = isset($params['destination']) ? $params['destination'] : false;
				$destination = $KUINK_CFG->dataRoot.'/'.$baseUploadDir.$destination;

				if($destination != false) {
					if(!is_dir($destination)){
						$oldumask = umask(0);
						mkdir($destination, 0777);
						umask($oldumask);
					}

					$dir_handle = @opendir($source) or die("Unable to open");
					while ($file = readdir($dir_handle)) {
						if($file!="." && $file!=".." && !is_dir("$source/$file"))
							copy("$source/$file","$destination/$file");
						if($file!="." && $file!=".." && is_dir("$source/$file")) {
							$recursiveParams = array("source"=>"$source/$file","destination"=>"$destination/$file");
							$this->copyFolder($recursiveParams);
						}
					}
					closedir($dir_handle);
					$result = 1;
				}
			}

			return $result;
	}


	function copyFileRaw($params) {
		$origin = (string) $params ['origin'];
		$destination = (string) $params ['destination'];

		copy( $origin, $destination );
	}


	/**
	 * Copy a file record to another destination
	 * 
	 * @param
	 *        	id original file id from fw_file table
	 * @param
	 *        	path destination path
	 * @param
	 *        	newName if is setted, the new file will be renamed to this name
	 * @return New file id
	 * @author Joao Patricio
	 * @since 2014-04-07
	 *       
	 */
	function copyFile($params) {
		global $KUINK_CFG;
		
		$config = $this->nodeconfiguration ['config'];
		
		$baseUploadDir = $KUINK_CFG->uploadRoot;
		                                                  
		// === params ===
		                                                  
		// ID File
		$id = ( string ) $params ['id'];
		
		// Copyy to this directory
		$copyTo = ( string ) $params ['path'];
		//print_object($copyTo);
		
		// With this new filename
		
		$newName = (isset ( $params ['newName'] )) ? ( string ) $params ['newName'] : false;
		
		// load file
		//$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
		//$file = $datasource->execute ( array (
		//		'table' => 'fw_file',
		//		'id' => $id 
		//) );

		$fileDa = new Kuink\Core\DataAccess ( 'framework,generic,load', 'framework', 'generic' );
		$file = $fileDa->execute( array (
			'table' => 'fw_file',
			'id' => $id 
		));

		//var_dump($params);
		//var_dump($id);
		//var_dump($file);
		
		// full origin path
		$originalFilePath = $file ['path'];
		/* corect the file path based on $KUINK_CFG->uploadVirtualPrefix temporary key*/
		$originalFilePath = str_replace($KUINK_CFG->uploadVirtualPrefix, '', $originalFilePath);		
		$originalFile = $KUINK_CFG->uploadRoot . '/' . $originalFilePath . '/' . $file ['name'];
		$originalFile = str_replace ( '//', '/', $originalFile );
		
		
		// full destination path
		$newName = (! $newName) ? $file ['name'] : $newName . '.' . $file ['ext'];
		if (strpos($copyTo, $baseUploadDir) === false)
			$destinationPath = $baseUploadDir.'/'.$copyTo;
		else
			$destinationPath = $copyTo;
		$destinationPath = str_replace ( '//', '/', $destinationPath );
		$destinationFile = $destinationPath . $newName;
		$destinationFile = str_replace ( '//', '/', $destinationFile );
		
		//var_dump($copyTo);
		//var_dump($baseUploadDir . '/' . $copyTo);
		//var_dump(dirname ( $destinationFile ));
		$registerPath = str_replace($baseUploadDir, '', $destinationPath);
		//print_object($destinationFile);
		//print_object($registerPath);

		mkdir ( dirname ( $destinationFile ), 0777, true );
		copy ( $originalFile, $destinationFile );
		//var_dump($originalFile);
		//var_dump($destinationFile);
		
		// register new file into fw_file
		$newFile = $this->register ( $file ['name'], $registerPath, $newName, $file ['size'], $file ['ext'], $file ['mimetype'], $KUINK_CFG->auth->user->id, '' );
		
		// return new id
		return $newFile;
	}
	
	/**
	 * create the record's folder
	 * 
	 * @param
	 *        	path destination path
	 * @author André Bittencourt
	 * @since 2015-07-07
	 *       
	 */
	function createFolder($params) {
		global $KUINK_CFG;
		
		$config = $this->nodeconfiguration ['config'];
		$baseUploadDir = $KUINK_CFG->uploadRoot;
		                                                  
		// folder's path
		$folderPath = ( string ) $params ['path'];
		
		// full path to the folder
		$finalPath =  $baseUploadDir . '/' . $folderPath;
		$finalPath = str_replace ( '//', '/', $finalPath );
		
		// create folder
		mkdir ( $finalPath, 0777, true );
		
		// return final path
		return $finalPath;
	}
	
	/**
	 * create the record's folder
	 * 
	 * @param
	 *        	originalPath folder's original path
	 * @param
	 *        	destinationPath folder's destination path
	 * @author André Bittencourt
	 * @since 2015-07-27
	 *       
	 */
	function moveFiles($params) {
		global $KUINK_CFG;
		
		$config = $this->nodeconfiguration ['config'];
		$baseUploadDir = $KUINK_CFG->uploadRoot;
		                                                  
		// path to the original folder
		$originalPath = ( string ) $params ['originalPath'];
		$pos = strpos ( $originalPath . '/', $baseUploadDir );
		if ($pos === FALSE)
			$originalFullPath =  $baseUploadDir . '/' . $originalPath;
		else
			$originalFullPath = $KUINK_CFG->uploadRoot . '/' . $originalPath;
		
		$originalFullPath = str_replace ( '//', '/', $originalFullPath ); // full original path
		                                                               
		// destination path
		$destinationPath = ( string ) $params ['destinationPath'];
		$pos = strpos ( $destinationPath . '/', $baseUploadDir );

		$destinationFullPath = $baseUploadDir . $destinationPath;
		/*
		if ($pos === FALSE)
			$destinationFullPath = $KUINK_CFG->dataRoot . '/' . $baseUploadDir . '/' . $destinationPath;
		else
			$destinationFullPath = $KUINK_CFG->dataRoot . '/' . $destinationPath;
		*/
		$destinationFullPath = str_replace ( '//', '/', $destinationFullPath ); // full destination path
		                                                                     
		$pos = strpos ( $originalFullPath . '/', $baseUploadDir );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $originalFullPath );

		//Create the destination directory if does not exists
		if (!is_dir(dirname ( $destinationFullPath )))
			mkdir ( dirname ( $destinationFullPath ), 0755, true );

		
		// move folder to final destination
		//kuink_mydebug('', $originalFullPath);
		//kuink_mydebug('', $destinationFullPath);
		if(rename( $originalFullPath, $destinationFullPath ))
			return 1;
		else
			return 0;			
	}
	
	/**
	 * Create a new file
	 * 
	 * @param
	 *        	filename File name
	 * @param
	 *        	path destination path
	 * @param
	 *        	extension file extension
	 * @param
	 *        	content file content
	 * @param
	 *        	desc file description
	 * @param
	 *        	guid file guid
	 * @return New file id
	 * @author Joao Patricio
	 * @since 2014-04-07
	 *       
	 */
	function createFile($params) {
		global  $KUINK_CFG;
		
		$config = $this->nodeconfiguration ['config'];
		$baseUploadDir = $KUINK_CFG->uploadRoot;
		$register = isset($params ['register']) ? (int) $params ['register'] : 1; //This file is to register or not? Defaults to register.
		// content
		$content = ( string ) $params ['content'];
		
		// filename
		$filename = ( string ) $params ['filename'];
		
		// extension
		$extension = ( string ) $params ['ext'];
		
		// path
		$path = ( string ) $params ['path'];
		$pathToRegister = $path;
		
		// description
		$description = (isset ( $params ['desc'] )) ? $params ['desc'] : '';
		
		$destinationPath = $baseUploadDir . '/' . $path;
		$destinationPath = str_replace ( '//', '/', $destinationPath );
		
		$destination = $destinationPath . '/' . $filename . '.' . $extension;
		$destination = str_replace ( '//', '/', $destination );
		
		//var_dump($destination);
		
		// Write the file
		$handle = fopen ( $destination, 'w' ) or die ( 'Cannot open file:  ' . $destination );
		fwrite ( $handle, $content );
		
		// Register the file
		$original_name = $filename . '.' . $extension;
		$path = $destinationPath;
		$name = $filename . '.' . $extension;
		$size = filesize ( $destination );
		// var_dump($size);
		$ext = $extension;
		
		$file_info = new finfo ( FILEINFO_MIME ); // object oriented approach!
		$mime = $file_info->buffer ( file_get_contents ( $destination ) ); // e.g. gives "image/jpeg"
		
		$id_user = $KUINK_CFG->auth->user->id;
		$desc = $description;
		
		$utils = new UtilsLib ( $this->nodeconfiguration, $this->msg_manager );
		$guid = $utils->GuidClean ();

		if ($register == 1)
			$record = $this->register ( $original_name, $pathToRegister, $name, $size, $ext, $mime, $id_user, $desc, $guid = '' );
		else
			$record = null;
		
		return $record;
	}
	function getFileChecksum($params) {
		global $KUINK_CFG;
		
		$id = ( string ) $params ['id'];
		// load file
		$datasource = new Kuink\Core\DataSource ( null, 'framework,generic,load', 'framework', 'generic' );
		$file = $datasource->execute ( array (
				'table' => 'fw_file',
				'id' => $id 
		) );
		
		// full origin path
		/* corect the file path based on $KUINK_CFG->uploadVirtualPrefix temporary key*/
		$path = $file ['path'];
		$path = str_replace($KUINK_CFG->uploadVirtualPrefix, '', $path);

		$originalFile = $KUINK_CFG->uploadRoot . '/' . $path . '/' . $file ['name'];
		$originalFile = str_replace ( '//', '/', $originalFile );
		
		return md5_file ( $originalFile );
	}
	
	/*
	 * Get the sub dirs of a given dir
	 */
	function getSubDirs($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$base = str_replace ( '//', '/', $base );		
		$pathName = realpath ( $base . $path );
		//$pos = strpos ( $pathName . '/', $base );
		//if ($pos === FALSE)
	  //	throw new \Exception ( 'No permission to access ' . $pathName );
		$contents = $this->directorySubDirs ( $pathName );

		return $contents;
	}
	
	/*
	 * Get the files
	 */
	function getFiles($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		//$pos = strpos ( $pathName . '/', $base );
		//if ($pos === FALSE)
		//	throw new \Exception ( 'No permission to access ' . $pathName );
		
		$contents = $this->directoryFiles ( $pathName );
		return $contents;
	}
	function createFileOrDirectory($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		$name = ( string ) $params ['name'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		$pos = strpos ( $pathName . '/', $base );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $pathName );
		
		$pathName = $pathName . '/' . $name;
		$pos = strpos ( $name, '.' );
		$mode = 0777;
		if ($pos === FALSE) {
			// Create the directory
			return is_dir ( $pathName ) || mkdir ( $pathName, $mode, true );
		} else {
			// Create the file
			$file = fopen ( $pathName, 'x+' ) or die ( "Unable to open file!" );
			fclose ( $file );
			return true;
		}
		
		return true;
	}
	function renameFileOrDirectory($params) {
		GLOBAL $KUINK_CFG;
		
		$oldName = ( string ) $params ['oldName'];
		$newName = ( string ) $params ['newName'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $oldName );
		$pos = strpos ( $pathName . '/', $base );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $pathName );
		$result = rename ( $base . '/' . $oldName, $base . '/' . $newName );
		
		return $result;
	}
	function deleteFileOrDirectory($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		$pos = strpos ( $pathName . '/', $base );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $pathName );
			
			// tries to delete the directory
		rmdir ( $pathName );
		// tries to delete the file
		unlink ( $pathName );
		return true;
	}
	function getFileType($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		$pos = strpos ( $pathName . '/', $base );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $pathName );
		
		$splited = explode ( '/', $pathName );
		
		if ($splited [count ( $splited ) - 1] == 'application.xml')
			return ('application');
		if ($splited [count ( $splited ) - 1] == 'process.xml')
			return ('process');
		if ($splited [count ( $splited ) - 2] == 'nodes')
			return ('node');
		if ($splited [count ( $splited ) - 2] == 'lib')
			return ('lib');
		if ($splited [count ( $splited ) - 2] == 'dataaccess')
			return ('dataaccess');
		if ($splited [count ( $splited ) - 2] == 'lang')
			return ('lang');
		if ($splited [count ( $splited ) - 2] == 'dd')
			return ('dd');
		if ($splited [count ( $splited ) - 2] == 'ui')
			return ('ui');
		if ($splited [count ( $splited ) - 2] == 'templates')
			return ('template');
		return 'error';
	}
	function getLevel($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$base2 = $KUINK_CFG->appRoot . 'apps';
		$pathName = realpath ( $base . $path );
		
		// Remove the base to get the level only for the path
		$pathName = str_replace ( $base2, '', $pathName );
		
		$parts = explode ( '/', $pathName );
		
		return (count ( $parts ) - 1);
	}
	
	/*
	 * Get the files
	 */
	function getFileContent($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		//$pos = strpos ( $pathName . '/', $base );
		//if ($pos === FALSE)
		//	throw new \Exception ( 'No permission to access ' . $pathName );
		
		$contents = file_get_contents ( $pathName );
		return $contents;
	}
	function setFileContent($params) {
		GLOBAL $KUINK_CFG;
		
		$path = ( string ) $params ['path'];
		$content = ( string ) $params ['content'];
		
		$base = $KUINK_CFG->appRoot . 'apps/';
		$pathName = realpath ( $base . $path );
		$pos = strpos ( $pathName . '/', $base );
		if ($pos === FALSE)
			throw new \Exception ( 'No permission to access ' . $pathName );
		
		$result = file_put_contents ( $pathName, $content );
		
		return $result;
	}
	static private function directoryFiles($directory) {
		// open this directory
		$myDirectory = opendir ( $directory );
		
		// get each entry
		while ( $entryName = readdir ( $myDirectory ) ) {
			if ($entryName != '.' && $entryName != '..' && is_file ( $directory . '/' . $entryName ))
				$dirArray [] = $entryName;
		}
		
		// close directory
		closedir ( $myDirectory );
		
		// sort 'em
		sort ( $dirArray );
		
		// remove self
		// unset( $dirArray[0] );
		return $dirArray;
	}
	static private function directorySubDirs($directory) {
		// open this directory
		$myDirectory = opendir ( $directory );

		// get each entry
		while ( $entryName = readdir ( $myDirectory ) ) {
			if ($entryName != '.' && $entryName != '..' && is_dir ( $directory . '/' . $entryName ))
				$dirArray [] = $entryName;
		}
	
		// close directory
		closedir ( $myDirectory );
		
		// sort 'em
		sort ( $dirArray );
		// remove self
		// unset( $dirArray[0] );
		return $dirArray;
	}
}

/*
 * **************************************** DEPRECATED *******************************************
 */
class upload_manager {
	/*
	 * Array to hold local copies of stuff in $_FILES
	 * @var array $files
	 */
	var $files;
	/**
	 * Holds all configuration stuff
	 * 
	 * @var array $config
	 */
	var $config;
	/**
	 * Keep track of if we're ok
	 * (errors for each file are kept in $files['whatever']['uploadlog']
	 * 
	 * @var boolean $status
	 */
	var $status;
	/**
	 * The course this file has been uploaded for.
	 * {@link $COURSE}
	 * (for logging and virus notifications)
	 * 
	 * @var course $course
	 */
	var $course;
	/**
	 * If we're only getting one file.
	 * (for logging and virus notifications)
	 * 
	 * @var string $inputname
	 */
	var $inputname;
	/**
	 * If we're given silent=true in the constructor, this gets built
	 * up to hold info about the process.
	 * 
	 * @var string $notify
	 */
	var $notify;
	
	/**
	 * Constructor, sets up configuration stuff so we know how to act.
	 *
	 * Note: destination not taken as parameter as some modules want to use the insertid in the path and we need to check the other stuff first.
	 *
	 * @uses $CFG
	 * @param string $inputname
	 *        	If this is given the upload manager will only process the file in $_FILES with this name.
	 * @param boolean $deleteothers
	 *        	Whether to delete other files in the destination directory (optional, defaults to false)
	 * @param boolean $handlecollisions
	 *        	Whether to use {@link handle_filename_collision()} or not. (optional, defaults to false)
	 * @param course $course
	 *        	The course the files are being uploaded for (for logging and virus notifications) {@link $COURSE}
	 * @param boolean $recoverifmultiple
	 *        	If we come across a virus, or if a file doesn't validate or whatever, do we continue? optional, defaults to true.
	 * @param int $modbytes
	 *        	Max bytes for this module - this and $course->maxbytes are used to get the maxbytes from {@link get_max_upload_file_size()}.
	 * @param boolean $silent
	 *        	Whether to notify errors or not.
	 * @param boolean $allownull
	 *        	Whether we care if there's no file when we've set the input name.
	 * @param boolean $allownullmultiple
	 *        	Whether we care if there's no files AT ALL when we've got multiples. This won't complain if we have file 1 and file 3 but not file 2, only for NO FILES AT ALL.
	 */
	function __construct($inputname = '', $deleteothers = false, $handlecollisions = false, $course = null, $recoverifmultiple = false, $modbytes = 0, $silent = false, $allownull = false, $allownullmultiple = true) {
		global $CFG, $SITE;
		
		debugging ( 'upload_manager class is deprecated, use new file picker instead', DEBUG_DEVELOPER );
		
		if (empty ( $course->id )) {
			$course = $SITE;
		}
		
		$this->config = new stdClass ();
		$this->config->deleteothers = $deleteothers;
		$this->config->handlecollisions = $handlecollisions;
		$this->config->recoverifmultiple = $recoverifmultiple;
		$this->config->maxbytes = get_max_upload_file_size ( $CFG->maxbytes, $course->maxbytes, $modbytes );
		$this->config->silent = $silent;
		$this->config->allownull = $allownull;
		$this->files = array ();
		$this->status = false;
		$this->course = $course;
		$this->inputname = $inputname;
		if (empty ( $this->inputname )) {
			$this->config->allownull = $allownullmultiple;
		}
	}
	
	/**
	 * Gets all entries out of $_FILES and stores them locally in $files and then
	 * checks each one against {@link get_max_upload_file_size()} and calls {@link cleanfilename()}
	 * and scans them for viruses etc.
	 * 
	 * @uses $CFG
	 * @uses $_FILES
	 * @return boolean
	 */
	function preprocess_files() {
		global $CFG, $OUTPUT;
		
		foreach ( $_FILES as $name => $file ) {
			$this->status = true; // only set it to true here so that we can check if this function has been called.
			if (empty ( $this->inputname ) || $name == $this->inputname) { // if we have input name, only process if it matches.
				$file ['originalname'] = $file ['name']; // do this first for the log.
				$this->files [$name] = $file; // put it in first so we can get uploadlog out in print_upload_log.
				$this->files [$name] ['uploadlog'] = ''; // initialize error log
				$this->status = $this->validate_file ( $this->files [$name] ); // default to only allowing empty on multiple uploads.
				if (! $this->status && ($this->files [$name] ['error'] == 0 || $this->files [$name] ['error'] == 4) && ($this->config->allownull || empty ( $this->inputname ))) {
					// this shouldn't cause everything to stop.. modules should be responsible for knowing which if any are compulsory.
					continue;
				}
				if ($this->status && ! empty ( $CFG->runclamonupload )) {
					$this->status = clam_scan_moodle_file ( $this->files [$name], $this->course );
				}
				if (! $this->status) {
					if (! $this->config->recoverifmultiple && count ( $this->files ) > 1) {
						$a = new stdClass ();
						$a->name = $this->files [$name] ['originalname'];
						$a->problem = $this->files [$name] ['uploadlog'];
						if (! $this->config->silent) {
							echo $OUTPUT->notification ( get_string ( 'uploadfailednotrecovering', 'moodle', $a ) );
						} else {
							$this->notify .= '<br />' . get_string ( 'uploadfailednotrecovering', 'moodle', $a );
						}
						$this->status = false;
						return false;
					} else if (count ( $this->files ) == 1) {
						
						if (! $this->config->silent and ! $this->config->allownull) {
							echo $OUTPUT->notification ( $this->files [$name] ['uploadlog'] );
						} else {
							$this->notify .= '<br />' . $this->files [$name] ['uploadlog'];
						}
						$this->status = false;
						return false;
					}
				} else {
					$newname = clean_filename ( $this->files [$name] ['name'] );
					if ($newname != $this->files [$name] ['name']) {
						$a = new stdClass ();
						$a->oldname = $this->files [$name] ['name'];
						$a->newname = $newname;
						$this->files [$name] ['uploadlog'] .= get_string ( 'uploadrenamedchars', 'moodle', $a );
					}
					$this->files [$name] ['name'] = $newname;
					$this->files [$name] ['clear'] = true; // ok to save.
					$this->config->somethingtosave = true;
				}
			}
		}
		if (! is_array ( $_FILES ) || count ( $_FILES ) == 0) {
			return $this->config->allownull;
		}
		$this->status = true;
		return true; // if we've got this far it means that we're recovering so we want status to be ok.
	}
	
	/**
	 * Validates a single file entry from _FILES
	 *
	 * @param object $file
	 *        	The entry from _FILES to validate
	 * @return boolean True if ok.
	 */
	function validate_file(&$file) {
		if (empty ( $file )) {
			return false;
		}
		if (! is_uploaded_file ( $file ['tmp_name'] ) || $file ['size'] == 0) {
			$file ['uploadlog'] .= "\n" . $this->get_file_upload_error ( $file );
			return false;
		}
		if ($file ['size'] > $this->config->maxbytes) {
			$file ['uploadlog'] .= "\n" . get_string ( 'uploadedfiletoobig', 'moodle', $this->config->maxbytes );
			return false;
		}
		return true;
	}
	
	/**
	 * Moves all the files to the destination directory.
	 *
	 * @uses $CFG
	 * @uses $USER
	 * @param string $destination
	 *        	The destination directory.
	 * @return boolean status;
	 */
	function save_files($destination) {
		global $CFG, $USER, $OUTPUT;
		
		if (! $this->status) { // preprocess_files hasn't been run
			$this->preprocess_files ();
		}
		
		// if there are no files, bail before we create an empty directory.
		if (empty ( $this->config->somethingtosave )) {
			return true;
		}
		
		$savedsomething = false;
		
		if ($this->status) {
			if (! (strpos ( $destination, $CFG->dataRoot ) === false)) {
				// take it out for giving to make_upload_directory
				$destination = substr ( $destination, strlen ( $CFG->dataRoot ) + 1 );
			}
			
			if ($destination {strlen ( $destination ) - 1} == '/') { // strip off a trailing / if we have one
				$destination = substr ( $destination, 0, - 1 );
			}
			
			if (! make_upload_directory ( $destination, true )) {
				$this->status = false;
				return false;
			}
			
			$destination = $CFG->dataRoot . '/' . $destination; // now add it back in so we have a full path
			
			$exceptions = array (); // need this later if we're deleting other files.
			
			foreach ( array_keys ( $this->files ) as $i ) {
				
				if (! $this->files [$i] ['clear']) {
					// not ok to save
					continue;
				}
				
				if ($this->config->handlecollisions) {
					$this->handle_filename_collision ( $destination, $this->files [$i] );
				}
				if (move_uploaded_file ( $this->files [$i] ['tmp_name'], $destination . '/' . $this->files [$i] ['name'] )) {
					chmod ( $destination . '/' . $this->files [$i] ['name'], $CFG->directorypermissions );
					$this->files [$i] ['fullpath'] = $destination . '/' . $this->files [$i] ['name'];
					$this->files [$i] ['uploadlog'] .= "\n" . get_string ( 'uploadedfile' );
					$this->files [$i] ['saved'] = true;
					$exceptions [] = $this->files [$i] ['name'];
					$savedsomething = true;
				}
			}
			if ($savedsomething && $this->config->deleteothers) {
				$this->delete_other_files ( $destination, $exceptions );
			}
		}
		if (empty ( $savedsomething )) {
			$this->status = false;
			if ((empty ( $this->config->allownull ) && ! empty ( $this->inputname )) || (empty ( $this->inputname ) && empty ( $this->config->allownullmultiple ))) {
				echo $OUTPUT->notification ( get_string ( 'uploadnofilefound' ) );
			}
			return false;
		}
		return $this->status;
	}
	
	/**
	 * Wrapper function that calls {@link preprocess_files()} and {@link viruscheck_files()} and then {@link save_files()}
	 * Modules that require the insert id in the filepath should not use this and call these functions seperately in the required order.
	 * @parameter string $destination Where to save the uploaded files to.
	 * 
	 * @return boolean
	 */
	function process_file_uploads($destination) {
		if ($this->preprocess_files ()) {
			return $this->save_files ( $destination );
		}
		return false;
	}
	
	/**
	 * Deletes all the files in a given directory except for the files in $exceptions (full paths)
	 *
	 * @param string $destination
	 *        	The directory to clean up.
	 * @param array $exceptions
	 *        	Full paths of files to KEEP.
	 */
	function delete_other_files($destination, $exceptions = null) {
		global $OUTPUT;
		$deletedsomething = false;
		if ($filestodel = get_directory_list ( $destination )) {
			foreach ( $filestodel as $file ) {
				if (! is_array ( $exceptions ) || ! in_array ( $file, $exceptions )) {
					unlink ( $destination . '/' . $file );
					$deletedsomething = true;
				}
			}
		}
		if ($deletedsomething) {
			if (! $this->config->silent) {
				echo $OUTPUT->notification ( get_string ( 'uploadoldfilesdeleted' ) );
			} else {
				$this->notify .= '<br />' . get_string ( 'uploadoldfilesdeleted' );
			}
		}
	}
	
	/**
	 * Handles filename collisions - if the desired filename exists it will rename it according to the pattern in $format
	 * 
	 * @param string $destination
	 *        	Destination directory (to check existing files against)
	 * @param object $file
	 *        	Passed in by reference. The current file from $files we're processing.
	 * @return void - modifies &$file parameter.
	 */
	function handle_filename_collision($destination, &$file) {
		if (! file_exists ( $destination . '/' . $file ['name'] )) {
			return;
		}
		
		$parts = explode ( '.', $file ['name'] );
		if (count ( $parts ) > 1) {
			$extension = '.' . array_pop ( $parts );
			$name = implode ( '.', $parts );
		} else {
			$extension = '';
			$name = $file ['name'];
		}
		
		$current = 0;
		if (preg_match ( '/^(.*)_(\d*)$/s', $name, $matches )) {
			$name = $matches [1];
			$current = ( int ) $matches [2];
		}
		$i = $current + 1;
		
		while ( ! $this->check_before_renaming ( $destination, $name . '_' . $i . $extension, $file ) ) {
			$i ++;
		}
		$a = new stdClass ();
		$a->oldname = $file ['name'];
		$file ['name'] = $name . '_' . $i . $extension;
		$a->newname = $file ['name'];
		$file ['uploadlog'] .= "\n" . get_string ( 'uploadrenamedcollision', 'moodle', $a );
	}
	
	/**
	 * This function checks a potential filename against what's on the filesystem already and what's been saved already.
	 * 
	 * @param string $destination
	 *        	Destination directory (to check existing files against)
	 * @param string $nametocheck
	 *        	The filename to be compared.
	 * @param object $file
	 *        	The current file from $files we're processing.
	 *        	return boolean
	 */
	function check_before_renaming($destination, $nametocheck, $file) {
		if (! file_exists ( $destination . '/' . $nametocheck )) {
			return true;
		}
		if ($this->config->deleteothers) {
			foreach ( $this->files as $tocheck ) {
				// if we're deleting files anyway, it's not THIS file and we care about it and it has the same name and has already been saved..
				if ($file ['tmp_name'] != $tocheck ['tmp_name'] && $tocheck ['clear'] && $nametocheck == $tocheck ['name'] && $tocheck ['saved']) {
					$collision = true;
				}
			}
			if (! $collision) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * ?
	 *
	 * @param object $file
	 *        	Passed in by reference. The current file from $files we're processing.
	 * @return string
	 */
	function get_file_upload_error(&$file) {
		switch ($file ['error']) {
			case 0 : // UPLOAD_ERR_OK
				if ($file ['size'] > 0) {
					$errmessage = get_string ( 'uploadproblem', $file ['name'] );
				} else {
					$errmessage = get_string ( 'uploadnofilefound' ); // / probably a dud file name
				}
				break;
			
			case 1 : // UPLOAD_ERR_INI_SIZE
				$errmessage = get_string ( 'uploadserverlimit' );
				break;
			
			case 2 : // UPLOAD_ERR_FORM_SIZE
				$errmessage = get_string ( 'uploadformlimit' );
				break;
			
			case 3 : // UPLOAD_ERR_PARTIAL
				$errmessage = get_string ( 'uploadpartialfile' );
				break;
			
			case 4 : // UPLOAD_ERR_NO_FILE
				$errmessage = get_string ( 'uploadnofilefound' );
				break;
			
			// Note: there is no error with a value of 5
			
			case 6 : // UPLOAD_ERR_NO_TMP_DIR
				$errmessage = get_string ( 'uploadnotempdir' );
				break;
			
			case 7 : // UPLOAD_ERR_CANT_WRITE
				$errmessage = get_string ( 'uploadcantwrite' );
				break;
			
			case 8 : // UPLOAD_ERR_EXTENSION
				$errmessage = get_string ( 'uploadextension' );
				break;
			
			default :
				$errmessage = get_string ( 'uploadproblem', $file ['name'] );
		}
		return $errmessage;
	}
	
	/**
	 * prints a log of everything that happened (of interest) to each file in _FILES
	 * 
	 * @param $return -
	 *        	optional, defaults to false (log is echoed)
	 */
	function print_upload_log($return = false, $skipemptyifmultiple = false) {
		$str = '';
		foreach ( array_keys ( $this->files ) as $i => $key ) {
			if (count ( $this->files ) > 1 && ! empty ( $skipemptyifmultiple ) && $this->files [$key] ['error'] == 4) {
				continue;
			}
			$str .= '<strong>' . get_string ( 'uploadfilelog', 'moodle', $i + 1 ) . ' ' . ((! empty ( $this->files [$key] ['originalname'] )) ? '(' . $this->files [$key] ['originalname'] . ')' : '') . '</strong> :' . nl2br ( $this->files [$key] ['uploadlog'] ) . '<br />';
		}
		if ($return) {
			return $str;
		}
		echo $str;
	}
	
	/**
	 * If we're only handling one file (if inputname was given in the constructor) this will return the (possibly changed) filename of the file.
	 * 
	 * @return boolean
	 */
	function get_new_filename() {
		if (! empty ( $this->inputname ) and count ( $this->files ) == 1 and $this->files [$this->inputname] ['error'] != 4) {
			return $this->files [$this->inputname] ['name'];
		}
		return false;
	}
	
	/**
	 * If we're only handling one file (if input name was given in the constructor) this will return the full path to the saved file.
	 * 
	 * @return boolean
	 */
	function get_new_filepath() {
		if (! empty ( $this->inputname ) and count ( $this->files ) == 1 and $this->files [$this->inputname] ['error'] != 4) {
			return $this->files [$this->inputname] ['fullpath'];
		}
		return false;
	}
	
	/**
	 * If we're only handling one file (if inputname was given in the constructor) this will return the ORIGINAL filename of the file.
	 * 
	 * @return boolean
	 */
	function get_original_filename() {
		if (! empty ( $this->inputname ) and count ( $this->files ) == 1 and $this->files [$this->inputname] ['error'] != 4) {
			return $this->files [$this->inputname] ['originalname'];
		}
		return false;
	}
	
	/**
	 * This function returns any errors wrapped up in red.
	 * 
	 * @return string
	 */
	function get_errors() {
		if (! empty ( $this->notify )) {
			return '<p class="notifyproblem">' . $this->notify . '</p>';
		} else {
			return null;
		}
	}
}

/*
 * **************************************** DEPRECATED *******************************************
 */

?>
