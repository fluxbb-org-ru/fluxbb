<?php

require PUN_ROOT.'include/common.php';

// Load language file for pages
require PUN_ROOT.'lang/'.$pun_user['language'].'/pages.php';

$alias = $db->escape($page_uri);
$result = $db->query("SELECT a.* FROM {$db_prefix}pages AS a WHERE a.alias='{$alias}'");
$alias_data = $db->fetch_assoc($result);

if (empty($alias_data['uri']))
    fileNotFound();

// Is post? Read it.
if (preg_match('#^viewtopic\.php\?pid=(\d+)#', $alias_data['uri'], $matches)) {
    $pid = intval($matches[1]);
    $result = $db->query("SELECT t.subject, p.* FROM {$db_prefix}posts AS p INNER JOIN {$db_prefix}topics AS t ON p.topic_id=t.id WHERE p.id={$pid}");
    $post_data = $db->fetch_assoc($result);
    $p = 1;
    $num_pages = 1;

// Is topic? Read it's post.
} elseif (preg_match('#^viewtopic\.php\?id=(\d+)#', $alias_data['uri'], $matches)) {
    $tid = intval($matches[1]);
    $p = isset($_GET['p']) ? intval($_GET['p']) : 1;
    $start_from = $p - 1;
    if ($start_from == 0)
        $result = $db->query("SELECT `t`.`subject`, `t`.`num_replies`, `p`.* FROM `{$db_prefix}topics` AS `t` INNER JOIN `{$db_prefix}posts` AS `p` ON `p`.`id`=`t`.`first_post_id` WHERE `t`.`id`={$tid}");
    else
        $result = $db->query("SELECT `t`.`subject`, `t`.`num_replies`, `p`.* FROM `{$db_prefix}topics` AS `t` INNER JOIN `{$db_prefix}posts` AS `p` ON `p`.`topic_id`=`t`.`id` WHERE `t`.`id`={$tid} ORDER BY `p`.`id` ASC LIMIT {$start_from},1");
    $post_data = $db->fetch_assoc($result);
    $num_pages = $post_data['num_replies'] + 1;

// Otherwise just redirect to link.
} else {
    if (preg_match('#^https?://#', $alias_data['uri'], $matches))
        header('Location: ' . $alias_data['uri']);
    else
        header('Location: ' . $pun_config['o_base_url'] . '/' . $alias_data['uri']);
    exit;
}

if (empty($post_data['message'])) {
    fileNotFound();
}

// Generate paging links
$paging_links = '<span class="pages-label">'.$lang_common['Pages'].' </span>'.paginate($num_pages, $p, $pun_config['o_base_url'].$page_uri);

if (Pages::canManage()) {
    $post_link = "\t\t\t".'<p class="postlink conr">'
                 . '<a class="pagemanage" href="' .$pun_config['o_base_url'] . '/admin_loader.php?plugin=' . Pages::plugin() . '&alias=' . urlencode($page_uri) . '">' . $lang_pages['Manage'] . '</a> | '
                 . '<a class="pageedit" href="' .$pun_config['o_base_url'] . '/edit.php?id=' . $post_data['id'] . '">' . $lang_pages['Edit'].'</a>'
                 . "</p>\n";
} else {
    $post_link = '';
}

require PUN_ROOT.'include/parser.php';

$self_url = $_SERVER['REQUEST_URI'];
$subject = pun_htmlspecialchars($post_data['subject']);
$message = parse_message($post_data['message'], $post_data['hide_smilies']);
$tpl_file = $alias_data['template'];

$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $subject);
define('PUN_ALLOW_INDEX', 1);
define('PUN_ACTIVE_PAGE', 'index');
require PUN_ROOT.'header.php';

?>
<div class="linkst">
    <div class="inbox crumbsplus">
        <ul class="crumbs">
            <li><?php echo $subject ?></li>
        </ul>
        <div class="pagepost">
<?php if ($num_pages > 1): ?>           <p class="pagelink conl"><?php echo $paging_links ?></p><?php endif; ?>
<?php echo $post_link ?>
        </div>
    </div>
</div>

<div class="box">
    <div class="inbox">
        <div class="postmsg">
            <?php echo $message ?>
        </div>
    </div>
</div>

<div class="postlinksb">
    <div class="inbox crumbsplus">
        <div class="pagepost">
<?php if ($num_pages > 1): ?>           <p class="pagelink conl"><?php echo $paging_links ?></p><?php endif; ?>
        </div>
        <div class="clearer"></div>
    </div>
</div>

<?php

require PUN_ROOT.'footer.php';
