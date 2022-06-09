<?php
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
set_time_limit(0);

spl_autoload_register(function ($class_name) {
    require_once $class_name . '.php';
});

require "../library/php/dbconnect.php";
require "../library/php/library.php";
require "secret/recaptcha.php";

function logAccess($letters, $pattern)
{
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->beginTransaction();
        $sql = 'INSERT INTO wordscape(letters, pattern, ipaddr) VALUES(:letters, :pattern, :ipaddr)';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['letters' => $letters, 'pattern' => $pattern, 'ipaddr' => getUserIP()]);
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        echo 'Connection failed: ' . $e->getMessage();
    }
}

function performAnalysis($letters, $pattern, $justRealWords, &$errMsg)
{
    $solution = "";
    try {
        $results = WordscapeAnalyzer::getWordscapeSolutions($letters, $pattern);
        if (count($results) < 1) {
            $errMsg = 'No Solutions Found';
        } else {
            $solution = WordscapeAnalyzer::outputSolutions($results, $justRealWords);
            if ($solution === "")
                $errMsg = "No Solutions Found";
        }
    } catch (Exception $ex) {
        $errMsg = "Error in Input: " . $ex->getMessage();
    }
    return $solution;
}

$title = "The Ultimate Wordscape / Crossword / Word Scramble Assistant!";
$current = "wordscape";
$letters = "";
$pattern = "";
$justRealWords = "justRealWords";
$solution = "";
$errMsg = '';
$showCaptCha = !isLocalHost();
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
    $letters = trim($_POST["letters"]);
    $pattern = trim($_POST["pattern"]);
    $justRealWords = isset($_POST["justRealWords"]) ? trim($_POST["justRealWords"]) : "";
    $showCaptCha = !isset($_POST["showCaptCha"]);

    if (isLocalHost() || !$showCaptCha)
        $okToPerFormAnalysis = true;
    else {
        $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
        $recaptcha_secret = SECRET_KEY;
        $recaptcha_response = $_POST['recaptcha_response'];
        $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response . "&remoteip=" .  getUserIP());
        $recaptcha = json_decode($recaptcha);
        $okToPerFormAnalysis = $recaptcha->success;
    }
    if ($okToPerFormAnalysis) {
        $showCaptCha = false;
        $solution = performAnalysis($letters, $pattern, $justRealWords, $errMsg);
        logAccess($letters, $pattern);
    } else {
        $errMsg = "Form Security 'I'm Not A Robot' Failed!";
    }
endif;
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Antonio C. Silvestri">
    <meta name="description" content="<?php echo $title; ?> Use this system when you get stuck on a Wordscape or Regular Crossword Puzzle word.">
    <link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="//use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="css/styles.css">

    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@bytecodeman">
    <meta name="twitter:title" content="<?php echo $title; ?>">
    <meta name="twitter:description" content="Use this system when you get stuck on a Wordscape or Regular Crossword Puzzle word.">
    <meta name="twitter:image" content="/specialapps/wordscape/img/wordscape.jpg">

    <meta property="og:url" content="/specialapps/wordscape/" />
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo $title; ?>" />
    <meta property="og:description" content="Use this system when you get stuck on a Wordscape or Regular Crossword Puzzle word." />
    <meta property="og:image" content="/specialapps/wordscape/img/wordscape.jpg" />

    <link rel="apple-touch-icon" sizes="180x180" href="/specialapps/wordscape/img/icons/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/specialapps/wordscape/img/icons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/specialapps/wordscape/img/icons/favicon-16x16.png">
    <link rel="manifest" href="/specialapps/wordscape/img/icons/site.webmanifest">
    <link rel="mask-icon" href="/specialapps/wordscape/img/icons/safari-pinned-tab.svg" color="#5bbad5">
    <link rel="shortcut icon" href="/specialapps/wordscape/img/icons/favicon.ico">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="msapplication-config" content="/specialapps/wordscape/img/icons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
    <script>
        (adsbygoogle = window.adsbygoogle || []).push({
            google_ad_client: "ca-pub-9626577709396562",
            enable_page_level_ads: true
        });
    </script>
</head>

<body>
    <?php include "../library/php/navbar.php"; ?>
    <div class="container">
        <div class="jumbotron">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="font-weight-bold"><?php echo $title; ?></h1>
                    <div class="clearfix">
                        <img src="img/wordscape.jpg" alt="" class="rounded mb-2 mr-4 float-left d-block img-fluid">
                        <p>Use this system when you get stuck on a Wordscape, Regular Crossword Puzzle, or Word Scramble.</p>
                        <p>Enter the characters allowed and the pattern of the word (underscores in pattern represent variable letters),
                            to produce all the possible combinations of characters. If the system recognizes a real word, it is displayed
                            in green. Sample Pattern: </p>
                        <pre>_ _ r _ y</pre>
                        <p>represents finding all 5 letter words with an <b>r</b> in the 3rd position and a <b>y</b> in the last.</p>
                        <p><b>Warning!</b> With many letters and variables in the pattern, there are VERY many possible combinations
                            that must be generated. It may take a long time to find solutions. <b>Patience Please!!!</b>
                    </div>
                    <p><a href="https://github.com/bytecodeman/wordscape" target="_blank" rel="noopener noreferrer">Source Code</a></p>
                    <p class="d-print-none"><a href="#" data-toggle="modal" data-target="#myModal">About <?php echo $title; ?></a></p>
                    <!--<div class="addthis_tipjar_inline"></div>-->
                </div>
                <div class="col-lg-4 d-print-none">
                    <!-- <script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script> -->
                    <!-- Mobile Ads -->
                    <ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-9626577709396562" data-ad-slot="7064413444" data-ad-format="auto"></ins>
                    <script>
                        (adsbygoogle = window.adsbygoogle || []).push({});
                    </script>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
            </div>
        </div>
        <div class="row">
            <div class="col">
                <?php if (!empty($errMsg)) : ?>
                    <div id="errMsg" role="alert" class="alert alert-danger h4 font-weight-bold alert-dismissible fade show">
                        <?php echo $errMsg; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
                <form id="wordscapeform" method="post" action="<?php echo htmlspecialchars(extractPath($_SERVER["PHP_SELF"])); ?>">
                    <div class="form-group mb-3">
                        <label for="letters">Enter Letters</label>
                        <input type="text" id="letters" name="letters" required class="form-control form-control-lg" placeholder="Enter Letters" value="<?php echo $letters; ?>">
                    </div>
                    <div class="btn-group mb-4" role="group" aria-label="Buttons for Letters">
                        <button type="button" class="btn btn-info" id="allLetters" data-toggle="tooltip" title="Load Letters Box with all 26 alphabetic characters">All Letters</button>
                        <button type="button" class="btn btn-secondary" id="clearLetters" data-toggle="tooltip" title="Clear the Letters Box">Clear</button>
                    </div>
                    <div class="form-group mb-3">
                        <label for="pattern">Enter Pattern: <span class="text-muted small">(Use Underscore _ for variable letter)</span></label> <input type="text" id="pattern" name="pattern" required class="form-control form-control-lg" placeholder="Enter Pattern (Underscore for variable letter)" value="<?php echo $pattern; ?>">
                    </div>
                    <div class="btn-group mb-4">
                        <button type="button" class="btn btn-info" id="scramblePattern" data-toggle="tooltip" title="Make Pattern a collection of underscores the size of the number of letters entered.  Ideal for Word Scrambles!">Scramble Pattern</button>
                        <button type="button" class="btn btn-secondary" id="clearPattern" data-toggle="tooltip" title="Clear the Pattern Box">Clear</button>
                    </div>
                    <div class="form-group form-check mb-4">
                        <input type="checkbox" class="form-check-input" id="justRealWords" name="justRealWords" value="justRealWords" <?php echo $justRealWords === "justRealWords" ? "checked" : ""; ?>>
                        <label class="form-check-label" for="justRealWords">Just Show Real Words <span class="text-muted small">(Could loose a solution if word is not in dictionary.)</span></label>
                    </div>
                    <?php if ($showCaptCha) : ?>
                        <input type="hidden" id="recaptchaResponse" name="recaptcha_response">
                    <?php else : ?>
                        <input type="hidden" name="showCaptCha" value="false">
                    <?php endif; ?>
                    <button type="submit" id="submit" name="submit" class="btn btn-primary btn-lg d-print-none" aria-label="Submit This Form">Submit</button>
                </form>
                <?php if (!empty($solution)) : ?>
                    <div id="solutionHeader" role="alert" class="alert alert-success h4 font-weight-bold alert-dismissible fade show mt-4">
                        Solutions Found!!!
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php echo $solution; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    require "../library/php/about.php";
    ?>

    <script src="//code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="js/gototop.js"></script>
    <script src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5a576c39d176f4a6"></script>
    <?php if ($showCaptCha) : ?>
        <script src='//www.google.com/recaptcha/api.js?render=<?php echo SITE_KEY; ?>'></script>
    <?php endif ?>
    <script>
        $(function() {
            <?php if ($showCaptCha) : ?>
                grecaptcha.ready(function() {
                    grecaptcha.execute('<?php echo SITE_KEY; ?>', {
                            action: 'wordscapeform'
                        })
                        .then(function(token) {
                            $("#recaptchaResponse").val(token);
                        });
                });
            <?php endif ?>

            //$('[data-toggle="tooltip"]').tooltip({container: 'body'});

            $("#letters, #pattern").keypress(function(e) {
                let $this = $(this);
                let char = e.which;
                if ($this[0] === $("#pattern")[0])
                    if (char === 95) {
                        return;
                    }
                if ((char >= 97) && (char <= 122)) { // lowercase
                    if (!e.ctrlKey && !e.metaKey && !e.altKey) { // no modifier key
                        char -= 32;
                    }
                }
                if (char < 65 || char > 90) {
                    e.preventDefault();
                } else {
                    let start = e.target.selectionStart;
                    let end = e.target.selectionEnd;
                    e.target.value = e.target.value.substring(0, start) + String.fromCharCode(char) + e.target.value.substring(end);
                    e.target.setSelectionRange(start + 1, start + 1);
                    e.preventDefault();
                }
            });

            $("#allLetters").on("click", function() {
                $("#letters").val("ABCDEFGHIJKLMNOPQRSTUVWXYZ");
                return false;
            });
            $("#clearLetters").on("click", function() {
                $("#letters").val("");
                return false;
            });

            $("#scramblePattern").on("click", function() {
                $("#pattern").val("_".repeat($("#letters").val().length));
                return false;
            });
            $("#clearPattern").on("click", function() {
                $("#pattern").val("");
                return false;
            });

            $("#wordscapeform").submit(function() {
                $("#errMsg").remove();
                const $errMsg = $('<div id="errMsg" role="alert" class="alert alert-danger h4 font-weight-bold alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');

                if (!this.checkValidity()) {
                    $(this).prepend($errMsg);
                    $("#errMsg").prepend("Invalid Inputs in Form");
                    return false;
                }
                if ($("#letters").val().length < 2) {
                    $(this).prepend($errMsg);
                    $("#errMsg").prepend("Less than 2 characters in Letters.");
                    $("#letters")[0].focus();
                    return false;
                }
                if ($("#pattern").val().length < 2) {
                    $(this).prepend($errMsg);
                    $("#errMsg").prepend("Less than 2 characters in Pattern.");
                    $("#pattern")[0].focus();
                    return false;
                }
                if ($("#pattern").val().length > $("#letters").val().length) {
                    $(this).prepend($errMsg);
                    $("#errMsg").prepend("Pattern has more characters than Letters.");
                    $("#pattern")[0].focus();
                    return false;
                }

                $("#solutionHeader, #resultsTable").hide();
                $("#submit").html('Please Wait <i class="fas fa-spinner fa-spin fa-lg ml-3"></i>').prop("disabled", true);
                return true;
            });

        });
    </script>
</body>

</html>