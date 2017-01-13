<?php

require_once(dirname(__FILE__) . '/src/lib.php');



$q = 'Dytiscidae';

if (isset($_GET['q']))
{
	$q = $_GET['q'];
}

$parameters = array(
		'q'					=> $q,
		'highlight_fields' 	=> '["default"]',
		'highlight_pre_tag' => '"<span style=\"color:white;background-color:green;\">"',
		'highlight_post_tag'=> '"</span>"',
		'highlight_number'	=> 5,
		'include_docs' 		=> 'true',
		'limit' 			=> 10,
		
		'group_field'		=> 'cluster'
	);
	
if ($bookmark != '')
{
	$parameters['bookmark'] = $bookmark;
}
			
//$url = '/_design/search/_search/all?' . http_build_query($parameters);

$url = 'https://rdmpage:peacrab280398@rdmpage.cloudant.com/findncite/_design/search/_search/all?' .  http_build_query($parameters);

//echo $url . '<br />';

//$resp = $couch->send("GET", "/" . $config['couchdb_options']['database'] . "/" . $url);
$json = get($url);
$obj = json_decode($json);

if (0)
{
	echo '<pre>';
	print_r($obj);
	echo '</pre>';
}
	// Display...
	echo 
'<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
		
		<script>
		</script>
	</head>
	<body style="font-family:sans-serif">';
	
echo '<div>';
echo '<form >
  <input style="font-size:24px;" name="q" placeholder="Search term" value="' . $q . '" >
  <input style="font-size:24px;" type="submit" value="Search">
</form>';
echo '</div>';

echo '<div style="position:relative">';
echo '<div style="width:600px;line-height:1.2em;">'; // border:1px solid rgb(128,128,128);
echo '<ol>';
foreach ($obj->groups as $group)
{
	echo '<li style="padding:5px;border-bottom:1px solid rgb(242,242,242);">';
	echo 'Cluster [' . count($group->rows) . '] ' . $group->by;
	
	
	echo '<ul>';
	foreach ($group->rows as $row)
	{
		echo '<li style="padding:5px;';
		
		if (count($group->rows) > 1)
		{
			echo 'border-right:6px solid orange;';
		}
		echo '">';

		$title = '';
		if (isset($row->doc->message->title))
		{
			if (is_array($row->doc->message->title))
			{
				$title = $row->doc->message->title[0];
			}
			else
			{
				$title = $row->doc->message->title;
			}
		}
		if (isset($row->doc->message->TI))
		{
			$title = $row->doc->message->TI[0];
		}
		echo '<div style="font-size:18px;font-weight:bold;">' . $title . '</div>';
		
		echo '<a href="' . $row->id . '" target="_new">' . $row->id . '</a>' . '<br />' ;
	
		
		/*
		echo '<pre>';
		print_r($row->doc->message);
		echo '</pre>';
		*/
		//echo $row->fields->default;
		
		echo '<div style="font-size:12px;color:green;">';
		foreach ($row->highlights->default as $highlight)
		{
			echo $highlight . '<br />';
		}
		echo '</div>';
		echo '</li>';
	}
	echo '</ul>';
	
	echo '</li>';
	
}
echo '</ol>';

/*
echo '</div>';
	echo '<div style="font-size:12px;position:absolute;top:0px;left:600px;width:300px;padding-left:10px;height:400px;border:1px solid orange;">';
	echo '<p style="padding:0px;margin:0px;" id="details">Stuff goes here</p>';
	echo '</div>';
	
	echo '</div>'; */
	
	echo
'	</body>
</html>';

?>