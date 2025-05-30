var app = angular.module('userSearchApp', []);

app.controller('SearchController', function($scope, $http) {
    // Available search options for the drop-down menu
    $scope.searchOptions = ['Name', 'Mobile Number', 'City', 'Address', 'Account Number'];

    // Initialize search parameters
    $scope.searchParams = {
        criteria: '', // Will hold the selected search criteria
        name: '',
        mobileNumber: '',
        city: '',
        address: '',
        accountNumber: ''
    };

    // Function to trigger the API call
    $scope.searchUsers = function() {
        // Prepare the query parameters dynamically based on the selected filter
        var queryParams = {};
        
        if ($scope.searchParams.criteria === 'Name') {
            queryParams.name = $scope.searchParams.name;
        } else if ($scope.searchParams.criteria === 'Mobile Number') {
            queryParams.mobileNumber = $scope.searchParams.mobileNumber;
        } else if ($scope.searchParams.criteria === 'City') {
            queryParams.city = $scope.searchParams.city;
        } else if ($scope.searchParams.criteria === 'Address') {
            queryParams.address = $scope.searchParams.address;
        } else if ($scope.searchParams.criteria === 'Account Number') {
            queryParams.accountNumber = $scope.searchParams.accountNumber;
        }

        // Call the PHP API with the selected filter and its value
        $http({
            method: 'GET',
            url: 'search_user.php',
            params: queryParams
        }).then(function(response) {
            $scope.users = response.data;
        }, function(error) {
            console.error('Error occurred:', error);
        });
    };
});
