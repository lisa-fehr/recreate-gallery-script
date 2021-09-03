<?

Class melan_thumbnail{

	private $db_link;
	private $Path;
	private $fullPath;
	private $thumbPath;
	private $id;

	function __construct($id){
		$this->db_link = @mysqli_connect("localhost","lisa","******","sushi");
		$this->Path = "";
		$this->fullPath = $_SERVER['DOCUMENT_ROOT']."/$this->Path";
		$this->thumbPath = "t";
		$this->id = $id
	}

	public function generateOrShowThumbnail(){
		$sql=@mysqli_query($this->db_link, "SELECT tag_assoc.*,gallery.*,tag.* FROM uber_tag_assoc tag_assoc, uber_gallery gallery, uber_tags tag where gallery.id = '".$this->id."' and gallery.id = tag_assoc.image_id and tag_assoc.tag_id = tag.id limit 1");

		$gallery=@mysqli_fetch_array($sql);
		$sql->close();

			$src_w = 125;
			$src_h = 175;

		if(file_exists($this->fullPath."/$gallery[directory]/t2/".preg_replace('/[.]gif$/', '.jpg', $gallery['thumb'])) && $gallery['thumb']){
			$size = GetImageSize($this->fullPath."/$gallery[directory]/t2/".preg_replace('/[.]gif$/', '.jpg', $gallery['thumb']));
		}
		//if((!file_exists($fullPath."/$gallery[directory]/t2/".ereg_replace('[.]gif$', '.jpg', $gallery['thumb'])) && $gallery['thumb']) || (isset($size) && ($size[0] != $src_w || $size[1] != $src_h))){

			if(file_exists($this->fullPath."/$gallery[directory]/originals/".$gallery['img'].".jpg")){
				$img = @imagecreatefromjpeg($this->fullPath."/$gallery[directory]/originals/$gallery[img].jpg");
			}else{
				$img = @imagecreatefromjpeg($this->fullPath."/$gallery[directory]/$gallery[img].jpg");
			}
			/*$mon=@ereg_replace("^.{2}(.{2}).*$","\\1",$gallery[thumb]);
			$day=@ereg_replace("^.{4}(.{2}).*$","\\1",$gallery[thumb]);
			$year=@ereg_replace("^(.{2}).*$","\\1",$gallery[thumb]);

			if($year>90 && $year<=99){
				$year="19".$year;
			}else{
				$year="20".$year;
			}

			$mon=@date("M", mktime(0, 0, 0, $mon, $day, $year));*/

			$thmb = @imagecreatetruecolor($src_w,$src_h);
			$back = @imagecreatetruecolor($src_w-2,$src_h-2);
			$bg=@imagecolorallocate($thmb, 105, 93, 86);
			$bg2=@imagecolorallocate($back, 0, 0, 0);
			@imagefilledrectangle($thmb, 0, 0, $src_w, $src_h, $bg);
			@imagefilledrectangle($back, 0, 0, $src_w-2, $src_h-2, $bg2);

			$link_color = @imagecolorallocate ($img, 255, 255, 255);
			$link_color2 = @imagecolorallocate ($img, 153, 153, 255);
			//$massage="+:$mon.$day,";$massage2="$year";

			@imagecopyresampled( $thmb, $back, 1, 1, 0, 0, $src_w-2, $src_h-2, imagesx($back), imagesy($back));

			//@imagecopyresampled( $thmb, $img, 1, 13, 0, 0, $src_w-2, $src_h-28, imagesx($img), imagesy($img));
			//@imagecopyresampled( $thmb, $img, 2, 27, 0, 0, $src_w-4, $src_h-29, imagesx($img), imagesy($img));
			@imagecopyresampled( $thmb, $img, 2, 2, 0, 0, $src_w-4, $src_h-20, imagesx($img), imagesy($img));
			//imagecopymerge ($thmb, $img, 0, 3, 0, 0, imagesx($img), imagesy($img),95); //196 x 58
			/*@ImageTTFText ($thmb, 8, 0, 3, 160, $link_color, $fullPath."/OCRAEXT.TTF","$massage");
			@ImageTTFText ($thmb, 8, 0, 65, 160, $link_color, $fullPath."/OCRAEXT.TTF","$massage2");*/
			//@ImageTTFText ($thmb, 8, 0, 2, 110, $link_color2, $_SERVER[DOCUMENT_ROOT]."/OCRAEXT.TTF",ereg_replace("^(.{15}).*$","\\1",$gallery[img]));
			//@ImageTTFText ($thmb, 8, 0, 2, 20, $link_color2, $_SERVER[DOCUMENT_ROOT]."/OCRAEXT.TTF",ereg_replace("^(.{15}).*$","\\1",$gallery[img]));
			$text = preg_replace("/^(.{21}).*$/","\\1",$gallery['img']);
			$text = preg_replace("/_/"," ",$text);
			$text_width = 6 * strlen($text);
			$position_center = ceil(($src_w - $text_width) / 2)+5; 
			@ImageTTFText ($thmb, 6, 0, $position_center, 169, $link_color2, $fullPath."/OCRAEXT.TTF",$text);



			@Header( "Content-type: image/jpeg");
			//ImageJPEG($thmb,NULL,95);
			//ImageGIF($thmb,$fullPath."/$gallery[directory]/t2/$gallery[thumb]");
			ImageJPEG($thmb,null,85);
			$image_value = ImageJPEG($thmb,"$this->fullPath/$gallery[directory]/t2/".preg_replace('/[.]gif$/', '.jpg', $gallery['thumb']), 85);
			//ImageJPEG($thmb);
			@imagedestroy($img);
			@imagedestroy($thmb);

		/*}else{
			@Header( "Content-type: image/gif");
			//include($this->fullPath."/$gallery[directory]/t2/$gallery[thumb]");
			$file=$this->fullPath."/$gallery[directory]/t2/$gallery[thumb]";
			if(!file_exists($file)){
				$file=$fullPath."/missing.jpg";
			}

			$fh = fopen($file, 'r');
			$theData = fread($fh, filesize($file));
			fclose($fh);
			echo $theData;

		}*/
	}

}
$thumbnail = new melan_thumbnail($_GET['id']);
$thumbnail->generateOrShowThumbnail();
?>