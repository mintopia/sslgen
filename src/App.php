<?php
	namespace Mintopia\SSLGen;

	class App {
		const DEFAULT_DURATION = 10;
		const DEFAULT_KEY_SIZE = 2048;
		const DEFAULT_CHAIN_SIZE = 0;

		public $csr;
		public $certificate;
		public $key;
		public $chain;

		public $duration;
		public $keySize;

		public function __construct() {
			$this->initialiseProperties();
			$this->updateCSR();
			$this->createCertificate();
		}

		protected function initialiseProperties() {
			$this->csr = new CSR;
			$this->duration = self::DEFAULT_DURATION;
			$this->keySize = self::DEFAULT_KEY_SIZE;
			$this->chain = self::DEFAULT_CHAIN_SIZE;
		}

		protected function isPost() {
			if (isset($_SERVER['REQUEST_METHOD']) && (strtolower($_SERVER['REQUEST_METHOD']) == 'post')) {
				return true;
			}
			return false;
		}

		protected function getPost($name, $default = null) {
			if (isset($_POST[$name])) {
				return $_POST[$name];
			}
			return $default;
		}

		protected function updateCSR() {
			if (!$this->isPost()) {
				return;
			}

			$propertyMapping = [
				'countryName' => 'countryname',
				'stateOrProvinceName' => 'region',
				'localityName' => 'locality',
				'organizationName' => 'organisation',
				'organizationalUnitName' => 'department',
				'commonName' => 'domain',
				'emailAddress' => 'email'
			];

			foreach ($propertyMapping as $classProperty => $postName) {
				$this->csr->{$classProperty} = $this->getPost($postName, $this->csr->{$classProperty});
			}
			
			$sans = $this->getPost('sans', $this->csr->getSANs());
			$this->csr->setSANs($sans);
		}

		protected function createCertificate() {
			if (!$this->isPost()) {
				return;
			}

			$this->duration = $this->getPost('duration', $this->duration);
			$this->keysize = $this->getPost('keysize', $this->keySize);

			$chain = $this->getPost('chain', 0);

			$parent = null;
			for ($i = 0; $i < $chain; $i++) {
				$csr = new CSR;
				$csr->commonName = 'host' . mt_rand(1000, 9999) . '.ca.example.com';
				$key = new PrivateKey($this->keysize);
				$parent = new Certificate($csr, $key, $this->duration, $parent);
			}


			$this->key = new PrivateKey($this->keysize);
			$this->certificate = new Certificate($this->csr, $this->key, $this->duration, $parent);
		}

		public function escape($value) {
			return htmlspecialchars($value);
		}

		public function formatx509($data) {
			return str_replace("\n", '\n', $data);
		}

		public function getURI() {
			if (isset($_SERVER['REQUEST_URI'])) {
				return $_SERVER['REQUEST_URI'];
			}
			return '';
		}
	}