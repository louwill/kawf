<?php

// some layout functions
function page_header($title)
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?echo $title;?></title>
<link rel=StyleSheet href="<? echo css_href("admin.css")?>" type="text/css">
</head>

<body>
<table class="outer">
  <tr>
    <td>
      <table class="inner">
        <tr>
          <th colspan="2" class="heading"><?echo $title;?></th>
        </tr>
        <tr>
          <td colspan="2">
<?php
}

function page_footer($back=true)
{
  global $user;
?>
         </td>
        </tr>
        <tr class="footing">
          <th>
<?php if (isset($user)) { ?>
	    <a href="logout.phtml?token=<? echo $user->token() ?>">Logout</a>
<?php } ?>
	  </th>
          <th class="right">
<?php if ($back) { ?>
	    <a href="/admin/">Back to admin page</a>
<?php } ?>
	  </th>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>
<?php
}

// Show a messgae
function page_show_message($message)
{
?>
            <table class="message">
              <tr><td><?echo $message;?></td></tr>
            </table>
<?php
}