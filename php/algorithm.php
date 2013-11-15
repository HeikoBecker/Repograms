<?php
	
	class algorithm {

		public function render($commitArray, $modus = 0){ //array looks like this [[$msg, $diff],[$msg, $diff],[$msg, $diff]]

			$count = count($commitArray);

			$all_diff = 0;

			for ($j = 0; $j < $count; $j++){

				$all_diff += $commitArray[$j][1]; 

				################################
				### negativen diff behandeln ###
				################################

			}

			################################################## 
			$width = 300; # Später die Breite des Rechtecks 
			$height = 300; # Später die Höhe des Rechtecks 
			$img = ImageCreate($width, $height); # Hier wird das Bild einer Variable zu gewiesen 
			################################################## 
			
			$x = 0; 	#links oben -> links
			$y = 0; 	#links oben -> oben
			$hohe = 15; 	#rechts unten -> links BREITE
			$z = $hohe;	#rechts unten -> oben HÖHE

			$pixel = $width * $height; #all pixels on picture
			$factor = ($pixel/$hohe) / $all_diff;

			for ($i = 0; $i < $count; $i++){
				$str = $commitArray[$i][0];
				$color = $this->commitToColor($modus, $str, $img);
 		 		#$color = ImageColorAllocate($img, 100, 100, 100);
 		 		$w = ($x+($diff*$factor));
				ImageFilledRectangle($img, $x, $y, $w, $z, $color); 
				if ($w > $width){
					$overlap = $w-$width;
					$x = 0;
					$y += $hohe;
					$w = $overlap;
					$z += $hohe;
					ImageFilledRectangle($img, $x, $y, $w, $z, $color); 
					$x += $w;
				}
				else{
					$x += $diff*$factor;
				}
			}

			imagepng($img, "name.png");
			return $img;
		}

		private function commitToColor($modus, $msg, $img){
			if ($msg == null or strlen($msg) == 0)
		      			return ImageColorAllocate($img, 211, 211, 211);
		    $msg = preg_replace("/[^a-zA-Z0-9 ]/" , "" , $msg);
		    $msg = strtolower($msg);
		    switch ($modus) {
				case 0:
		    		$first = substr($msg, 0, 1);
		    		$r = $this->letterValue($first, 0);
		    		if (strlen($msg) > 1){
		    			$second = substr($msg, 1, 1);
		    			$g = 0.3 + 0.6 * $this->letterValue ($second, 1);
		    			if (strlen($msg) > 2) {
		    				$third = substr($msg, 2, 1);
		    				$b = 0.4 + 0.5 * $this->letterValue ($third, 2);
		    			}
		    			else {
		    				$b = 0;
		    			}
		    		}
		    		else{
		    			$g = 0;
		    		}
		    		$color = ImageColorAllocate($img, $r, $g, $b);
		    		return $color;
				case 1:
					// /* hier muss der String noch zerteilt werden */
					// for ($i=0; $i < $anzahl; $i++) { 
					// 	#array_search($stringRep[i], $keyword_Array)
					// }
					break;
				default:
					echo "Hier läuft was schief.";
					break;
			}
		}


		private function letterValue($letter) {

			$letterArray = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

			$numberArray = array("y", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");


			if (is_numeric($letter)){
		    	$value = 22 * array_search($letter, $numberArray);
			}
			else{
		    	$value = 8 * array_search($letter, $letterArray);
		    }
		    return $value;
		}

	}


?>