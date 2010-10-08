<?php

	require_once("../../../wp-config.php");

	global $wpdb, $table_prefix;

	if (isset($_GET['tweetsafter']))
	{
		$time = date('Y-m-d G:i:s', strtotime("-5 minutes"));
		if ($_GET['tweetsafter'])
		{
			$time = date('Y-m-d G:i:s', $_GET['tweetsafter']);
		}

		$query = "SELECT * FROM {$table_prefix}twitfeed WHERE tweet_time >= '$time' ORDER BY tweet_time DESC";

		$tweets = $wpdb->get_results($query);

		foreach($tweets as $tweet)
		{
                        $text = mb_convert_encoding($tweet->tweet_text, 'HTML-ENTITIES', 'UTF-8');
			$time = strtotime($tweet->tweet_time);
			$time = strtotime("-4 hours", $time);
			$time = date('Y-m-d h:i:s A', $time);
                        echo "<div><img src='{$tweet->profile_image_url}' /><span>\n";
                        echo "<span><a href='http://twitter.com/{$tweet->screen_name}/'>{$tweet->screen_name}</a>\n";
                        echo "{$text}<br/><span>{$time}</span></span></span></div>\n";
		}
	}
?>
