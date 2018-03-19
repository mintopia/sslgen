<?php
	namespace Mintopia\SSLGen;
	
	class Certificate {
		public $csr;
		public $duration;
		public $key;
		public $parent;

		protected $certificate;
		protected $opensslCSR;

		public function __construct($csr, $key, $duration, $parent = null) {
			$this->csr = $csr;
			$this->key = $key;
			$this->duration = $duration;
			$this->parent = $parent;

			$this->csr->generate($this->key);
			$this->opensslCSR = $this->csr->getCSR();

			$caKey = $this->key->getKey();
			$caCert = null;
			if ($this->parent) {
				$caCert = $parent->export();
				$caKey = $parent->key->getKey();
			}

			$this->certificate = openssl_csr_sign($this->opensslCSR, $caCert, $caKey, 365 * $this->duration, $this->csr->getExtensions(), $this->getSerialNumber());
		}
		
		protected function getSerialNumber()
		{
			return time();
		}

		public function export() {
			$output = null;
			openssl_x509_export($this->certificate, $output);
			return $output;
		}
	}