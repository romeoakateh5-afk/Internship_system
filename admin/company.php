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

    /* Get all companies with their user information */
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.user_id,
            c.company_name,
            c.address,
            c.industry,
            c.phone,
            u.name,
            u.email

        FROM company c

        INNER JOIN user_s u
            ON c.user_id = u.id

        ORDER BY c.id DESC
    ");

    $stmt->execute();

    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $companies = [];

    $error = "Unable to load companies.";

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Companies - Admin</title>

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

.admin-info {
    background: white;
    padding: 10px 16px;
    border-radius: 8px;

    box-shadow:
        0 3px 10px rgba(0,0,0,0.06);

    font-size: 14px;
}

/* CARD */

.companies-card {
    background: white;
    padding: 30px;
    border-radius: 12px;

    box-shadow:
        0 3px 12px rgba(0,0,0,0.06);

    overflow-x: auto;
}

.companies-card h2 {
    color: #1f4e79;
    margin-bottom: 8px;
}

.description {
    color: #777;
    margin-bottom: 25px;
    font-size: 14px;
}

/* TABLE */

table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
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

/* EMPTY */

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

/* MOBILE */

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


<!-- SIDEBAR -->

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


        <a href="company.php"
           class="active">

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


<!-- MAIN CONTENT -->

<div class="main-content">


    <div class="top-area">

        <h1>Registered Companies</h1>


        <div class="admin-info">

            👤
            <?php
            echo htmlspecialchars($admin_name);
            ?>

        </div>

    </div>


    <div class="companies-card">


        <h2>Companies</h2>


        <p class="description">

            View companies registered in the internship
            placement system.

        </p>


        <?php if (isset($error)): ?>

            <div class="error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>


        <?php elseif (empty($companies)): ?>

            <div class="empty">

                <h3>No companies found.</h3>

                <p>
                    Companies that create profiles will appear here.
                </p>

            </div>


        <?php else: ?>


            <table>

                <thead>

                    <tr>

                        <th>ID</th>
                        <th>User ID</th>
                        <th>Company Name</th>
                        <th>Contact Name</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Industry</th>
                        <th>Phone</th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($companies as $company): ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["id"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["user_id"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["company_name"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["name"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["email"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["address"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["industry"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $company["phone"]
                                );
                                ?>
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