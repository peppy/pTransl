<?php

global $smarty, $conn;

if (!isset($conn))
	require_once("include.db.php");
	
if (isset($dbOnly))
	return;

require_once('smarty/libs/Smarty.class.php');

function linkenize($str)
{
     $str = trim($str);
     $str = sanitize_html_string($str);
     $str = preg_replace('=([^\s]*:\/\/)(www.)?([^<\s]{0,60})([0-9;&#]*)([^<\s]*)=','<a href="http://\\2\\3\\4\\5" target=\'_new\'>\\1\\2\\3\\4..</a>',$str);
     return $str;
}

function sanitize_html_string($string)
{
  $pattern[0] = '/\&([^#])/';
  $pattern[1] = '/</';
  $pattern[2] = "/>/";
  $pattern[3] = '/\n/';
  $pattern[4] = '/"/';
  $pattern[5] = "/'/";
  $pattern[6] = "/%/";
  $pattern[7] = '/\(/';
  $pattern[8] = '/\)/';
  $pattern[9] = '/\+/';
  $pattern[10] = '/-/';
  $replacement[0] = '&amp;\1';
  $replacement[1] = '&lt;';
  $replacement[2] = '&gt;';
  $replacement[3] = '<br>';
  $replacement[4] = '&quot;';
  $replacement[5] = '&#39;';
  $replacement[6] = '&#37;';
  $replacement[7] = '&#40;';
  $replacement[8] = '&#41;';
  $replacement[9] = '&#43;';
  $replacement[10] = '&#45;';
  return preg_replace($pattern, $replacement, $string);
}

// sanitize a string in prep for passing a single argument to system() (or similar)
function sanitize_system_string($string, $min='', $max='')
{
  $pattern = '/(;|\||`|>|<|&|^|"|'."\n|\r|'".'|{|}|[|]|)/i'; // no piping, passing possible environment variables ($),
                           // seperate commands, nested execution, file redirection,
                           // background processing, special commands (backspace, etc.), quotes
                           // newlines, or some other special characters
  $string = preg_replace($pattern, '', $string);
  //$string = '"'.preg_replace('/\$/', '\\\$', $string).'"'; //make sure this is only interpretted as ONE argument
  $len = strlen($string);
  if((($min != '') && ($len < $min)) || (($max != '') && ($len > $max)))
    return FALSE;
  return $string;
}

function xyago($datefrom,$dateto=-1,$longf=false)
{
    if($datefrom==0) { return "A long time ago"; }
    if($dateto==-1) { $dateto = time(); }

    $difference = $dateto - $datefrom;

    if($difference < 60)
      $interval = "s";
    elseif($difference >= 60 && $difference<60*60)
      $interval = "n";
    elseif($difference >= 60*60 && $difference<60*60*48)
      $interval = "h";
    elseif($difference >= 60*60*48 && $difference<60*60*24*7)
      $interval = "d";
    elseif($difference >= 60*60*24*7 && $difference < 60*60*24*30)
      $interval = "ww";
    elseif($difference >= 60*60*24*30 && $difference < 60*60*24*365)
      $interval = "m";
    elseif($difference >= 60*60*24*365)
      $interval = "y";

    switch($interval)
    {
       case "m":
       $months_difference = floor($difference / 60 / 60 / 24 / 29);
       while (mktime(date("H", $datefrom), date("i", $datefrom),
date("s", $datefrom), date("n", $datefrom)+($months_difference),
date("j", $dateto), date("Y", $datefrom)) < $dateto)
       {
          $months_difference++;
       }
       $datediff = $months_difference;

       // We need this in here because it is possible
       // to have an 'm' interval and a months
       // difference of 12 because we are using 29 days
       // in a month

       if($datediff==12)
       {
          $datediff--;
       }

       $res = ($datediff==1) ? "$datediff month ago" : "$datediff months ago";
       break;

       case "y":
       $datediff = floor($difference / 60 / 60 / 24 / 365);
       $res = ($datediff==1) ? "$datediff year ago" : "$datediff
years ago";
       break;

       case "d":
       $datediff = floor($difference / 60 / 60 / 24);
       $res = ($datediff==1) ? "$datediff day ago" : "$datediff
days ago";
       break;

       case "ww":
       $datediff = floor($difference / 60 / 60 / 24 / 7);
       $res = ($datediff==1) ? "$datediff week ago" : "$datediff weeks ago";
       break;

       case "h":
       $datediff = floor($difference / 60 / 60);
       $res = $datediff .($longf?" hours ago":"h");
       break;

       case "n":
       $datediff = floor($difference / 60);
       $res = $datediff . ($longf?" minutes ago":"m");
       break;

       case "s":
       $datediff = $difference;
       $res = $datediff .($longf?" seconds ago":"s");
       break;
    }
    return $res;
}

function nicedate($date)
{
    return date("d M Y \\a\\t H:i",strtotime($date));
}

function shorten($string, $length = 10)
{
    if (strlen($string) > $length)
        return substr($string,0,$length) . "...";
    else
        return $string;
}

function doPagination($currentPage, $recordsPerPage, $rowCount, $showInfo = false, $baseUrl)
{
	$first = (($currentPage - 1) * $recordsPerPage + 1);
	$last = min($rowCount,($currentPage * $recordsPerPage));
	$displayLimit = 10;
	
	if ($showInfo)	
		$pagination .= $rowCount > 0 ? "Displaying $first to $last of $rowCount results.<br/>" :
		               "No results found.  Please try generalising your search further!";
		
	$pageCount = ceil($rowCount/$recordsPerPage);
	if ($recordsPerPage < $rowCount) {
		if ($currentPage > 1)
			$pagination .= "<a href='$baseUrl&page=".($currentPage-1)."'>Prev</a> ";
		for ($i = 1; $i < $pageCount + 1; $i++)
		{
			if ($i == $currentPage)
				$pagination .= '<b>'.$i . '</b> ';
			else if ($i == 1 || (abs($i- $currentPage) < $displayLimit) || $i == $pageCount)
				$pagination .= "<a href='$baseUrl&page=$i'>$i</a> ";
			else if (abs($i- $currentPage) == $displayLimit)
				$pagination .= ' ... ';
		}
		if ($last != $rowCount)
			$pagination .= "<a href='$baseUrl&page=".($currentPage+1)."'>Next</a>";
	}
	
	return $pagination;
}


function do_offset($level){
    $offset = "";             // offset for subarry 
    for ($i=1; $i<$level;$i++){
    $offset = $offset . "<td></td>";
    }
    return $offset;
}

function show_array($array, $level, $sub){
    if (is_array($array) == 1){          // check if input is an array
       foreach($array as $key_val => $value) {
           $offset = "";
           if (is_array($value) == 1){   // array is multidimensional
			   $echo .= "<tr>";
			   $offset = do_offset($level);
			   $echo .= $offset . "<td>" . $key_val . "</td>";
			   $echo .= show_array($value, $level+1, 1);
			   }
			   else{                        // (sub)array is not multidim
			   if ($sub != 1){          // first entry for subarray
				   $echo .= "<tr nosub>";
				   $offset = do_offset($level);
			   }
			   $sub = 0;
			   $echo .= $offset . "<th main ".$sub." width=\"50\">" . $key_val . 
				   "</th><td>" . $value . "</td>"; 
			   $echo .= "</tr>\n";
           }
       } //foreach $array

	   return $echo;
    }  
    else{ // argument $array is not an array
        return;
    }
}

function html_show_array($array){
  $echo = "<table class='beatmapListing'>\n";
  $echo .= show_array($array, 1, 0);
  $echo .= "</table>\n";
  return $echo;
}

function getTableSuffix($mode)
{
	switch ($mode)
	{
		default:
			return "";
		case 1:
			return "_taiko";
		case 2:
			return "_fruits";
	}
}

function in_array_column($text, $column, $array)
{
    if (!empty($array) && is_array($array))
    {
        for ($i=0; $i < count($array); $i++)
        {
            if ($array[$i][$column]==$text || strcmp($array[$i][$column],$text)==0) return true;
        }
    }
    return false;
}

function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}

?>
