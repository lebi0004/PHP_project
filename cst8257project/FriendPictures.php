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

include("./common/header.php");

$friends = $user->fetchAllFriends();

foreach ($friends as $friend) {
    $friendAlbums = $friend->fetchAllAlbums($accessibilityCode = 'shared' );
    echo "<h1>{$friend->getName()}'s Albums</h1>";
    foreach ($friendAlbums as $album) {
        $album->fetchAllPictures();
        echo "<h2>{$album->getTitle()}</h2>";
        echo "<div class='row'>";
        foreach ($album->fetchAllPictures() as $picture) {
            echo "<div class='col-md-3'>";
            echo "<img src='{$picture->getFilePath()}' class='img-thumbnail' alt='{$picture->getTitle()}'>";
            echo "<p>{$picture->getTitle()}</p>";
            echo "</div>";
        }
        echo "</div>";
    }
}



include('./common/footer.php'); 

?>