<?php


// Cluster references that we think are the "same"

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/couchsimple.php');
require_once (dirname(__FILE__) . '/fingerprint.php');
require_once (dirname(__FILE__) . '/lcs.php');



//----------------------------------------------------------------------------------------

$parents = array();

function makeset($x) {
	global $parents;
	
	$parents[$x] = $x;
}

function find($x) {
	global $parents;
	
	if ($x == $parents[$x]) {
		return $x;
	} else {
		return find($parents[$x]);
	}
}

function union($x, $y) {
	global $parents;
	
	$x_root = find($x);
	$y_root = find($y);
	$parents[$x_root] = $y_root;
	
}

//----------------------------------------------------------------------------------------
// get list of things to match


//----------------------------------------------------------------------------------------

// look up hash, if we get hits then test
$hash = array("2005", "104", "103");

// Jäger Heteropodinae: Transfers and Synonymies (Arachnida: Araneae: Sparassidae) Acta Arachnologica 51 1 33-61
$hash = array("2002", "51", "33");

// 10.4102/koedoe.v31i1.493
// fails because CrossRef metadata lacks page numbers FFS
$hash = array("1988", "31", "161");

$url = '_design/hash/_view/numbers_citation?key=' . urlencode(json_encode($hash)) . '&include_docs=true';

/*
if ($config['stale'])
{
	$url .= '&stale=ok';
}	
*/
	
$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
	
$response_obj = json_decode($resp);

//print_r($response_obj);

// If we have more than one reference with the same hash, compare and cluster
$n = count($response_obj->rows);

if ($n > 1)
{
	
	for ($i = 0; $i < $n; $i++)
	{
		makeset($i);
	}

	for ($i = 1; $i < $n; $i++)
	{
		for ($j = 0; $j < $i; $j++)
		{
			$v1 = $response_obj->rows[$i]->value;
			$v2 = $response_obj->rows[$j]->value;
			
			echo $v1 . "\n";
			echo $v2 . "\n";
					
			$v1 = finger_print($v1);
			$v2 = finger_print($v2);
			
			$lcs = new LongestCommonSequence($v1, $v2);
			$d = $lcs->score();
			
			// echo $d;
			
			$score = min($d / strlen($v1), $d / strlen($v2));
			
			if ($score > 0.80)
			{
				echo "MATCH\n";
				
				union($i, $j);
			}
		}
	}
	
	$blocks = array();
	
	for ($i = 0; $i < $n; $i++)
	{
		$p = $parents[$i];
		
		if (!isset($blocks[$p]))
		{
			$blocks[$p] = array();
		}
		$blocks[$p][] = $i;
	}
		
	print_r($blocks);
	
	// merge things 
	foreach ($blocks as $block)
	{
		if (count($block) > 1)
		{
			$cluster_id = $response_obj->rows[$block[0]]->doc->_id;
			
			foreach ($block as $i)
			{
				$doc = $response_obj->rows[$i]->doc;
				$doc->cluster_id = $cluster_id;
				
				echo $doc->_id . ' ' . $doc->cluster_id . "\n";
				
				// update
				$couch->add_update_or_delete_document($doc, $doc->_id, 'update');
			}
		}
	}
	
	
	
	
	
	
	
	
}
	



// OK, merge these records


?>

