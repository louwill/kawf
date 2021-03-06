<?php

$user->req("ForumAdmin");

/* If submit is set, shove the data into the database (well, after some */
/* error checking) */
if (isset($_POST['submit'])) {
  if(!is_valid_integer($_POST['fid']))
      err_not_found("Invalid fid");
  $fid=$_POST['fid'];
  $name=$_POST['name'];
  $shortname=$_POST['shortname'];

  if (isset($_POST['read']))
    $options[] = "Read";
  if (isset($_POST['postthread']))
    $options[] = "PostThread";
  if (isset($_POST['postreply']))
    $options[] = "PostReply";
  if (isset($_POST['postedit']))
    $options[] = "PostEdit";
  if (isset($_POST['offtopic']))
    $options[] = "OffTopic";
  if (isset($_POST['searchable']))
    $options[] = "Searchable";
  if (isset($_POST['logintoread']))
    $options[] = "LoginToRead";
  if (isset($_POST['externallysearchable']))
    $options[] = "ExternallySearchable";

  if (isset($options))
    $options = implode(",", $options);
  else
    $options = "";

  db_exec("replace into f_forums " .
		"( fid, name, shortname, options ) " .
		"values ( ?, ?, ?, ?)",
		array($fid, $name, $shortname, $options));

  Header("Location: index.phtml?message=" . urlencode("Forum Modified"));
  exit;
}

/* If we find an ID, means that we're in update mode */
if (!is_valid_integer($_GET['fid'])) {
  page_header("Modify forum");
#  page_show_nav("1.2");
  ads_die("", "No forum ID specified (fid)");
}

$forum = db_query_first("select * from f_forums,f_indexes where f_forums.fid=f_indexes.fid and f_forums.fid = ?", array($_GET['fid']));
$options = explode(",", $forum['options']);

foreach ($options as $value)
  $options[$value] = true;

page_header("Modify '" . $forum['name'] . "' fid=".$forum['fid']);
#page_show_nav("1.2");
?>

<form method="post" action="<?php echo basename($_SERVER['PHP_SELF']);?>">
<input type="hidden" name="fid" value="<?php echo $forum['fid'];?>">


<table>
 <tr>
  <td>
   <table>
    <tr>
     <td>Long Name:</td><td><input type="text" name="name" value="<?php echo $forum['name']; ?>"></td>
    </tr>
    <tr>
     <td>Short Name:</td><td><input type="text" name="shortname" value="<?php echo $forum['shortname']; ?>"></td>
    </tr>
    <tr><td>Active:</td><td><?php echo $forum['active']?></td></tr>
    <tr><td>Deleted:</td><td><?php echo $forum['deleted']?></td></tr>
    <tr><td>Offtopic:</td><td><?php echo $forum['offtopic']?></td></tr>
    <tr><td>Moderated:</td><td><?php echo $forum['moderated']?></td></tr>
   </table>
  </td>
  <td>
   <table>
    <tr>
     <td>Read Messages:</td>
     <td><input type="checkbox" name="read"<?php if (isset($options['Read'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Posting new threads:</td>
     <td><input type="checkbox" name="postthread"<?php if (isset($options['PostThread'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Posting new replies:</td>
     <td><input type="checkbox" name="postreply"<?php if (isset($options['PostReply'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Edit Posts:</td>
     <td valign="top"><input type="checkbox" name="postedit"<?php if (isset($options['PostEdit'])) echo " checked"; ?>><small>(includes deleting)</small></td>
    </tr>
    <tr>
     <td>Off-Topic Posts:</td>
     <td valign="top"><input type="checkbox" name="offtopic"<?php if (isset($options['OffTopic'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Searchable:</td>
     <td valign="top"><input type="checkbox" name="searchable"<?php if (isset($options['Searchable'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Require login to read:</td>
     <td valign="top"><input type="checkbox" name="logintoread"<?php if (isset($options['LoginToRead'])) echo " checked"; ?>></td>
    </tr>
    <tr>
     <td>Externally searchable robots meta tag:</td>
     <td valign="top"><input type="checkbox" name="externallysearchable"<?php if (isset($options['ExternallySearchable'])) echo " checked"; ?>></td>
    </tr>
   </table>
  </td>
 </tr>
</table>

<center><input type="submit" name="submit" value="Update"></center>
</form>


<?php
page_footer();
?>
