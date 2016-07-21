<?php

	class Certificate {
		public $csr;
		public $duration;
		public $key;

		protected $certificate;
		protected $opensslCSR;

		public function __construct($csr, $key, $duration = null) {
			if (!$duration) {
				$duration = App::DEFAULT_DURATION;
			}

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

	class PrivateKey {
		public function __construct($keySize = null) {
			if (!$keySize) {
				$keySize = $app->keySize;
			}
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

	class CSR {
		public $countryName = 'UK';
		public $stateOrProvinceName = 'Gloucestershire';
		public $localityName = 'Gloucester';
		public $organizationName = 'Fasthosts Internet Ltd';
		public $organizationalUnitName = 'Technical';
		public $commonName = '*';
		public $emailAddress = 'support@fasthosts.co.uk';

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

	class App {
		const DEFAULT_DURATION = 10;
		const DEFAULT_KEY_SIZE = 2048;

		public $csr;
		public $certificate;
		public $key;

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
		}

		protected function createCertificate() {
			if (!$this->isPost()) {
				return;
			}

			$this->duration = $this->getPost('duration', $this->duration);
			$this->keysize = $this->getPost('keysize', $this->keySize);

			$this->key = new PrivateKey($this->keysize);
			$this->certificate = new Certificate($this->csr, $this->key, $this->duration);
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

	function escape($value) {
		global $app;
		return $app->escape($value);
	}

	$app = new App;
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>SSL Certificate Generator</title>

		<link rel="stylesheet" href="https://static-content.fhpaas.fasthosts.net.uk/bootstrap/3.3.6/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://static-content.fhpaas.fasthosts.net.uk/highlight.js/9.5.0/styles/monokai-sublime.css">
		<link rel="stylesheet" href="https://static-content.fhpaas.fasthosts.net.uk/fonts/css/open-sans.css">
		<link rel="stylesheet" href="css/ssl.css">
	</head>
	<body>
		<div class="container">
			<h1 class="page-header">SSL Certificate Generator</h1>
			
			<p>
				Fill in the form below to generate a new self-signed certificate.
			</p>

			<form action="<?php echo escape($app->getURI()); ?>" method="POST" class="form-horizontal">
				<div class="form-group">
					<label for="domain" class="col-sm-2 control-label">Domain (CN)</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="domain" id="domain" placeholder="Domain Name (CN)" value="<?php echo escape($app->csr->commonName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="email" class="col-sm-2 control-label">Email</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="email" id="email" placeholder="Email" value="<?php echo escape($app->csr->emailAddress); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="department" class="col-sm-2 control-label">Department</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="department" id="department" placeholder="Country Name" value="<?php echo escape($app->csr->organizationalUnitName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="organisation" class="col-sm-2 control-label">Organisation</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="organisation" id="organisation" placeholder="Organisation" value="<?php echo escape($app->csr->organizationName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="locality" class="col-sm-2 control-label">Town/City</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="locality" id="locality" placeholder="Town/City" value="<?php echo escape($app->csr->localityName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="region" class="col-sm-2 control-label">Region</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="region" id="region" placeholder="Region" value="<?php echo escape($app->csr->stateOrProvinceName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="countryname" class="col-sm-2 control-label">Country</label>
					<div class="col-sm-4">
						<input type="text" class="form-control" name="countryname" id="countryname" placeholder="Country Name" value="<?php echo escape($app->csr->countryName); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="countryname" class="col-sm-2 control-label">Duration (Years)</label>
					<div class="col-sm-1">
						<input type="text" class="form-control" name="duration" id="duration" placeholder="Duration" value="<?php echo escape($app->duration); ?>">
					</div>
				</div>

				<div class="form-group">
					<label for="countryname" class="col-sm-2 control-label">Key Size</label>
					<div class="col-sm-1">
						<input type="text" class="form-control" name="keysize" id="keysize" placeholder="Key Size" value="<?php echo escape($app->keySize); ?>">
					</div>
				</div>

				<div class="form-group">
					<div class="col-sm-offset-2 col-sm-10">
						<button type="submit" class="btn btn-primary">Generate</button>
					</div>
				</div>
			</form>

			<?php if ($app->certificate) { ?>
				<form class="output">
					<div class="form-group">
						<label>CSR</label>
						<textarea class="form-control"><?php echo escape($app->csr->export()); ?></textarea>
						<br />
						<span class="text-muted">Without Linebreaks</span>
						<textarea class="form-control"><?php echo escape($app->formatx509($app->csr->export())); ?></textarea>
					</div>

					<div class="form-group">
						<label>Certificate</label>
						<textarea class="form-control"><?php echo escape($app->certificate->export()); ?></textarea>
						<br />
						<span class="text-muted">Without Linebreaks</span>
						<textarea class="form-control"><?php echo escape($app->formatx509($app->certificate->export())); ?></textarea>
					</div>

					<div class="form-group">
						<label>Private Key</label>
						<textarea class="form-control"><?php echo escape($app->key->export()); ?></textarea>
						<br />
						<span class="text-muted">Without Linebreaks</span>
						<textarea class="form-control"><?php echo escape($app->formatx509($app->key->export())); ?></textarea>
					</div>
				</form>
			<?php } ?>
		</div>
	</div>

	<script src="https://static-content.fhpaas.fasthosts.net.uk/jquery/1.12.4/jquery.min.js"></script>
	<script src="https://static-content.fhpaas.fasthosts.net.uk/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	<script src="https://static-content.fhpaas.fasthosts.net.uk/highlight.js/9.5.0/highlight.min.js"></script>
	<script src="js/ssl.js"></script>
	</body>
</html>