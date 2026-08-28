<?php
session_start();

require_once "../config/database.php";

/* Check student login */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$applications = [];
$student = null;


/* =========================
   GET STUDENT PROFILE
========================= */

$stmt = $pdo->prepare("
    SELECT id, user_id, student_id, school, program, level, phone
    FROM student
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$student = $stmt->fetch(PDO::FETCH_ASSOC);


/* =========================
   STUDENT PROFILE NOT FOUND
========================= */

if (!$student) {

    $error = "Student profile not found. Please complete your student profile first.";

} else {

    $student_id = $student["id"];


    /* =========================
       GET APPLICATIONS
    ========================= */

    $stmt = $pdo->prepare("
        SELECT
            sa.id AS application_id,
            sa.application_date,
            sa.status,

            i.id AS internship_id,
            i.title,
            i.description,
            i.location,
            i.duration,
            i.status AS internship_status,

            c.company_name

        FROM students_applications sa

        INNER JOIN internship i
            ON sa.offer_id = i.id

        INNER JOIN company c
            ON i.company_id = c.id

        WHERE sa.student_id = ?

        ORDER BY sa.application_date DESC
    ");

    $stmt->execute([$student_id]);

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Applications - Internship System</title>


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


        .student-info {

            background: white;

            padding: 10px 16px;

            border-radius: 8px;

            box-shadow:
                0 3px 10px rgba(0,0,0,0.06);

            font-size: 14px;
        }


        /* =========================
           APPLICATION CARD
        ========================= */

        .applications-container {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(320px, 1fr));

            gap: 22px;
        }


        .application-card {

            background: white;

            border-radius: 12px;

            padding: 25px;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.06);

            transition: 0.2s;
        }


        .application-card:hover {

            transform: translateY(-3px);

            box-shadow:
                0 6px 18px rgba(0,0,0,0.09);
        }


        .application-card h2 {

            color: #1f4e79;

            font-size: 20px;

            margin-bottom: 8px;
        }


        .company {

            color: #666;

            font-size: 14px;

            margin-bottom: 20px;
        }


        .details {

            margin-bottom: 20px;
        }


        .details p {

            margin-bottom: 9px;

            font-size: 14px;

            color: #555;
        }


        .details strong {

            color: #333;
        }


        /* =========================
           STATUS
        ========================= */

        .status-container {

            display: flex;

            justify-content: space-between;

            align-items: center;

            padding-top: 18px;

            border-top:
                1px solid #eee;
        }


        .status-label {

            font-size: 14px;

            font-weight: bold;
        }


        .status {

            padding: 7px 13px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

            text-transform: uppercase;
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
           EMPTY STATE
        ========================= */

        .empty {

            background: white;

            border-radius: 12px;

            padding: 70px 30px;

            text-align: center;

            box-shadow:
                0 3px 12px rgba(0,0,0,0.06);
        }


        .empty-icon {

            font-size: 50px;

            margin-bottom: 15px;
        }


        .empty h2 {

            color: #555;

            margin-bottom: 10px;
        }


        .empty p {

            color: #888;

            margin-bottom: 20px;
        }


        .browse-button {

            display: inline-block;

            background: #1f4e79;

            color: white;

            text-decoration: none;

            padding: 12px 20px;

            border-radius: 7px;
        }


        .browse-button:hover {

            opacity: 0.9;
        }


        /* =========================
           ERROR
        ========================= */

        .error {

            background: #fdecec;

            color: #a83232;

            padding: 18px;

            border-radius: 8px;
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

        <p>Student Portal</p>

    </div>


    <div class="navigation">


        <a href="dashboard.php">

            <span class="icon">🏠</span>

            <span>Dashboard</span>

        </a>


        <a href="internships.php">

            <span class="icon">💼</span>

            <span>Internship Opportunities</span>

        </a>


        <a href="applications.php"
           class="active">

            <span class="icon">📄</span>

            <span>My Applications</span>

        </a>


        <a href="profile.php">

            <span class="icon">👤</span>

            <span>My Profile</span>

        </a>


    </div>


    <div class="logout-section">

        <a href="../logout.php"
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

        <h1>My Applications</h1>


        <?php if ($student): ?>

            <div class="student-info">

                <?php
                echo htmlspecialchars(
                    $student["student_id"]
                );
                ?>

            </div>

        <?php endif; ?>

    </div>


    <?php if (isset($error)): ?>


        <div class="error">

            <?php echo htmlspecialchars($error); ?>

        </div>


    <?php elseif (empty($applications)): ?>


        <div class="empty">


            <div class="empty-icon">
                📄
            </div>


            <h2>No Applications Yet</h2>


            <p>
                You haven't applied for any
                internship opportunities yet.
            </p>


            <a href="internships.php"
               class="browse-button">

                Browse Internships

            </a>


        </div>


    <?php else: ?>


        <div class="applications-container">


            <?php foreach ($applications as $application): ?>


                <div class="application-card">


                    <h2>

                        <?php
                        echo htmlspecialchars(
                            $application["title"]
                        );
                        ?>

                    </h2>


                    <div class="company">

                        🏢

                        <?php
                        echo htmlspecialchars(
                            $application["company_name"]
                        );
                        ?>

                    </div>


                    <div class="details">


                        <p>

                            <strong>📍 Location:</strong>

                            <?php
                            echo htmlspecialchars(
                                $application["location"]
                            );
                            ?>

                        </p>


                        <p>

                            <strong>⏱ Duration:</strong>

                            <?php
                            echo htmlspecialchars(
                                $application["duration"]
                            );
                            ?>

                        </p>


                        <p>

                            <strong>📅 Applied:</strong>

                            <?php
                            echo htmlspecialchars(
                                $application["application_date"]
                            );
                            ?>

                        </p>


                    </div>


                    <div class="status-container">


                        <span class="status-label">

                            Application Status

                        </span>


                        <?php

                        $status =
                            strtolower(
                                trim(
                                    $application["status"]
                                )
                            );

                        ?>


                        <span class="status
                            <?php
                            echo htmlspecialchars(
                                $status
                            );
                            ?>">

                            <?php

                            if ($status === "accepted") {

                                echo "✅ Accepted";

                            } elseif ($status === "rejected") {

                                echo "❌ Rejected";

                            } else {

                                echo "⏳ Pending";

                            }

                            ?>

                        </span>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


</body>

</html>