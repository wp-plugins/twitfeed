<?php

	interface ICache
	{
		// Should return bool.
		// Only returns true if item if not there and is added
		public function add($var, $data);

		// Should return bool
		// Returns true if item is successfully added or updated
		public function set($var, $data);

		// Mixed, get a value.
		public function get($var);

		// Should return bool
		// Remove a value, returns true if item was deleted
		// False if item did not exist
		public function delete($var);
	}
?>
