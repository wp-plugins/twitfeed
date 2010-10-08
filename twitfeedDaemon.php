<?php
	require_once("../../../wp-config.php");
	require_once("phirehose/lib/Phirehose.php");
	require_once("MemCacheLock.php");
	require_once("MemCacheCache.php");

	class TwitFeed_Daemon extends Phirehose
	{
		const STREAM_LOCK 	= "TWITTER_STREAM_LOCK";
		const STREAM_DATA 	= "TWITTER_STREAM_DATA";
		const MAX_LOCK_ATTEMPTS	= 10;

		const MEMCACHE_HOST	= "localhost";
		const MEMCACHE_PORT	= 11211;

		private $cache;
		private $lock;

		public function __construct()
		{
			$this->cache = new MemCacheCache();
			$this->cache->delete(self::STREAM_DATA);

			$this->lock = new MemCacheLock();
			$this->lock->releaseLock(self::STREAM_LOCK);

			parent::__construct($user, $password); //, Phirehose::METHOD_FILTER);

			$this->checkFilterPredicates();
		}

		public function enqueueStatus($status)
		{
			if ($this->getMethod() == Phirehose::METHOD_SAMPLE && rand(1,50) != 25)
				return;

			// Dump the status update if we can't get a lock
			if (!$this->lock->getLock(self::STREAM_LOCK))
			{
				return;
			}

			// Get the data and append the stream
			// default it if it doesn't exist
			$data = $this->cache->get(self::STREAM_DATA);
			if ($data === false)
			{
				$data = array();
			}
			$data[] = $status;

			$this->cache->set(self::STREAM_DATA, $data);

			// Release the lock			
			$this->lock->releaseLock(self::STREAM_LOCK);
		}

		protected function checkFilterPredicates()
		{
			global $wpdb, $table_prefix;

			$results = $wpdb->get_results("SELECT * FROM {$table_prefix}twitfeed_settings");
			$results = $results[0];

			$this->setCredentials($results->account, $results->password);

			if ($results->method == 'FOLLOW-USER')
			{
				$this->setMethod(Phirehose::METHOD_FILTER);
				$ids = split(",", $results->filterIds);
				$trimmedIds = array();
				foreach($ids as $id)
				{
					$trimmedId = (int)trim($id);
					if ($trimmedId)
						$trimmedIds[] = $trimmedId;
				}
				$this->setFollow($trimmedIds);
			}
			else if ($results->method == 'FOLLOW-TRACK')
			{
				$this->setMethod(Phirehose::METHOD_FILTER);
				$tracks = split(",", $results->sampleText);
				$trimmedTracks = array();
				foreach ($tracks as $track)
				{
					$trimmedTrack = trim($track);
					if ($trimmedTrack)
						$trimmedTracks[] = $trimmedTrack;
				}
				$this->setTrack($trimmedTracks);

			}
			else
			{
				$this->setMethod(Phirehose::METHOD_SAMPLE);
			}
		}
	}
?>
