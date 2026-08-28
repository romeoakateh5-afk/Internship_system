<?php
session_start();

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Protect Admin Dashboard
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../home/login.php");
    exit;
}

$name = $_SESSION["name"];
$email = $_SESSION["email"];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Internship System</title>

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
        ========================== */

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

            z-index: 1000;
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

        /* =========================
           NAVIGATION
        ========================== */

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
            background: rgba(255,255,255,0.18);
        }

        .navigation .icon {
            width: 25px;
            text-align: center;
        }

        /* =========================
           LOGOUT
        ========================== */

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
        ========================== */

        .main-content {

            margin-left: 250px;

            padding: 35px;

            min-height: 100vh;
        }

        /* =========================
           TOP AREA
        ========================== */

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

        /* =========================
           WELCOME CARD
        ========================== */

        .welcome {

            background: white;

            padding: 30px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .welcome h2 {

            color: #1f4e79;

            margin-bottom: 10px;
        }

        .welcome p {

            color: #666;

            line-height: 1.6;
        }

        /* =========================
           DASHBOARD CARDS
        ========================== */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(210px, 1fr));

            gap: 20px;
        }

        .card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.06);
        }

        .card h3 {

            color: #1f4e79;

            margin-bottom: 8px;
        }

        .card p {

            color: #666;

            font-size: 14px;

            line-height: 1.5;
        }

        /* =========================
           MOBILE
        ========================== */

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


<!-- =====================================================
     LEFT SIDEBAR
====================================================== -->

<div class="sidebar">


    <div class="sidebar-header">

        <h2>Internship System</h2>

        <p>Administrator Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php" class="active">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="users.php">

            <span class="icon">👥</span>

            <span>Users</span>

        </a>


        <a href="students.php">

            <span class="icon">🎓</span>

            <span>Students</span>

        </a>


        <a href="companies.php">

            <span class="icon">🏢</span>

            <span>Companies</span>

        </a>


        <a href="supervisors.php">

            <span class="icon">👨‍🏫</span>

            <span>Supervisors</span>

        </a>


        <a href="placements.php">

            <span class="icon">📌</span>

            <span>Placements</span>

        </a>


        <a href="reports.php">

            <span class="icon">📊</span>

            <span>Reports</span>

        </a>


    </div>


    <div class="logout-section">

        <a href="../home/logout.php" class="logout">

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>


</div>



<!-- =====================================================
     MAIN CONTENT
====================================================== -->

<div class="main-content">


    <div class="top-area">

        <h1>Admin Dashboard</h1>

        <div class="user-info">

            <?php echo htmlspecialchars($name); ?>

        </div>

    </div>



    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($name); ?> 👋
        </h2>

        <p>
            Welcome to the administration dashboard of the
            Digital Placement and Supervision of Cameroon
            Students Internship System.
        </p>

    </div>



    <div class="cards">


        <div class="card">

            <h3>Users</h3>

            <p>
                Manage students, companies, supervisors
                and system administrators.
            </p>

        </div>


        <div class="card">

            <h3>Students</h3>

            <p>
                View and manage registered students.
            </p>

        </div>


        <div class="card">

            <h3>Companies</h3>

            <p>
                Manage organizations offering internship
                opportunities.
            </p>

        </div>


        <div class="card">

            <h3>Supervisors</h3>

            <p>
                Manage academic and internship supervisors.
            </p>

        </div>


        <div class="card">

            <h3>Placements</h3>

            <p>
                Monitor student internship placements.
            </p>

        </div>


        <div class="card">

            <h3>Reports</h3>

            <p>
                View internship statistics and reports.
            </p>

        </div>


    </div>


</div>


</body>

</html>