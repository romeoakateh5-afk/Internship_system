<?php
session_start();

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../home/login.php");
    exit;
}

require_once "../config/database.php";

$name = $_SESSION["name"];

/* Get all available internship opportunities */
$stmt = $pdo->prepare("
    SELECT 
        internship.id,
        internship.title,
        internship.description,
        internship.location,
        internship.duration,
        internship.status,
        user_s.name AS company_name
    FROM internship
    INNER JOIN user_s
        ON internship.company_id = user_s.id
    WHERE internship.status = 'open'
    ORDER BY internship.created_at DESC
");

$stmt->execute();

$internship = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Find Internship - Internship System</title>

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

        /* =========================
           MAIN CONTENT
        ========================== */

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

        /* =========================
           SEARCH
        ========================== */

        .search-box {
            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 25px;

            box-shadow: 0 3px 10px rgba(0,0,0,0.06);

            display: flex;

            gap: 10px;
        }

        .search-box input {
            flex: 1;

            padding: 12px;

            border: 1px solid #ddd;

            border-radius: 7px;

            outline: none;

            font-size: 14px;
        }

        .search-box button {
            padding: 12px 20px;

            border: none;

            border-radius: 7px;

            background: #1f4e79;

            color: white;

            cursor: pointer;
        }

        /* =========================
           INTERNSHIP CARDS
        ========================== */

        .internship-grid {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(280px, 1fr));

            gap: 20px;
        }

        .internship-card {

            background: white;

            padding: 25px;

            border-radius: 10px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.06);
        }

        .internship-card h2 {

            color: #1f4e79;

            margin-bottom: 12px;

            font-size: 20px;
        }

        .company {

            color: #555;

            font-weight: bold;

            margin-bottom: 12px;
        }

        .details {

            color: #666;

            font-size: 14px;

            line-height: 1.7;

            margin-bottom: 18px;
        }

        .apply-button {

            display: inline-block;

            background: #1f4e79;

            color: white;

            text-decoration: none;

            padding: 10px 18px;

            border-radius: 7px;

            font-size: 14px;
        }

        .apply-button:hover {

            opacity: 0.9;
        }

        .empty {

            background: white;

            padding: 40px;

            border-radius: 10px;

            text-align: center;

            color: #777;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.06);
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

            .search-box {
                flex-direction: column;
            }

        }

    </style>

</head>

<body>


<!-- =========================
     SIDEBAR
========================== -->

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


        <a href="internships.php" class="active">

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

        <h1>Find Internship</h1>

        <div class="user-info">

            <?php echo htmlspecialchars($name); ?>

        </div>

    </div>


    <!-- SEARCH -->

    <div class="search-box">

        <input
            type="text"
            placeholder="Search internship opportunities..."
        >

        <button type="button">
            Search
        </button>

    </div>


    <!-- INTERNSHIPS -->

    <?php if (count($internship) > 0): ?>

        <div class="internship-grid">

            <?php foreach ($internship as $internship): ?>

                <div class="internship-card">

                    <h2>
                        <?php
                        echo htmlspecialchars(
                            $internship["title"] ?? "Internship Opportunity"
                        );
                        ?>
                    </h2>


                    <p class="company">

                        <?php
                        echo htmlspecialchars(
                            $internship["company_name"] ?? "Company"
                        );
                        ?>

                    </p>


                    <p class="details">

                        <?php
                        echo htmlspecialchars(
                            $internship["description"] ?? "No description available."
                        );
                        ?>

                    </p>


                    
                    <a href="apply.php?offer_id=<?php echo $internship['id']; ?>"
                    class="apply-button">
    Apply Now
</a>

                </div>

            <?php endforeach; ?>

        </div>

    <?php else: ?>

        <div class="empty">

            <h2>No Internship Opportunities Yet</h2>

            <p>
                Internship opportunities posted by companies
                will appear here.
            </p>

        </div>

    <?php endif; ?>


</div>


</body>

</html>