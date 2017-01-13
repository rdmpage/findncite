<?php

require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/couchsimple.php');


//----------------------------------------------------------------------------------------
// CrossRef API
function get_work($doi)
{
	$data = null;
	
	$url = 'https://api.crossref.org/v1/works/http://dx.doi.org/' . $doi;
	
	$json = get($url);
	
	if ($json != '')
	{
		$obj = json_decode($json);
		if ($obj)
		{
			$doc = new stdclass;
			
			$doc->{'message-format'} = 'application/vnd.crossref-api-message+json';
			
			$doc->_id = 'http://dx.doi.org/' . $doi;
			$doc->cluster_id = $doi;
			$doc->{'message-timestamp'} = date("c", time());
			$doc->{'message-modified'} 	= $doc->{'message-timestamp'};
						
			$doc->message = $obj->message;
		}
	}
	
	return $doc;
}


$dois = array(
'10.1676/04-064',
'10.1676/0043-5643(2001)113[0001:ANZTAT]2.0.CO;2'
);

$dois=array(
'10.2476/asjaa.50.183'
);

$dois=array(
'10.1111/j.1463-6409.2005.00189.x',
'10.1111/j.1365-3113.2005.00289.x',
'10.1636/04-56.1',
'10.1002/jmor.10296',
'10.1163/187631205788912804',
'10.1590/s0101-81752005000100022',
'10.1590/s0101-81752005000300039',
'10.1636/h03-70.1',
'10.1080/00222930400008868',
'10.5962/bhl.part.80289',
'10.2476/asjaa.54.81',
'10.1590/s1676-06032005000300019',
);

$dois=array(
'10.2476/asjaa.51.33'
);

$dois=array(
'10.1111/j.1096-3642.2007.00316.x',
'10.1016/j.ympev.2008.04.027',
'10.1636/CA10-57.1',
'10.1206/0003-0090(2000)254<0001:NWPSAP>2.0.CO;2',
'10.1206/0003-0090(2001)260<0001:TPOAAP>2.0.CO;2',
'10.1046/j.1096-3642.2003.00046.x',
'10.1046/j.1096-3642.2003.00053.x',
'10.1046/j.0024-4082.2003.00082.x',
'10.1007/0-387-24320-8_15',
'10.1080/00222930903207876',
'10.5402/2011/345606',
'10.2476/asjaa.62.75',
'10.5656/KSAE.2014.10.0.059',
'10.11646/zootaxa.3909.1.1',
'10.11646/zootaxa.3709.1.1'
);

$dois=array(
'10.1636/Sh06-55.1',
'10.5479/si.00810282.496',
'10.1146/annurev.es.22.110191.003025',
'10.1111/j.1096-3642.1998.tb01290.x',
'10.1111/j.1463-6409.1979.tb00638.x',
'10.1093/bioinformatics/17.8.754',
'10.1111/j.1095-8312.2005.00516.x',
'10.1098/rspb.2006.3699',
'10.1016/j.ympev.2010.02.021',
'10.5531/db.iz.0001.',
'10.1080/21560382.1904.9626437',
'10.1111/j.1463-6409.2008.00328.x',
'10.1093/bioinformatics/btg180',
'10.1006/anbe.2001.1961',
'10.1071/IS10001',
'10.1016/j.ympev.2007.08.008',
'10.1163/156853985X00325'
);

$dois=array('10.2476/asjaa.51.109');

foreach ($dois as $doi)
{
	$doc = get_work($doi);
	
	//print_r($doc);
	
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

?>
