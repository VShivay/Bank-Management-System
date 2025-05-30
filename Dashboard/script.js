document.querySelector("form").addEventListener("submit", function (event) {
  const mobileNumber = document.querySelector('[name="mobileNumber"]').value;
  const password = document.querySelector('[name="password"]').value;

  if (mobileNumber.length !== 10 || isNaN(mobileNumber)) {
    alert("Please enter a valid 10-digit mobile number");
    event.preventDefault();
  }

  if (password.length < 6) {
    alert("Password must be at least 6 characters");
    event.preventDefault();
  }
});
// Example of client-side validation for the Transfer Funds form
document.querySelector("form").addEventListener("submit", function (event) {
  const recipientAccountId = document.querySelector(
    '[name="recipient_account_id"]'
  ).value;
  const amount = document.querySelector('[name="amount"]').value;

  // Check if the recipient account ID is empty
  if (recipientAccountId.trim() === "") {
    alert("Recipient account ID is required.");
    event.preventDefault(); // Prevent form submission
  }

  // Validate that the amount is a positive number
  if (isNaN(amount) || amount <= 0) {
    alert("Please enter a valid amount.");
    event.preventDefault(); // Prevent form submission
  }
});
