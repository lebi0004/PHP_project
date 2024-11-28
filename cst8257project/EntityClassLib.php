<?php

include_once 'functions.php';

class User {
    private $userId;
    private $name;
    private $phone;
    private $password;

    public function __construct($userId, $name, $phone, $password) {
        $this->userId = $userId;
        $this->name = $name;
        $this->phone = $phone;
        $this->password = $password;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getName() {
        return $this->name;
    }

    public function getPhone() {
        return $this->phone;
    }

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
        $sql = "INSERT INTO Album (Title, Description, Accessibility_Code, Owner_Id) VALUES (:title, :description, :accessibility, :owner_id)";
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
