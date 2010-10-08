<?php

	require_once("ILock.php");
	require_once("MemCacheCache.php");

	class MemCacheLock implements ILock
	{

		private $cache;

		public function __construct()
		{
			$this->cache = new MemCacheCache();
		}

                // Returns bool
                // True if lock
                public function getLock($lockId, $value = 1, $retries = 10, $retrywait = 10000 )
		{
			$gotLock = false;

                        // Attempt to get locks for 100ms
                        for ($i=0;$i<$retries;$i++)
                        {
                                if (($gotLock = $this->cache->add($lockId, $value)) === false)
                                {
                                        // Sleep 10ms
                                        usleep($retrywait);
                                }
				else
				{
					break;
				}
                        }
                        return $gotLock;
			
		}

                // Returns bool
                // Release lock
                public function releaseLock($lockId)
		{
			return $this->cache->delete($lockId);
		}
	}
?>
