<?php

/*
Plugin Name: Radioactivity
Version: 0.1-alpha
Description: Pulls playlist information from Radioactivity for WOMM-LP.
Author: Evan Flynn
Author URI: http://readevalprintloop.info/
Text Domain: radioactivity
Domain Path: /languages
*/

function _radioactivity_get_tag($item, $tag) {
	$ns = 'http://www.radioactivity.fm/inc/namespace';

	return $item->get_item_tags($ns, $tag)[0]['data'];
}

function radioactivity_feed() {
	require_once 'vendor/autoload.php';

	$ns = 'http://www.radioactivity.fm/inc/namespace';
	$rss = fetch_feed( 'http://womm.radioactivity.fm/feeds/onair.xml' );

	if ( ! is_wp_error( $rss ) ) {
		$item = $rss->get_item();
	}

	$dj = array(
		'bio'   => _radioactivity_get_tag($item, 'bio'),
		'photo' => _radioactivity_get_tag($item, 'photourl'),
	);

	$rss = fetch_feed( 'http://womm.radioactivity.fm/feeds/showonair.xml' );

	if ( ! is_wp_error( $rss ) ) {
		$item = $rss->get_item();
	}

	$show = array(
		'name' => _radioactivity_get_tag($item, 'showname'),
		'dj'   => _radioactivity_get_tag($item, 'showdj'),
		'info' => _radioactivity_get_tag($item, 'showinfo'),
		'link' => _radioactivity_get_tag($item, 'showlink'),
		'latewarning' => _radioactivity_get_tag($item, 'latewarning'),
		'schedule' => _radioactivity_get_tag($item, 'showschedule'),
	);

	ob_start();
?>
<table>
	<tr>
		<td width="20%">Current Show</td>
		<td width="80%"><a href="<?= $show['link'] ?>"><?= $show['name'] ?></a></td>
	</tr>

	<tr>
		<td width="20%">Current DJ</td>
		<td width="80%"><?php

if ($dj['photo']):
	$string_to_display = "DJ: " . htmlspecialchars($show['name'] . '<br>');
	$string_to_display .= "Show: " . htmlspecialchars($show['name'] . '<br>');
	$string_to_display .= "Bio: "  . htmlspecialchars($dj['bio']);
?>
<a target="_new" data-lightbox="image-0" title="<?= $string_to_display ?>" href="<?= $dj['photo'] ?>">
<?php endif; ?><?= $show['dj'] ?></a>
</td>
</tr>

<?php if ($dj['bio']): ?>
	<tr>
		<td width="20%">DJ Bio</td>
		<td width="80%"><?= $dj['bio'] ?></td>
	</tr>
<?php endif; ?>

<?php if (substr_count($show['latewarning'], "was seen over") > 0): ?>
	<tr>
		<td colspan="2" width="80%">The last play was seen over 15 minutes ago,
		so there may be no Live DJ at the moment...</td>
	</tr>
<?php endif; ?>
</table>

<table>
<tr>
	<td width="10%">Time</td>
	<td width="20%">Title</td>
	<td width="20%">Artist</td>
	<td width="20%">Album</td>
	<td width="20%">Label</td>
	<td width="10%">Genre</td>
</tr>
<?php
	$rss = fetch_feed( 'http://womm.radioactivity.fm/feeds/last25.xml' );

	foreach ($rss->get_items(0) as $item): ?>
	<tr>
	 <td width="10%"><?= _radioactivity_get_tag($item, 'time') ?></td>
	 <td width="20%"><?= _radioactivity_get_tag($item, 'track') ?></td>
	 <td width="20%"><?= _radioactivity_get_tag($item, 'artist') ?></td>
	 <td width="20%"><?= _radioactivity_get_tag($item, 'album' ) ?></td>
	 <td width="20%"><?= _radioactivity_get_tag($item, 'label' ) ?></td>
	 <td width="10%"><?= _radioactivity_get_tag($item, 'genre' ) ?></td>
	</tr>
<?php endforeach; ?>
</table>
<?php
	$return = ob_get_contents();
	ob_end_clean();
	return $return;
}

add_shortcode( 'radioactivity', 'radioactivity_feed' );
