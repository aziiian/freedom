<?php
include '../bin/conn.php';
include '../bin/access.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['adminName'])) {
    header("Location: ../sign-in.php");
    exit;
}

// Fetch the admin's full name from the database
$adminFullName = "Admin"; // Default if query fails
if ($stmt = $conn->prepare("SELECT fullname FROM admin WHERE username = ?")) {
    $stmt->bind_param("s", $_SESSION['adminName']);
    $stmt->execute();
    $stmt->bind_result($fullName);
    if ($stmt->fetch()) {
        $adminFullName = $fullName;
    }
    $stmt->close();
}

// Fetch all admins from the database
$sql_admins = "SELECT aid, username, fullname, phoneno FROM admin";
$admins_result = $conn->query($sql_admins);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Management</title>
  <link rel="stylesheet" href="../css/adm.css">
  <style>
    /* General Styles */
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f9;
    color: #333;
    }

    header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    background-color: #2ea2cc;
    color: white;
    border-bottom: 4px solid #2ea2cc;
    }

    header img {
    height: 50px;
    }

    header h1 {
    margin: 0;
    font-size: 24px;
    }

    header .admin-name {
    font-size: 16px;
    font-weight: bold;
    }

    /* Container */
    .admin-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    width: 40%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    }

    .add-user-btn, .back-btn {
    background-color: #2ea2cc;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
    border-radius: 5px;
    }

    .add-user-btn:hover, .back-btn:hover {
    background-color: #0056b3;
    }

    /* Table */
    table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    padding: 20px;
    }

    table th, table td {
    text-align: left;
    padding: 10px;
    border: 1px solid #ddd;
    }

    table th {
    background-color: #000;
    color: white;
    }

    table tr:nth-child(even) {
    background-color: #f9f9f9;
    }

    table tr:hover {
    background-color: #f1f1f1;
    }

    /* Action Buttons */
    .action-btn {
    padding: 5px 10px;
    border: none;
    width: auto;
    border-radius: 5px;
    cursor: pointer;
    color: white;
    }

    .edit-btn {
    background-color: #FFC107;
    }

    .edit-btn:hover {
    background-color: #d39e00;
    }

    .delete-btn {
    background-color: #DC3545;
    }

    .delete-btn:hover {
    background-color: #a71d2a;
    }

    /* Popup and Overlay */
    .overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
    }

    .overlay.active {
    display: block;
    }

    .flex-d{
        display: flex;
        padding: 5px;
        justify-content: space-between;
    }
    .popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 400px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    }

    .popup.active {
    display: block;
    }

    .popup h3 {
    margin-top: 0;
    color: #007BFF;
    }

    .popup form {
    display: flex;
    flex-direction: column;
    }

    .popup form label {
    margin-top: 10px;
    font-weight: bold;
    }

    .popup form input, .popup form select {
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 14px;
    }

    .popup form button {
    margin-top: 20px;
    background-color: #007BFF;
    color: white;
    padding: 10px 15px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    border-radius: 5px;
    }

    .popup form button:hover {
    background-color: #0056b3;
    }

    /* Responsive Design */
    @media screen and (max-width: 600px) {
    .admin-container {
        padding: 10px;
    }

    .header {
        flex-direction: column;
        align-items: flex-start;
    }

    table th, table td {
        font-size: 12px;
    }

    .popup {
        width: 90%;
    }
    }

  </style>
</head>

<body>
  <header>
    <img src="../css/shofco.png" alt="Logo">
    <h1>Admin Management</h1>
    <div class="admin-name">
      <?php echo "Welcome, " . htmlspecialchars($adminFullName); ?>
    </div>
  </header>

  <main>
    <div class="admin-container">
      <div class="header">
        <button class="add-user-btn" onclick="togglePopup('add')">Add New User</button>
        <a href="../admin.php" class="back-btn">Back to Admin</a>
      </div>

      <!-- Add/Edit User Popup -->
      <div class="overlay" id="overlay" onclick="togglePopup('')"></div>
      <div class="popup" id="popup">
        <h3 id="popup-title">Add New User</h3>
        <form id="user-form" action="process_admin.php" method="POST">
          <input type="hidden" name="action" value="add" id="form-action">
          <input type="hidden" name="admin_id" id="admin-id">

          <label for="username">Username</label>
          <input type="text" name="username" id="username" required>

          <label for="fullname">Full Name</label>
          <input type="text" name="fullname" id="fullname" required>

          <label for="password">Password (leave blank to keep current)</label>
          <input type="password" name="password" id="password">

          <label for="phoneno">Phone Number</label>
          <input type="text" name="phoneno" id="phoneno" required>

          <label for="aid">Access Level</label>
          <select name="aid" id="aid" required>
              <option value="1">Super Admin</option>
              <option value="2">Admin</option>
              <option value="3">Staff</option>
          </select>

          <button type="submit">Save</button>
      </form>

      </div>

      <!-- Admins Table -->
      <table>
        <thead>
          <tr>
            <th>Full Name</th>
            <th>Phone Number</th>
            <th>Access Level</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $admins_result->fetch_assoc()) : ?>
            <tr>
              <td><?= htmlspecialchars($row['fullname']) ?></td>
              <td><?= htmlspecialchars($row['phoneno']) ?></td>
              <td>
                <?php
                switch ($row['aid']) {
                  case 1:
                    echo "Super Admin";
                    break;
                  case 2:
                    echo "Admin";
                    break;
                  case 3:
                    echo "Staff";
                    break;
                }
                ?>
              </td>
              <td class="flex-d">
                <button class="action-btn edit-btn" onclick="editUser(<?= htmlspecialchars(json_encode($row)) ?>)">Edit</button>
                <form action="process_admin.php" method="POST" style="display: inline;">  
                <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="admin_id" value="<?= htmlspecialchars($row['aid']) ?>">
                  <button class="action-btn delete-btn" type="submit" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    function togglePopup(action = 'add') {
        const overlay = document.getElementById('overlay');
        const popup = document.getElementById('popup');
        overlay.classList.toggle('active');
        popup.classList.toggle('active');
    }

    function editUser(adminData) {
        togglePopup('edit'); // Open the popup

        // Populate the form fields with admin data
        document.getElementById('popup-title').textContent = 'Edit User';
        document.getElementById('form-action').value = 'edit';
        document.getElementById('admin-id').value = adminData.aid;
        document.getElementById('username').value = adminData.username;
        document.getElementById('fullname').value = adminData.fullname;
        document.getElementById('password').value = ''; // Leave empty for security
        document.getElementById('phoneno').value = adminData.phoneno;
        document.getElementById('aid').value = adminData.accessLevel;
    }
  </script>

</body>

</html>
