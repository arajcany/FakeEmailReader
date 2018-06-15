<?php
if (is_file('config.php')) {
    require_once('config.php');
} elseif (is_file('config.sample.php')) {
    file_put_contents('config.php', file_get_contents('config.sample.php'));
    exit("Please configure paths in config.php");
} else {
    exit("Config file missing!");
}

require_once __DIR__ . '/vendor/autoload.php';

$Parser = new PhpMimeMailParser\Parser();
$ParserLoop = new PhpMimeMailParser\Parser();


if (is_file("mode.txt")) {
    $mode = file_get_contents("mode.txt");

    if ($mode == 'dev') {
        $directory = $devDirectory;
        $header = 'Emails Sent via DEV';
    }

    if ($mode == 'uat') {
        $directory = $uatDirectory;
        $header = 'Emails Sent via UAT';
    }
}else{
    file_put_contents("mode.txt", "dev");
    $directory = $devDirectory;
    $header = 'Emails Sent via DEV';
}


if (isset($_GET['m'])) {
    if ($_GET['m'] == 'dev') {
        $directory = $devDirectory;
        file_put_contents("mode.txt", "dev");
        $header = 'Emails Sent via DEV';
    }

    if ($_GET['m'] == 'uat') {
        $directory = $uatDirectory;
        file_put_contents("mode.txt", "uat");
        $header = 'Emails Sent via UAT';
    }
}

$delete = false;
if (isset($_GET['c']) && $_GET['c'] == 'clear') {
    $delete = true;
}

$scanned_directory = array_diff(scandir($directory, 1), ['..', '.', 'mode.txt']);

$files = [];
foreach ($scanned_directory as $item) {
    if ($delete == false && is_file($directory . $item)) {
        $ordering = date('YmdHis', filectime($directory . $item)) . "_" . $item;
        $files[$ordering] = $item;
    }

    if ($delete == true && is_file($directory . $item)) {
        unlink($directory . $item);
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>FakeEmailReader Emails</title>

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
          integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">


    <!-- Custom styles for this template -->
    <style>
        body {
            padding-top: 5rem;
        }

        .starter-template {
            padding: 3rem 1.5rem;
            text-align: center;
        }

    </style>
</head>

<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="/FakeEmailReader/">FakeEmailReader</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault"
            aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarsExampleDefault">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a class="nav-link" href="?m=dev">DEV Emails</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="?m=uat">UAT Emails</a>
            </li>
        </ul>
    </div>
</nav>

<main role="main" class="container">

    <div class="starter-template">
        <h1><?= $header ?></h1>
    </div>

    <?php
    echo "<a href=\"?r=1\">Refresh</a>";
    echo " | ";
    echo "<a href=\"?c=clear\">Clear All</a>";
    echo "<br>";

    foreach ($files as $file) {
        $ParserLoop->setText(file_get_contents($directory . $file));
        $hTo = $ParserLoop->getHeader('to');
        $title = date('Y-m-d H:i:s', filectime($directory . $file)) . " - To: " . $hTo;

        echo "<a href=\"?q={$file}\">{$title}</a>";
        echo "<br>";
    }


    echo "<hr>";

    if (isset($_GET['q']) && is_file($directory . $_GET['q'])) {
        $Parser->setText(file_get_contents($directory . $_GET['q']));

        $headers = $Parser->getHeaders();
        $text = $Parser->getMessageBody('text');
        $html = $Parser->getMessageBody('html');
    } else {
        $headers = '';
        $text = '';
        $html = '';
    }
    ?>
    <div class="row">
        <div class="col-lg-12">
            <h1>Message Headers</h1>
            <pre>
                <?php print_r($headers) ?>
                </pre>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-lg-12">
            <h1>Text Content</h1>
            <pre>
                <?php print_r($text) ?>
                </pre>
        </div>
    </div>

    <hr>

    <div class="row">
        <div class="col-lg-12">
            <h1>HTML Content</h1>
            <div width="100%">
                <?php echo($html) ?>
            </div>
        </div>
    </div>

    <hr>

</main><!-- /.container -->

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
        integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
        integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
        crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
        integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
        crossorigin="anonymous"></script>
</body>
</html>

