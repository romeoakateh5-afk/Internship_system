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

$placements = [];
$error = "";

/*
|--------------------------------------------------------------------------
| Get placement records
|--------------------------------------------------------------------------
|
| placement
|   student_id   -> student.id
|   company_id   -> company.id
|   supervisor_id -> supervisor.id
|   offer_id     -> internship.id
|
*/

try {

    $stmt = $pdo->prepare("
        SELECT
            p.id AS placement_id,

            p.start_date,
            p.end_date,
            p.status,

            s.student_id,
            s.school,
            s.program,
            s.level,

            su.staff_id,
            su.department,

            c.company_name,

            i.title AS internship_title,
            i.location,
            i.duration

        FROM placement p

        LEFT JOIN student s
            ON p.student_id = s.id

        LEFT JOIN company c
            ON p.company_id = c.id

        LEFT JOIN supervisor su
            ON p.supervisor_id = su.id

        LEFT JOIN internship i
            ON p.offer_id = i.id

        ORDER BY p.id DESC
    ");

    $stmt->execute();

    $placements = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $error = "Unable to load placement records.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Placements - Admin</title>

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

.placement-card {
    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    overflow-x: auto;
}

.placement-card h2 {
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

    min-width: 1100px;
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

.active {
    background: #e4f7eb;

    color: #207a43;
}

.completed {
    background: #e8eef7;

    color: #245985;
}

.cancelled {
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


        <a href="application.php">

            <span class="icon">📄</span>

            <span>Applications</span>

        </a>


        <a href="placement.php"
           class="active">

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


        <h1>Student Placements</h1>


        <div class="admin-info">

            👤

            <?php
            echo htmlspecialchars($admin_name);
            ?>

        </div>


    </div>


    <div class="placement-card">


        <h2>Placement Records</h2>


        <p class="description">

            View students who have been placed for
            internship opportunities.

        </p>


        <?php if ($error !== ""): ?>


            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>


        <?php elseif (empty($placements)): ?>


            <div class="empty">

                <h3>No placement records found.</h3>

                <p>
                    Student placement records will
                    appear here.
                </p>

            </div>


        <?php else: ?>


            <table>


                <thead>

                    <tr>

                        <th>ID</th>

                        <th>Student ID</th>

                        <th>School</th>

                        <th>Program</th>

                        <th>Company</th>

                        <th>Internship</th>

                        <th>Location</th>

                        <th>Supervisor ID</th>

                        <th>Start Date</th>

                        <th>End Date</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                    <?php foreach ($placements as $placement): ?>


                        <tr>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["placement_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["student_id"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["school"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["program"]
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["company_name"]
                                    ?? "Not assigned"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["internship_title"]
                                    ?? "Not assigned"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["location"]
                                    ?? "Not specified"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["staff_id"]
                                    ?? "Not assigned"
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["start_date"]
                                    ?? ""
                                );
                                ?>

                            </td>


                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $placement["end_date"]
                                    ?? ""
                                );
                                ?>

                            </td>


                            <td>


                                <?php

                                $status =
                                    strtolower(
                                        $placement["status"]
                                        ?? ""
                                    );

                                ?>


                                <span class="status
                                    <?php
                                    echo htmlspecialchars($status);
                                    ?>">

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $status ?: "Unknown"
                                        )
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