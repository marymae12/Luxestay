<<<<<<< HEAD
<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        redirect('user/index.php');
    } else {
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'user') {
                redirect('user/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            redirect('login.html?error=' . urlencode("Invalid username or password."));
        }
    } else {
        redirect('login.html?error=' . urlencode("Please fill in all fields."));
    }
} else {
    // If accessed directly via GET, redirect to the HTML view
    redirect('login.html');
}
=======
<?php
session_start();
require_once 'config.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        redirect('user/index.php');
    } else {
        redirect('index.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            if ($user['role'] === 'user') {
                redirect('user/index.php');
            } else {
                redirect('index.php');
            }
        } else {
            redirect('login.html?error=' . urlencode("Invalid username or password."));
        }
    } else {
        redirect('login.html?error=' . urlencode("Please fill in all fields."));
    }
} else {
    // If accessed directly via GET, redirect to the HTML view
    redirect('login.html');
}
>>>>>>> 665f4a3a3c4c4205492a17f248bce813b22a689e
?>