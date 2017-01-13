<?php

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/ris.php');

$force = false;
$force = true;

function ris_import($reference)
{
	global $couch;
	global $config;
	global $force;
	
	//print_r($reference);
	
	$doc = new stdclass;
	
	$doc->_id = $reference['_id'];
	
	// By default each reference is its own cluster
	$doc->cluster_id = $reference['_id'];
	
	// Identifiers we will use as cluster ids:
	$guid = '';
	
	// DOIs
	if (isset($reference['DO']))
	{
		$guid = $reference['DO'][0];
	}
	
	if ($guid == '')
	{
		if (isset($reference['UR']))
		{
			foreach ($reference['UR'] as $url)
			{
				if (preg_match('/http:\/\/www.jstor.org\/stable\//', $url))
				{
					$guid = $url;
				}
			}
		}
	}
	
	if ($guid != '')
	{
		$doc->cluster_id = $guid;
	}
	
	unset($reference['_id']);
	
	$doc->{'message-format'} = 'application/x-research-info-systems';
	$doc->{'message-timestamp'} = date("c", time());
	$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
	
	$doc->message = $reference;

	//echo json_encode($doc);
	
	// add to database
	$exists = $couch->exists($doc->_id);
	if (!$exists)
	{
		$couch->add_update_or_delete_document($doc, $doc->_id, 'add');	
	}
	else
	{
		if ($force)
		{
			$couch->add_update_or_delete_document($doc, $doc->_id, 'update');
		}
	}

}


$filename = '';
if ($argc < 2)
{
	echo "Usage: import.php <RIS file> <mode>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}


$file = @fopen($filename, "r") or die("couldn't open $filename");
fclose($file);

import_ris_file($filename, 'ris_import');



?>