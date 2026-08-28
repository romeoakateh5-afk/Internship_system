<?php
session_start();

require_once "../config/database.php";

/* Check admin login */
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../home/login.php");
    exit;
}

$admin_name = $_SESSION["name"] ?? "Administrator";

try {

    /*
     * Get applications together with:
     * - Student information
     * - Company information
     * - Internship information
     */

    $stmt = $pdo->prepare("
        SELECT
            sa.id AS application_id,
            sa.application_date,
            sa.status,

            s.student_id,
            s.school,
            s.program,
            s.level,
            s.phone,

            u.name AS student_name,
            u.email AS student_email,

            i.id AS internship_id,
            i.title AS internship_title,
            i.location,
            i.duration,

            c.id AS company_id,
            c.company_name

        FROM students_applications sa

        INNER JOIN student s
            ON sa.student_id = s.id

        INNER JOIN user_s u
            ON s.user_id = u.id

        INNER JOIN internship i
            ON sa.offer_id = i.id

        INNER JOIN company c
            ON i.company_id = c.id

        ORDER BY sa.application_date DESC
    ");

    $stmt->execute();

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $applications = [];

    $error = "Unable to load applications.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Applications - Admin</title>

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
    background: rgba(255,255,255,0.13);
}

/* =========================
   MAIN
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

.admin-info {
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

.applications-card {
    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    overflow-x: auto;
}

.applications-card h2 {
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

    min-width: 1200px;
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

    font-size: 12px;

    font-weight: bold;
}

.pending {
    background: #fff4d6;

    color: #956c00;
}

.accepted {
    background: #e4f7eb;

    color: #207a43;
}

.rejected {
    background: #fde8e8;

    color: #a83232;
}

/* =========================
   EMPTY
========================= */

.empty {
    text-align: center;

    padding: 60px 20px;

    color: #777;
}

.error {
    background: #fdecec;

    color: #a83232;

    padding: 15px;

    border-radius: 7px;

    margin-bottom: 20px;
}

/* =========================
   MOBILE
========================= */

@media (max-width: 700px) {

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

        <p>Administrator Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="student.php">

            <span class="icon">🎓</span>

            <span>Students</span>

        </a>


        <a href="company.php">

            <span class="icon">🏢</span>

            <span>Companies</span>

        </a>


        <a href="offer.php">

            <span class="icon">💼</span>

            <span>Internships</span>

        </a>


        <a href="application.php"
           class="active">

            <span class="icon">📄</span>

            <span>Applications</span>

        </a>


        <a href="placement.php">

            <span class="icon">📌</span>

            <span>Placements</span>

        </a>


        <a href="supervisor.php">

            <span class="icon">👨‍🏫</span>

            <span>Supervisors</span>

        </a>


        <a href="evaluation.php">

            <span class="icon">⭐</span>

            <span>Evaluations</span>

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


        <h1>Student Applications</h1>


        <div class="admin-info">

            👤
            <?php
            echo htmlspecialchars($admin_name);
            ?>

        </div>


    </div>


    <div class="applications-card">


        <h2>Applications</h2>


        <p class="description">

            View all internship applications submitted
            by students.

        </p>


        <?php if (isset($error)): ?>


            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>


        <?php elseif (empty($applications)): ?>


            <div class="empty">

                <h3>No applications found.</h3>

                <p>
                    Student applications will appear here.
                </p>

            </div>


        <?php else: ?>


            <table>


                <thead>

                    <tr>

                        <th>Application ID</th>

                        <th>Student ID</th>

                        <th>Student Name</th>

                        <th>Email</th>

                        <th>School</th>

                        <th>Program</th>

                        <th>Level</th>

                        <th>Phone</th>

                        <th>Company</th>

                        <th>Internship</th>

                        <th>Location</th>

                        <th>Duration</th>

                        <th>Application Date</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($applications as $application): ?>


                        <tr>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["application_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["student_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["student_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["student_email"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["school"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["program"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["level"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["phone"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["company_name"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["internship_title"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["location"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["duration"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $application["application_date"]
                                );
                                ?>

                            </td>


                            <td>


                                <?php

                                $status =
                                    strtolower(
                                        $application["status"]
                                    );

                                ?>


                                <span class="status
                                    <?php
                                    echo htmlspecialchars($status);
                                    ?>">

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars($status)
                                    );
                                    ?>

                                </span>


                            </td>


                        </tr>


                    <?php endforeach; ?>


                </tbody>


            </table>


        <?php endif; ?>


    </div>


</div>


</body>

</html>