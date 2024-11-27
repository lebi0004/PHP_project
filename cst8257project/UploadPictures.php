<?php
require_once 'EntityClassLib.php';
require_once 'Functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php"); // Redirect to login page
    exit();
}

// Get the logged-in user
$user = $_SESSION['user'];
$albumId = $_POST['albumId'] ?? null;
$album = $albumId ? Album::read($albumId) : null;

$txtTitle = $_POST['txtTitle'] ?? '';
$txtDescription = $_POST['txtDescription'] ?? '';

$successMessage = '';
$errorMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnUpload'])) {
    if (isset($_FILES['txtUpload']) && $albumId) {
        $supportedImageTypes = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
        $uploadedFiles = [];
        $fileNames = is_array($_FILES['txtUpload']['name']) ? $_FILES['txtUpload']['name'] : [$_FILES['txtUpload']['name']];
        $tmpPaths = is_array($_FILES['txtUpload']['tmp_name']) ? $_FILES['txtUpload']['tmp_name'] : [$_FILES['txtUpload']['tmp_name']];
        $errorCodes = is_array($_FILES['txtUpload']['error']) ? $_FILES['txtUpload']['error'] : [$_FILES['txtUpload']['error']];

        for ($i = 0; $i < count($fileNames); $i++) {
            $originalName = $fileNames[$i];
            $tmpFilePath = $tmpPaths[$i];
            $errorCode = $errorCodes[$i];

            if ($errorCode == UPLOAD_ERR_OK) {
                $fileType = exif_imagetype($tmpFilePath);
                if (!in_array($fileType, $supportedImageTypes)) {
                    $errorMessage = "The file type of '{$originalName}' is not allowed. Please upload JPG, JPEG, GIF, or PNG files.<br>";
                    continue;
                }
                $picture = new Picture($originalName, $albumId, $txtTitle, $txtDescription);
                $filePath = $picture->saveToUploadFolder($tmpFilePath, $albumId);
                $picture->create();
                $uploadedFiles[] = $originalName;
            } elseif ($errorCode == 1) {
                $errorMessage = "Error uploading file '{$originalName}': File is too large.<br>";
            } elseif ($errorCode == 4) {
                $errorMessage = "No files selected for upload.";
            } else {
                $errorMessage = "Error occurred while uploading the file(s). Please try again later.<br/>";
            }
        }
        if (!empty($uploadedFiles)) {
            $successMessage = "Successfully uploaded " . count($uploadedFiles) . " image(s) to the album '" . $album->getTitle() . "'.";
        }
    } elseif (!$albumId) {
        $errorMessage = "Please select an album to upload the pictures.";
    }
}
$albums = $user->fetchAllAlbums();

include("./common/header.php");
?>

<div class="container mb-5 mt-3">
    <div class="card border-light">
        <div class="card-body">
            <h1 class="card-title text-center text-dark mb-3 display-6 animated-border">
                Upload Pictures
            </h1>
            <div class="container">
            <?php if (count($albums) > 0): ?>
                <div class="text-start">
                    <small>
                        Accepted image types: JPG, JPEG, GIF and PNG. <br>
                        You can upload multiple pictures at a time by holding the shift key while selecting images. <br>
                        When uploading multiple pictures, the title and description will apply to all pictures. <br>
                    </small>
                </div>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success text-center mt-2" role="alert">
                        <?php echo $successMessage; ?>
                    </div>
                    <hr>
                <?php elseif (!empty($errorMessage)): ?>
                    <div class="alert alert-danger text-center mt-2" role="alert">
                        <?php echo $errorMessage; ?>
                    </div>
                    <hr>
                <?php endif; ?>
            
                    <form class="my-3" action="UploadPictures.php" method="post" enctype="multipart/form-data">
                        <div class="form-group mb-3">
                            <label for="albumId">Upload to Album</label>
                            <select class="form-control" name="albumId" id="albumId">
                                <option value="" disabled selected>-- Select an Album --</option>
                                <?php foreach ($albums as $album): ?>
                                    <option value="<?= $album->getAlbumId(); ?>"><?= $album->getTitle(); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="txtUpload">Image(s) to Upload</label>
                            <input type="file" class="form-control-file" name="txtUpload[]" id="txtUpload" multiple accept=".jpg,.jpeg,.gif,.png" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="txtTitle">Title</label>
                            <input type="text" class="form-control" name="txtTitle" id="txtTitle" />
                        </div>
                        <div class="form-group mb-3">
                            <label for="txtDescription">Description</label>
                            <textarea class="form-control" name="txtDescription" id="txtDescription"></textarea>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" name="btnUpload">Submit</button>
                            <button type="reset" class="btn btn-secondary">Clear</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="fs-5 text-center mt-3" role="alert">
                        You do not have any albums. Please <a href="AddAlbum.php">create an album</a> first.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('./common/footer.php'); ?>
