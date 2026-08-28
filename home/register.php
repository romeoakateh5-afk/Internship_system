
<?php

require_once "../config/database.php";

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "";

    // Check that all fields were received
    if ($name === "" || $email === "" || $password === "" || $role === "") {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } else {

        try {

            // Check if email already exists
            $check = $pdo->prepare(
                "SELECT id FROM user_s WHERE email = ?"
            );

            $check->execute([$email]);

            if ($check->fetch()) {

                $message = "Email already exists.";
                $messageType = "error";

            } else {

                // Hash password
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert user
                $stmt = $pdo->prepare(
                    "INSERT INTO user_s (name, email, password, role)
                     VALUES (?, ?, ?, ?)"
                );

                $stmt->execute([
                    $name,
                    $email,
                    $hashedPassword,
                    $role
                ]);

                $message = "Account created successfully!";
                $messageType = "success";
            }

        } catch (PDOException $e) {

            $message = "Database error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Internship System</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            min-height: 100vh;
            background: #f4f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .message {
    padding: 12px;
    margin-bottom: 20px;
    border-radius: 7px;
    text-align: center;
    font-size: 14px;
}

.message.success {
    background: #e8f5e9;
    color: #2e7d32;
}

.message.error {
    background: #ffebee;
    color: #c62828;
}

        .register-container {
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo h1 {
            font-size: 26px;
            color: #1e3a8a;
            margin-bottom: 8px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 7px;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1e3a8a;
        }

        .register-btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 7px;
            background: #1e3a8a;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .register-btn:hover {
            background: #172f70;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #1e3a8a;
            text-decoration: none;
            font-weight: bold;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="register-container">

        <?php if (!empty($message)): ?>
    <div class="message">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

        <div class="logo">
            <h1>Internship System</h1>
            <p>Create your account</p>
        </div>

        <form action="" method="POST">

            <div class="form-group">
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter your full name"
                    required
                >
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Create a password"
                    required
                >
            </div>

            <div class="form-group">
                <label for="role">Register As</label>

                <select id="role" name="role" required>
                    <option value="">Select your role</option>
                    <option value="student">Student</option>
                    <option value="company">Company</option>
                    <option value="supervisor">Supervisor</option>
                </select>
            </div>

            <button type="submit" class="register-btn">
                Create Account
            </button>

        </form>

        <div class="login-link">
            Already have an account?
            <a href="login.php">Login</a>
        </div>

    </div>

</body>
</html>