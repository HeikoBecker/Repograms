<?php
	session_start();
	require_once(__DIR__."/../lib/vcs/RepoFactory.class.php");
	require_once("algorithm.php");
	require_once("functions.php");
	dump();

	/**
	 * Check the formular input and start rendering
	 */
	if (checkInput($_SESSION['repourl'])) {
		if (isset ($_SESSION['repo']))
			error_log("Repo var set");
		error_log("Rendering");
		renderRepo($_SESSION['repourl']);
	}
	
	/**
	 * Callback function to update progress info
	 * @param string $msg
	 * @return NULL
	 */
	function callback($msg = null) {
		error_log("Callback called ".$_SESSION['progress']);
		$_SESSION['loading_info'] = $msg;
		$_SESSION['progress'] = $_SESSION['progress']+25;
		return null;
	}
	
	/**
	 * Initialize session variables
	 */
//	function initVariables() {
//		$_SESSION['loading_info']     = '';  
//		$_SESSION['progress'] 	      = 0;
//		$_SESSION['error_message']    = '';     
//		$_SESSION['title']            = ''; 
//		$_SESSION['repourl']          = '';
//		$_SESSION['finish']           = false;
//		$_SESSION['width']  = 768;                                  
//		$_SESSION['height'] = 512;
//	}
	
	/**
	 * Check formular input if url is empty
	 */
	function checkInput($repourl = null) {
          if (strpos($repourl, "http") === false) {
			$_SESSION['error_message'] = 'Invalid repository url.';
                        $_SESSION['error'] = true;
			return false;
          }
		if(!str_replace(' ','',$repourl) != '') {
			$_SESSION['error_message'] = 'Invalid repository url.';
                        $_SESSION['error'] = true;
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * Renders the repository with the provided $repourl and displays the image on image.php
	 * Session-Variables: image, title, error_message, 
	 */
	function renderRepo($repourl = null) {
		error_log($_SESSION['STATE']);
		try {
			switch  ($_SESSION['STATE']) {
				case 0:
					$_SESSION['repo'] = RepoFactory::createRepo($repourl,$_SESSION['start'], $_SESSION['end']);
					error_log("Repo created");
					$_SESSION['STATE']=1;
					return;
				case 1:
					$alg = new Algorithm();
					$_SESSION['image'] = $alg->render($_SESSION['repo']->getAllCommits(), 0,$_SESSION['width'], $_SESSION['height']);
					error_log("Image created");
					$start = strrpos($repourl, '/');
					$_SESSION['title'] = substr($repourl, $start+1, strrpos($repourl, '.')-$start-1);
					unsetAll();
					$_SESSION['finish'] = true;
					$_SESSION['state']=0;
					return;
			}
		} catch (Exception $e) {
			unsetAll();
			$_SESSION['error'] = true;
			$_SESSION['error_message'] = $e->getMessage();
			$_SESSION['finish'] = false;
		}
	}

	/**
	 * Unsets session variables: error_message, current_progress, loading, loading_info
	 */
	function unsetAll() {
		unset($_SESSION['error_message']);
		unset($_SESSION['loading_info']);
	}
	
?>
