<?php

	interface ILock
	{
		// Returns bool
		// True if lock 
		public function getLock($lockID, $value = 1, $retries = 10, $retrywait = 10000 );

		// Returns bool
		// Release lock
		public function releaseLock($lockId);
	}
?>
