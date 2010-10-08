<?php
    /*
    Plugin Name: TwitFeed
    Plugin URI: http://zenwerx.com/projects/twitfeed/
    Description: Set up a live feed from twitter to your site
    Version: 0.0.1
    Author: Michael Carpenter
    Author URI: http://zenwerx.com/

    */

	$createTwitFeed = "CREATE TABLE IF NOT EXISTS `{$table_prefix}twitfeed` (
		`tweet_id` BIGINT(20),
		`user_id` BIGINT(20),
		`screen_name` VARCHAR(100),
		`profile_image_url` VARCHAR(255),
		`tweet_text` VARCHAR(140),
		`tweet_time` DATETIME,
		PRIMARY KEY (`tweet_id`),
		INDEX (`user_id`),
		INDEX (`tweet_time`)
	) TYPE = MyISAM;";

	$createTwitSettings = "CREATE TABLE IF NOT EXISTS `{$table_prefix}twitfeed_settings` (
		`method` VARCHAR(20),
		`filterIds` TEXT,
		`sampleText` TEXT,
		`account` VARCHAR(100),
		`password` VARCHAR(100)
	) TYPE = MyISAM;";

	function twitfeed_install()
	{
		global $wpdb, $table_prefix, $createTwitFeed, $createTwitSettings;

		$wpdb->get_results($createTwitFeed);
		$wpdb->get_results($createTwitSettings);
		$result = $wpdb->get_results("SELECT * FROM {$table_prefix}twitfeed_settings");
		if (!count($result))
		{
			$wpdb->get_results("INSERT INTO {$table_prefix}twitfeed_settings VALUES ('SAMPLE', '', '', '', '')");
		}
	}

	function twitfeed_settings()
	{
		global $wpdb, $table_prefix;

		$profileDataUrl = "http://twitter.com/users/show/%s.json";

		$accountUpdate = false;
		$optionsUpdate = false;

		if ($_REQUEST['twitfeed_save_account'])
		{
			$wpdb->get_results("UPDATE {$table_prefix}twitfeed_settings SET account='{$_POST['account']}', password='{$_POST['password']}'");

			$accountUpdate = true;
		}

		if ($_REQUEST['twitfeed_save_options'])
		{
			$followers = split(",", $_POST['followers']);
			$followerIds = "";
			foreach($followers as $follower)
			{
				$data = getUrlData(sprintf($profileDataUrl, trim($follower)));
				$follower = json_decode($data);
				if ($follower)
					$followerIds .= ( $followerIds ? ", " : "" ) . $follower->id;
			}

			$wpdb->get_results("UPDATE {$table_prefix}twitfeed_settings SET method='{$_POST['method']}', filterIds='{$followerIds}', sampleText='{$_POST['sampletext']}'");

			$optionsUpdate = true;			
		}

		$options = $wpdb->get_results("SELECT * FROM {$table_prefix}twitfeed_settings");
		$options = $options[0];
	
		$followers = "";
		
		$followerIds = split(",", $options->filterIds);
		foreach($followerIds as $follower)
		{
			$data = getUrlData(sprintf($profileDataUrl, trim($follower)));
			$follower = json_decode($data);
			if ($follower)
				$followers .= ( $followers ? ", " : "" ) . $follower->screen_name;
		}
		
		?>
		<div class="wrapper">
		<form method="post" id="twitfeed_options">
			<h2>Twitfeed Options</h2><br />
			<div class='updated'>
			<p><strong>Track Words</strong> : Enter text to track from twitter feeds. Separate terms by commas. (Only works in track mode)</p>
			<p><strong>Followers</strong> : Enter twitter screen names. Separate names by commas. Only works in follow mode.</p><br />
			<p><strong>Sample Mode</strong> : Mostly for testing if your feed is working. Lots of tweets, can cause performance issues.</p><br />
			<p>Settings may take up to 2 minutes to refresh.</p>
			</div><br />
			<?
			if ($accountUpdate)
				echo "<div class='updated'>Account info updated.</div>";

			if ($optionsUpdate)
				echo "<div class='updated'>Followers and sample text updated.</div>";

			?>
			<div>
			<h3>Account Info</h3>
			<table width='100%'>
			<tr><td width='15%'>Account :</td><td><input type='text' id='account' name='account' value='<? echo $options->account; ?>'/></td></tr>
			<tr><td>Password :</td><td><input type='password' id='password' name='password' />
			<em>This password is not stored securely, we recommend not using your personal account.</em>
			</td></tr>
			</table>
			<br />
			<input type='submit' name='twitfeed_save_account' value='Save Account' />
			</div><br />
			<div>
			<h3>Feed Options</h3>
			<table width='100%'>
			<tr><td width='15%'>Method :</td><td><select id='method' name='method'>
				<option value='FOLLOW-USER' <? if ($options->method == 'FOLLOW-USER') echo 'selected'; ?>>Follow Users</option>
				<option value='FOLLOW-TRACK' <? if ($options->method == 'FOLLOW-TRACK') echo 'selected'; ?>>Follow Track</option>
				<option value='SAMPLE' <? if ($options->method == 'SAMPLE') echo 'selected'; ?>>Sample</option>
			</select></td></tr>
			<tr><td valign='top'>Sample Text :</td><td><textarea id='sampletext' name='sampletext' style='width: 100%'><? echo $options->sampleText; ?></textarea><td></tr>
			<tr><td valign='top'>Followers :</td><td><textarea id='followers' name='followers' style='width: 100%'><? echo $followers; ?></textarea></td></tr>
			</table>
			<br />
			<input type='submit' name='twitfeed_save_options' value='Save Options' />
			</div>
		</form>
		</div>
		<?
	}

 	function twitfeed_adminmenu()
 	{
        	add_options_page('Twitfeed Settings', 'Twitfeed', 9, 'twitfeed.php', 'twitfeed_settings');
    	}

	function twitfeed_feed()
	{
		$blogurl = get_bloginfo('url');

		$out = "
<div class=\"content\">
<h2 id=\"twitheader\">Twitter Live Feed</h2>
<div id='livefeed'>
</div>
<link media=\"screen\" type=\"text/css\" href=\"$blogurl/wp-content/plugins/twitfeed/twitfeed.css\" rel=\"stylesheet\">
<script>
        var lastUpdate = 0;

        function ajaxUpdate()
        {
                jQuery.ajax({
                        url: '$blogurl/wp-content/plugins/twitfeed/twitfeedAjax.php?tweetsafter='
                                        + (lastUpdate == 0 ? '' : lastUpdate),
                        success: function(data)
                        {
                                var feed = $(\"livefeed\");
                                feed.innerHTML = (data + feed.innerHTML);
                        }
                })
                lastUpdate = Math.round((new Date()).getTime() / 1000);
        }

        ajaxUpdate();

        var timerId = setInterval( 'ajaxUpdate()', 5000 );
</script>
</div>
";
		return $out;
	}


	// get contents using curl
        function getUrlData($url) {
            $ch = curl_init();
            $timeout = 5; // set to zero for no timeout
            curl_setopt ($ch, CURLOPT_URL, $url);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
            return $file_contents;
        }

	add_shortcode('twitfeed', 'twitfeed_feed');

    	// Add menu and install/upgrade db when activating
    	add_action('admin_menu','twitfeed_adminmenu',1);
    	if (isset($_GET['activate']) && $_GET['activate'] == 'true')
    	{
        	add_action('init', 'twitfeed_install');
    	}
	
?>
