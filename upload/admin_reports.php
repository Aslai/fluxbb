<?php

/*---

	Copyright (C) 2008-2010 FluxBB.org
	based on code copyright (C) 2002-2005 Rickard Andersson
	License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher

---*/

// Tell header.php to use the admin template
define('PUN_ADMIN_CONSOLE', 1);

define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';
require PUN_ROOT.'include/common_admin.php';


if (!$pun_user['is_admmod'])
	message($lang_common['No permission']);

// Load the admin_reports.php language file
require PUN_ROOT.'lang/'.$admin_language.'/admin_reports.php';

// Zap a report
if (isset($_POST['zap_id']))
{
	confirm_referrer('admin_reports.php');

	$zap_id = intval(key($_POST['zap_id']));

	$result = $db->query('SELECT zapped FROM '.$db->prefix.'reports WHERE id='.$zap_id) or error('Unable to fetch report info', __FILE__, __LINE__, $db->error());
	$zapped = $db->result($result);

	if ($zapped == '')
		$db->query('UPDATE '.$db->prefix.'reports SET zapped='.time().', zapped_by='.$pun_user['id'].' WHERE id='.$zap_id) or error('Unable to zap report', __FILE__, __LINE__, $db->error());

	redirect('admin_reports.php', $lang_admin_reports['Report zapped redirect']);
}


$page_title = array(pun_htmlspecialchars($pun_config['o_board_title']), $lang_admin_common['Admin'], $lang_admin_common['Reports']);
define('PUN_ACTIVE_PAGE', 'admin');
require PUN_ROOT.'header.php';

generate_admin_menu('reports');

?>
	<div class="blockform">
		<h2><span><?php echo $lang_admin_reports['New reports head'] ?></span></h2>
		<div class="box">
			<form method="post" action="admin_reports.php?action=zap">
<?php

$result = $db->query('SELECT r.id, r.topic_id, r.forum_id, r.reported_by, r.created, r.message, p.id AS pid, t.subject, f.forum_name, u.username AS reporter FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id WHERE r.zapped IS NULL ORDER BY created DESC') or error('Unable to fetch report list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.pun_htmlspecialchars($cur_report['reporter']).'</a>' : $lang_admin_reports['Deleted user'];
		$forum = ($cur_report['forum_name'] != '') ? '<a href="viewforum.php?id='.$cur_report['forum_id'].'">'.pun_htmlspecialchars($cur_report['forum_name']).'</a>' : $lang_admin_reports['Deleted'];
		$topic = ($cur_report['subject'] != '') ? '<a href="viewtopic.php?id='.$cur_report['topic_id'].'">'.pun_htmlspecialchars($cur_report['subject']).'</a>' : $lang_admin_reports['Deleted'];
		$post = str_replace("\n", '<br />', pun_htmlspecialchars($cur_report['message']));
		$postid = ($cur_report['pid'] != '') ? '<a href="viewtopic.php?pid='.$cur_report['pid'].'#p'.$cur_report['pid'].'">Post #'.$cur_report['pid'].'</a>' : $lang_admin_reports['Deleted'];
		$report_location = array($forum, $topic, $postid);

?>
				<div class="inform">
					<fieldset>
						<legend><?php printf($lang_admin_reports['Report subhead'], format_time($cur_report['created'])) ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>

									<th scope="row"><?php printf($lang_admin_reports['Reported by'], $reporter) ?></th>
									<td><?php echo implode($lang_admin_reports['Location seperator'], $report_location) ?></td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_reports['Reason'] ?><div><input type="submit" name="zap_id[<?php echo $cur_report['id'] ?>]" value="<?php echo $lang_admin_reports['Zap'] ?>" /></div></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php

	}
}
else
{

?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_common['None'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_admin_reports['No new reports'] ?></p>
						</div>
					</fieldset>
				</div>
<?php

}

?>
			</form>
		</div>
	</div>

	<div class="blockform block2">
		<h2><span><?php echo $lang_admin_reports['Last 10 head'] ?></span></h2>
		<div class="box">
			<div class="fakeform">
<?php

$result = $db->query('SELECT r.id, r.topic_id, r.forum_id, r.reported_by, r.message, r.zapped, r.zapped_by AS zapped_by_id, p.id AS pid, t.subject, f.forum_name, u.username AS reporter, u2.username AS zapped_by FROM '.$db->prefix.'reports AS r LEFT JOIN '.$db->prefix.'posts AS p ON r.post_id=p.id LEFT JOIN '.$db->prefix.'topics AS t ON r.topic_id=t.id LEFT JOIN '.$db->prefix.'forums AS f ON r.forum_id=f.id LEFT JOIN '.$db->prefix.'users AS u ON r.reported_by=u.id LEFT JOIN '.$db->prefix.'users AS u2 ON r.zapped_by=u2.id WHERE r.zapped IS NOT NULL ORDER BY zapped DESC LIMIT 10') or error('Unable to fetch report list', __FILE__, __LINE__, $db->error());

if ($db->num_rows($result))
{
	while ($cur_report = $db->fetch_assoc($result))
	{
		$reporter = ($cur_report['reporter'] != '') ? '<a href="profile.php?id='.$cur_report['reported_by'].'">'.pun_htmlspecialchars($cur_report['reporter']).'</a>' : $lang_admin_reports['Deleted user'];
		$forum = ($cur_report['forum_name'] != '') ? '<a href="viewforum.php?id='.$cur_report['forum_id'].'">'.pun_htmlspecialchars($cur_report['forum_name']).'</a>' : $lang_admin_reports['Deleted'];
		$topic = ($cur_report['subject'] != '') ? '<a href="viewtopic.php?id='.$cur_report['topic_id'].'">'.pun_htmlspecialchars($cur_report['subject']).'</a>' : $lang_admin_reports['Deleted'];
		$post = str_replace("\n", '<br />', pun_htmlspecialchars($cur_report['message']));
		$post_id = ($cur_report['pid'] != '') ? '<a href="viewtopic.php?pid='.$cur_report['pid'].'#p'.$cur_report['pid'].'">Post #'.$cur_report['pid'].'</a>' : $lang_admin_reports['Deleted'];
		$zapped_by = ($cur_report['zapped_by'] != '') ? '<a href="profile.php?id='.$cur_report['zapped_by_id'].'">'.pun_htmlspecialchars($cur_report['zapped_by']).'</a>' : $lang_admin_reports['NA'];
		$zapped_by = ($cur_report['zapped_by'] != '') ? '<strong>'.pun_htmlspecialchars($cur_report['zapped_by']).'</strong>' : $lang_admin_reports['NA'];
		$report_location = array($forum, $topic, $post_id);

?>
				<div class="inform">
					<fieldset>
						<legend><?php printf($lang_admin_reports['Zapped subhead'], format_time($cur_report['zapped']), $zapped_by) ?></legend>
						<div class="infldset">
							<table class="aligntop" cellspacing="0">
								<tr>
									<th scope="row"><?php printf($lang_admin_reports['Reported by'], $reporter) ?></th>
									<td><?php echo implode($lang_admin_reports['Location seperator'], $report_location) ?></td>
								</tr>
								<tr>
									<th scope="row"><?php echo $lang_admin_reports['Reason'] ?></th>
									<td><?php echo $post ?></td>
								</tr>
							</table>
						</div>
					</fieldset>
				</div>
<?php

	}
}
else
{

?>
				<div class="inform">
					<fieldset>
						<legend><?php echo $lang_admin_common['None'] ?></legend>
						<div class="infldset">
							<p><?php echo $lang_admin_reports['No zapped reports'] ?></p>
						</div>
					</fieldset>
				</div>
<?php

}

?>
			</div>
		</div>
	</div>
	<div class="clearer"></div>
</div>
<?php

require PUN_ROOT.'footer.php';
