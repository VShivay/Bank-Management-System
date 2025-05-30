<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.html"); // Redirect to login page if not logged in
    exit();
}

$servername = "localhost";
$username = "root"; // Update with your database username
$password = ""; // Update with your database password
$dbname = "banksystem"; // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Now, you can proceed with the search functionality for logged-in admins
?>
<!DOCTYPE html>
<html lang="en" ng-app="userSearchApp">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Search</title>
    <script src="https://code.angularjs.org/1.8.2/angular.min.js"></script>
    <script src="searchController.js"></script>
    <style>
        
    /* General Reset */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        color: #333;
        padding: 10px;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 20px;
    }

    .container {
        max-width: 1000px;
        margin: 0 auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    form {
        margin-bottom: 20px;
    }

    label {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 5px;
        display: block;
    }

    select, input {
        width: 100%;
        padding: 10px;
        margin: 8px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 16px;
    }

    button {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
    }

    button:hover {
        background-color: #45a049;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th, td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    th {
        background-color: #4CAF50;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .no-results {
        text-align: center;
        font-size: 18px;
        color: #666;
        margin-top: 20px;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .container {
            padding: 15px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        label, select, input, button {
            font-size: 14px;
            padding: 8px;
        }

        table {
            font-size: 14px;
        }

        th, td {
            padding: 8px;
        }

        .no-results {
            font-size: 16px;
        }
    }

    @media (max-width: 480px) {
        h1 {
            font-size: 20px;
        }

        form {
            padding: 0;
        }

        select, input {
            font-size: 14px;
            padding: 10px;
        }

        button {
            font-size: 14px;
            padding: 10px 15px;
        }

        table {
            font-size: 12px;
        }

        th, td {
            padding: 6px;
        }

        .no-results {
            font-size: 14px;
        }
    }
</style>


</head>
<body>
    <div class="container" ng-controller="SearchController">
        <h1>Search Users</h1>

        <!-- Search Form -->
        <form ng-submit="searchUsers()">
            <!-- Dropdown to select the search filter type -->
            <label for="searchCriteria">Search By:</label>
            <select id="searchCriteria" ng-model="searchParams.criteria" ng-options="option for option in searchOptions">
                <option value="">Select a criteria</option>
            </select>

            <!-- Input field based on selected criteria -->
            <div ng-if="searchParams.criteria === 'Name'">
                <label for="name">Name:</label>
                <input type="text" id="name" ng-model="searchParams.name" />
            </div>
            <div ng-if="searchParams.criteria === 'Mobile Number'">
                <label for="mobileNumber">Mobile Number:</label>
                <input type="text" id="mobileNumber" ng-model="searchParams.mobileNumber" />
            </div>
            <div ng-if="searchParams.criteria === 'City'">
                <label for="city">City:</label>
                <input type="text" id="city" ng-model="searchParams.city" />
            </div>
            <div ng-if="searchParams.criteria === 'Address'">
                <label for="address">Address:</label>
                <input type="text" id="address" ng-model="searchParams.address" />
            </div>
            <div ng-if="searchParams.criteria === 'Account Number'">
                <label for="accountNumber">Account Number:</label>
                <input type="text" id="accountNumber" ng-model="searchParams.accountNumber" />
            </div>

            <button type="submit">Search</button>
        </form>

        <div ng-if="users && users.length > 0">
            <h2>Search Results:</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Mobile Number</th>
                        <th>Email</th>
                        <th>City</th>
                        <th>Account Number</th>
                        <th>Account Type</th>
                        <th>Balance</th>
                        <th>Address</th>
                        <th>Date of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="user in users">
                        <td>{{ user.Name }}</td>
                        <td>{{ user.MobileNumber }}</td>
                        <td>{{ user.Email }}</td>
                        <td>{{ user.City }}</td>
                        <td>{{ user.AccountNumber }}</td>
                        <td>{{ user.Acc_type }}</td>
                        <td>{{ user.Balance | currency }}</td>
                        <td>{{ user.Address }}</td>
                        <td>{{ user.DOB  | date:'dd-MM-yyyy'}}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div ng-if="!users || users.length === 0" class="no-results">
            <p>No results found.</p>
        </div>
    </div>
</body>
</html>
