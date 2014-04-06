<?php

$topics = array();
// TODO: it'll be good to cache rows by TTL
		$sql = 
"SELECT 
	f.id AS fid, f.forum_name, 
	t.id AS tid, t.subject, t.last_post,
	p.id, p.poster, p.posted, p.poster_id, p.message, p.hide_smilies
FROM 
	{$db->prefix}topics AS t INNER JOIN
	{$db->prefix}posts AS p ON p.id=t.last_post_id INNER JOIN
	{$db->prefix}forums AS f ON f.id=t.forum_id LEFT JOIN 
	{$db->prefix}forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id={$pun_user['group_id']})
WHERE 
	(fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL
ORDER BY
	t.last_post DESC
LIMIT 25";
$result = $db->query($sql);
while ($cur_topic = $db->fetch_assoc($result)) {
    $topics[] = $cur_topic;
}

?>
        <h2><span><?php echo $lang_common['Topic searches'] ?></span></h2>
		<div class="box">
			<div class="inbox">
				<ul>
<?php 
	foreach ($topics as $cur_topic): 
//        die(var_export($cur_topic, true));
		$post_ref = $pun_config['o_base_url'] . '/viewtopic.php?pid=' . $cur_topic['id'] . '#p' . $cur_topic['id'];
		$tid = $cur_topic['tid'];
		$fid = $cur_topic['fid'];
		$item_class = '';
		if (!$pun_user['is_guest'] && 
		    $cur_topic['last_post'] > $pun_user['last_visit'] && 
		   (!isset($tracked_topics['topics'][$tid]) || $tracked_topics['topics'][$tid] < $cur_topic['last_post']) && 
		   (!isset($tracked_topics['forums'][$fid]) || $tracked_topics['forums'][$fid] < $cur_topic['last_post'])) {
			$item_class = ' class="isactive"';
		}
			
?>
				<li<?php echo $item_class ?>><div class="item">
					<?php echo format_time($cur_topic['posted'], false) . ' ' . $lang_common['by'] ?>
					<strong><?php echo pun_htmlspecialchars($cur_topic['poster']) ?></strong>:<br/>
					<a class="inline" href="<?php echo $post_ref ?>"><?php echo pun_htmlspecialchars($cur_topic['subject']) ?></a>
				</div></li>
<?php 

	endforeach; 

?>
				</ul>
			</div>
		</div>
