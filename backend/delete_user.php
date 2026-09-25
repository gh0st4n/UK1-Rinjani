
<?php
require_once "database/connection.php";
require_once "classes/Auth.php";
require_once "classes/User.php";

$db = (new Database())->getConnection();
$auth = new Auth($db);

// Hanya role admin yang boleh menghapus user
$auth->checkRole(['admin']);

// Pastikan ada parameter ID di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $userObj = new User($db);

    if ($userObj->delete($id)) {
        // DIPERBARUI: Diarahkan ke tabel_user.php
        header("Location: tabel_user.php?status=deleted");
        exit();
    } else {
        // DIPERBARUI: Diarahkan ke tabel_user.php
        header("Location: tabel_user.php?status=error");
        exit();
    }
} else {
    // DIPERBARUI: Diarahkan ke tabel_user.php
    header("Location: tabel_user.php");
    exit();
}
