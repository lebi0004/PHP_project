<?php
include_once 'EntityClassLib.php';
include_once 'Functions.php';
include("./common/header.php");

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is authenticated
if (!isset($_SESSION['user']) || !($_SESSION['user'] instanceof User)) {
    $_SESSION['redirect_url'] = basename($_SERVER['PHP_SELF']);
    header("Location: Login.php");
    exit();
}

// Get the logged-in user
$user = $_SESSION['user'];

// Handle friend requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = getPDO();
        if (isset($_POST['accept'])) {
            foreach ($_POST['friend_requests'] as $friendId) {
                // Update friendship status to 'accepted'
                $stmt = $pdo->prepare('UPDATE Friendship SET Status = ? WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?');
                $stmt->execute(['accepted', $friendId, $user->getUserId()]);
            }
        } elseif (isset($_POST['decline'])) {
            foreach ($_POST['friend_requests'] as $friendId) {
                // Delete the friend request
                $stmt = $pdo->prepare('DELETE FROM Friendship WHERE Friend_RequesterId = ? AND Friend_RequesteeId = ?');
                $stmt->execute([$friendId, $user->getUserId()]);
            }
        } elseif (isset($_POST['defriend'])) {
            foreach ($_POST['friends'] as $friendId) {
                // Delete friendship from both directions
                $stmt = $pdo->prepare('DELETE FROM Friendship WHERE (Friend_RequesterId = ? AND Friend_RequesteeId = ?) OR (Friend_RequesterId = ? AND Friend_RequesteeId = ?)');
                $stmt->execute([$user->getUserId(), $friendId, $friendId, $user->getUserId()]);
            }
        }
        header("Location: MyFriends.php");
        exit();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

// Fetch the user's friends with their full names
$pdo = getPDO();
$stmt = $pdo->prepare('
    SELECT 
        u.UserId, u.Name AS FullName, 
        (SELECT COUNT(*) FROM Album WHERE Owner_Id = f.Friend_RequesterId AND Accessibility_Code = "shared") AS SharedAlbums
    FROM 
        Friendship f
    JOIN 
        User u 
    ON 
        (u.UserId = f.Friend_RequesterId OR u.UserId = f.Friend_RequesteeId)
    WHERE 
        (f.Friend_RequesterId = ? OR f.Friend_RequesteeId = ?) AND f.Status = "accepted" AND u.UserId != ?
');
$stmt->execute([$user->getUserId(), $user->getUserId(), $user->getUserId()]);
$friends = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending friend requests with their full names
$stmt = $pdo->prepare('
    SELECT 
        u.UserId, u.Name AS FullName
    FROM 
        Friendship f
    JOIN 
        User u 
    ON 
        u.UserId = f.Friend_RequesterId
    WHERE 
        f.Friend_RequesteeId = ? AND f.Status = "pending"
');
$stmt->execute([$user->getUserId()]);
$friendRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mb-5 mt-3">
    <div class="shadow py-2 px-3 mb-5 bg-body-tertiary rounded">
        <div class="card-body">
            <h1 class="mb-2 animated-border display-6">My Friends</h1>
            <p class="text-center">Welcome <b><?php echo htmlspecialchars($user->getName()); ?></b>! (Not you? <a href="Login.php">change user here</a>)</p>

            <!-- Friends List -->
            <form method="post">
                <h4>Friends List</h4>
                <?php if (!empty($friends)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Friend's Full Name</th>
                                <th>Friend's User ID</th>
                                <th>Shared Albums</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($friends as $friend): ?>
                                <tr>
                                    <td><input type="checkbox" name="friends[]" value="<?= $friend['UserId'] ?>"></td>
                                    <td><?= htmlspecialchars($friend['FullName']) ?></td>
                                    <td>
                                        <a href="FriendPictures.php?friendId=<?= $friend['UserId'] ?>">
                                            <?= htmlspecialchars($friend['UserId']) ?>
                                        </a>
                                    </td>
                                    <td><?= $friend['SharedAlbums'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="defriend" class="btn btn-danger" onclick="return confirm('Are you sure you want to defriend the selected friends?')">Defriend Selected</button>
                <?php else: ?>
                    <p>You have no friends yet.</p>
                <?php endif; ?>
            </form>

            <!-- Friend Requests -->
            <h4>Friend Requests</h4>
            <form method="post">
                <?php if (!empty($friendRequests)): ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Requester Full Name</th>
                                <th>Requester User ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($friendRequests as $request): ?>
                                <tr>
                                    <td><input type="checkbox" name="friend_requests[]" value="<?= $request['UserId'] ?>"></td>
                                    <td><?= htmlspecialchars($request['FullName']) ?></td>
                                    <td><?= htmlspecialchars($request['UserId']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" name="accept" class="btn btn-success">Accept Selected</button>
                    <button type="submit" name="decline" class="btn btn-warning" onclick="return confirm('Are you sure you want to decline the selected friend requests?')">Decline Selected</button>
                <?php else: ?>
                    <p>No pending friend requests.</p>
                <?php endif; ?>
            </form>

            <a href="AddFriend.php" class="btn btn-primary">Add Friends</a>
        </div>
    </div>
</div>
<?php include('./common/footer.php'); ?>