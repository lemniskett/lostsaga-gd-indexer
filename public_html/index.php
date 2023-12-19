<?php
# Configuration starts here
define('BASE_URL', 'https://lsgd.lemniskett.moe');
# Configuration ends here

$request_uri = explode("?", $_SERVER['REQUEST_URI'])[0];
$controller = explode("/", $request_uri);
if ($controller[count($controller) - 1] == "") {
    array_pop($controller);
}
array_shift($controller);
if(count($controller) > 2) {
    http_response_code(404);
    die();
}

$server = $controller[0];
$page = $controller[1];

if ($server == "") {
    header("Location: ".BASE_URL."/lso/1");
    die();
}

if ($page == "") {
    header("Location: ".BASE_URL."/$server/1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Saga GD scraper</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</head>
<body>
    <nav class="navbar navbar-light bg-light shadow">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Lost Saga GD Index</span>
            <form class="d-flex" role="search" id="page-form">
                <input class="form-control me-2" type="number" placeholder="Go to page..." aria-label="Page" name="page" value="<?= $page ?>">
                <button class="btn btn-outline-success ml-2" type="submit">Go!</button>
            </form>
        </div>
    </nav>
    <div class="container-fluid text-center">
        <div class="row p-4">
            <?php
                chdir("./textures/$server/$page");
                $files = glob("*.jpg");

                foreach ($files as $filename) {
                    echo <<<HTML
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 p-2 rounded-lg">
                        <div class="card shadow-sm">
                            <img src="/textures/$server/$page/$filename" class="card-img-top" style="width: 100%;">
                            <div class="card-body">
                                <p class="card-text">$filename</p>
                            </div>
                        </div>
                    </div>
                    HTML;
                }
            ?>
        </div>
        <nav aria-label="Page" class="container-fluid d-flex justify-content-center"">
            <ul class="pagination">
                <li class="page-item"><a class="page-link <?php if ($page == 1) echo "d-none"; ?>" href="<?= BASE_URL ?>/<?= $server ?>/<?= $page - 1 ?>">Previous</a></li>
                <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/<?= $server ?>/<?= $page + 1 ?>">Next</a></li>
            </ul>
        </nav>
    </div>
    <script>
        document.getElementById("page-form").addEventListener("submit", function(e) {
            e.preventDefault();
            window.location.href = `<?= BASE_URL ?>/<?= $server ?>/${this["page"].value}`
        })
    </script>
</body>
</html>