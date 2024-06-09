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
        #searchBar, #searchSurveyBar, #searchEditSurveyBar {
            width: calc(100% - 22px); /* Match the width of the scrollable container, minus padding and border */
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .button-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        .button-group button {
            flex: 1 1 auto;
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            margin-top: 10px;
        }
        .button-group button:hover {
            background-color: #0056b3;
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

        function filterUsers() {
            var input, filter, table, rows, username, email, i;
            input = document.getElementById('searchBar');
            filter = input.value.toUpperCase();
            table = document.getElementById('userTable');
            rows = table.getElementsByTagName('tr');

            for (i = 1; i < rows.length; i++) {
                username = rows[i].getElementsByTagName('td')[1];
                email = rows[i].getElementsByTagName('td')[2];
                if (username || email) {
                    if (username.innerHTML.toUpperCase().includes(filter) || email.innerHTML.toUpperCase().includes(filter)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        function filterSurveys() {
            var input, filter, table, rows, title, i;
            input = document.getElementById('searchSurveyBar');
            filter = input.value.toUpperCase();
            table = document.getElementById('surveyTable');
            rows = table.getElementsByTagName('tr');

            for (i = 1; i < rows.length; i++) {
                title = rows[i].getElementsByTagName('td')[1];
                if (title) {
                    if (title.innerHTML.toUpperCase().includes(filter)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }

        function filterEditSurveys() {
            var input, filter, table, rows, title, i;
            input = document.getElementById('searchEditSurveyBar');
            filter = input.value.toUpperCase();
            table = document.getElementById('editSurveyTable');
            rows = table.getElementsByTagName('tr');

            for (i = 1; i < rows.length; i++) {
                title = rows[i].getElementsByTagName('td')[1];
                if (title) {
                    if (title.innerHTML.toUpperCase().includes(filter)) {
                        rows[i].style.display = "";
                    } else {
                        rows[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
        
        <div class="button-group">
            <button onclick="showSection('edit-surveys')">Edit Existing Surveys</button>
            <button onclick="window.location.href='createSurvey.php'">Create a New Survey</button>
            <button onclick="showSection('manage-users')">Users</button>
            <button onclick="showSection('view-surveys')">View Surveys</button>
            <button onclick="window.location.href='logoutHandler.php'">Log out</button>
        </div>

        <div id="edit-surveys" class="section hidden">
            <h2>Edit Existing Surveys</h2>
            <input type="text" id="searchEditSurveyBar" onkeyup="filterEditSurveys()" placeholder="Search by survey title">
            <div class="scrollable-container">
                <table class="survey-table" id="editSurveyTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($survey = $edit_surveys->fetch_assoc()): ?>
                        <tr class="survey-item">
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
            <input type="text" id="searchBar" onkeyup="filterUsers()" placeholder="Search by username or email">
            <div class="scrollable-container">
                <table class="user-table" id="userTable">
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
                        <tr class="user-item">
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
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
            <input type="text" id="searchSurveyBar" onkeyup="filterSurveys()" placeholder="Search by survey title">
            <div class="scrollable-container">
                <table class="survey-table" id="surveyTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($survey = $view_surveys->fetch_assoc()): ?>
                        <tr class="survey-item">
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