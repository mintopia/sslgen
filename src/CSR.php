<?php
	namespace Mintopia\SSLGen;

	class CSR {
		public $countryName = 'GB';
		public $stateOrProvinceName = 'Gloucestershire';
		public $localityName = 'Gloucester';
		public $organizationName = 'Mintopia';
		public $organizationalUnitName = 'Development';
		public $commonName = '*';
		public $emailAddress = 'jess@mintopia.net';
		public $altNames = [];
		
		protected $configFile;
		protected $csr;

		public function getCSR() {
			return $this->csr;
		}

		public function generate($key) {
			if ($this->csr) {
				return;
			}
			
			$privKey = $key->getKey();
			
			$this->csr = openssl_csr_new($this->getDN(), $privKey, $this->getExtensions(), $this->getExtraOptions());
			if ($this->csr === false) {
				throw new \Exception(openssl_error_string());
			}
		}
		
		public function getSANs()
		{
			return implode("\r\n", $this->altNames);
		}
		
		public function setSANs($str)
		{
			$names = explode("\r\n", $str);
			$this->altNames = array_filter($names);
		}
		
		public function getExtensions()
		{
			if (!$this->altNames)
			{
				return null;
			}
			
			return [
				'config' => $this->getConfigName()
			];
		}
		
		protected function getConfigName()
		{
			if (!$this->configFile) {
				$this->configFile = $this->makeConfig();
			}
			return $this->configFile;
		}
		
		protected function getExtraOptions()
		{
			return null;
		}
		
		protected function makeConfig()
		{
			$config = <<<EOF
[ req ]
distinguished_name = req_distinguished_name
req_extensions = v3_req
x509_extensions	= v3_req

[ req_distinguished_name ]

[ v3_req ]
basicConstraints = CA:FALSE
keyUsage = nonRepudiation, digitalSignature, keyEncipherment
subjectAltName = @san

[ san ]

EOF;
			$i = 1;
			$altNames = $this->altNames;
			if (!in_array($this->commonName, $altNames)) {
				$altNames[] = $this->commonName;
			}
			foreach ($altNames as $name) {
				$config .= "DNS.{$i} = {$name}\r\n";
				$i++;
			}
			$filename = tempnam(sys_get_temp_dir(), 'ssl');
			file_put_contents($filename, $config);
			return $filename;
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