<?php
require_once 'util.php';

$current_tags=array();
$current_locale_name='';

$depth=0;
$pos=0;
$xml_parser=NULL;
$data=NULL;
$newdata=NULL;

function as_replace($data_dir, $sku, $text_dir) {
	global $xml_parser, $data, $newdata;
	// p($data_dir);
	// p($sku);
	// p($text_dir);

	function copy_range($start, $end)
	{
		global $data, $newdata;
		p("Copy range $start to $end");
		$newdata.=substr($data, $start, $end-$start);
	}

	function append_new($text, $tag) {
		global $newdata;
		$newdata.="\n<$tag>".htmlspecialchars($text)."</$tag>";
	}

	function startTag($parser, $name, $attribs) {
		global $current_tags, $depth, $current_locale_name;
		$depth+=1;
		$tag=strtolower($name);		
	    array_push($current_tags, $tag);
	    if ($tag==='locale') {	    
	    	$current_locale_name=$attribs['NAME'];    
	    }
	    spaces($depth); p('+'.$name);
	}

	function endTag($parser, $name){
		global $current_tags, $depth, $pos, $xml_parser, $text_dir, $current_locale_name;

		$tag=strtolower(array_pop($current_tags));
		
		if ($tag==='locale') {	    
	    	$current_locale_name='';    
	    }	    
	    $newpos=xml_get_current_byte_index($xml_parser);	
		if ($tag==='description'||$tag==='version_whats_new') {
			$newtext=file_get_contents(trim($text_dir)."/$tag/$current_locale_name");
			if (strlen($newtext)>0) {
				append_new($newtext, $tag);
			}
			$pos=$newpos;
		}
		else {
			copy_range($pos, $newpos);
			$pos=$newpos;
		}

		spaces($depth); p('-'.$tag);
		$depth-=1;
	}

	function contents($parser, $text){
		global $current_tags, $depth, $pos;
	}

	$data_file=trim($data_dir)."/".$sku.".itmsp/metadata.xml";
	$data=file_get_contents($data_file);

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startTag", "endTag");
	xml_set_character_data_handler($xml_parser, "contents");
	if(!(xml_parse($xml_parser, $data, true))){
	    die("Error on line " . xml_get_current_line_number($xml_parser));
	} 

	$new_data_dir=trim($data_dir)."/".$sku."-new.itmsp";
	if (!file_exists($new_data_dir)) {
		mkdir($new_data_dir);
	}
	file_put_contents($new_data_dir."/metadata.xml", $newdata);
}
?>