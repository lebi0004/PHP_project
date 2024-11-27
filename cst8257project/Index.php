<?php 
include_once 'Functions.php';
include("./common/header.php");

$user = $_SESSION['user'] ?? null;

?>
<div class="container mb-3">
    <div class="card border-light">
        <div class="card-body">
            <h1 class="card-title text-start text-dark mb-3 display-6 animated-border">Welcome to Algonquin Social Media Website<span class="text-primary"></span>!</h1>
            <?php if (isset($user)) { ?>
                <p class="card-text text-start text-dark mb-3 display-6 animated-border">
                    Hello, <?= $user->getName() ?>! <br><br>
                </p>
            <?php } else { ?>
                <p class="card-text fs-5">
                    If this is your first time, please <a class="text-decoration-none" href="./NewUser.php">sign up</a>.<br><br>
                    Already have an account? <a class="text-decoration-none" href="./Login.php">Log in</a> now.
                </p>
            <?php } ?>
        </div>
    </div>
</div>

<?php include('./common/footer.php'); ?>