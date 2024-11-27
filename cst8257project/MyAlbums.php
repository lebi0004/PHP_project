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
        Album::delete($albumId);
        $successMessage = "Album deleted successfully!";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
    $albums = getUserAlbums($user->getUserId());
}
?>

<script>
    // Function to hide success message after a few seconds
    function hideMessage() {
        const messageElement = document.querySelector('.alert');
        if (messageElement) {
            setTimeout(() => {
                messageElement.style.display = 'none';
            }, 3000); // Hide after 3 seconds
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        hideMessage();
    });
</script>

<div class="container mb-5 mt-4">
    <div class="shadow p-4 mb-5 bg-body-tertiary rounded" style="max-width: 800px; margin: auto;">
        <h1 class="mb-2 animated-border display-6">My Albums</h1>
        <p class="text-center">Welcome <b><?php echo htmlspecialchars($user->getName()); ?></b>! (Not you? <a href="Login.php">change user here</a>)</p>
        <!-- Success message -->
        <?php if (!empty($successMessage)): ?>
            <div id="successMessage" class="alert alert-success disappearing-message"><?php echo $successMessage; ?></div>
        <?php endif;
        if (empty($albums)) { ?>
            <p class="fs-5 mt-5 text-center">You do not have any albums. <a href="AddAlbum.php">Create a New Album</a></p>
        <?php } else { ?>
            <a href="AddAlbum.php" class="btn btn-primary btn-sm mb-3">New Album</a>
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
                                    <a href="MyAlbums.php?delete_album=<?php echo $album['Album_Id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this album?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_changes" class="btn btn-success btn-sm">Save Changes</button>
            </form>
        <?php }
        ?>


    </div>
</div>


<?php include('./common/footer.php'); ?>