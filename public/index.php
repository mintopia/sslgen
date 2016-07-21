<?php
	require('../vendor/autoload.php');

	use Mintopia\SSLGen\App;

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