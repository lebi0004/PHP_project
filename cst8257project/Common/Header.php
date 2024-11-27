<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en" style="position: relative; min-height: 100%;">
<head>
    <title>Algonquin Social Media</title>
    <meta charset="utf-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<style>
    footer {
      background-color: #004400;
      color: white;
      text-align: center;
      padding-top: 10px;
      font-size: 0.9em;
      margin-top: 40px;
    }

    a {
        text-decoration: none;
    }

    .animated-border {
      padding: 5px;
      text-align: center;
      background: linear-gradient(90deg, red, blue, green, yellow, red);
      background-size: 400% 400%;
      background-clip: text;
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: borderAnimation 5s linear infinite alternate, fadeInOut 5s ease-in-out infinite;
    }

    .disappearing-message {
      animation: disappear 5s forwards;
    }

    @keyframes disappear {
      0% {
        opacity: 1;
      }
      100% {
        opacity: 0;
      }
    }

    @keyframes fadeInOut {
      0% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }

      100% {
        opacity: 1;
      }
    }

    @keyframes borderAnimation {
      0% {
        background-position: 0% 50%;
      }

      100% {
        background-position: 100% 50%;
      }
    }
    .card {
            margin-top: 40px;
            padding: 30px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        h2.card-title strong {
            font-weight: bold;
            color: #333;
        }
  </style>
<body>
    <div class="wrapper mb-5" style="display: flex; flex-direction: column;">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-2">
            <div class="container-fluid">
                <a class="navbar-brand" href="http://www.algonquincollege.com" style="padding: 10px">
                    <img src="Common/img/AC2.png" alt="Algonquin College" style="max-height: 30px; width:auto;"/>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav w-100 justify-content-around">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="Index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="MyFriends.php">My Friends</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="MyAlbums.php">My Albums</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="MyPictures.php">My Pictures</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="UploadPictures.php">Upload Pictures</a>
                        </li>
                        <li class="nav-item ms-auto">
                            <?php if (isset($_SESSION['user'])): ?>
                                <a class="nav-link" href="Logout.php">Log Out</a>
                            <?php else: ?>
                                <a class="nav-link" href="Login.php">Log In</a>
                            <?php endif; ?>
                        </li>
                    </ul>
                </div>
            </div>  
        </nav>
        <div class="content px-4">
