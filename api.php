<?php

error_reporting(E_ALL);

require_once (dirname(__FILE__) . '/api_utils.php');
require_once (dirname(__FILE__) . '/src/couchsimple.php');

//--------------------------------------------------------------------------------------------------
function default_display()
{
	echo "hi";
}



//--------------------------------------------------------------------------------------------------
// Full text search
function display_search ($q, $bookmark = '', $callback = '')
{
	global $config;
	global $couch;
	
	$rows_per_page = 10;
			
	if ($q == '')
	{
		$obj = new stdclass;
		$obj->rows = array();
		$obj->total_rows = 0;
		$obj->bookmark = '';	
		
		// Add status
		$obj->status = 404;
			
	}
	else
	{		
		
		$parameters = array(
			'q'					=> $q,
			'highlight_fields' 	=> '["default"]',
			'highlight_pre_tag' => '"<span style=\"color:white;background-color:green;\">"',
			'highlight_post_tag'=> '"</span>"',
			'highlight_number'	=> 5,
			'include_docs' 		=> 'false',
			'limit' 			=> 10,
		
			'group_field'		=> 'cluster'
		);		
			
		if ($bookmark != '')
		{
			$parameters['bookmark'] = $bookmark;
		}
					
		//$url = '/_design/citation/_search/all?' . http_build_query($parameters);		
		//$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
		
		$url = 'https://rdmpage:peacrab280398@rdmpage.cloudant.com/findncite/_design/search/_search/all?' .  http_build_query($parameters);
		
		
		
		//echo $url;
		$resp = get($url);
		
		//echo $resp;
		
		$obj = json_decode($resp);
		
		// Add status
		$obj->status = 200;
	}
	
	api_output($obj, $callback);
}



//--------------------------------------------------------------------------------------------------
function main()
{
	$callback = '';
	$handled = false;
	
	//print_r($_GET);
	
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	if (isset($_GET['callback']))
	{	
		$callback = $_GET['callback'];
	}
	
	// Submit job
	/*
	if (!$handled)
	{
		if (isset($_GET['id']))
		{	
			$id = $_GET['id'];
			
			$format = '';
			
			if (isset($_GET['format']))
			{
				$format = $_GET['format'];
				
				if (isset($_GET['style']))
				{
					$style = $_GET['style'];
					display_formatted_citation($id, $style);
					$handled = true;
				}
			}
			
			if (!$handled)
			{
				display_one($id, $format, $callback);
				$handled = true;
			}
			
		}
	}
	*/
	
	if (!$handled)
	{
		if (isset($_GET['q']))
		{	
			$q = $_GET['q'];
			
			$bookmark = '';
			if (isset($_GET['bookmark']))
			{
				$bookmark = $_GET['bookmark'];
			}			
			
			display_search($q, $bookmark, $callback);
			$handled = true;
		}			
	}
	
	if (!$handled)
	{
		default_display();
	}
	
		

}


main();

?>