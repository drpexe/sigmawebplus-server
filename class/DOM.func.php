<?php
	defined('VALID_ENTRY_POINT') or die('');

	//Get InnerHTML for DOM
	//http://stackoverflow.com/questions/2087103/innerhtml-in-phps-domdocument
	function DOMinnerHTML(DOMNode $element)
	{
		$innerHTML = "";
		$children  = $element->childNodes;
	
		foreach ($children as $child)
		{
			$innerHTML .= $element->ownerDocument->saveHTML($child);
		}
	
		return $innerHTML;
	}
?>