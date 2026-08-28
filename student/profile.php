<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../home/login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$message = "";
$messageType = "";

/* Check if student profile already exists */
$stmt = $pdo->prepare("
    SELECT *
    FROM student
    WHERE user_id = ?
");
$stmt->execute([$user_id]);

$profile = $stmt->fetch(PDO::FETCH_ASSOC);


/* Save profile */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $student_id = trim($_POST["student_id"] ?? "");
    $school = trim($_POST["school"] ?? "");
    $program = trim($_POST["program"] ?? "");
    $level = trim($_POST["level"] ?? "");
    $phone = trim($_POST["phone"] ?? "");

    if (
        
        empty($student_id) ||
        empty($school) ||
        empty($program) ||
        empty($level) ||
        empty($phone)
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } else {

        try {

            if ($profile) {

                /* Update existing profile */

                $stmt = $pdo->prepare("
                    UPDATE student
                    SET 
                    student_id = ?,
                        school = ?,
                        program = ?,
                        level = ?,
                        phone = ?
                    WHERE user_id = ?
                ");

                $stmt->execute([
                    $student_id,
                    $school,
                    $program,
                    $level,
                    $phone,
                    
                ]);

                $message = "Student profile updated successfully!";
                $messageType = "success";

            } else {

                /* Create new profile */

                $stmt = $pdo->prepare("
                    INSERT INTO student
                    (user_id, student_id, school, program, level, phone)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $user_id,
                    $student_id,
                    $school,
                    $program,
                    $level,
                    $phone
                ]);

                $message = "Student profile created successfully!";
                $messageType = "success";
            }

            /* Reload profile */

            $stmt = $pdo->prepare("
                SELECT *
                FROM student
                WHERE user_id = ?
            ");

            $stmt->execute([$user_id]);

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            $message = "Unable to save profile: " . $e->getMessage();
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

<title>Student Profile - Internship System</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #333;
}

/* SIDEBAR */

.sidebar {
    position: fixed;
    left: 0;
    top: 0;

    width: 250px;
    height: 100vh;

    background: #1f4e79;
    color: white;

    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.15);
}

.sidebar-header h2 {
    font-size: 22px;
    margin-bottom: 5px;
}

.sidebar-header p {
    font-size: 13px;
    opacity: 0.8;
}

.navigation {
    padding: 20px 12px;
    flex: 1;
}

.navigation a {
    display: flex;
    align-items: center;
    gap: 12px;

    color: white;
    text-decoration: none;

    padding: 13px 15px;
    margin-bottom: 6px;

    border-radius: 7px;

    transition: 0.2s;
}

.navigation a:hover {
    background: rgba(255,255,255,0.13);
}

.navigation a.active {
    background: rgba(255,255,255,0.20);
}

.icon {
    width: 25px;
    text-align: center;
}

.logout-section {
    padding: 15px 12px;
    border-top: 1px solid rgba(255,255,255,0.15);
}

.logout {
    display: flex;
    align-items: center;
    gap: 12px;

    color: white;
    text-decoration: none;

    padding: 13px 15px;

    border-radius: 7px;
}

.logout:hover {
    background: rgba(255,255,255,0.13);
}

/* MAIN */

.main-content {
    margin-left: 250px;
    padding: 35px;
    min-height: 100vh;
}

.top-area {
    display: flex;
    justify-content: space-between;
    align-items: center;

    margin-bottom: 30px;
}

.top-area h1 {
    color: #1f4e79;
    font-size: 28px;
}

.user-info {
    background: white;

    padding: 10px 16px;

    border-radius: 8px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}

/* PROFILE CARD */

.profile-card {
    background: white;

    max-width: 850px;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.06);
}

.profile-card h2 {
    color: #1f4e79;
    margin-bottom: 8px;
}

.profile-card > p {
    color: #777;
    font-size: 14px;
    margin-bottom: 25px;
}

/* MESSAGE */

.message {
    padding: 13px 15px;
    border-radius: 7px;
    margin-bottom: 20px;
    font-size: 14px;
}

.success {
    background: #e8f7ee;
    color: #217a45;
    border: 1px solid #b9e5ca;
}

.error {
    background: #fdecec;
    color: #a83232;
    border: 1px solid #f2bcbc;
}

/* FORM */

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: bold;
}

.form-group input,
.form-group select {
    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;

    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus {
    border-color: #1f4e79;
}

.form-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;
}

.submit-button {
    background: #1f4e79;

    color: white;

    border: none;

    padding: 13px 22px;

    border-radius: 7px;

    font-size: 15px;

    cursor: pointer;
}

.submit-button:hover {
    opacity: 0.9;
}

/* MOBILE */

@media (max-width: 768px) {

    .sidebar {
        width: 210px;
    }

    .main-content {
        margin-left: 210px;
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-header">

        <h2>Internship System</h2>

        <p>Student Portal</p>

    </div>


    <div class="navigation">

        <a href="dashboard.php">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="profile.php" class="active">

            <span class="icon">👤</span>

            <span>My Profile</span>

        </a>


        <a href="internships.php">

            <span class="icon">🔎</span>

            <span>Find Internship</span>

        </a>


        <a href="applications.php">

            <span class="icon">📄</span>

            <span>My Applications</span>

        </a>


        <a href="supervision.php">

            <span class="icon">👨‍🏫</span>

            <span>Supervision</span>

        </a>


        <a href="feedback.php">

            <span class="icon">💬</span>

            <span>Feedback</span>

        </a>

    </div>


    <div class="logout-section">

        <a href="../home/logout.php" class="logout">

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>

</div>


<!-- MAIN CONTENT -->

<div class="main-content">

    <div class="top-area">

        <h1>My Profile</h1>

        <div class="user-info">

            <?php echo htmlspecialchars($_SESSION["name"]); ?>

        </div>

    </div>


    <div class="profile-card">

        <h2>Student Information</h2>

        <p>
            Complete your student profile before applying
            for internship opportunities.
        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo $messageType; ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="">


            <div class="form-grid">
                

                <div class="form-group">

                    <label for="student_id">
                        Student ID
                    </label>

                    <input
                        type="text"
                        id="student_id"
                        name="student_id"
                        value="<?php echo htmlspecialchars($profile["student_id"] ?? ""); ?>"
                        placeholder="Enter your student ID"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="school">
                        School
                    </label>

                    <input
                        type="text"
                        id="school"
                        name="school"
                        value="<?php echo htmlspecialchars($profile["school"] ?? ""); ?>"
                        placeholder="Enter your school"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="program">
                        Program
                    </label>

                    <input
                        type="text"
                        id="program"
                        name="program"
                        value="<?php echo htmlspecialchars($profile["program"] ?? ""); ?>"
                        placeholder="Example: Software Engineering"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="level">
                        Level
                    </label>

                    <select id="level" name="level" required>

                        <option value="">Select Level</option>

                        <option value="Level 1"
                        <?php echo (($profile["level"] ?? "") === "Level 1") ? "selected" : ""; ?>>
                            Level 1
                        </option>

                        <option value="Level 2"
                        <?php echo (($profile["level"] ?? "") === "Level 2") ? "selected" : ""; ?>>
                            Level 2
                        </option>

                        <option value="Level 3"
                        <?php echo (($profile["level"] ?? "") === "Level 3") ? "selected" : ""; ?>>
                            Level 3
                        </option>

                        <option value="Level 4"
                        <?php echo (($profile["level"] ?? "") === "Level 4") ? "selected" : ""; ?>>
                            Level 4
                        </option>

                    </select>

                </div>

            </div>


            <div class="form-group">

                <label for="phone">
                    Phone 
                </label>

                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    value="<?php echo htmlspecialchars($profile["phone"] ?? ""); ?>"
                    placeholder="Enter your phone number"
                    required
                >

            </div>


            <button
                type="submit"
                class="submit-button"
            >
                Save Profile
            </button>


        </form>

    </div>

</div>

</body>

</html>