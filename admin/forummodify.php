<?php

$user->req("ForumAdmin");

/* If submit is set, shove the data into the database (well, after some */
/* error checking) */
if (isset($submit)) {
  if (isset($read))
    $options[] = "Read";
  if (isset($post))
    $options[] = "Post";

  if (isset($options))
    $options = implode(",", $options);
  else
    $options = "";

  sql_query("replace into f_forums " .
		"( fid, name, shortname, options ) " .
		"values " .
		"( $fid, " .
		" '" . addslashes($name) . "'," .
		" '" . addslashes($shortname) . "'," .
		" '" . addslashes($options) . "'" .
		")");

  Header("Location: index.phtml?message=" . urlencode("Forum Modified"));
  exit;
}  

/* If we find an ID, means that we're in update mode */
if (!isset($fid)) {
  page_header("Modify forum");
#  page_show_nav("1.2");
  ads_die("", "No forum ID specified (fid)");
}

$forum = sql_querya("select * from f_forums where fid = '" . addslashes($fid) . "'");
$options = explode(",", $forum['options']);

foreach ($options as $name => $value)
  $options[$value] = true;

page_header("Modify forum '" . $forum['name'] . "'");
#page_show_nav("1.2");
?>

<form method="post" action="<?php echo basename($PHP_SELF);?>">
<input type="hidden" name="fid" value="<?php echo $fid;?>">
<table>
 <tr>
  <td>fid:</td>
  <td><?php echo $forum['fid']; ?></td>
 </tr>
 <tr>
  <td>Long Name:</td>
  <td><input type="text" name="name" value="<?php echo $forum['name']; ?>"></td>
 </tr>
 <tr>
  <td>Short Name:</td>
  <td><input type="text" name="shortname" value="<?php echo $forum['shortname']; ?>"></td>
 </tr>
 <td>
  <td>Reading enabled:</td>
  <td><input type="checkbox" name="read"<?php if (isset($options['Read'])) echo " checked"; ?>></td>
 </tr>
 <td>
  <td>Posting enabled:</td>
  <td><input type="checkbox" name="post"<?php if (isset($options['Post'])) echo " checked"; ?>></td>
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" name="submit" value="Go"></td>
 </tr>
</table>
</form>

<?php
page_footer();
?>