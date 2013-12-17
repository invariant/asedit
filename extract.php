<?php
require_once 'util.php';

//
// extract descriptions from metadata.xml
//

$current_tags=array();
$current_contents=array();
$current_locale_name='';

function as_extract($data_dir, $sku, $text_dir) {

	function startTag($parser, $name, $attribs) {
		global $current_tags, $current_contents, $current_locale_name;

		$tag=strtolower($name);
	    array_push($current_tags, $tag);
	    array_push($current_contents, ''); 

	    if ($name==='locale') {	    
	    	$current_locale_name=$attribs['NAME'];    
	    }
	}

	function endTag($parser, $name){
		global $current_tags, $current_contents, $current_locale_name, $depth, $text_dir;

		$tag=array_pop($current_tags);
		$contents=array_pop($current_contents);
		
		if ($tag==='description'||$tag==='version_whats_new') {
			p ("Got $tag for $current_locale_name: " . strlen($contents) . "bytes");
			$dir=trim($text_dir)."/$tag";
			if (!file_exists($dir)) {
				mkdir($dir, 0777, true);	
			} 			
			file_put_contents($dir."/$current_locale_name", $contents);
		}
	}

	function contents($parser, $data){
		global $current_contents;

		$buffer=array_pop($current_contents);
		$buffer.=$data;
		array_push($current_contents, $buffer);
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