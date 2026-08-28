<?php
session_start();

require_once "../config/database.php";


/* =========================
   CHECK ADMIN LOGIN
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: ../home/login.php");
    exit;
}


/* =========================
   ADMIN INFORMATION
========================= */

$admin_name = $_SESSION["name"] ?? "Administrator";


/* =========================
   GET STATISTICS
========================= */

try {

    /* Total students */
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM student
    ");

    $total_students = $stmt->fetchColumn();


    /* Total companies */
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM company
    ");

    $total_companies = $stmt->fetchColumn();


    /* Total internships */
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM internship
    ");

    $total_internships = $stmt->fetchColumn();


    /* Total applications */
    $stmt = $pdo->query("
        SELECT COUNT(*) 
        FROM students_applications
    ");

    $total_applications = $stmt->fetchColumn();


    /* Accepted applications */
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM students_applications
        WHERE status = 'accepted'
    ");

    $accepted_applications = $stmt->fetchColumn();


    /* Pending applications */
    $stmt = $pdo->query("
        SELECT COUNT(*)
        FROM students_applications
        WHERE status = 'pending'
    ");

    $pending_applications = $stmt->fetchColumn();


} catch (PDOException $e) {

    $total_students = 0;
    $total_companies = 0;
    $total_internships = 0;
    $total_applications = 0;
    $accepted_applications = 0;
    $pending_applications = 0;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

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


.admin-info {

    background: white;

    padding: 10px 16px;

    border-radius: 8px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}


/* =========================
   WELCOME
========================= */

.welcome {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    margin-bottom: 25px;
}


.welcome h2 {

    color: #1f4e79;

    margin-bottom: 8px;
}


.welcome p {

    color: #777;
}


/* =========================
   STATISTICS
========================= */

.stats-grid {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 20px;

    margin-bottom: 30px;
}


.stat-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    display: flex;

    align-items: center;

    gap: 18px;
}


.stat-icon {

    width: 55px;

    height: 55px;

    border-radius: 10px;

    background: #eaf2f8;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 27px;
}


.stat-info h3 {

    font-size: 28px;

    color: #1f4e79;

    margin-bottom: 4px;
}


.stat-info p {

    color: #777;

    font-size: 14px;
}


/* =========================
   QUICK ACTIONS
========================= */

.quick-card {

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);
}


.quick-card h2 {

    color: #1f4e79;

    margin-bottom: 20px;
}


.quick-links {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 15px;
}


.quick-links a {

    padding: 18px;

    background: #f4f7fb;

    border-radius: 8px;

    text-decoration: none;

    color: #1f4e79;

    text-align: center;

    font-weight: bold;

    transition: 0.2s;
}


.quick-links a:hover {

    background: #e7eef5;

}


/* =========================
   MOBILE
========================= */

@media (max-width: 900px) {

    .stats-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }


    .quick-links {

        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media (max-width: 700px) {

    .sidebar {

        width: 210px;
    }


    .main-content {

        margin-left: 210px;

        padding: 20px;
    }


    .stats-grid {

        grid-template-columns: 1fr;
    }


    .quick-links {

        grid-template-columns: 1fr;
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


        <a href="dashboard.php"
           class="active">

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


        <h1>Admin Dashboard</h1>


        <div class="admin-info">

            👤
            <?php
            echo htmlspecialchars($admin_name);
            ?>

        </div>


    </div>


    <!-- WELCOME -->


    <div class="welcome">


        <h2>
            Welcome, <?php
            echo htmlspecialchars($admin_name);
            ?>!
        </h2>


        <p>
            Here's an overview of the internship
            placement system.
        </p>


    </div>


    <!-- STATISTICS -->


    <div class="stats-grid">


        <div class="stat-card">


            <div class="stat-icon">
                🎓
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $total_students;
                    ?>
                </h3>

                <p>
                    Total Students
                </p>

            </div>


        </div>


        <div class="stat-card">


            <div class="stat-icon">
                🏢
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $total_companies;
                    ?>
                </h3>

                <p>
                    Total Companies
                </p>

            </div>


        </div>


        <div class="stat-card">


            <div class="stat-icon">
                💼
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $total_internships;
                    ?>
                </h3>

                <p>
                    Total Internships
                </p>

            </div>


        </div>


        <div class="stat-card">


            <div class="stat-icon">
                📄
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $total_applications;
                    ?>
                </h3>

                <p>
                    Total Applications
                </p>

            </div>


        </div>


        <div class="stat-card">


            <div class="stat-icon">
                ✅
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $accepted_applications;
                    ?>
                </h3>

                <p>
                    Accepted Applications
                </p>

            </div>


        </div>


        <div class="stat-card">


            <div class="stat-icon">
                ⏳
            </div>


            <div class="stat-info">

                <h3>
                    <?php
                    echo $pending_applications;
                    ?>
                </h3>

                <p>
                    Pending Applications
                </p>

            </div>


        </div>


    </div>


    <!-- QUICK ACTIONS -->


    <div class="quick-card">


        <h2>
            Quick Access
        </h2>


        <div class="quick-links">


            <a href="student.php">
                🎓<br>
                Students
            </a>


            <a href="company.php">
                🏢<br>
                Companies
            </a>


            <a href="offer.php">
                💼<br>
                Internships
            </a>


            <a href="application.php">
                📄<br>
                Applications
            </a>


        </div>


    </div>


</div>


</body>

</html>