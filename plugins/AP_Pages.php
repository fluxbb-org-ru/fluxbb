<?php

if (!defined('PUN'))
    exit;

define('PUN_PLUGIN_LOADED', 1);

// Load the page management functions and language file for it
require PUN_ROOT.'include/pages.php';
require PUN_ROOT.'lang/'.$pun_user['language'].'/pages.php';

$self_url = 'admin_loader.php?plugin=' . basename(__FILE__);

if (isset($_POST['save_page'])) {

    $alias = isset($_POST['alias']) ? ('/'.ltrim($_POST['alias'], '/')) : '';
    $uri = isset($_POST['uri']) ? $_POST['uri'] : '';
    $template = isset($_POST['template']) ? $_POST['template'] : '';

    // If it is forum-inside URL, leave significant part only
    $uri = Pages::withoutPrefix($uri);

    if (strlen($alias) == 0 || strlen($uri) == 0) {
        $error = 'Not all required fields are filled';
    } else {
        $alias = $db->escape($alias);
        $uri = $db->escape($uri);
        $template = $db->escape($template);
        $now = time();
        // Insert or update record
        $db->query("UPDATE `{$db_prefix}pages` SET `uri`='{$uri}', `template`='{$template}', `editor_id`={$pun_user['id']}, `edited`={$now} WHERE `alias`='{$alias}'") or error('Unable to update pages', __FILE__, __LINE__, $db->error());
        if (!$db->affected_rows())
            $db->query("INSERT INTO `{$db_prefix}pages`(`alias`, `uri`, `template`, `editor_id`, `edited`) VALUES('{$alias}', '{$uri}', '{$template}', {$pun_user['id']}, {$now})") or error('Unable to insert into pages', __FILE__, __LINE__, $db->error());
        redirect($self_url, $lang_pages['Save redirect']);
    }

} elseif (isset($_POST['del_page']) && isset($_POST['del_page_comply'])) {

    $alias = isset($_POST['alias']) ? $_POST['alias'] : '';
    $db->query("DELETE FROM `{$db_prefix}pages` WHERE `alias`='{$alias}'") or error('Unable to delete from pages', __FILE__, __LINE__, $db->error());
    redirect($self_url, $lang_pages['Delete redirect']);

} else {

    $alias = isset($_GET['alias']) ? $_GET['alias'] : '';
    $uri = isset($_GET['uri']) ? $_GET['uri'] : '';
    $template = isset($_GET['template']) ? $_GET['template'] : '';

    // Try to complete priperties from existing record
    if ($alias != '' && $uri == '') {
        $result = $db->query("SELECT p.`uri`, p.`template`, p.`editor_id`, u.`username` AS `editor`, p.`edited` FROM `{$db_prefix}pages` AS p LEFT JOIN `{$db_prefix}users` AS u ON u.`id`=p.`editor_id` WHERE p.`alias`='" . $db->escape($alias) . "'");
        if ($db->num_rows($result))
            extract($db->fetch_assoc($result));
    } elseif ($alias == '' && $uri != '') {
        $result = $db->query("SELECT p.`alias`, p.`template`, p.`editor_id`, u.`username` AS `editor`, p.`edited` FROM `{$db_prefix}pages` AS p LEFT JOIN `{$db_prefix}users` AS u ON u.`id`=p.`editor_id` WHERE p.`uri`='" . $db->escape($uri) . "'");
        if ($db->num_rows($result))
            extract($db->fetch_assoc($result));
    }
    // Collect existing pages
    $result = $db->query("SELECT p.*, u.`username` AS `editor` FROM `{$db_prefix}pages` AS p LEFT JOIN `{$db_prefix}users` AS u ON u.`id`=p.`editor_id` ORDER BY p.`alias` ASC");
    $rows = $tids = $pids = array();
    while ($row = $db->fetch_assoc($result)) {
        $page_id = $row['id'];
        $rows[$page_id] = $row;
        // Is post or topic?
        if (preg_match('#^viewtopic\.php\?pid=(\d+)#', $row['uri'], $matches)) {
            $pids[$page_id] = intval($matches[1]);
        } elseif (preg_match('#^viewtopic\.php\?id=(\d+)#', $row['uri'], $matches)) {
            $tids[$page_id] = intval($matches[1]);
        }
    }
    
    // Retrieve subjects when possible
    if (!empty($tids)) {
        $result = $db->query("SELECT `t`.`id`, `t`.`subject` FROM `{$db_prefix}topics` AS `t` WHERE `t`.`id` IN(" . implode(',', $tids) . ")");
        while ($row = $db->fetch_assoc($result)) {
            $rows[array_search($row['id'], $tids)]['subject'] = $row['subject'];
        }
    }
    if (!empty($pids)) {
        $result = $db->query("SELECT `p`.`id`, `t`.`subject` FROM `{$db_prefix}posts` AS `p` INNER JOIN `{$db_prefix}topics` AS `t` ON `p`.`topic_id`=`t`.`id` WHERE `p`.`id` IN(" . implode(',', $pids) . ")");
        while ($row = $db->fetch_assoc($result)) {
            $rows[array_search($row['id'], $pids)]['subject'] = $row['subject'];
        }
    }

    $d = dir(PUN_ROOT . 'include/template');
    $templates = array();
    while (($entry = $d->read()) !== FALSE) {
        if (substr($entry, -4) == '.tpl')
            $templates[] = $entry;
    }
    $d->close();
    sort($templates);
    array_unshift($templates, '');

}

// Display the admin navigation menu
generate_admin_menu($plugin);

if (isset($_POST['del_page']) || isset($_GET['del_page'])) {

?>
    <div class="blockform">
        <h2><span><?php echo $lang_pages['Pages plugin'] ?></span></h2>
        <div class="box">
            <form method="post" action="<?php echo $self_url ?>">
                <input type="hidden" name="del_page" value="1" />
                <input type="hidden" name="alias" value="<?php echo $alias ?>" />
                <div class="inform">
                    <fieldset>
                        <legend><?php echo $lang_pages['Confirm delete'] ?></legend>
                        <div class="infldset">
                            <p><?php printf($lang_pages['Delete page text'], htmlspecialchars($alias)) ?></p>
                        </div>
                    </fieldset>
                </div>
                <p class="buttons"><input type="submit" name="del_page_comply" value="<?php echo $lang_pages['Delete'] ?>" /><a href="javascript:history.go(-1)"><?php echo $lang_admin_common['Go back'] ?></a></p>
            </form>
        </div>
    </div>

<?php

} else {

?>

    <div class="plugin blockform">
        <h2><span><?php echo $lang_pages['Pages plugin'] ?></span></h2>
        <div class="box">
            <div class="inbox">
                <?php echo $lang_pages['Plugin intro'] ?>
            </div>
        </div>

        <h2 class="block2"><span><?php echo $lang_pages['Add modify'] ?></span></h2>
        <div class="box">
            <form id="example" method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
                <div class="inform">
                    <fieldset>
                        <legend><?php echo $lang_pages['Page properties'] ?></legend>
                        <div class="infldset">
                        <table class="aligntop" cellspacing="0">
                            <tr>
                                <th scope="row"><?php echo $lang_pages['Alias'] ?></th>
                                <td>
                                    <input type="text" name="alias" size="50" tabindex="2" value="<?php echo $alias ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_pages['URI'] ?></th>
                                <td>
                                    <input type="text" name="uri" size="50" tabindex="3" value="<?php echo $uri ?>" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php echo $lang_pages['Template'] ?></th>
                                <td>
                                    <select name="template" tabindex="4">
<?php foreach ($templates as $entry): ?>
                                    <option value="<?php echo $entry ?>"<?php if ($entry == $template) echo ' selected="selected"' ?>><?php echo htmlspecialchars($entry) ?></option>
<?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
<?php

    if (isset($editor) && isset($edited)) {

?>
                            <tr>
                                <th scope="row"><?php echo $lang_pages['Last edit'] ?></th>
                                <td>
                                    <?php echo htmlspecialchars($editor) . ', ' . format_time($edited) ?>
                                </td>
                            </tr>
<?php

    }

?>
                        </table>
                        </div>
                    </fieldset>
                </div>
                <p class="buttons">
                    <input type="submit" value="<?php echo $lang_pages['Save'] ?>" name="save_page">
                    <input type="submit" value="<?php echo $lang_pages['Delete'] ?>" name="del_page">
                </p>
            </form>
        </div>

        <h2 class="block2"><span><?php echo $lang_pages['Existing pages'] ?></span></h2>
        <div class="blocktable">
            <div class="inbox">
            <table class="aligntop">
            <thead>
                <tr>
                    <th class="tcl" scope="col"><?php echo $lang_pages['Alias and URI'] ?></th>
                    <th class="tc2" scope="col"><?php echo $lang_pages['Template'] ?></th>
                    <th class="tc3" scope="col"><?php echo $lang_pages['Action'] ?></th>
                    <th class="tcr" scope="col"><?php echo $lang_pages['Last edit'] ?></th>
                </tr>
            </thead>
            <tbody>
<?php
    if (!isset($base_url)) {
        $page_base_url = $pun_config['o_base_url'];
    }
    foreach ($rows as $row) {

?>
                <tr>
                    <td class="tcl">
<?php if (isset($row['subject'])): ?>
                        <strong><a href="<?php echo $page_base_url.$row['alias'] ?>"><?php echo htmlspecialchars($row['subject']) ?></a></strong><br />
<?php endif; ?>
                        <a href="<?php echo $page_base_url.$row['alias'] ?>"><?php echo $row['alias'] ?></a> &rarr;
                        <?php echo $row['uri'] ?>

                    </td>
                    <td class="tc2">
                        <?php echo $row['template'] ?>

                    </td>
                    <td class="tc3">
                        <a href="<?php echo $self_url . '&amp;alias=' . $row['alias'] ?>"><?php echo $lang_pages['Manage'] ?></a>
                        <a href="<?php echo $self_url . '&amp;alias=' . $row['alias'] . '&amp;del_page=1' ?>"><?php echo $lang_pages['Delete'] ?></a>
                    </td>
                    <td class="tcr">
                        <?php echo htmlspecialchars($row['editor']) . ' ' . format_time($row['edited']) ?>

                    </td>
                </tr>
<?php

    }

?>
            </tbody>
            </table>
            </div>
        </div>

    </div>

<?php

}