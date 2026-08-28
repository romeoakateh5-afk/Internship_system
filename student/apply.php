<?php
session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Check if student is logged in
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['user_id'])) {
    die("Please login first.");
}

$user_id = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Get the internship ID from the URL
|--------------------------------------------------------------------------
| Example: apply.php?offer_id=2
*/
if (!isset($_GET['offer_id']) || !is_numeric($_GET['offer_id'])) {
    die("Invalid internship ID.");
}

$internship_id = (int) $_GET['offer_id'];

/*
|--------------------------------------------------------------------------
| Get the student's profile
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT id, user_id, student_id
    FROM student
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student profile not found. Please complete your profile first.");
}

$student_id = $student['id'];

/*
|--------------------------------------------------------------------------
| Get the internship
|--------------------------------------------------------------------------
*/
$stmt = $pdo->prepare("
    SELECT *
    FROM internship
    WHERE id = ?
");

$stmt->execute([$internship_id]);

$internship = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$internship) {
    die("Internship opportunity not found.");
}

/*
|--------------------------------------------------------------------------
| Process application
|--------------------------------------------------------------------------
*/
$message = "";
$messageType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    | Check if student already applied
    */
    $check = $pdo->prepare("
        SELECT id
        FROM students_applications
        WHERE student_id = ? AND offer_id = ?
    ");

    $check->execute([
        $student_id,
        $internship_id
    ]);

    if ($check->fetch()) {

        $message = "You have already applied for this internship.";
        $messageType = "warning";

    } else {

        /*
        | Insert application
        */
        $stmt = $pdo->prepare("
            INSERT INTO students_applications
            (student_id, offer_id, application_date, status)
            VALUES (?, ?, NOW(), 'pending')
        ");

        $stmt->execute([
            $student_id,
            $internship_id
        ]);

        $message = "Application submitted successfully!";
        $messageType = "success";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Apply for Internship</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f7fb;
            padding: 40px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        h1 {
            color: #1f4e79;
            margin-bottom: 25px;
        }

        .internship-info {
            margin-bottom: 25px;
        }

        .internship-info p {
            margin: 12px 0;
            color: #444;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .success {
            background: #d4edda;
            color: #155724;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
        }

        .apply-btn {
            background: #1f4e79;
            color: white;
            border: none;
            padding: 14px 25px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 16px;
        }

        .apply-btn:hover {
            background: #163a5c;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #1f4e79;
        }
    </style>
</head>

<body>

<div class="container">

    <h1>Apply for Internship</h1>

    <?php if ($message): ?>
        <div class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="internship-info">

        <p>
            <strong>Title:</strong>
            <?php echo htmlspecialchars($internship['title']); ?>
        </p>

        <p>
            <strong>Description:</strong>
            <?php echo htmlspecialchars($internship['description']); ?>
        </p>

        <p>
            <strong>Location:</strong>
            <?php echo htmlspecialchars($internship['location']); ?>
        </p>

        <p>
            <strong>Duration:</strong>
            <?php echo htmlspecialchars($internship['duration']); ?>
        </p>

        <p>
            <strong>Status:</strong>
            <?php echo htmlspecialchars($internship['status']); ?>
        </p>

    </div>

    <?php if (!$message || $messageType === "warning"): ?>

        <form method="POST">

            <button type="submit" class="apply-btn">
                Apply Now
            </button>

        </form>

    <?php endif; ?>

    <a href="internships.php" class="back">
        ← Back to Internship Opportunities
    </a>

</div>

</body>
</html>