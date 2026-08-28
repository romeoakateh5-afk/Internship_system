<?php
session_start();

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "company"
) {
    header("Location: ../home/login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];
$name = $_SESSION["name"] ?? "";

$message = "";
$messageType = "";


/*
|--------------------------------------------------------------------------
| GET COMPANY PROFILE
|--------------------------------------------------------------------------
| We must use company.id as internship.company_id.
| We must NOT use users.user_id directly.
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id, user_id, company_name
    FROM company
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Check company profile
|--------------------------------------------------------------------------
*/

if (!$company) {

    $message = "Company profile not found. Please complete your company profile first.";
    $messageType = "error";

} else {

    $company_id = $company["id"];


    /*
    |--------------------------------------------------------------------------
    | HANDLE FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $duration = trim($_POST["duration"] ?? "");


        if (
            empty($title) ||
            empty($description) ||
            empty($location) ||
            empty($duration)
        ) {

            $message = "Please fill in all fields.";
            $messageType = "error";

        } else {

            try {

                /*
                |--------------------------------------------------------------------------
                | INSERT INTERNSHIP
                |--------------------------------------------------------------------------
                | IMPORTANT:
                | Use $company_id, NOT $_SESSION["user_id"]
                |--------------------------------------------------------------------------
                */

                $stmt = $pdo->prepare("
                    INSERT INTO internship
                    (
                        company_id,
                        title,
                        description,
                        location,
                        duration
                    )
                    VALUES
                    (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $company_id,
                    $title,
                    $description,
                    $location,
                    $duration
                ]);


                $message = "Internship opportunity posted successfully!";
                $messageType = "success";


                /*
                |--------------------------------------------------------------------------
                | Clear form after successful submission
                |--------------------------------------------------------------------------
                */

                $_POST = [];


            } catch (PDOException $e) {

                $message = "Unable to post internship: " . $e->getMessage();
                $messageType = "error";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Post Internship - Internship System</title>

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


/* =========================
   SIDEBAR
========================= */

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


/* =========================
   LOGOUT
========================= */

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


/* =========================
   MAIN CONTENT
========================= */

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

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}


/* =========================
   FORM CARD
========================= */

.form-card {
    background: white;

    max-width: 850px;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);
}

.form-card h2 {
    color: #1f4e79;

    margin-bottom: 8px;
}

.form-card .description {
    color: #777;

    font-size: 14px;

    margin-bottom: 25px;
}


/* =========================
   MESSAGE
========================= */

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


/* =========================
   FORM
========================= */

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;

    margin-bottom: 8px;

    font-size: 14px;

    font-weight: bold;

    color: #444;
}

.form-group input,
.form-group textarea {
    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 7px;

    outline: none;

    font-size: 14px;

    font-family: Arial, sans-serif;
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: #1f4e79;
}

.form-group textarea {
    min-height: 130px;

    resize: vertical;
}


/* =========================
   BUTTON
========================= */

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


/* =========================
   MOBILE
========================= */

@media (max-width: 768px) {

    .sidebar {
        width: 210px;
    }

    .main-content {
        margin-left: 210px;

        padding: 20px;
    }

    .top-area {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

}

</style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================= -->

<div class="sidebar">

    <div class="sidebar-header">

        <h2>Internship System</h2>

        <p>Company Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="profile.php">

            <span class="icon">🏢</span>

            <span>Company Profile</span>

        </a>


        <a href="post_internship.php" class="active">

            <span class="icon">➕</span>

            <span>Post Internship</span>

        </a>


        <a href="applications.php">

            <span class="icon">📄</span>

            <span>Applications</span>

        </a>


        <a href="students.php">

            <span class="icon">🎓</span>

            <span>Placed Students</span>

        </a>


        <a href="supervision.php">

            <span class="icon">👨‍🏫</span>

            <span>Supervision</span>

        </a>


        <a href="evaluation.php">

            <span class="icon">⭐</span>

            <span>Evaluation</span>

        </a>


    </div>


    <div class="logout-section">

        <a href="../home/logout.php" class="logout">

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>

</div>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="main-content">


    <div class="top-area">

        <h1>Post Internship</h1>


        <div class="user-info">

            <?php echo htmlspecialchars($name); ?>

        </div>

    </div>


    <div class="form-card">


        <h2>Create Internship Opportunity</h2>


        <p class="description">

            Provide the details of the internship opportunity
            you want to offer to students.

        </p>


        <?php if (!empty($message)): ?>

            <div class="message <?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <?php if ($company): ?>

        <form method="POST" action="">


            <div class="form-group">

                <label for="title">
                    Internship Title
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Example: Software Engineering Intern"
                    required
                >

            </div>


            <div class="form-group">

                <label for="description">
                    Internship Description
                </label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Describe the internship opportunity..."
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label for="location">
                    Location
                </label>

                <input
                    type="text"
                    id="location"
                    name="location"
                    placeholder="Example: Buea, Cameroon"
                    required
                >

            </div>


            <div class="form-group">

                <label for="duration">
                    Duration
                </label>

                <input
                    type="text"
                    id="duration"
                    name="duration"
                    placeholder="Example: 2 months"
                    required
                >

            </div>


            <button
                type="submit"
                class="submit-button"
            >
                Post Internship
            </button>


        </form>

        <?php endif; ?>


    </div>


</div>


</body>

</html>