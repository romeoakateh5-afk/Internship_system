<?php
session_start();

require_once "../config/database.php";

/* Check login */
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "company") {
    header("Location: ../home/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$message = "";
$applications = [];


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


/* Company profile not found */

if (!$company) {

    $message = "Company profile not found.";

} else {

    $company_id = $company["id"];


    /* =========================
       HANDLE APPLICATION UPDATE
    ========================= */

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $application_id = $_POST["application_id"] ?? "";
        $status = $_POST["status"] ?? "";

        $allowed_statuses = [
            "pending",
            "accepted",
            "rejected"
        ];


        if (
            $application_id !== "" &&
            in_array($status, $allowed_statuses, true)
        ) {

            try {

                /* Get application and verify
                   that it belongs to this company */

                $stmt = $pdo->prepare("
                    SELECT
                        sa.id,
                        sa.student_id,
                        sa.offer_id,
                        i.company_id
                    FROM students_applications sa

                    INNER JOIN internship i
                        ON sa.offer_id = i.id

                    WHERE sa.id = ?
                    AND i.company_id = ?
                    LIMIT 1
                ");

                $stmt->execute([
                    $application_id,
                    $company_id
                ]);

                $application = $stmt->fetch(PDO::FETCH_ASSOC);


                if (!$application) {

                    $message = "Application not found.";

                } else {

                    /*
                     * Update application status
                     */

                    $stmt = $pdo->prepare("
                        UPDATE students_applications
                        SET status = ?
                        WHERE id = ?
                    ");

                    $stmt->execute([
                        $status,
                        $application_id
                    ]);


                    /*
                     * If company ACCEPTS the student,
                     * create a placement record.
                     */

                    if ($status === "accepted") {

                        /*
                         * Check if placement already exists.
                         */

                        $check = $pdo->prepare("
                            SELECT id
                            FROM placement
                            WHERE student_id = ?
                            AND company_id = ?
                            AND offer_id = ?
                            LIMIT 1
                        ");

                        $check->execute([
                            $application["student_id"],
                            $company_id,
                            $application["offer_id"]
                        ]);

                        $existing_placement = $check->fetch(PDO::FETCH_ASSOC);


                        /*
                         * Create placement only if
                         * it does not already exist.
                         */

                        if (!$existing_placement) {

                            $stmt = $pdo->prepare("
                                INSERT INTO placement
                                (
                                    student_id,
                                    company_id,
                                    offer_id,
                                    start_date,
                                    end_date,
                                    status
                                )
                                VALUES
                                (?, ?, ?, NULL, NULL, 'active')
                            ");

                            $stmt->execute([
                                $application["student_id"],
                                $company_id,
                                $application["offer_id"]
                            ]);
                        }
                    }


                    /*
                     * If application is rejected,
                     * do not create a placement.
                     */

                    header("Location: applications.php");
                    exit;
                }

            } catch (PDOException $e) {

                $message = "Unable to update application. Please check your database.";
            }
        }
    }


    /* =========================
       GET STUDENT APPLICATIONS
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
            i.description,
            i.location,
            i.duration,
            i.status AS internship_status

        FROM students_applications sa

        INNER JOIN student s
            ON sa.student_id = s.id

        INNER JOIN internship i
            ON sa.offer_id = i.id

        WHERE i.company_id = ?

        ORDER BY sa.application_date DESC
    ");

    $stmt->execute([$company_id]);

    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Student Applications</title>

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

        .sidebar {
            position: fixed;
            left: 0;
            top: 0;

            width: 280px;
            height: 100vh;

            background: #245985;
            color: white;

            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 28px 22px;

            border-bottom: 1px solid rgba(255,255,255,0.15);
        }

        .sidebar-header h2 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 14px;
            opacity: 0.85;
        }

        .navigation {
            padding: 20px 14px;
            flex: 1;
        }

        .navigation a {
            display: flex;
            align-items: center;
            gap: 13px;

            padding: 14px 16px;
            margin-bottom: 7px;

            color: white;
            text-decoration: none;

            border-radius: 8px;
        }

        .navigation a:hover {
            background: rgba(255,255,255,0.12);
        }

        .navigation a.active {
            background: rgba(255,255,255,0.20);
        }

        .logout-section {
            padding: 15px 14px;

            border-top: 1px solid rgba(255,255,255,0.15);
        }

        .logout {
            display: flex;
            align-items: center;
            gap: 13px;

            padding: 14px 16px;

            color: white;
            text-decoration: none;

            border-radius: 8px;
        }

        .logout:hover {
            background: rgba(255,255,255,0.12);
        }

        .main-content {
            margin-left: 280px;

            padding: 40px;

            min-height: 100vh;
        }

        .top-area {
            display: flex;
            justify-content: space-between;
            align-items: center;

            margin-bottom: 30px;
        }

        .top-area h1 {
            color: #245985;
            font-size: 30px;
        }

        .company-badge {
            background: white;

            padding: 11px 18px;

            border-radius: 8px;

            box-shadow: 0 3px 10px rgba(0,0,0,0.06);
        }

        .applications-card {
            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow: 0 3px 12px rgba(0,0,0,0.06);

            overflow-x: auto;
        }

        .applications-card h2 {
            color: #245985;

            margin-bottom: 8px;
        }

        .description {
            color: #777;

            margin-bottom: 25px;
        }

        .empty {
            text-align: center;

            padding: 60px 20px;
        }

        .empty h2 {
            color: #666;

            margin-bottom: 10px;
        }

        .empty p {
            color: #888;
        }

        table {
            width: 100%;

            border-collapse: collapse;

            min-width: 1100px;
        }

        th {
            background: #245985;

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

        .status-form {
            display: flex;

            gap: 7px;

            align-items: center;
        }

        .status-form select {
            padding: 8px;

            border: 1px solid #ddd;

            border-radius: 6px;
        }

        .status-form button {
            padding: 8px 12px;

            border: none;

            border-radius: 6px;

            background: #245985;

            color: white;

            cursor: pointer;
        }

        .message {
            background: #fff4d6;

            color: #765900;

            padding: 15px;

            border-radius: 7px;

            margin-bottom: 20px;
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

        <a href="dashboard.php">
            🏠
            <span>Dashboard</span>
        </a>

        <a href="profile.php">
            👤
            <span>Company Profile</span>
        </a>

        <a href="post_internship.php">
            ➕
            <span>Create Internship</span>
        </a>

        <a href="internships.php">
            📋
            <span>My Internships</span>
        </a>

        <a href="applications.php" class="active">
            📄
            <span>Applications</span>
        </a>

    </div>


    <div class="logout-section">

        <a href="../logout.php" class="logout">

            🚪
            <span>Logout</span>

        </a>

    </div>

</div>


<!-- MAIN CONTENT -->

<div class="main-content">


    <div class="top-area">

        <h1>Student Applications</h1>

        <div class="company-badge">

            <?php
            echo htmlspecialchars(
                $company["company_name"] ?? "Company"
            );
            ?>

        </div>

    </div>


    <div class="applications-card">


        <?php if ($message !== ""): ?>

            <div class="message">

                <?php echo htmlspecialchars($message); ?>

            </div>


        <?php elseif (empty($applications)): ?>

            <div class="empty">

                <h2>No Applications Yet</h2>

                <p>
                    Students who apply for your internships
                    will appear here.
                </p>

            </div>


        <?php else: ?>


            <h2>Applications Received</h2>

            <p class="description">
                Students who have applied for your internship opportunities.
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
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($applications as $application): ?>

                        <tr>

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

                                <span class="status
                                    <?php
                                    echo htmlspecialchars(
                                        $application["status"]
                                    );
                                    ?>">

                                    <?php
                                    echo ucfirst(
                                        htmlspecialchars(
                                            $application["status"]
                                        )
                                    );
                                    ?>

                                </span>

                            </td>


                            <td>

                                <form
                                    method="POST"
                                    class="status-form"
                                >

                                    <input
                                        type="hidden"
                                        name="application_id"
                                        value="<?php
                                        echo htmlspecialchars(
                                            $application["application_id"]
                                        );
                                        ?>"
                                    >


                                    <select name="status">

                                        <option
                                            value="pending"
                                            <?php
                                            echo $application["status"] === "pending"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Pending
                                        </option>

                                        <option
                                            value="accepted"
                                            <?php
                                            echo $application["status"] === "accepted"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Accepted
                                        </option>

                                        <option
                                            value="rejected"
                                            <?php
                                            echo $application["status"] === "rejected"
                                                ? "selected"
                                                : "";
                                            ?>
                                        >
                                            Rejected
                                        </option>

                                    </select>


                                    <button type="submit">
                                        Update
                                    </button>

                                </form>

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