<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

// Include config file
require_once "surveyDB.php";

// Fetch all surveys
function fetchAllSurveys($mysqli) {
    $sql = "SELECT id, title FROM Surveys";
    $result = $mysqli->query($sql);
    return $result;
}

// Fetch all users
function fetchAllUsers($mysqli, $search = '') {
    $sql = "SELECT id, username, email FROM Users";
    if ($search) {
        $search = "%" . $mysqli->real_escape_string($search) . "%";
        $sql .= " WHERE username LIKE '$search' OR email LIKE '$search'";
    }
    $result = $mysqli->query($sql);
    return $result;
}

$search_error = "";
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
    $stmt = $mysqli->prepare("SELECT id FROM Users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $search_query, $search_query);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id);
        $stmt->fetch();
        $stmt->close();
        header("Location: viewUser.php?id=$user_id");
        exit;
    } else {
        $search_error = "User not found.";
    }
    $stmt->close();
}

$users = fetchAllUsers($mysqli);
$edit_surveys = fetchAllSurveys($mysqli);
$view_surveys = fetchAllSurveys($mysqli);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .hidden {
            display: none;
        }
        .user-table th, .user-table td, .survey-table th, .survey-table td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
        }
        .user-table th, .survey-table th {
            background-color: #007BFF;
            color: white;
        }
    </style>
    <script>
        function showSection(sectionId) {
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.add('hidden');
            });
            document.getElementById(sectionId).classList.remove('hidden');
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
        
        <button onclick="showSection('edit-surveys')">Edit Existing Surveys</button>
        <button onclick="window.location.href='createSurvey.php'">Create a New Survey</button>
        <button onclick="showSection('manage-users')">Users</button>
        <button onclick="showSection('view-surveys')">View Surveys</button>
        <button onclick="window.location.href='logoutHandler.php'">Log out</button>

        <div id="edit-surveys" class="section hidden">
            <h2>Edit Existing Surveys</h2>
            <div class="scrollable-container">
                <table class="survey-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($survey = $edit_surveys->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $survey['id']; ?></td>
                            <td><?php echo htmlspecialchars($survey['title']); ?></td>
                            <td>
                                <button type="button" onclick="window.location.href='editSurvey.php?id=<?php echo $survey['id']; ?>'">Edit</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="manage-users" class="section hidden">
            <h2>Users</h2>
            <form method="get">
                <input type="text" name="search" placeholder="Search by username or email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <input type="submit" value="Search">
                <span class="error-message"><?php echo $search_error; ?></span>
            </form>
            <div class="scrollable-container">
                <table class="user-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td id="username_<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></td>
                            <td id="email_<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <button type="button" onclick="window.location.href='viewUser.php?id=<?php echo $user['id']; ?>'">View</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="view-surveys" class="section hidden">
            <h2>View Surveys</h2>
            <div class="scrollable-container">
                <table class="survey-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($survey = $view_surveys->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $survey['id']; ?></td>
                            <td><?php echo htmlspecialchars($survey['title']); ?></td>
                            <td>
                                <button type="button" onclick="window.location.href='viewSurveyDetails.php?survey_id=<?php echo $survey['id']; ?>'">View</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>