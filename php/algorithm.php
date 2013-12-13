	<?php
	require_once("convert.php");
	require_once("action.php");
	class algorithm {

	
		public function render($commitObjectArray, $modus = 0, $width, $height){ //array looks like this [[$msg, $diff],[$msg, $diff],[$msg, $diff]]
			$commitArray = $this->preprocess($commitObjectArray);
			$count = count($commitArray);

			callback('Start rendering...');

			$all_diff = 0;

			for ($j = 0; $j < $count; $j++){

				$all_diff += $commitArray[$j][1]; 

				################################
				### negativen diff behandeln ###
				################################

			}

			################################################## 
			#$width = 600; # Später die Breite des Rechtecks 
			#$height = 600; # Später die Höhe des Rechtecks 
			$datei = fopen("visualization-".session_id().".svg",  "w+");
			$s = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"no\"?>
					<svg
					width=\"".$width."px\" height=\"".$height."px\" version=\"1.1\" id=\"test\"
					xmlns:svg=\"http://www.w3.org/2000/svg\"> 
					<defs
				    id=\"defs4\" />
				  <metadata
				     id=\"metadata7\">
				    <rdf:RDF>
				      <cc:Work
				         rdf:about=\"\">
				        <dc:format>image/svg+xml</dc:format>
				        <dc:type
				           rdf:resource=\"http://purl.org/dc/dcmitype/StillImage\" />
				        <dc:title></dc:title>
				      </cc:Work>
				    </rdf:RDF>
				  </metadata>
				  <g
				     id=\"layer1\">
				    <g
				       transform=\"translate(-34.000015,268.36218)\"
				       id=\"g24941\">";
			fwrite($datei, $s);
			################################################## 
			
			$x = 0; 	#links oben -> links
			$y = 0; 	#links oben -> oben
			$hohe = 16;
			while($width%$hohe != 0)
			$hohe--; 	#rechts unten -> links BREITE

			$z = $hohe;	#rechts unten -> oben HÖHE

			$pixel = $width * $height; #all pixels on picture
			$factor = ($pixel/$hohe) / $all_diff;

			callback('Initialize image...');

			$returnArray = array();

			for ($i = 0; $i < $count; $i++){
				$diff = $commitArray[$i][1];
				$str = $commitArray[$i][0];
				$color = $this->commitToColor($modus, $str);
				$w = ($x+($diff*$factor));
 		 		if ($w > $width)
 		 		while ($w > $width){
 		 			$block = array(($width-$x), $hohe, $color);
 		 			$this->writeBlock($datei, $color, $x, $y, ($width-$x), $hohe);
					$overlap = $w-$width;
 		 			$x = 0;
					$y += $hohe;
					$w = $overlap;
					$z += $hohe;
					if ($w > $width){
						$block = array($width, $hohe, $color);
						$this->writeBlock($datei, $color, $x, $y, $width, $hohe);
					}
					else{
						$block = array($overlap, $hohe, $color);
						$this->writeBlock($datei, $color, $x, $y, $overlap, $hohe);
					}
					$x += $w;

				}
				else{
					$block = array(($diff*$factor), $hohe, $color);
					$this->writeBlock($datei, $color, $x, $y, ($diff*$factor), $hohe);
					$x += $diff*$factor;
				}
				$returnArray[] = $block;
			}

			callback('Initialize image...');
			fwrite($datei, "</g> </g></svg>");
			fclose($datei);
			return $returnArray;
		}

		private function commitToColor($modus, $msg){
			$conv = new convert();
			if ($msg == null)
		      	#return ImageColorAllocate($img, 211, 211, 211);
				return array(211,211,211);
		    $msg = preg_replace("/[^a-zA-Z0-9 ]/" , "" , $msg);
		    $msg = strtolower($msg);
		    if (strlen($msg) == 0)
		      	#return ImageColorAllocate($img, 211, 211, 211);
				return array(211,211,211);
		    switch ($modus) {
				case 0:
					$msg = preg_replace("/[^a-zA-Z0-9]/" , "" , $msg);
		    		$first = substr($msg, 0, 1);
					$h = $this->letterValue($first, 0);
		    		if (strlen($msg) > 1){
		    			$second = substr($msg, 1, 1);
		    			$s = 0.3 + 0.6 * $this->letterValue ($second, 1);
		    			if (strlen($msg) > 2) {
		    				$third = substr($msg, 2, 1);
		    				$l = 0.4 + 0.5 * $this->letterValue ($third, 2);
		    			}
		    			else {
		    				$l = 0.6;
		    			}
		    		}
		    		else{
		    			$s = 0.5;
		    			$l = 0.6;
		    		}
		    		$convArray = $conv->ColorHSLToRGB($h,$s,$l);
		    		$r = $convArray['r'];
		    		$g = $convArray['g'];
		    		$b = $convArray['b'];
		    		$color = array($r,$g,$b);
		    		/*$r = round($r);
		    		$g = round($g);
		    		$b = round($b);
		    		$color = ImageColorAllocate($img, $r, $g, $b);*/
		    		return $color;
				case 1:
					$keys1 = array("add", "new", "create");
					$section1 = array_fill_keys($keys1, "section1");
					
					$keys2 = array("delete", "remove");
					$section2 = array_fill_keys($keys2, "section2");
					
					$keys3 = array("fix", "bug");
					$section3 = array_fill_keys($keys3, "section3");

					$keyword_Array = array_merge($section1, $section2, $section3);
					
					$stringRep = explode(" ", $msg);
										
					$sec1 = 0;
					$sec2 = 0;
					$sec3 = 0;					
					
					$anzahl = count($stringRep);
					
					foreach($stringRep as $string){ 
						foreach($keys1 as $key){
							if(!((strripos($string, $key)) === false))
							{
								$sec1++;
							}
						}
						foreach($keys2 as $key){
							if(!((strripos($string, $key)) === false))
							{
								$sec2++;
							}
						}
						foreach($keys3 as $key){
							if(!((strripos($string, $key)) === false))
							{
								$sec3++;
							}
						}
						
					}

					$max = $sec1 + $sec2 + $sec3;
					$p = 0;
					if($max != 0)
						$p = (255 / $max);
					
					$sec1 = $p * $sec1;
					$sec2 = $p * $sec2;
					$sec3 = $p * $sec3;
					
					#$color = ImageColorAllocate($img, $sec1, $sec2, $sec3);
		    		$color = $sec1.",".$sec2.",".$sec3;
		    		return $color;
					break;
				default:
					echo "Hier läuft was schief.";
					break;
			}
		}


		private function preprocess($obj){
			require_once(__DIR__."/../lib/vcs/Commit.interface.php");
			for ($i = 0; $i < count($obj); $i++){
				$array[$i] = array($obj[$i]->CommitMessage(), $obj[$i]->NumChangedLines()) ;
			}
			return $array;
		}


		private function letterValue($letter) {

			$letterArray = array("a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z");

			$numberArray = array("y", "0", "1", "2", "3", "4", "5", "6", "7", "8", "9");


			if (is_numeric($letter)){
		    	$value = array_search($letter, $numberArray);
		    	$value = ($value * (26/10))-1;
			}
			else{
		    	$value = array_search($letter, $letterArray);
		    }
		    return $value /26;
		}

		private function writeBlock($datei, $color, $x, $y, $w, $h){
			$s = " <rect 	x = \"".$x."\" y =\"".$y."\" width =\"".$w."\" height=\"".$h."\"
					rx=\"0\" ry=\"0\" fill=\"rgb(".$color[0].",".$color[1].",".$color[2].")\"
					stroke=\"none\"
					stroke-width=\"0\"
					id =\"rect\"/> 			";
			fwrite($datei, $s);
		}
	}
?>
