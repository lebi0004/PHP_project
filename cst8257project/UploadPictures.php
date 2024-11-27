<?php
include_once 'EntityClassLib.php';
include_once 'Functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php");
    exit();
} else {
    $user = $_SESSION['user'];
}
$albumId = $_POST['albumId'] ?? null;
$txtTitle = $_POST['txtTitle'] ?? '';
$txtDescription = $_POST['txtDescription'] ?? '';

$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnUpload'])) {
    extract($_POST);

    if (isset($_FILES['txtUpload'])) {
        $allowedExtensions = ['jpg', 'png', 'gif', 'jpeg'];
        $uploadedFiles = [];


        $fileNames = is_array($_FILES['txtUpload']['name']) ? $_FILES['txtUpload']['name'] : [$_FILES['txtUpload']['name']];
        $tmpPaths = is_array($_FILES['txtUpload']['tmp_name']) ? $_FILES['txtUpload']['tmp_name'] : [$_FILES['txtUpload']['tmp_name']];
        $errorCodes = is_array($_FILES['txtUpload']['error']) ? $_FILES['txtUpload']['error'] : [$_FILES['txtUpload']['error']];

        for ($i = 0; $i < count($fileNames); $i++) {
            $originalName = $fileNames[$i];
            $tmpFilePath = $tmpPaths[$i];
            $errorCode = $errorCodes[$i];


            if ($errorCode !== UPLOAD_ERR_OK) {
                echo "Error uploading file '{$originalName}' (Error code: {$errorCode}).<br>";
                continue;
            }


            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                echo "Error uploading file '{$originalName}' (Invalid file type).<br>";
                continue;
            }

            $picture = new Picture($originalName, $albumId, $txtTitle, $txtDescription);
            $filePath = $picture->saveToUploadFolder($tmpFilePath, $albumId);
            $picture->create();
            $uploadedFiles[] = $originalName;
        }
        if (!empty($uploadedFiles)) {
            $successMessage = "The following files were uploaded successfully: " . implode(", ", $uploadedFiles) . ".";
        }
    }

    $albums = $user->fetchAllAlbums();
} else {
    $albums = $user->fetchAllAlbums();
}

include("./common/header.php");

?>

<h1 class="display-5 text-center">Upload Pictures</h1>
<div class="container">
    <p class="lead">
        Accepted image types: jpg, jpeg, gif and png. <br>
        You can upload multiple pictures at a time by holding the shift key while selecting images. <br>
        When uploading multiple pictures, the title and description will apply to all pictures. <br>
    </p>

    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success text-center" role="alert">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <form class="my-3" action="UploadPictures.php" method="post" enctype="multipart/form-data">
        <div class="form-group mb-4">
            <label for="albumId">Upload to Album</label>
            <select class="form-control" name="albumId" id="albumId">
                <option value="" disabled selected>-- Select an Album --</option>
                <?php foreach ($albums as $album): ?>
                    <option value="<?= $album->getAlbumId(); ?>"><?= $album->getTitle(); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group mb-4">
            <label for="txtUpload">Image(s) to Upload</label>
            <input type="file" class="form-control-file" name="txtUpload[]" id="txtUpload" multiple accept=".jpg,.jpeg,.gif,.png" />
        </div>
        <div class="form-group mb-4">
            <label for="txtTitle">Title</label>
            <input type="text" class="form-control" name="txtTitle" id="txtTitle" />
        </div>
        <div class="form-group mb-4">
            <label for="txtDescription">Description</label>
            <textarea class="form-control" name="txtDescription" id="txtDescription"></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary" name="btnUpload">Submit</button>
            <button type="reset" class="btn btn-secondary">Clear</button>
        </div>
    </form>
</div>

<?php include('./common/footer.php'); ?>