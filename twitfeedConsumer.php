<?php
	require_once("MemCacheCache.php");
	require_once("MemCacheLock.php");
	require_once("../../../wp-config.php");

	class TestConsumer
	{

		const STREAM_LOCK = "TWITTER_STREAM_LOCK";
		const STREAM_DATA = "TWITTER_STREAM_DATA";

		private $cache;
		private $lock;

		public function __construct()
		{
			$this->cache = new MemCacheCache();
			$this->lock  = new MemCacheLock();
		}


		public function consume()
		{
			while(true)
			{
				$this->lock->getLock(self::STREAM_LOCK);			
				$statusArray = $this->cache->get(self::STREAM_DATA);	
				$this->cache->delete(self::STREAM_DATA);
				$this->lock->releaseLock(self::STREAM_LOCK);

				if (is_array($statusArray))
				{
					foreach($statusArray as $status)
					{
						$status = json_decode($status);
						if ($status)
							$this->insertToDatabase($status);
					}
				}
				sleep(5);
			};
		}	

		function insertToDatabase($status)
		{
			global $wpdb, $table_prefix;

			$text = $status->text; //htmlentities($status->text);
			$text = mysql_real_escape_string($text);
			$date = date('Y-m-d G:i:s', strtotime($status->created_at));
			$query = "INSERT INTO {$table_prefix}twitfeed VALUES ( {$status->id}, {$status->user->id}, '{$status->user->screen_name}',
					'{$status->user->profile_image_url}', '{$text}', '$date')";

			$wpdb->get_results($query);
		}

	}
?>
