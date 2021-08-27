<?

$Path="images";
$fullPath=$_SERVER['DOCUMENT_ROOT']."/$Path";
$val=$_GET['val'];
$entry=$_GET['entry'];

	$img = @imagecreatefromjpeg($fullPath."/$val/$entry");
	//$mon=@ereg_replace("^.{2}(.{2}).*$","\\1",$gallery[thumb]);
	//$day=@ereg_replace("^.{4}(.{2}).*$","\\1",$gallery[thumb]);
	$year=@ereg_replace("^(.{2}).*$","\\1",$gallery[thumb]);

	if($year>90 && $year<=99){
		$year="19".$year;
	}else{
		$year="20".$year;
	}

	$src_w = 95;
	$src_h = 115;

	// Constraints
	$max_width = $src_w;
	$max_height = $src_h;
	$width1=imagesx($img);
	$height1=imagesy($img);
	$ratioh = $max_height/$height1;
	$ratiow = $max_width/$width1;
	$ratio = min($ratioh, $ratiow);
	// New dimensions
	$width = intval($ratio*$width1);
	$height = intval($ratio*$height1);

	if($width<$src_w){
		//$width=$src_w;
		//$height=intval($ratiow*$height1);

	}
	if($height<$src_h){
		//$height=$src_h;
		//$width=intval($ratioh*$width1);

	}


	$thmb = @imagecreatetruecolor($src_w,$src_h);
	$back = @imagecreatetruecolor($src_w-2,$src_h-2);
	$box = @imagecreatetruecolor($src_w-4,$src_h-29);
	$bg=@imagecolorallocate($thmb, 105, 93, 86);
	$bg2=@imagecolorallocate($back, 0, 0, 0);
	@imagefilledrectangle($thmb, 0, 0, $src_w, $src_h, $bg);
	@imagefilledrectangle($back, 0, 0, $src_w-2, $src_h-2, $bg2);

	$link_color = @imagecolorallocate ($img, 255, 255, 255);
	$link_color2 = @imagecolorallocate ($img, 75, 69, 55);

	@imagecopyresampled( $thmb, $back, 1, 1, 0, 0, $src_w-2, $src_h-2, imagesx($back), imagesy($back));

	@imagecopyresampled( $thmb, $img, 2, 2, 0, 0, $src_w-4, $src_h-29, imagesx($img), imagesy($img));
	@ImageTTFText ($thmb, 8, 0, 68, 100, $link_color, $_SERVER[DOCUMENT_ROOT]."/OCRAEXT.TTF","$year");
	@ImageTTFText ($thmb, 5, 0, 5, 109, $link_color2, $_SERVER[DOCUMENT_ROOT]."/OCRAEXT.TTF",ereg_replace("^(.{21}).*$","\\1",$entry));

	@Header( "Content-type: image/gif");
    ImageGIF($thmb);
	@imagedestroy($img);@imagedestroy($thmb);@imagedestroy($back);@imagedestroy($box);

?>
