<?php
function fetch_thread($thread, $vmid = 0)
{
  global $user, $indexes;

  $index = find_msg_index($thread['mid']);
  $sql = "select mid, tid, pid, aid, state, (UNIX_TIMESTAMP(date) - $user->tzoff) as unixtime, subject, flags, name, email, views from f_messages$index where tid = '" . $thread['tid'] . "' order by mid";
  $result = mysql_query($sql) or sql_error($sql);
  while ($message = mysql_fetch_array($result)) {
    $message['date'] = strftime("%Y-%m-%d %H:%M:%S", $message['unixtime']);
    $message['pmid'] = $message['pid'];
    $messages[] = $message;
  }

  $index++;
  if (isset($indexes[$index])) {
    $sql = "select mid, tid, pid, aid, state, (UNIX_TIMESTAMP(date) - $user->tzoff) as unixtime, subject, flags, name, email, views from f_messages$index where tid = '" . $thread['tid'] . "' order by mid";
    $result = mysql_query($sql) or sql_error($sql);
    while ($message = mysql_fetch_array($result)) {
      $message['date'] = strftime("%Y-%m-%d %H:%M:%S", $message['unixtime']);
      $message['pmid'] = $message['pid'];
      $messages[] = $message;
    }
  }

  /* Filter out moderated or deleted messages, if necessary */
  reset($messages);
  while (list($key, $msg) = each($messages)) {
    $tree[$msg['mid']][] = $key;
    $tree[$msg['pmid']][] = $key;
  }

  if ($vmid) {
    /* Walk down from the viewed message to the root to find the path */
    $pmid = $vmid;
    do {
      $path[$pmid] = true;
      $key = reset($tree[$pmid]);
      $pmid = $messages[$key]['pmid'];
    } while ($pmid);

    $messages = filter_messages($messages, $tree, reset($tree), $path);
  } else
    $messages = filter_messages($messages, $tree, reset($tree));

  return array($messages, $tree);
}
?>