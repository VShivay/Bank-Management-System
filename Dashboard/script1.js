document.addEventListener("DOMContentLoaded", function() {
    const userMenuButton = document.getElementById("user-menu-button");
    const userMenu = document.getElementById("user-menu");
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    userMenuButton.addEventListener("click", function(event) {
        userMenu.classList.toggle("hidden");
        event.stopPropagation();
    });

    document.addEventListener("click", function(event) {
        if (!userMenu.contains(event.target) && !userMenuButton.contains(event.target)) {
            userMenu.classList.add("hidden");
        }
    });

    mobileMenuButton.addEventListener("click", function() {
        mobileMenu.classList.toggle("hidden");
    });
});
document.addEventListener("DOMContentLoaded", function() {
    fetchChartData();
});

function fetchChartData() {
    fetch('get_credit_card_chart.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('creditCardChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Outstanding Amount', 'Remaining Balance'],
                    datasets: [{
                        data: [data.outstanding, data.remaining],
                        backgroundColor: ['#ff6f61', '#4caf50'],
                        borderColor: ['#ff6f61', '#4caf50'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                color: 'black',
                                font: {
                                    size: 14
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': $' + tooltipItem.raw.toFixed(2);
                                }
                            }
                        }
                    }
                }
            });

            // Update usage details
            const totalLimit = data.limit;
            const usagePercentage = ((data.outstanding / totalLimit) * 100).toFixed(2);
            document.getElementById('usagePercentage').innerText = usagePercentage + '%';
            document.getElementById('availableLimit').innerText = '$' + data.remaining.toFixed(2);
            document.getElementById('totalLimit').innerText = '$' + totalLimit;
        })
        .catch(error => console.error('Error fetching chart data:', error));
}

angular.module('bankApp', [])
            .controller('BankController', function($scope, $http) {
                // Fetch transactions
                $http.get('getTransactions.php')
                    .then(function(response) {
                        if (response.data.error) {
                            console.error(response.data.error);
                        } else {
                            $scope.transactions = response.data;
                        }
                    }, function(error) {
                        console.error('Error fetching transactions:', error);
                    });
                    $scope.formatAmount = function(transaction) {
                        if (transaction.TransactionType === 'Deposit') {
                            return '+' + transaction.Amount;
                        }else{
                            return '-'+transaction.Amount;

                        }
                        
                    };

                // Fetch Fixed Deposit details
                $http.get('getFDDetails.php')
                .then(function(response) {
                    if (response.data.error) {
                        console.error(response.data.error);
                    } else {
                        $scope.fixedDeposits = response.data;

                        // Use plain JavaScript to add click event listeners
                        setTimeout(function() {
                            response.data.forEach(fd => {
                                var fdElement = document.getElementById("fd-" + fd.FDID);
                                if (fdElement) {
                                    fdElement.onclick = function() {
                                        window.location.href = 'fd_details.php?fdid=' + fd.FDID;
                                    };
                                }
                            });
                        }, 500);
                    }
                }, function(error) {
                    console.error('Error fetching fixed deposits:', error);
                });

                // Function to format amount with + for Deposit
                
            });
    