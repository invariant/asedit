<?php
require_once 'util.php';

$current_tags=array();
$current_contents=array();
$current_locale_name='';
$depth=0;

function as_extract($data_dir, $sku, $text_dir) {
	// p($data_dir);
	// p($sku);
	// p($text_dir);

	function startTag($parser, $name, $attribs) {
		global $current_tags, $current_contents, $current_locale_name, $depth;
		$depth+=1;

	    array_push($current_tags, $name);
	    array_push($current_contents, ''); 
	    if ($name==='LOCALE') {	    
	    	$current_locale_name=$attribs['NAME'];    
	    }

	    #spaces($depth); p('+'.$name);
	}

	function endTag($parser, $name){
		global $current_tags, $current_contents, $current_locale_name, $depth, $text_dir;

		$tag=strtolower(array_pop($current_tags));
		$contents=array_pop($current_contents);
		
		if ($tag==='description'||$tag==='version_whats_new') {
			p ("Got $tag for $current_locale_name");
			$dir=trim($text_dir)."/$tag";
			if (!file_exists($dir)) mkdir($dir, 0777, true);
			$filename=$dir."/$current_locale_name";			
			file_put_contents($filename, $contents);
		}

		#spaces($depth); p('-'.$tag);
		$depth-=1;
	}

	function contents($parser, $data){
		global $current_tags, $current_contents, $current_locale_name, $depth;

		//var_dump($data);
		$buffer=array_pop($current_contents);
		$buffer.=$data;
		array_push($current_contents, trim($buffer));
	}



	$data_file=trim($data_dir)."/".$sku.".itmsp/metadata.xml";
	$data=file_get_contents($data_file);

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startTag", "endTag");
	xml_set_character_data_handler($xml_parser, "contents");
	if(!(xml_parse($xml_parser, $data, true))){
	    die("Error on line " . xml_get_current_line_number($xml_parser));
	} 
}
?>