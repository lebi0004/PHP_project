<?php
include_once 'EntityClassLib.php';
include_once 'Functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php"); // Redirect to login page
    exit();
}

$user = $_SESSION['user'];
$albums = $user->fetchAllAlbums();

require_once("./common/header.php");

?>
<div class="container mt-5">
    <div class="dropdown mb-3">
        <button class="btn btn-secondary dropdown-toggle" type="button" id="albumDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            Select Album
        </button>
        <div class="dropdown-menu" aria-labelledby="albumDropdown">
            <?php foreach ($albums as $album) { ?>
                <a class="dropdown-item" href="#album<?=$album->getAlbumId();?>"><?=$album->getTitle();?></a>
            <?php } ?>
        </div>
    </div>

    <?php foreach ($albums as $album) { ?>
        <div id="album<?=$album->getAlbumId();?>" class="album-section">
            <h2><?=$album->getTitle();?></h2>
            <p><?= $album->getDescription(); ?></p>
            <div class="row">
                <?php foreach ($album->fetchAllPictures() as $picture) { ?>
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <img src="<?=$picture->getFilePath();?>" class="card-img-top" alt="...">
                            <div class="card-body">
                                <h5 class="card-title"><?=$picture->getTitle();?></h5>
                                <p class="card-text"><?=$picture->getDescription();?></p>
                                <p class="card-text"><small class="text-muted">Comments: <?=$picture->getComments();?></small></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
</div>
<?php require_once("./common/footer.php"); ?>