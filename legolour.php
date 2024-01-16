<?php
$path = "test2.gif";

$scale = abs(htmlspecialchars($_GET["scale"]));
if(empty($scale)){
	$scale=1;
}
$url = htmlspecialchars($_GET["imageurl"]);
error_log("URL= $url");
if(!empty($url)){
		$data = file_get_contents($url);
		$im = imagecreatefromstring($data);
		// TODO get extension
		imagegif($im, "./write/temp.gif");
		$path = "./write/temp.gif";
}

$img = imagecreatefromgif($path);
$w = imagesx($img);
$h = imagesy($img);

// REMOVE WHITEBOX AROUND IMAGE
$b_top = 0;
$b_btm = 0;
$b_lft = 0;
$b_rt = 0;

$l= imagecolorat($img, 0, $b_top);
error_log("eerste pixelkleur = $l");
//top
for(; $b_top < imagesy($img); ++$b_top) {
  for($x = 0; $x < imagesx($img); ++$x) {
    if(imagecolorat($img, $x, $b_top) != $l) {
       break 2;
    }
  }
}
//bottom
for(; $b_btm < imagesy($img); ++$b_btm) {
  for($x = 0; $x < imagesx($img); ++$x) {
    if(imagecolorat($img, $x, imagesy($img) - $b_btm-1) != $l) {
       break 2;
    }
  }
}
//left
for(; $b_lft < imagesx($img); ++$b_lft) {
  for($y = 0; $y < imagesy($img); ++$y) {
    if(imagecolorat($img, $b_lft, $y) != $l) {
       break 2;
    }
  }
}
//right
for(; $b_rt < imagesx($img); ++$b_rt) {
  for($y = 0; $y < imagesy($img); ++$y) {
    if(imagecolorat($img, imagesx($img) - $b_rt-1, $y) != $l) {
       break 2;
    }
  }
}
//copy the contents, excluding the border
$newimg = imagecreatetruecolor(
    imagesx($img)-($b_lft+$b_rt), imagesy($img)-($b_top+$b_btm));

$w = $w-($b_lft+$b_rt);
error_log( "b_lft=$b_lft   b_rt=$b_rt");
$h = $h-($b_top+$b_btm);
error_log("b_top= $b_top   b_btm=$b_btm");

//$newimg = imagecreate($w,$h);
imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));

//create new "blank" image
//$newimg = imagecreatetruecolor($w, $h);
$img = $newimg;

//listed color
//palette array for the 21226
$palette = array(
array(242,205,55),
array(255,255,255),
array(228,205,158),
array(88,42,18),
array(201,26,9),
array(254,138,24),
array(170,125,85),
array(187,233,11),
array(173,195,192),
array(10,52,99),
array(7,139,201),
array(228,173,200),
array(255,240,58),
array(248,187,61),
array(75,159,74),
array(5,19,29)
);

$yellow =imagecolorallocate($newimg,242,205,55);
$white = imagecolorallocate($newimg,255,255,255);
$tan = imagecolorallocate($newimg,228,205,158);
$reddishbrown = imagecolorallocate($newimg,88,42,18);
$red = imagecolorallocate($newimg,201,26,9);
$orange = imagecolorallocate($newimg,254,138,24);
$mediumnougat = imagecolorallocate($newimg,170,125,85);
$lime = imagecolorallocate($newimg,187,233,11);
$lightaqua = imagecolorallocate($newimg,173,195,192);
$darkblue = imagecolorallocate($newimg,10,52,99);
$darkazure = imagecolorallocate($newimg,7,139,201);
$brightpink = imagecolorallocate($newimg,228,173,200);
$brightlightyellow = imagecolorallocate($newimg,255,240,58);
$brightlightorange = imagecolorallocate($newimg,248,187,61);
$brightgreen = imagecolorallocate($newimg,75,159,74);
$black = imagecolorallocate($newimg,5,19,29);

$colourarray = array($yellow,$white,$tan,$reddishbrown,$red,$orange,$mediumnougat,$lime,$lightaqua,$darkblue,$darkazure,$brightpink,$brightlightyellow,$brightlightorange,$brightgreen,$black);

$tt = imagecolorstotal($img);
//echo "Image color total: $tt";

function absoluteColorDistance(array $color_a, array $color_b): int {
    return
        abs($color_a[0] - $color_b["red"]) +
        abs($color_a[1] - $color_b["green"]) +
        abs($color_a[2] - $color_b["blue"]);
}

function getnearestpixel(array $imgarray, array $palette){
        //set the distance absurd hight
	$distance = 99999;
	//set iterator on 0
	$i = 0;
	$rightrgbarrayindex = null;
	do
	{		
		//check if distance is lower than current distance
		$absdist = absoluteColorDistance($palette[$i],$imgarray);
//		echo "ABSDIST = $absdist";
		if ($absdist < $distance){
			$distance = $absdist;
			$rightrgbarrayindex = $i;
		}
		$i++;
	}
	while($i < count($palette));
	return $rightrgbarrayindex;
}

for($x = $b_lft; $x < $w; $x++) {
    for($y = $b_top; $y < $h; $y++) {

        // pixel color at (x, y)
        $rgb = imagecolorat($img, $x, $y);

	//get nearestpixelarray
	$nearestpixelarrayindex = getnearestpixel(imagecolorsforindex($img, $rgb),$palette);

	//set the pixel colour with our pallet in newimg
	$color = $colourarray[$nearestpixelarrayindex];
	imagesetpixel($newimg, $x, $y, $color);

    }
}

$scaledimg = imagecreate($w*$scale,$h*$scale);
imagecopyresampled($scaledimg,$newimg,0,0,0,0,$w*$scale,$h*$scale,$w,$h);
//imagegif($scaledimg,'./write/output.gif');

header('Content-Type: image/gif');
imagegif($scaledimg);
?>
