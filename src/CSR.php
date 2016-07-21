<?php
	namespace Mintopia\SSLGen;

	class CSR {
		public $countryName = 'UK';
		public $stateOrProvinceName = 'Gloucestershire';
		public $localityName = 'Gloucester';
		public $organizationName = 'Mintopia';
		public $organizationalUnitName = 'Development';
		public $commonName = '*';
		public $emailAddress = 'jess@mintopia.net';

		protected $csr;

		public function getCSR() {
			return $this->csr;
		}

		public function generate($key) {
			if ($this->csr) {
				return;
			}
			$opensslKey = $key->getKey();
			$this->csr = openssl_csr_new($this->getDN(), $opensslKey);
			if ($this->csr === false) {
				throw new \Exception(openssl_error_string());
			}
		}

		protected function getDN() {
			return [
				'countryName' => $this->countryName,
				'stateOrProvinceName' => $this->stateOrProvinceName,
				'localityName' => $this->localityName,
				'organizationName' => $this->organizationName,
				'organizationalUnitName' => $this->organizationalUnitName,
				'commonName' => $this->commonName,
				'emailAddress' => $this->emailAddress
			];
		}

		public function export() {
			$output = null;
			openssl_csr_export($this->csr, $output);
			return $output;
		}
	}