<?php
include_once 'EntityClassLib.php';
include_once 'Functions.php';
include("./common/header.php");

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is authenticated
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php");
    exit();
}

$user = $_SESSION['user'];
$options = getAccessibilityOptions(); // Fetch accessibility options

// Handle form submission to update accessibility
$successMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_changes'])) {
    try {
        foreach ($_POST['accessibility'] as $albumId => $newAccessibility) {
            updateAlbumAccessibility($albumId, $newAccessibility);
        }
        $successMessage = "Accessibility updated successfully!";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }

    // Refresh the albums data to reflect changes
    $albums = getUserAlbums($user->getUserId());
} else {
    // Initial fetch of albums
    $albums = getUserAlbums($user->getUserId());
}

// Handle delete request
if (isset($_GET['delete_album'])) {
    $albumId = $_GET['delete_album'];
    try {
        deleteAlbum($albumId);
        echo "<div class='alert alert-success'>Album deleted successfully!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
    header("Location: MyAlbums.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Albums</title>
    <script>
        // Function to hide success message after a few seconds
        function hideMessage() {
            const messageElement = document.getElementById('successMessage');
            if (messageElement) {
                setTimeout(() => {
                    messageElement.style.display = 'none';
                }, 3000); // Hide after 3 seconds
            }
        }
    </script>
</head>
<body onload="hideMessage()">
<div class="container mt-5">
    <h1 class="mb-4">My Albums</h1>
    <p>Welcome <b><?php echo htmlspecialchars($user->getName()); ?></b>! (Not you? <a href="Login.php">change user here</a>)</p>
    <a href="AddAlbum.php" class="btn btn-primary mb-3">Create a New Album</a>
    
    <!-- Success message -->
    <?php if (!empty($successMessage)): ?>
        <div id="successMessage" class="alert alert-success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <form method="post" action="MyAlbums.php">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Number of Pictures</th>
                    <th>Accessibility</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($albums as $album): ?>
                    <tr>
                        <td><a href="MyPictures.php?album_id=<?php echo $album['Album_Id']; ?>"><?php echo htmlspecialchars($album['Title']); ?></a></td>
                        <td><?php echo $album['PictureCount']; ?></td>
                        <td>
                            <select class="form-select" name="accessibility[<?php echo $album['Album_Id']; ?>]">
                                <?php foreach ($options as $option): ?>
                                    <option value="<?php echo $option['Accessibility_Code']; ?>" 
                                        <?php echo ($album['Accessibility_Code'] == $option['Accessibility_Code']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($option['Description']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <a href="MyAlbums.php?delete_album=<?php echo $album['Album_Id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this album?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit" name="save_changes" class="btn btn-success">Save Changes</button>
    </form>
</div>
</body>
</html>
<?php include('./common/footer.php'); ?>