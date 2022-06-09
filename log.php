<?php
	require "../library/php/dbconnect.php";

	error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

	spl_autoload_register(function ($class_name) {
		require_once $class_name . '.php';
	});

	$current = "wordscape";
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>The Ultimate Wordscape Assistant! Log</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Antonio C. Silvestri">
	<meta NAME="robots" content="noindex,nofollow">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" href="css/styles.css">
</head>
<body>
	<?php include "../library/php/navbar.php"; ?>
    <div class="container">
        <div class="jumbotron">
			<div class="row">
				<div class="col-lg-8">
					<h1>The Ultimate Wordscape Assistant! Log</h1>
					<p class="d-print-none"><a href="#" data-toggle="modal" data-target="#myModal">The Ultimate Wordscape Assistant! Log</a></p>
				</div>
				<div class="col-lg-4 d-print-none">
				</div>
			</div>
		</div>
        <div class="row">
            <div class="col">
				<?php
					try {
						$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
						$pdo = new PDO($dsn, DB_USER, DB_PASS);
						$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
						$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
						$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
						$stmt = $pdo->query("SELECT letters, pattern, time, ipaddr FROM wordscape Order by id desc limit 100");
						if ($stmt->execute() <> 0) :
				?>
				<div class="table-responsive">
					<table class="table table-striped table-sm">
						<thead>
							<tr>
								<th scope="col">Letters</th>
								<th scope="col">pattern</th>
								<th scope="col">Time</th>
								<th scope="col">IP Address</th>
								<th scope="col">GeoLocation</th>
							</tr>
						</thead>
						<tbody>
				<?php
					while($logitem = $stmt->fetch()) :
						echo "<tr>\n";
						echo "<td>{$logitem->letters}</td>\n";
						echo "<td>{$logitem->pattern}</td>\n";
						echo "<td>{$logitem->time}</td>\n";
						echo "<td><a href=\"#\" class=\"ipaddr\">{$logitem->ipaddr}</a></td>\n";
						echo "<td></td>\n";
						echo "</tr>\n";
					endwhile;
				?>
						</tbody>
					</table>
				<?php
						endif;
					}
					catch (Exception $e) {
						echo 'Connection failed: ' . $e->getMessage();
					}
				?>
			</div>
		</div>
    </div>

	<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">The Ultimate Wordscape Assistant!</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Designed and Coded by:</p>
                    <p><a href="mailto:silvestri@stcc.edu">Antonio C. Silvestri</a></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	<script src="../library/js/geolocateclick.js"></script> 
</body>
</html>
