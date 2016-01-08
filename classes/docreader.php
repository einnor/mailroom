<?php
class docreader{
	public function read_file_doc($filename) {//http://stackoverflow.com/questions/7358637/reading-doc-file-in-php
        if(($fh = fopen($filename, 'r')) !== false ){
			$headers = fread($fh, 0xA00);
			// 1 = (ord(n)*1) ; Document has from 0 to 255 characters
			$n1 = ( ord($headers[0x21C]) - 1 );
			// 1 = ((ord(n)-8)*256) ; Document has from 256 to 63743 characters
			$n2 = ( ( ord($headers[0x21D]) - 8 ) * 256 );
			// 1 = ((ord(n)*256)*256) ; Document has from 63744 to 16775423 characters
			$n3 = ( ( ord($headers[0x21E]) * 256 ) * 256 );
			// 1 = (((ord(n)*256)*256)*256) ; Document has from 16775424 to 4294965504 characters
			$n4 = ( ( ( ord($headers[0x21F]) * 256 ) * 256 ) * 256 );
			// Total length of text in the document
			$textLength = ($n1 + $n2 + $n3 + $n4);
			$extracted_plaintext = fread($fh, $textLength);
			// simple print character stream without new lines
			//echo $extracted_plaintext;
			// if you want to see your paragraphs in a new line, do this
			$extracted_plaintext = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $extracted_plaintext);
			$extracted_plaintext = str_replace('</w:r></w:p>', "\r\n", $extracted_plaintext);
			
			$extracted_plaintext = strip_tags($extracted_plaintext);
			
			return nl2br($extracted_plaintext);
           // need more spacing after each paragraph use another nl2br
        }
    } 
	function parseWord($userDoc){//Stuff i found! http://www.experts-exchange.com/Programming/Languages/Scripting/PHP/Q_28317907.html
		$fileHandle = fopen($userDoc, "r");
		$word_text = @fread($fileHandle, filesize($userDoc));
		$line = "";
		$tam = filesize($userDoc);
		$nulos = 0;
		$caracteres = 0;
		for($i=1536; $i<$tam; $i++)
		{
			$line .= $word_text[$i];
	
			if( $word_text[$i] == 0)
			{
				$nulos++;
			}
			else
			{
				$nulos=0;
				$caracteres++;
			}
	
			if( $nulos>1996)
			{   
				break;  
			}
		}
	
		//echo $caracteres;
	
		$lines = explode(chr(0x0D),$line);
		//$outtext = "<pre>";
	
		$outtext = "";
		foreach($lines as $thisline)
		{
			$tam = strlen($thisline);
			if( !$tam )
			{
				continue;
			}
	
			$new_line = ""; 
			for($i=0; $i<$tam; $i++)
			{
				$onechar = $thisline[$i];
				if( $onechar > chr(240) )
				{
					continue;
				}
	
				if( $onechar >= chr(0x20) )
				{
					$caracteres++;
					$new_line .= $onechar;
				}
	
				if( $onechar == chr(0x14) )
				{
					$new_line .= "</a>";
				}
	
				if( $onechar == chr(0x07) )
				{
					$new_line .= "\t";
					if( isset($thisline[$i+1]) )
					{
						if( $thisline[$i+1] == chr(0x07) )
						{
							$new_line .= "\n";
						}
					}
				}
			}
			//troca por hiperlink
			$new_line = str_replace("HYPERLINK" ,"<a href=",$new_line); 
			$new_line = str_replace("\o" ,">",$new_line); 
			$new_line .= "\n";
	
			//link de imagens
			$new_line = str_replace("INCLUDEPICTURE" ,"<br><img src=",$new_line); 
			$new_line = str_replace("\*" ,"><br>",$new_line); 
			$new_line = str_replace("MERGEFORMATINET" ,"",$new_line); 
	
	
			$outtext .= nl2br($new_line);
		}
	
	 return $outtext;
	}  
}
/*$filename = "file1.docx";

$content = read_file_docx($filename);
if($content !== false) {
	$myfile=nl2br($content);
	$mycnt=preg_split('/\n|\r/', $myfile, -1, PREG_SPLIT_NO_EMPTY);
	//print_r($mycnt);
	//echo nl2br($content);	
}
else {
	echo 'Couldn\'t the file. Please check that file.';
}*/