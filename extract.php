<?php
require_once 'util.php';

//
// extract and replace descriptions between metadata.xml and textfiles
//

$current_tags=array();
$current_contents=array();
$current_locale_name='';

$xml_parser=NULL;
$pos=0;
$data=NULL;
$newdata=NULL;

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
	    p("Error on line " . xml_get_current_line_number($xml_parser));
		return false;
	} 
	return true;
}

function as_replace($data_dir, $sku, $text_dir) {
	global $xml_parser, $data, $newdata;

	function copy_range($start, $end)
	{
		global $data, $newdata;
		//p("Copy range $start to $end");
		$newdata.=substr($data, $start, $end-$start);
	}

	function append_new($text, $tag) {
		global $newdata;
		$newdata.="\n<$tag>".htmlspecialchars($text)."</$tag>";
	}

	function startTag($parser, $name, $attribs) {
		global $current_tags, $current_locale_name;
		$tag=strtolower($name);		
	    array_push($current_tags, $tag);
	    if ($tag==='locale') {	    
	    	$current_locale_name=$attribs['NAME'];    
	    }
	}

	function endTag($parser, $name){
		global $current_tags, $pos, $xml_parser, $text_dir, $current_locale_name;

		$tag=array_pop($current_tags);
		
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
	}

	function contents($parser, $text){
		// do nothing		
	}

	# back up the package dir
	$package_dir=trim($data_dir)."/".$sku.".itmsp";
	$backup_dir=trim($data_dir)."/".$sku."-backup-".time().".itmsp";
	`cp -r "$package_dir" "$backup_dir"`;

	$data_file=$package_dir."/metadata.xml";
	$data=file_get_contents($data_file);

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startTag", "endTag");
	xml_set_character_data_handler($xml_parser, "contents");
	if(!(xml_parse($xml_parser, $data, true))){
	    p("Error on line " . xml_get_current_line_number($xml_parser));
		return false;
	} 

	file_put_contents($data_file, $newdata);
	return true;
}
?>