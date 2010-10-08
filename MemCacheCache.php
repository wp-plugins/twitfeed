<?php
	require_once("ICache.php");

	class MemCacheCache implements ICache
	{
		const HOST = "localhost";
		const PORT = 11211;

		private $memcache;

		public function __construct()
		{
			$this->memcache = new Memcache();
			if(!$this->memcache->connect(self::HOST, self::PORT))
			{
				throw new Exception("Can't connect to memcached");
			}
		}

                // Should return bool.
                // Only returns true if item if not there and is added
                public function add($var, $data)
		{
			return $this->memcache->add($var, $data);
		}

                // Should return bool
                // Returns true if item is successfully added or updated
                public function set($var, $data)
		{
			return $this->memcache->set($var, $data);
		}

                // Mixed, get a value.
                public function get($var)
		{
			return $this->memcache->get($var);
		}

                // Should return bool
                // Remove a value, returns true if item was deleted
                // False if item did not exist
                public function delete($var)
		{
			return $this->memcache->delete($var, 0);
		}
	}
?>
