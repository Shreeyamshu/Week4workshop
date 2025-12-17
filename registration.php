<?php

$errors = [];
$success = "";
$name = $email = "";


$file = "users.json";

if (!file_exists($file)) {
    file_put_contents($file, json_encode([]));
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";


    if (empty($name)) {
        $errors["name"] = "Name is required.";
    }

    if (empty($email)) {
        $errors["email"] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Invalid email format.";
    }

    if (empty($password)) {
        $errors["password"] = "Password is required.";
    } elseif (!preg_match('/^(?=.*[^a-zA-Z0-9]).{8,}$/', $password)) {
        $errors["password"] = "Password must be at least 8 characters and contain a special character.";
    }

    if ($password !== $confirm) {
        $errors["confirm"] = "Passwords do not match.";
    }

    
    if (empty($errors)) {
        try {
            $json = file_get_contents($file);
            $users = json_decode($json, true);

            if (!is_array($users)) {
                $users = [];
            }

          
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            
            $newUser = [
                "name" => $name,
                "email" => $email,
                "password" => $hashedPassword
            ];

        
            $users[] = $newUser;

            
            if (file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT))) {
                $success = "Registration successful!";
                $name = $email = "";
            } else {
                $errors["file"] = "Error writing to users.json.";
            }
        } catch (Exception $e) {
            $errors["file"] = "File handling error.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Registration</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .error { color: red; }
        .success { color: green; margin-bottom: 15px;}
        .field { margin-bottom: 10px; }
    </style>
</head>
<body>

<h2>User Registration</h2>

<?php if (!empty($success)): ?>
    <div class="success"><?= $success ?></div>
<?php endif; ?>

<?php if (isset($errors["file"])): ?>
    <div class="error"><?= $errors["file"] ?></div>
<?php endif; ?>

<form method="POST" action="">
    <div class="field">
        <label>Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">
        <div class="error"><?= $errors["name"] ?? "" ?></div>
    </div>

    <div class="field">
        <label>Email:</label><br>
        <input type="text" name="email" value="<?= htmlspecialchars($email) ?>">
        <div class="error"><?= $errors["email"] ?? "" ?></div>
    </div>

    <div class="field">
        <label>Password:</label><br>
        <input type="password" name="password">
        <div class="error"><?= $errors["password"] ?? "" ?></div>
    </div>

    <div class="field">
        <label>Confirm Password:</label><br>
        <input type="password" name="confirm_password">
        <div class="error"><?= $errors["confirm"] ?? "" ?></div>
    </div>

    <button type="submit">Register</button>
</form>

</body>
</html>
