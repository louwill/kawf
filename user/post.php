<?php

$user->req();

/* Check the data to make sure they entered stuff */
if (!isset($postcookie) || !isset($forum)) {
  /* Hmm, how did this happen? Redirect them back to the main page */
  Header("Location: http://$SERVER_NAME$SCRIPT_NAME/");
  exit;
}

include_once("textwrap.inc");
include_once("strip.inc");

$tpl->set_file(array(
  "header" => "header.tpl",
  "footer" => "footer.tpl",
  "post" => "post.tpl",
  "message" => "message.tpl",
  "forum_header" => "forum/" . $forum['shortname'] . ".tpl",
));

$tpl->set_block("post", "preview");
$tpl->set_block("post", "form");
$tpl->set_block("post", "accept");

$tpl->set_block("message", "forum_admin");
$tpl->set_block("message", "parent");
$tpl->set_block("message", "changes");

$tpl->set_var(array(
  "forum_admin" => "",
  "parent" => "",
  "changes" => "",
));

$tpl->parse("FORUM_HEADER", "forum_header");
$tpl->parse("HEADER", "header");
$tpl->parse("FOOTER", "footer");

$urlroot = "/ads";
/* We get our money from ads, make sure it's there */
include_once("ads.inc");

$ad = ads_view("a4.org," . $forum['shortname'], "_top");
$tpl->set_var("AD", $ad);

function stripcrap($string)
{
  global $no_tags;

  $string = striptag($string, $no_tags);
  $string = stripspaces($string);
  $string = ereg_replace("<", "&lt;", $string);
  $string = ereg_replace(">", "&gt;", $string);

  return $string;
}

function demoronize($string)
{
  /* Remove any and all non-ISO Microsoft extensions */
  $string = preg_replace("/\x82/", ",", $string);
  $string = preg_replace("/\x83/", "<em>f</em>", $string);
  $string = preg_replace("/\x84/", ",,", $string);
  $string = preg_replace("/\x85/", "...", $string);

  $string = preg_replace("/\x88/", "^", $string);
  $string = preg_replace("/\x89/", " �/��", $string);

  $string = preg_replace("/\x8B/", "<", $string);
  $string = preg_replace("/\x8C/", "Oe", $string);

  $string = preg_replace("/\x91/", "`", $string);
  $string = preg_replace("/\x92/", "'", $string);
  $string = preg_replace("/\x93/", "\"", $string);
  $string = preg_replace("/\x94/", "\"", $string);

  $string = preg_replace("/\x95/", "*", $string);
  $string = preg_replace("/\x96/", "-", $string);
  $string = preg_replace("/\x97/", "--", $string);
  $string = preg_replace("/\x98/", "<sup>~</sup>", $string);
  $string = preg_replace("/\x99/", "<sup>TM</sup>", $string);

  $string = preg_replace("/\x9B/", ">", $string);
  $string = preg_replace("/\x9C/", "oe", $string);

  return $string;
}

/* Strip any tags from the data */
$message = striptag($message, $standard_tags);
$message = stripspaces($message);
$message = demoronize($message);

$subject = stripcrap($subject);
/*
$subject = striptag($subject, $subject_tags);
*/
$subject = stripspaces($subject);
$subject = demoronize($subject);

/* Sanitize the strings */
$name = stripcrap($user->name);
if (isset($ExposeEmail))
  $email = stripcrap($user->email);
else
  $email = "";

$url = stripcrap($url);
$urltext = stripcrap($urltext);
$imageurl = stripcrap($imageurl);

if (isset($pid)) {
  $index = find_msg_index($pid);
  if ($index >= 0) {
    $sql = "select * from f_messages$index where mid = '" . addslashes($pid) . "'";
    $result = mysql_query($sql) or sql_error($sql);

    if (mysql_num_rows($result))
      $parent = mysql_fetch_array($result);
  }
}

if (empty($subject)) {
  /* Subject is required */
  $error .= "Subject is required!<br>\n";
} elseif (isset($parent) && $subject == "Re: " . $parent['subject'] && empty($message) && empty($url)) {
  $error .= "No change to subject or message, is this what you wanted?<br>\n";
} elseif (strlen($subject) > 100) {
  /* Subject is too long */
  $error .= "Subject line too long! Truncated to 100 characters<br>\n";
  $subject = substr($subject, 0, 100);
}

$url = stripspaces($url);
$imageurl = stripspaces($imageurl);

$url = ereg_replace(" ", "%20", $url);
$imageurl = ereg_replace(" ", "%20", $imageurl);

if (!empty($url) && !eregi("^[a-z]+://", $url))
  $url = "http://$url";

if (!empty($imageurl) && !eregi("^[a-z]+://", $imageurl))
  $imageurl = "http://$imageurl";

if (!empty($imageurl) && !isset($imgpreview))
  $preview = 1;

if ((isset($error) || isset($preview)) && (!empty($imageurl))) {
  echo "<font face=\"Verdana, Arial, Geneva\" color=\"#ff0000\"><i><b>Picture Verification:</b> If you see your picture below then please scroll down and hit Post Message to complete your posting. If no picture appears then your link was set incorrectly or your image is not valid a JPG or GIF file. Correct the image type or URL link to the picture in the box below and hit Preview Message to re-verify that your picture will be visible.</i></font><br>\n";
  $imgpreview = 1;
}

if (isset($ExposeEmail)) {
  /* Lame spamification */
  $_email = preg_replace("/@/", "&#" . ord('@') . ";", $user->email);
  $msg_nameemail = "<a href=\"mailto:" . $_email . "\">" . $user->name . "</a>";
} else
  $msg_nameemail = $user->name;

if (!empty($imageurl))
  $msg_message = "<center><img src=\"$imageurl\"></center><p>";
else
  $msg_message = "";

$msg_message .= preg_replace("/\n/", "<br>\n", $message);

if (!empty($url)) {
  if (!empty($urltext))
    $msg_message .= "<ul><li><a href=\"" . $url . "\" target=\"_top\">" . $urltext . "</a></ul>\n";
   else
    $msg_message .= "<ul><li><a href=\"" . $url . "\" target=\"_top\">" . $url . "</a></ul>\n";
}

if (!empty($user->signature)) {
  $signature = preg_replace("/\n/", "<br>\n", $user->signature);
  $msg_message .= "<p>" . $signature . "\n";
}

if (!isset($preview))
  $tpl->set_var("preview", "");

$date = date("Y-m-d H:i:s");

$tpl->set_var(array(
  "MSG_MESSAGE" => $msg_message,
  "MSG_NAMEEMAIL" => $msg_nameemail,
  "MSG_SUBJECT" => $subject,
  "MSG_DATE" => $date,
));

if (isset($error) || isset($preview)) {
  $action = "post";

  include_once("post.inc");

  $tpl->set_var("accept", "");
} else {
  $flags[] = "NewStyle";

  if (empty($message))
    $flags[] = "NoText";

  if (!empty($url) || eregi("<[[:space:]]*a[[:space:]]+href", $message))
    $flags[] = "Link";

  if (!empty($imageurl) || eregi("<[[:space:]]*img[[:space:]]+src", $message))
    $flags[] = "Picture";

  $flagset = implode(",", $flags);

  if (!empty($imageurl))
    $message = "<center><img src=\"$imageurl\"></center><p>" . $message;

  /* Add it into the database */
  /* Check to make sure this isn't a duplicate */
  $sql = "insert into f_dupposts ( cookie, fid, tstamp ) values ('" . addslashes($postcookie) . "', " . $forum['fid'] . ", NOW() )";
  $result = mysql_query($sql);

  if (!$result) {
    if (mysql_errno() != 1062)
      sql_error($sql);

    $mid = sql_query1("select mid from f_dupposts where cookie = '" . addslashes($postcookie) . "'");
  } else {
    /* Grab a new mid, this should work reliably */
    do {
      $sql = "select max(id) + 1 from f_unique where fid = " . $forum['fid'] . " and type = 'Message'";
      $result = mysql_query($sql) or sql_error($sql);

      list ($mid) = mysql_fetch_row($result);

      $sql = "insert into f_unique ( fid, type, id ) values ( " . $forum['fid'] . ", 'Message', $mid )";
      $result = mysql_query($sql);
    } while (!$result && mysql_errno() == 1062);

    if (!$result)
      sql_error($sql);

    $newmessage = 1;

    sql_query("update f_dupposts set mid = $mid where cookie = '" . addslashes($postcookie) . "'");
  }

  /* Add the message to the last index */
  $index = end($indexes);

  $mtable = "f_messages" . $index['iid'];
  $ttable = "f_threads" . $index['iid'];

  if (!isset($newmessage))
    $sql = "update $mtable set " .
	"name = '" . addslashes($name) . "', " .
	"email = '" . addslashes($email) . "', " .
	"date = NOW(), " .
	"ip = '$REMOTE_ADDR', " .
	"flags = '$flagset', " .
	"subject = '" . addslashes($subject) . "', " .
	"message = '" . addslashes($message) . "', " .
	"url = '" . addslashes($url) . "', " .
	"urltext = '" . addslashes($urltext) . "' " .
	"where mid = '" . addslashes($mid) . "'";
  else
    $sql = "insert into $mtable " .
	"( mid, aid, pid, tid, name, email, date, ip, flags, subject, message, url, urltext ) values ( '" . addslashes($mid) . "', '".addslashes($user->aid)."', '".addslashes($pid)."', '".addslashes($tid)."', '".addslashes($name)."', '".addslashes($email)."', NOW(), '$REMOTE_ADDR', '$flagset', '".addslashes($subject)."', '".addslashes($message)."', '".addslashes($url)."', '".addslashes($urltext)."');";

  $result = mysql_query($sql) or sql_error($sql);

  if (isset($newmessage)) {
    if (!$pid) {
      /* Grab a new tid, this should work reliably */
      do {
        $sql = "select max(id) + 1 from f_unique where fid = " . $forum['fid'] . " and type = 'Thread'";
        $result = mysql_query($sql) or sql_error($sql);

        list ($tid) = mysql_fetch_row($result);

        $sql = "insert into f_unique ( fid, type, id ) values ( " . $forum['fid'] . ", 'Thread', $mid )";
        $result = mysql_query($sql);
      } while (!$result && mysql_errno() == 1062);

      if (!$result)
        sql_error($sql);

      $sql = "update $mtable set tid = $tid where mid = $mid";
      mysql_query($sql) or sql_error($sql);

      $sql = "insert into $ttable ( tid, mid ) values ( $tid, $mid )";
      mysql_query($sql) or sql_error($sql);

      $sql = "update f_indexes set maxtid = $tid where iid = " . $index['iid'] . " and maxtid < $tid";
      mysql_query($sql) or sql_error($sql);
    } else {
      $sql = "update $ttable set replies = replies + 1 where tid = '" . addslashes($tid) . "'";
      mysql_query($sql) or sql_error($sql);
    }

    $sql = "update f_indexes set maxmid = $mid where iid = " . $index['iid'] . " and maxmid < $mid";
    mysql_query($sql) or sql_error($sql);

    if (!$pid) {
      $sql = "update f_indexes set active = active + 1 where iid = " . $index['iid'];
      mysql_query($sql) or sql_error($sql);
    }

    $sql = "update u_forums set posts = posts + 1 where aid = " . $user->aid;
    mysql_query($sql);
  } else
    echo "<font color=#ff0000>Duplicate message detected, overwriting</font>";

  $sql = "insert into f_updates ( fid, mid ) values ( " . $forum['fid'] . ", '" . addslashes($mid) . "' )";
  mysql_query($sql);

  if (!empty($TrackThread)) {
    $options = "";

    if (isset($EmailFollowup))
      $options = "SendEmail";

    $sql = "select * from f_tracking where fid = " . $forum['fid'] . " and aid = '" . $user->aid . "' and tid = '" . addslashes($tid) . "'";
    $result = mysql_query($sql) or sql_error($sql);

    if (!mysql_num_rows($result)) {
      $sql = "insert into f_tracking ( fid, tid, aid, options ) values ( " . $forum['fid'] . ", '" . addslashes($tid) . "', '" . addslashes($user->aid) . "', '$options' )";
      mysql_query($sql) or sql_error($sql);
    }
  }

  include_once("mailfrom.inc");

  $sql = "select * from f_tracking where fid = " . $forum['fid'] . " and tid = '" . addslashes($tid) . "' and options = 'SendEmail' and aid != " . $user->aid;
  $result = mysql_query($sql) or sql_error($sql);

  if (mysql_num_rows($result) > 0) {
#    $index = find_thread_index($tid);
    $index = $index['iid'];
    $sql = "select * from f_threads$index where tid = '" . addslashes($tid) . "'";
    $res2 = mysql_query($sql) or sql_error($sql);

    $thread = mysql_fetch_array($res2);

    $index = find_msg_index($thread['mid']);
    $sql = "select subject from f_messages$index where mid = " . $thread['mid'];
    $res2 = mysql_query($sql) or sql_error($sql);

    list($t_subject) = mysql_fetch_row($res2);

    $e_message = "Subject: Followup to thread '$t_subject'\n" .
	"From: accounts@audiworld.com\n" .
	"X-Mailer: PHP/" . phpversion() . "\n\n" .

	$user->name . " had posted a followup to a thread you are " .
	"tracking. You can read the message by going to " .
	"http://$_url/" . $forum['shortname'] . "/msgs/$mid.phtml\n\n" .

	"The message that was just posted was:\n\n" .

	"Subject: $subject\n\n" .

	substr($message, 0, 1024);

    if (strlen($message) > 1024) {
      $bytes = strlen($message) - 1024;
      $plural = ($bytes == 1) ? '' : 's';
      $e_message .= "...\n\nMessage continues for another $bytes byte$plural\n";
    }

    $foo = strlen($e_message);
    $e_message = textwrap($e_message, 78, "\n");

    $e_message .= "\n--\naudiworld.com\n";

    while ($track = mysql_fetch_array($result)) {
      $sql = "select email from u_users where aid = " . $track['aid'];
      $res2 = mysql_query($sql) or sql_error($sql);

      if (!mysql_num_rows($res2))
        continue;

      list($email) = mysql_fetch_row($res2);

      mailfrom("followup-" . $track['aid'] . "@bounce.audiworld.com", $email,
	"To: $email\n" . $e_message);
    }
  }

  $tpl->set_var(array(
    "FORUM_SHORTNAME" => $forum['shortname'],
    "MSG_MID" => $mid,
    "form" => "",
  ));
}

$tpl->parse("PREVIEW", "message");
$tpl->pparse("CONTENT", "post");
?>
