<?php
session_start();

require_once "../config/database.php";

/* Make sure the user is logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$message = "";
$messageType = "";

/* Get existing company profile */
$stmt = $pdo->prepare("
    SELECT id, user_id, company_name, address, industry, phone
    FROM company
    WHERE user_id = ?
    LIMIT 1
");

$stmt->execute([$user_id]);

$profile = $stmt->fetch(PDO::FETCH_ASSOC);


/* Save profile */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $company_name = trim($_POST["company_name"] ?? "");
    $address      = trim($_POST["address"] ?? "");
    $industry     = trim($_POST["industry"] ?? "");
    $phone        = trim($_POST["phone"] ?? "");

    if (
        $company_name === "" ||
        $address === "" ||
        $industry === "" ||
        $phone === ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } else {

        try {

            /* Update existing profile */
            if ($profile) {

                $stmt = $pdo->prepare("
                    UPDATE company
                    SET company_name = ?,
                        address = ?,
                        industry = ?,
                        phone = ?
                    WHERE user_id = ?
                ");

                $stmt->execute([
                    $company_name,
                    $address,
                    $industry,
                    $phone,
                    $user_id
                ]);

                $message = "Company profile updated successfully!";
                $messageType = "success";

            }

            /* Create new profile */
            else {

                $stmt = $pdo->prepare("
                    INSERT INTO company
                    (user_id, company_name, address, industry, phone)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmt->execute([
                    $user_id,
                    $company_name,
                    $address,
                    $industry,
                    $phone
                ]);

                $message = "Company profile created successfully!";
                $messageType = "success";
            }

            /* Reload profile */
            $stmt = $pdo->prepare("
                SELECT id, user_id, company_name, address, industry, phone
                FROM company
                WHERE user_id = ?
                LIMIT 1
            ");

            $stmt->execute([$user_id]);

            $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {

            $message = "Unable to save profile: " . $e->getMessage();
            $messageType = "error";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Company Profile</title>

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

            font-size: 15px;
        }

        .navigation a:hover {
            background: rgba(255,255,255,0.12);
        }

        .navigation a.active {
            background: rgba(255,255,255,0.20);
        }

        .icon {
            width: 25px;
            text-align: center;
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


        /* MAIN CONTENT */

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


        /* PROFILE CARD */

        .profile-card {
            background: white;

            max-width: 900px;

            padding: 35px;

            border-radius: 12px;

            box-shadow: 0 3px 12px rgba(0,0,0,0.06);
        }

        .profile-card h2 {
            color: #245985;

            margin-bottom: 8px;
        }

        .profile-description {
            color: #777;

            margin-bottom: 28px;

            font-size: 14px;
        }


        /* MESSAGES */

        .message {
            padding: 14px 16px;

            border-radius: 7px;

            margin-bottom: 22px;

            font-size: 14px;
        }

        .success {
            background: #e8f7ee;

            color: #217a45;

            border: 1px solid #b9e5ca;
        }

        .error {
            background: #fdecec;

            color: #a83232;

            border: 1px solid #f2bcbc;
        }


        /* FORM */

        .form-grid {
            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 22px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-group label {
            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: bold;
        }

        .form-group input {
            width: 100%;

            padding: 13px;

            border: 1px solid #ddd;

            border-radius: 7px;

            font-size: 14px;

            outline: none;
        }

        .form-group input:focus {
            border-color: #245985;
        }

        .submit-button {
            background: #245985;

            color: white;

            border: none;

            padding: 13px 25px;

            border-radius: 7px;

            font-size: 15px;

            cursor: pointer;
        }

        .submit-button:hover {
            opacity: 0.9;
        }


        /* MOBILE */

        @media (max-width: 768px) {

            .sidebar {
                width: 220px;
            }

            .main-content {
                margin-left: 220px;

                padding: 20px;
            }

            .form-grid {
                grid-template-columns: 1fr;
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

        <a href="dashboard.php">
            <span class="icon">🏠</span>
            <span>Dashboard</span>
        </a>


        <a href="profile.php" class="active">
            <span class="icon">👤</span>
            <span>Company Profile</span>
        </a>


        <a href="post_internship.php">
            <span class="icon">➕</span>
            <span>Create Internship</span>
        </a>


        <a href="internships.php">
            <span class="icon">📋</span>
            <span>My Internships</span>
        </a>


        <a href="applications.php">
            <span class="icon">📄</span>
            <span>Applications</span>
        </a>

    </div>


    <div class="logout-section">

        <a href="../logout.php" class="logout">

            <span>🚪</span>

            <span>Logout</span>

        </a>

    </div>

</div>


<!-- MAIN CONTENT -->

<div class="main-content">


    <div class="top-area">

        <h1>Company Profile</h1>

        <div class="company-badge">

            Company

        </div>

    </div>


    <div class="profile-card">

        <h2>Company Information</h2>

        <p class="profile-description">

            Complete your company profile so students can
            identify your company when applying for internships.

        </p>


        <?php if ($message !== ""): ?>

            <div class="message <?php echo htmlspecialchars($messageType); ?>">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <form method="POST" action="">


            <div class="form-grid">


                <div class="form-group">

                    <label for="company_name">
                        Company Name
                    </label>

                    <input
                        type="text"
                        id="company_name"
                        name="company_name"
                        placeholder="Enter company name"
                        value="<?php echo htmlspecialchars($profile["company_name"] ?? ""); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="industry">
                        Industry
                    </label>

                    <input
                        type="text"
                        id="industry"
                        name="industry"
                        placeholder="Example: Information Technology"
                        value="<?php echo htmlspecialchars($profile["industry"] ?? ""); ?>"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="address">
                    Address
                </label>

                <input
                    type="text"
                    id="address"
                    name="address"
                    placeholder="Enter company address"
                    value="<?php echo htmlspecialchars($profile["address"] ?? ""); ?>"
                    required
                >

            </div>


            <div class="form-group">

                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    placeholder="Enter company phone number"
                    value="<?php echo htmlspecialchars($profile["phone"] ?? ""); ?>"
                    required
                >

            </div>


            <button
                type="submit"
                class="submit-button"
            >
                Save Company Profile
            </button>


        </form>

    </div>

</div>


</body>

</html>