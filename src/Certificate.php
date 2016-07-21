<?php
	namespace Mintopia\SSLGen;
	
	class Certificate {
		public $csr;
		public $duration;
		public $key;

		protected $certificate;
		protected $opensslCSR;

		public function __construct($csr, $key, $duration) {
			$this->csr = $csr;
			$this->key = $key;
			$this->duration = $duration;

			$this->csr->generate($this->key);
			$this->opensslCSR = $this->csr->getCSR();
			$opensslKey = $this->key->getKey();
			$this->certificate = openssl_csr_sign($this->opensslCSR, null, $opensslKey, 365 * $this->duration);
		}

		public function export() {
			$output = null;
			openssl_x509_export($this->certificate, $output);
			return $output;
		}
	}