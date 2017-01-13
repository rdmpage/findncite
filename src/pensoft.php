<?php

// BDJ XML

require_once (dirname(__FILE__) . '/couchsimple.php');

$dois = array();
$force = true; // true if we want to overwrite any previous loading

//----------------------------------------------------------------------------------------
function post_process_citation(&$citation)
{
	global $dois;

	// cluster id (use identiifer where possible)
	$guid = '';
	
	if (isset($citation->identifier))
	{
		foreach ($citation->identifier as $identifier)
		{
			switch ($identifier->type)
			{
				case 'doi':
					$guid = $identifier->id;
					$citation->cluster_id = $identifier->id;
					
					$dois[] = $identifier->id;
					break;
					
				case 'handle':
					if ($guid == '')
					{
						$guid = $identifier->id;
						$citation->cluster_id = $identifier->id;
					}
					break;
					
				default:
					break;
			}
		}
	}
	
	// DOI as link...
	if ($guid == '')
	{
		if (isset($citation->link))
		{
			foreach ($citation->link as $link)
			{
				switch ($link->anchor)
				{
					case 'LINK':
						if (preg_match('/http[s]?:\/\/(dx)?doi\.org\/(?<doi>.*)/', $link->url, $m))
						{
							$guid = $m['doi'];
							$citation->cluster_id = $m['doi'];
							$dois[] = $m['doi'];
						}
						break;
					
					default:
						break;
				}
			}
		}
	}
	
	// id is order in list of refs
	$citation->_id = 'http://dx.doi.org/' . $citation->_id;
	
	// if no external identifier cluster id is same as record
	if ($guid == '')
	{
		$citation->cluster_id = $citation->_id;
	}
	
	if (!isset($citation->genre))
	{
		$citation->genre = 'generic';
		
		if (isset($citation->journal))
		{
			$citation->genre = 'article';
		}
	}
	
	// clean up
	if (isset($citation->identifier) && count($citation->identifier) == 0)
	{
		unset($citation->identifier);
	}
	if (isset($citation->link) && count($citation->link) == 0)
	{
		unset($citation->link);
	}

	$doc = new stdclass;
	
	$doc->{'message-format'} = 'application/json';
	
	$doc->_id = $citation->_id;
	$doc->cluster_id = $citation->cluster_id;
	
	unset($citation->_id);
	unset($citation->cluster_id);
	
	$doc->{'message-timestamp'} = date("c", time());
	$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
				
	$doc->message = $citation;

	return $doc;
}


//--------------------------------------------------------------------------------------------------
// extract list of references from XML and return as BibJSON array
function get_references_from_xml($xml)
{		
	global $couch;
	global $force;

	$xp = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load(dirname(__FILE__) . '/pensoft2bibjson.xsl');
	$xp->importStylesheet($xsl);
	
	$dom = new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);

	$json = $xp->transformToXML($dom);
	
	$obj = json_decode($json);
	
	$n = count($obj);
	for ($i = 0; $i < $n; $i++)
	{
		$doc = post_process_citation($obj[$i]);					
		
		print_r($doc);
		
		//echo json_encode($doc);
		
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
}



$xml = file_get_contents('spiders.xml');

$xml = file_get_contents('2798.xml');


get_references_from_xml($xml);

print_r($dois);

echo join("',\n'", $dois);


?>
