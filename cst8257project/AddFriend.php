<?php

include_once 'EntityClassLib.php';
include_once 'Functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php");
    exit();
}

// Function to ensure the FriendshipStatus table is initialized
function initializeFriendshipStatus($pdo) {
    $statuses = [
        ['pending', 'Friend request pending'],
        ['accepted', 'Friend request accepted']
    ];

    foreach ($statuses as $status) {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM FriendshipStatus WHERE Status_Code = ?');
        $stmt->execute([$status[0]]);
        if ($stmt->fetchColumn() == 0) {
            $insertStmt = $pdo->prepare('INSERT INTO FriendshipStatus (Status_Code, Description) VALUES (?, ?)');
            $insertStmt->execute($status);
        }
    }
}

// Get the logged-in user
$user = $_SESSION['user'];

$errors = [];
$successes = [];

try {
    $pdo = getPDO();

    // Ensure the FriendshipStatus table is initialized
    initializeFriendshipStatus($pdo);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $friendId = trim($_POST['friendId']);

        // Validate input
        if (empty($friendId)) {
            $errors[] = 'Please enter a User ID.';
        } elseif ($friendId === $user->getUserId()) {
            $errors[] = 'You cannot send a friend request to yourself.';
        } else {
            // Check if the user exists
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM User WHERE UserId = ?');
            $stmt->execute([$friendId]);
            if ($stmt->fetchColumn() === 0) {
                $errors[] = 'The specified user does not exist.';
            } else {
                // Check for existing relationships
                $stmt = $pdo->prepare('SELECT COUNT(*) FROM Friendship WHERE 
                    (Friend_RequesterId = ? AND Friend_RequesteeId = ?) OR 
                    (Friend_RequesterId = ? AND Friend_RequesteeId = ?)');
                $stmt->execute([$user->getUserId(), $friendId, $friendId, $user->getUserId()]);
                if ($stmt->fetchColumn() > 0) {
                    $errors[] = 'You are already friends or have a pending request with this user.';
                } else {
                    // Send a friend request
                    $stmt = $pdo->prepare('INSERT INTO Friendship (Friend_RequesterId, Friend_RequesteeId, Status) VALUES (?, ?, ?)');
                    $stmt->execute([$user->getUserId(), $friendId, 'pending']);
                    $successes[] = "Friend request sent to $friendId.";
                }
            }
        }
    }
} catch (Exception $e) {
    $errors[] = 'An error occurred: ' . htmlspecialchars($e->getMessage());
}

include("./common/header.php");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <h1 class="mb-2 animated-border display-6">Add Friends</h1>
        <p class="text-center">Welcome <b><?php echo htmlspecialchars($user->getName()); ?></b>! (Not you? <a href="Login.php">change user here</a>)</p>
    <style>
        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h3>Add Friend</h3>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Success Messages -->
    <?php if (!empty($successes)): ?>
        <div class="alert alert-success">
            <?php foreach ($successes as $success): ?>
                <p><?= htmlspecialchars($success) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post">
        <div class="form-group">
            <label for="friendId">Enter the User ID of the friend you want to add:</label>
            <input type="text" name="friendId" id="friendId" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Send Friend Request</button>
    </form>
</div>

<?php include('./common/footer.php'); ?>
</body>
</html>
