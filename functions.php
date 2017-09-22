<?php
// Error reporting
error_reporting(0);

// Report all errors in local server
if($_SERVER['HTTP_HOST'] == 'localhost')
{
  error_reporting(E_ALL);
}

// Define base URL
function base_url($file = '')
{
  $protocol = ( strtolower( substr($_SERVER["SERVER_PROTOCOL"], 0, 5) ) == 'https://') ? 'https://' : 'http://';
  $host_name = $_SERVER['HTTP_HOST'];
  $path_info = pathinfo($_SERVER['PHP_SELF']);

  return $protocol . $host_name . $path_info['dirname'] . '/' . $file;
}

function usage_stat()
{
  // Initiate usage statistics.
  $stat_file = 'stat.txt';

  if( ! file_exists($stat_file) )
  {
  	$handle = fopen($stat_file, 'w');
  	fwrite($handle, '0');

  	fclose($handle);
  }

  $handle = fopen($stat_file, 'r');
  $stat = fread($handle, filesize($stat_file));

  // Increment usage statistics.
  $stat = $stat + 1;

  $handle = fopen($stat_file, 'w+');
  fwrite($handle, $stat);

  fclose($handle);
}
