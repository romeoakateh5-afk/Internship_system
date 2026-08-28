<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "company") {
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

    <title>Company Dashboard - Internship System</title>

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

        .user-info {
            background: white;

            padding: 10px 16px;

            border-radius: 8px;

            box-shadow: 0 3px 10px rgba(0,0,0,0.06);

            font-size: 14px;
        }

        /* WELCOME */

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

        /* CARDS */

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

        }

    </style>

</head>

<body>


<!-- SIDEBAR -->

<div class="sidebar">

    <div class="sidebar-header">

        <h2>Internship System</h2>

        <p>Company Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php" class="active">

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


<!-- MAIN CONTENT -->

<div class="main-content">


    <div class="top-area">

        <h1>Company Dashboard</h1>


        <div class="user-info">

            <?php echo htmlspecialchars($name); ?>

        </div>

    </div>


    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($name); ?> 👋
        </h2>

        <p>
            Welcome to your company internship dashboard.
            From here, you can manage your company profile,
            post internship opportunities, review student
            applications and supervise students placed
            within your organization.
        </p>

    </div>


    <div class="cards">


        <div class="card">

            <h3>Company Profile</h3>

            <p>
                View and manage your organization's information.
            </p>

        </div>


        <div class="card">

            <h3>Post Internship</h3>

            <p>
                Create and publish internship opportunities
                for students.
            </p>

        </div>


        <div class="card">

            <h3>Applications</h3>

            <p>
                Review applications submitted by students.
            </p>

        </div>


        <div class="card">

            <h3>Placed Students</h3>

            <p>
                View students currently placed in your
                organization.
            </p>

        </div>


        <div class="card">

            <h3>Supervision</h3>

            <p>
                Monitor and manage students during their
                internship.
            </p>

        </div>


        <div class="card">

            <h3>Evaluation</h3>

            <p>
                Provide feedback and evaluate students'
                internship performance.
            </p>

        </div>


    </div>


</div>


</body>

</html>