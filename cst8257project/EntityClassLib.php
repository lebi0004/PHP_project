<?php

include_once 'functions.php';
class User {
    private $userId;
    private $name;
    private $phone;
    private $password; // Add this property to store the hashed password

    // Constructor to initialize the User object
    public function __construct($userId, $name, $phone, $password) {
        $this->userId = $userId;
        $this->name = $name;
        $this->phone = $phone;
        $this->password = $password; // Store the hashed password
    }

    // Getter methods to retrieve user properties
    public function getUserId() {
        return $this->userId;
    }

    public function getName() {
        return $this->name;
    }

    public function getPhone() {
        return $this->phone;
    }

    // Getter for the hashed password
    public function getPassword() {
        return $this->password;
    }

    public function fetchAllAlbums() {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM album WHERE Owner_Id = ?");
        $stmt->execute([$this->userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $albums = [];
        foreach ($result as $row) {
            $albums[] = new Album($row['Title'], $row['Description'], $row['Accessibility_Code'], $row['Owner_Id'], $row['Album_Id']);
        }
        return $albums;
    }
}

class Album {
    private $albumId;
    private $title;
    private $description;
    private $accessibilityCode;
    private $ownerId;

    public function __construct($title, $description, $accessibilityCode, $ownerId, $albumId = null) {
        $this->albumId = $albumId;
        $this->title = $title;
        $this->description = $description;
        $this->accessibilityCode = $accessibilityCode;
        $this->ownerId = $ownerId;
    }

    public function getAlbumId() {
        return $this->albumId;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getAccessibilityCode() {
        return $this->accessibilityCode;
    }

    public function getOwnerId() {
        return $this->ownerId;
    }

    public function setAlbumId($albumId) {
        $this->albumId = $albumId;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setAccessibilityCode($accessibilityCode) {
        $this->accessibilityCode = $accessibilityCode;
    }

    public function setOwnerId($ownerId) {
        $this->ownerId = $ownerId;
    }


    public function create() {
        $pdo = getPDO();
        $sql = "INSERT INTO Album (Title, Description, Accessibility_Code, Owner_Id) 
                VALUES (:title, :description, :accessibility, :owner_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'title' => $this->title,
            'description' => $this->description,
            'accessibility' => $this->accessibilityCode,
            'owner_id' => $this->ownerId
        ]);

        if ($stmt->execute()) {
            $this->albumId = $pdo->lastInsertId();
        } else {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Error creating album: " . $errorInfo[2]);
        }
    }


    public static function read($albumId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM album WHERE Album_Id = ?");
        $stmt->execute([$albumId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $album = new Album($row['Title'], $row['Description'], $row['Accessibility_Code'], $row['Owner_Id'], $row['Album_Id']);
        } else {
            throw new Exception("Album not found.");
        }
        return $album;
    }


    public function update() {
        if ($this->albumId === null) {
            throw new Exception("Cannot update album without an Album_Id.");
        }

        $pdo = getPDO();
        $stmt = $pdo->prepare("UPDATE album SET Title = ?, Description = ?, Accessibility_Code = ?, Owner_Id = ? WHERE Album_Id = ?");
        if (!$stmt->execute([$this->title, $this->description, $this->accessibilityCode, $this->ownerId, $this->albumId])) {
            throw new Exception("Error updating album: " . $stmt->errorInfo());
        }
    }

    public static function delete($albumId) {
        $pdo = getPDO();
        $album = Album::read($albumId);
        $pictures = $album->fetchAllPictures();
        if (count($pictures) > 0) {
            foreach ($pictures as $picture) {
                Picture::delete($picture->getPictureId());
            }
        }
        $stmt = $pdo->prepare("DELETE FROM album WHERE Album_Id = ?");
        if (!$stmt->execute([$albumId])) {
            throw new Exception("Error deleting album: " . $stmt->errorInfo());
        }
    }


    public function fetchAllPictures() {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM picture WHERE Album_Id = ?");
        $stmt->execute([$this->albumId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pictures = [];
        foreach ($result as $row) {
            $pictures[] = new Picture($row['File_Name'], $row['Album_Id'], $row['Title'], $row['Description'], $row['Picture_Id']);
        }
        return $pictures;
    }
}


class Picture {

    private $pictureId;
    private $albumId;
    private $fileName;
    private $title;
    private $description;
    private $comments;

    public function __construct($fileName, $albumId, $title = null, $description = null, $pictureId = null) {
        $this->pictureId = $pictureId; 
        $this->albumId = $albumId;
        $this->fileName = $fileName;
        $this->title = $title;
        $this->description = $description;
    }

    public function getPictureId() {
        return $this->pictureId;
    }

    public function getAlbumId() {
        return $this->albumId;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getComments() {
        return $this->comments;
    }

    public function setPictureId($pictureId) {
        $this->pictureId = $pictureId;
    }


    public function create() {
        $pdo = getPDO();
        $stmt = $pdo->prepare("INSERT INTO picture (Album_Id, File_Name, Title, Description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$this->albumId, $this->fileName, $this->title, $this->description]);
        if ($stmt->rowCount() > 0) {
            $this->pictureId = $pdo->lastInsertId();
        } else {
            throw new Exception("Error creating picture: " . $stmt->errorInfo());
        }
    }

    public function saveToUploadFolder($tmpFilePath, $albumId) {
        $uploadDir = "./uploads/album_$albumId/";

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $uniqueFileName = uniqid() . "_" . basename($this->fileName);
        $destination = $uploadDir . $uniqueFileName;

        if (!move_uploaded_file($tmpFilePath, $destination)) {
            throw new Exception("Failed to upload file.");
        }
        $this->fileName = $uniqueFileName;

        return $destination;
    }

    public function getFilePath() {
        return "uploads/album_{$this->albumId}/" . $this->fileName;
    }


    public static function read($pictureId) {
        $pdo = getPDO();
        $stmt = $pdo->prepare("SELECT * FROM picture WHERE Picture_Id = ?");
        $stmt->execute([$pictureId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $picture = new Picture($row['File_Name'], $row['Album_Id'], $row['Title'], $row['Description'], $row['Picture_Id']);
        } else {
            throw new Exception("Picture not found.");
        }

        return $picture;
    }


    public static function delete($pictureId) {
        $pdo = getPDO();
        $picture = Picture::read($pictureId);
        $filePath = $picture->getFilePath();
        $stmt = $pdo->prepare("DELETE FROM picture WHERE Picture_Id = ?");
        $stmt->execute([$pictureId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Error deleting picture: " . $stmt->errorInfo());
        }

        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                throw new Exception("Error deleting picture file from the file system.");
            }
        }
    }
}