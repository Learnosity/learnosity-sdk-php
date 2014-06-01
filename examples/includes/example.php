<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Learnosity SDK Examples</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="./includes/static/images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="./includes/static/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="./includes/static/vendor/bootstrap/css/bootstrap-theme.min.css">
    <link rel="stylesheet" href="./includes/static/vendor/codemirror/codemirror.css">
    <link rel="stylesheet" href="./includes/static/css/main.css">
    <script src="./includes/static/js/main.js"></script>
</head>
<body>

<div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand logo" href="index.php">Learnosity SDK</a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="index.php?service=assess">Assess</a></li>
                <li><a href="index.php?service=author">Author</a></li>
                <li><a href="index.php?service=data">Data</a></li>
                <li><a href="index.php?service=items">Items</a></li>
                <li><a href="index.php?service=questions">Questions</a></li>
                <li><a href="index.php?service=reports">Reports</a></li>
                <li><a href="index.php?service=schemas">Schemas</a></li>
            </ul>
            <div class="pull-right">
                <ul class="nav navbar-nav">
                    <li>
                        <a href="https://github.com/Learnosity/learnosity-sdk-php" class="text-muted">
                            <span class="glyphicon glyphicon-file"></span> View source
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/Learnosity/learnosity-sdk-php/archive/master.zip" download="learnosity-sdk-php.master.zip" class="text-muted">
                            <span class="glyphicon glyphicon-cloud-download"></span> Download
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">

    <?php if (empty($service)) { ?>
        <div>
        <h1>Learnosity SDK Examples</h1>
        <p>This example site doesn't make requests to any Learnosity API. It simply
        creates initialisation objects for you that you could use to initialise
        any of the Learnosity APIs.</p>
        <p>Choose an example from the list below to see the initialisation object,
        review the relevant file in the <em>services</em> directory to see how
        the SDK was used:</p>
        <ul>
            <li><a href="index.php?service=assess">Assess</a></li>
            <li><a href="index.php?service=author">Author</a></li>
            <li><a href="index.php?service=data">Data</a></li>
            <li><a href="index.php?service=items">Items</a></li>
            <li><a href="index.php?service=questions">Questions</a></li>
            <li><a href="index.php?service=reports">Reports</a></li>
            <li><a href="index.php?service=schemas">Schemas</a></li>
        </ul>
        </div>
    <?php } else { ?>
        <div class="jumbotron">
            <h1 class="landing-heading"><?php echo $heading; ?> Example</h1>
            <?php echo $description; ?>
        </div>
        <div class="preview"><pre><code id="code"></code></pre></div>
        <script>
            <?php if ($service !== 'data') { ?>
                document.getElementById('code').innerHTML = library.json.prettyPrint(<?php echo $requestPacket; ?>);
            <?php } else { ?>
                document.getElementById('code').innerHTML = <?php echo "'" . addslashes($requestPacket) . "'"; ?>;
            <?php } ?>
        </script>
    <?php } ?>

    </div>
</div>

</body>
</html>
