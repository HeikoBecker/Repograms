<?php
error_reporting(-1);
require_once('./php/utils.php');
startSessionIfNotStarted();
require_once('./config.inc.php');

$file = $_GET['file'];
$modeDownload = $_GET['mode'];

if (isset($_GET['mode']))
 convertImage($modeDownload);
download_file($file);
header('location: '.$_SERVER['HTTP_REFERER']);


function convertImage($modeDownload) {
	$image = new Imagick(_IMAGEDIR.'visualization-'.session_id().'.svg');
	if($modeDownload == "png")
			$image->setImageFormat("png");
	else if ($modeDownload == "jpeg" || $modeDownload == "jpg")
		 $image->setImageFormat("jpg");
	else if ($modeDownload == "pdf")
		$image->setImageFormat("pdf");
//	$image->resizeImage($_SESSION["width"]*2, $_SESSION["height"]*2, imagick::FILTER_LANCZOS, 1);
	$image->writeImage(_IMAGEDIR.'visualization-'.session_id().'.'.$modeDownload);
}

function download_file( $fullPath ){

  // Must be fresh start
  if( headers_sent() )
    die('Headers Sent');

  // Required for some browsers
  if(ini_get('zlib.output_compression'))
    ini_set('zlib.output_compression', 'Off');

  // File Exists?
  if( file_exists($fullPath) ){

    // Parse Info / Get Extension
    $fsize = filesize($fullPath);
    $path_parts = pathinfo($fullPath);
    $ext = strtolower($path_parts["extension"]);

    // Determine Content Type
    switch ($ext) {
      case "png": $ctype="image/png"; break;
      case "jpeg":
      case "jpg": $ctype="image/jpg"; break;
      case "svg": $ctype="image/svg"; break;
      case "pdf": $ctype="image/pdf"; break;
      default: $ctype="application/force-download";
    }

    header("Pragma: public"); // required
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private",false); // required for certain browsers
    header("Content-Type: $ctype");
    header("Content-Disposition: attachment; filename=\"".basename($fullPath)."\";" );
    header("Content-Transfer-Encoding: binary");
    header("Content-Length: ".$fsize);
    ob_clean();
    flush();
    readfile( $fullPath );

  } else
    die('File Not Found');

}
?>
