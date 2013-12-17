<?php
require_once 'util.php';
function as_extract($data_dir, $sku, $text_dir) {
	p($data_dir);
	p($sku);
	p($text_dir);

	$data_file=trim($data_dir)."/".$sku.".itmsp/metadata.xml";
	$data=file_get_contents($data_file);

	$current_tag='';
	$current_contents='';

	$current_locale_name='';

	function startTag($parser, $data, $attribs){
	    $current_tag=$data;
	    $current_contents='';
	    if ($current_tag==='LOCALE') {
	    	$current_locale_name=$attribs['name'];
	    }
	}

	function endTag($parser, $data){
		if ($current_tag==='LOCALE') {
			p($current_locale_name);	    
		}
		$current_tag='';
		$current_contents='';
	}

	function contents($parser, $data){
		$current_contents.=$data;
	}

	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startTag", "endTag");
	xml_set_character_data_handler($xml_parser, "contents");
	if(!(xml_parse($xml_parser, $data, true))){
	    die("Error on line " . xml_get_current_line_number($xml_parser));
	} 
}
?>