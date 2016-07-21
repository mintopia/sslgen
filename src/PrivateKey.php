<?php
	namespace Mintopia\SSLGen;

	class PrivateKey {
		public function __construct($keySize) {
			$this->key = openssl_pkey_new([
				'private_key_bits' => $keySize
			]);
		}

		public function getKey() {
			return $this->key;
		}

		public function export() {
			$output = null;
			openssl_pkey_export($this->key, $output);
			return $output;
		}
	}