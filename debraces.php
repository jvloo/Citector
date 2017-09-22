<?php
$braces = '';
$debraces = '';
if($_POST)
{
  $braces = $_POST['braces'];

  $re = '/(?<=\{)(.*?)(?=\})/';
	preg_match($re, $braces, $in_braces);
print_r($in_braces);
  $re = '~\\b(' . implode('|', $in_braces) . ')\\b~';
  $debraces = preg_replace($re, '$0aaa', $braces);

}


 ?>
 <html>
 <head>
 </head>
 <body>
   <form method="POST" action="debraces.php">
     <textarea name="braces"><?php echo $braces; ?></textarea>
     <input type="submit" />
     <?php echo $debraces; ?>
   </form>
 </body>
 </html>
