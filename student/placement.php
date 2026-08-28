<?php
session_start();

require_once "../config/database.php";

/* Check if student is logged in */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../home/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get student profile */
$stmt = $pdo->prepare("
    SELECT id, student_id, school, program, level, phone
    FROM student
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student profile not found.");
}

$student_profile_id = $student["id"];

/* Get student's placement */
$stmt = $pdo->prepare("
    SELECT
        p.id AS placement_id,
        p.start_date,
        p.end_date,
        p.status,

        c.company_name,

        i.title AS internship_title,
        i.description,
        i.location,
        i.duration

    FROM placement p

    INNER JOIN company c
        ON p.company_id = c.id

    INNER JOIN internship i
        ON p.offer_id = i.id

    WHERE p.student_id = ?

    ORDER BY p.id DESC
");

$stmt->execute([$student_profile_id]);

$placements = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Placement</title>

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

/* LOGOUT */

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

.student-info {
    background: white;
    padding: 10px 16px;

    border-radius: 8px;

    box-shadow: 0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}

/* CARD */

.placement-card {
    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow: 0 3px 12px rgba(0,0,0,0.06);

    margin-bottom: 25px;
}

.placement-card h2 {
    color: #1f4e79;
    margin-bottom: 8px;
}

.description {
    color: #777;
    margin-bottom: 25px;
}

/* PLACEMENT DETAILS */

.details {
    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 18px;
}

.detail-box {
    background: #f8fafc;

    padding: 18px;

    border-radius: 8px;

    border: 1px solid #edf0f4;
}

.detail-box strong {
    display: block;

    color: #666;

    font-size: 13px;

    margin-bottom: 7px;
}

.detail-box span {
    color: #222;

    font-size: 15px;

    font-weight: 500;
}

/* STATUS */

.status {
    display: inline-block;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

    text-transform: capitalize;
}

.active {
    background: #e4f7eb;
    color: #207a43;
}

.completed {
    background: #e5eef8;
    color: #245985;
}

.cancelled {
    background: #fde8e8;
    color: #a83232;
}

/* EMPTY */

.empty {
    text-align: center;

    padding: 60px 20px;
}

.empty-icon {
    font-size: 50px;

    margin-bottom: 15px;
}

.empty h2 {
    color: #666;

    margin-bottom: 10px;
}

.empty p {
    color: #888;

    font-size: 14px;
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

    .top-area {
        flex-direction: column;

        align-items: flex-start;

        gap: 15px;
    }

    .details {
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


        <a href="profile.php">

            <span class="icon">👤</span>

            <span>My Profile</span>

        </a>


        <a href="internships.php">

            <span class="icon">📋</span>

            <span>Internships</span>

        </a>


        <a href="applications.php">

            <span class="icon">📄</span>

            <span>My Applications</span>

        </a>


        <a href="placement.php" class="active">

            <span class="icon">🎓</span>

            <span>My Placement</span>

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

        <h1>My Placement</h1>


        <div class="student-info">

            Student ID:

            <strong>
                <?php echo htmlspecialchars($student["student_id"]); ?>
            </strong>

        </div>

    </div>


    <?php if (empty($placements)): ?>


        <div class="placement-card">

            <div class="empty">

                <div class="empty-icon">
                    🎓
                </div>

                <h2>No Placement Found</h2>

                <p>
                    You have not been placed in an internship yet.
                    Once a company accepts your application,
                    your placement information will appear here.
                </p>

            </div>

        </div>


    <?php else: ?>


        <?php foreach ($placements as $placement): ?>


            <div class="placement-card">

                <h2>
                    Internship Placement
                </h2>

                <p class="description">
                    Your internship placement information.
                </p>


                <div class="details">


                    <div class="detail-box">

                        <strong>Company</strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $placement["company_name"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Internship</strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $placement["internship_title"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Location</strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $placement["location"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Duration</strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $placement["duration"]
                            );
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Start Date</strong>

                        <span>
                            <?php
                            echo !empty($placement["start_date"])
                                ? htmlspecialchars($placement["start_date"])
                                : "Not specified";
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>End Date</strong>

                        <span>
                            <?php
                            echo !empty($placement["end_date"])
                                ? htmlspecialchars($placement["end_date"])
                                : "Not specified";
                            ?>
                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Status</strong>

                        <span>

                            <span class="status
                                <?php
                                echo htmlspecialchars(
                                    strtolower($placement["status"])
                                );
                                ?>">

                                <?php
                                echo ucfirst(
                                    htmlspecialchars(
                                        $placement["status"]
                                    )
                                );
                                ?>

                            </span>

                        </span>

                    </div>


                    <div class="detail-box">

                        <strong>Placement ID</strong>

                        <span>
                            <?php
                            echo htmlspecialchars(
                                $placement["placement_id"]
                            );
                            ?>
                        </span>

                    </div>


                </div>

            </div>


        <?php endforeach; ?>


    <?php endif; ?>


</div>

</body>

</html>