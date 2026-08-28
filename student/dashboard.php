<?php
session_start();

/* Protect the student dashboard */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../home/login.php");
    exit;
}

$name = $_SESSION["name"];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Dashboard - Internship System</title>

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
           LEFT SIDEBAR
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
            background: rgba(255,255,255,0.20);
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
           WELCOME SECTION
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

            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
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


<!-- =========================
     LEFT SIDEBAR
========================== -->

<div class="sidebar">


    <div class="sidebar-header">

        <h2>Internship System</h2>

        <p>Student Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php" class="active">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="profile.php">

            <span class="icon">👤</span>

            <span>My Profile</span>

        </a>


        <a href="internships.php">

            <span class="icon">🔍</span>

            <span>Find Internship</span>

        </a>


        <a href="applications.php">

            <span class="icon">📄</span>

            <span>My Applications</span>

        </a>


        <a href="placement.php">

            <span class="icon">📌</span>

            <span>My Placement</span>

        </a>


        <a href="activities.php">

            <span class="icon">📝</span>

            <span>Activities</span>

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
========================== -->

<div class="main-content">


    <div class="top-area">

        <h1>Student Dashboard</h1>


        <div class="user-info">

            <?php echo htmlspecialchars($name); ?>

        </div>

    </div>


    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($name); ?> 👋
        </h2>

        <p>
            Welcome to your student internship dashboard.
            From here, you can manage your profile, search
            for internship opportunities, track applications,
            monitor your placement and manage your internship
            activities.
        </p>

    </div>


    <div class="cards">


        <div class="card">

            <h3>My Profile</h3>

            <p>
                View and manage your personal information.
            </p>

        </div>


        <div class="card">

            <h3>Find Internship</h3>

            <p>
                Search and explore available internship
                opportunities.
            </p>

        </div>


        <div class="card">

            <h3>My Applications</h3>

            <p>
                Track the internship opportunities you
                have applied for.
            </p>

        </div>


        <div class="card">

            <h3>My Placement</h3>

            <p>
                View information about your current
                internship placement.
            </p>

        </div>


        <div class="card">

            <h3>Activities</h3>

            <p>
                Record and monitor activities completed
                during your internship.
            </p>

        </div>


        <div class="card">

            <h3>Evaluation</h3>

            <p>
                View feedback and evaluation related to
                your internship.
            </p>

        </div>


    </div>


</div>


</body>

</html>