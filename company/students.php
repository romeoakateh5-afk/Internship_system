<?php
session_start();

require_once "../config/database.php";


/* =========================
   CHECK COMPANY LOGIN
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "company"
) {
    header("Location: ../home/login.php");
    exit;
}


$user_id = $_SESSION["user_id"];

$company = null;
$students = [];
$error = "";


/* =========================
   GET COMPANY PROFILE
========================= */

$stmt = $pdo->prepare("
    SELECT id, user_id, company_name
    FROM company
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$company = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$company) {

    $error = "Company profile not found.";

} else {

    $company_id = $company["id"];


    /* =========================
       GET ACCEPTED STUDENTS
    ========================= */

    $stmt = $pdo->prepare("
        SELECT
            sa.id AS application_id,
            sa.application_date,
            sa.status,

            s.id AS student_profile_id,
            s.student_id,
            s.school,
            s.program,
            s.level,
            s.phone,

            i.id AS internship_id,
            i.title AS internship_title,
            i.location,
            i.duration

        FROM students_applications sa

        INNER JOIN student s
            ON sa.student_id = s.id

        INNER JOIN internship i
            ON sa.offer_id = i.id

        WHERE i.company_id = ?
        AND sa.status = 'accepted'

        ORDER BY sa.application_date DESC
    ");

    $stmt->execute([$company_id]);

    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Placed Students - Internship System</title>


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

    border-bottom:
        1px solid rgba(255,255,255,0.15);
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

    background:
        rgba(255,255,255,0.13);
}


.navigation a.active {

    background:
        rgba(255,255,255,0.20);
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

    border-top:
        1px solid rgba(255,255,255,0.15);
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

    background:
        rgba(255,255,255,0.13);
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


.company-info {

    background: white;

    padding: 10px 16px;

    border-radius: 8px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}


/* =========================
   CARD
========================= */

.content-card {

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    overflow-x: auto;
}


.content-card h2 {

    color: #1f4e79;

    margin-bottom: 8px;
}


.description {

    color: #777;

    margin-bottom: 25px;

    font-size: 14px;
}


/* =========================
   TABLE
========================= */

table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1000px;
}


th {

    background: #1f4e79;

    color: white;

    padding: 14px;

    text-align: left;

    font-size: 14px;
}


td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    font-size: 14px;

    vertical-align: top;
}


tr:hover {

    background: #f8fafc;
}


/* =========================
   STATUS
========================= */

.status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    background: #e4f7eb;

    color: #207a43;

    font-size: 12px;

    font-weight: bold;
}


/* =========================
   EMPTY
========================= */

.empty {

    text-align: center;

    padding: 70px 20px;
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
}


/* =========================
   ERROR
========================= */

.error {

    background: #fdecec;

    color: #a83232;

    padding: 15px;

    border-radius: 8px;

    margin-bottom: 20px;
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


        <a href="post_internship.php">

            <span class="icon">➕</span>

            <span>Post Internship</span>

        </a>


        <a href="applications.php">

            <span class="icon">📄</span>

            <span>Applications</span>

        </a>


        <a href="students.php"
           class="active">

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

        <a href="../home/logout.php"
           class="logout">

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


        <h1>Placed Students</h1>


        <?php if ($company): ?>

            <div class="company-info">

                <?php
                echo htmlspecialchars(
                    $company["company_name"]
                );
                ?>

            </div>

        <?php endif; ?>


    </div>


    <?php if ($error): ?>


        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>


    <?php elseif (empty($students)): ?>


        <div class="content-card">


            <div class="empty">


                <div class="empty-icon">
                    🎓
                </div>


                <h2>No Placed Students Yet</h2>


                <p>
                    Students whose applications are accepted
                    will appear here.
                </p>


            </div>


        </div>


    <?php else: ?>


        <div class="content-card">


            <h2>Accepted Students</h2>


            <p class="description">

                Students whose internship applications
                have been accepted by your company.

            </p>


            <table>


                <thead>

                    <tr>

                        <th>Student ID</th>

                        <th>School</th>

                        <th>Program</th>

                        <th>Level</th>

                        <th>Phone</th>

                        <th>Internship</th>

                        <th>Location</th>

                        <th>Duration</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($students as $student): ?>


                        <tr>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["student_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["school"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["program"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["level"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["phone"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["internship_title"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["location"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $student["duration"]
                                );
                                ?>

                            </td>


                            <td>

                                <span class="status">

                                    ✅ Accepted

                                </span>

                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>


            </table>


        </div>


    <?php endif; ?>


</div>


</body>

</html>