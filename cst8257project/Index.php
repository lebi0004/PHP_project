<?php
include_once 'Functions.php';
include("./common/header.php");

$user = $_SESSION['user'] ?? null;

?>
<div class="container mb-2">
    <div class="card border-light">
        <div class="card-body">
            <?php if (isset($user)) { ?>
                <p class="card-text text-start text-dark mb-2 display-6 animated-border">
                    Hello, <?= $user->getName() ?>! <br><br>
                </p>
            <?php } ?>
            <h1 class="card-title text-start text-dark mb-3 display-6 animated-border ms-4">
                Welcome to Algonquin Social Media Website<span class="text-primary"></span>!
            </h1>
            <?php if (!isset($user)) { ?>
                <p class="card-text fs-5 mt-3 ms-5">
                    If this is your first time, please <a href="./NewUser.php">sign up</a>.<br><br>
                    Already have an account? <a href="./Login.php">Log in</a> now.<br>
                </p>
            <?php } ?>
        </div>
    </div>
</div>

<?php include('./common/footer.php'); ?>