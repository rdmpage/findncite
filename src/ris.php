<?php

/**
 * @file ris.php
 *
 */

$debug = false;


//--------------------------------------------------------------------------------------------------
function process_ris_key($key, $value, &$obj)
{
	global $debug;
	
	switch ($key)
	{
			
		case 'T1':
			$value = preg_replace('/([^\s])\(/', '$1 (', $value);	
			$value = str_replace("\ü", "ü", $value);
			$value = str_replace("\ö", "ö", $value);

			$value = str_replace("“", "\"", $value);
			$value = str_replace("”", "\"", $value);
						
			$obj[$key][] = $value;
			break;
				
		// Handle cases where both pages SP and EP are in this field
		case 'SP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				$obj['SP'][] = $matches['spage'];
				$obj['EP'][] = $matches['epage'];
			}
			else
			{
				$obj['SP'][] = $value;
			}				
			break;

		case 'EP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				$obj['SP'][] = $matches['spage'];
				$obj['EP'][] = $matches['epage'];
			}
			else
			{
				$obj['EP'][] = $value;
			}				
			break;
			
		case 'PY': // used by Ingenta, and others
		case 'Y1':
		   $date = $value; 
		   		   
		   //2001 [2002]
		   if (preg_match("/^(?<year>[0-9]{4})\s+\[/", $date, $matches))
		   {                       
				   $obj['Y1'][] = $matches['year'];
		   }
		   
		   if (preg_match("/(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/(?<day>[0-9]{1,2})/", $date, $matches))
		   {                       
			   $obj['Y1'][] = $matches['year'];
			   $obj['PY'][] = sprintf("%d-%02d-%02d", $matches['year'], $matches['month'], $matches['day']);			   
		   }
		   

		   if (preg_match("/^(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/(\/)?$/", $date, $matches))
		   {                       
				   $obj['Y1'][] = $matches['year'];
		   }

		   if (preg_match("/^(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})$/", $date, $matches))
		   {                       
				   $obj['Y1'][] = $matches['year'];
		   }

		   if (preg_match("/[0-9]{4}\/\/\//", $date))
		   {                       
			   $year = trim(preg_replace("/\/\/\//", "", $date));
			   if ($year != '')
			   {
					$obj['Y1'][] = $year;
			   }
		   }

		   if (preg_match("/^[0-9]{4}$/", $date))
		   {                       
				  $obj['Y1'][] = $date;
		   }
		   
		   
		   if (preg_match("/^(?<year>[0-9]{4})\-[0-9]{2}\-[0-9]{2}$/", $date, $matches))
		   {
		   		$obj['Y1'][]= $matches['year'];
				$obj['PY'][] = $date;
		   }
		   
		   break;

		default:
			if ($value != '')
			{
				$obj[$key][] = $value;
			}
			break;
	}
}



//--------------------------------------------------------------------------------------------------
function import_ris($ris, $callback_func = '')
{
	global $debug;
	
	$volumes = array();
	
	$rows = explode("\n", $ris);
	
	$state = 1;	
		
	foreach ($rows as $r)
	{
		$parts = explode ("  - ", $r);
		
		$key = '';
		if (isset($parts[1]))
		{
			$key = trim($parts[0]);
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = array();
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
						
			// Cleaning...						
			if ($debug)
			{
				print_r($obj);
			}	
			
			if ($callback_func != '')
			{
				$callback_func($obj);
			}
			
		}
		
		if ($state == 1)
		{
			if (isset($value))
			{
				process_ris_key($key, $value, $obj);
			}
		}
	}
	
	
}


//--------------------------------------------------------------------------------------------------
// Use this function to handle very large RIS files
function import_ris_file($filename, $callback_func = '')
{
	global $debug;
	$debug = false;
	
	$file_handle = fopen($filename, "r");
			
	$state = 1;	
	
	while (!feof($file_handle)) 
	{
		$r = fgets($file_handle);
		$parts = explode ("  - ", $r);
		
		//print_r($parts);
		
		$key = '';
		if (isset($parts[1]))
		{
			$key = trim($parts[0]);
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = array();
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
			
			// generate an identifier, don't use DOI as we will get that record
			// from CrossRef
			
			$id = '';
			
			// URL
			if ($id == '')
			{
				if (isset($obj['UR']))
				{
					$id = $obj['UR'][0];
				}
			}
			
			// PDF
			if ($id == '')
			{
				if (isset($obj['L1']))
				{
					$id = $obj['L1'][0];
				}
			}
			
			// 
			if ($id == '')
			{
				foreach ($obj as $k => $v)
				{
					switch ($k)
					{
						case 'ID':
						case 'TI':
						case 'AU':
						case 'JO':
						case 'SN':
						case 'VL':
						case 'IS':
						case 'SP':
						case 'EP':
						case 'Y1':
							$id = join(" ", $v);
							break;
					}
				}
			}
			
			$obj['_id'] = $id;
									
			// Cleaning...						
			if ($debug)
			{
				print_r($obj);
			}	
			
			if ($callback_func != '')
			{
				$callback_func($obj);
			}
			
		}
		
		if ($state == 1)
		{
			if (isset($value))
			{
				process_ris_key($key, $value, $obj);
			}
		}
	}
	
	
}


?>