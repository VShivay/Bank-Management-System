-- Create Database
CREATE DATABASE BankSystem;
USE BankSystem;
-- User Table
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    MobileNumber VARCHAR(10) NOT NULL UNIQUE,
    DOB DATE NOT NULL,
    Email VARCHAR(255) NOT NULL,
    IFSCCode VARCHAR(11) NOT NULL,
    PANCardNumber VARCHAR(11) NOT NULL,
    AadhaarNumber VARCHAR(16) NOT NULL UNIQUE,
    Address VARCHAR(255) NOT NULL,
    City VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL,
    PinCode VARCHAR(6) NOT NULL
);
-- Admin Table
CREATE TABLE Admins (
    AdminID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(255) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL
);
-- Cashier Table
CREATE TABLE Cashiers (
    CashierID VARCHAR(6) PRIMARY KEY,
    Name VARCHAR(255) NOT NULL,
    Password VARCHAR(255) NOT NULL
);
-- Account Table
CREATE TABLE Accounts (
    AccountID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    AccountNumber CHAR(12) NOT NULL UNIQUE,
    Acc_type ENUM('Saving', 'Current') NOT NULL,
    Balance DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
-- Transaction Table
CREATE TABLE Transactions (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY,
    AccountID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    TransactionType ENUM('Deposit', 'Withdrawal', 'Transfer', 'DebitCard','FD Creation','BillPay') NOT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    RecipientAccountID INT NULL,
    FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID),
    FOREIGN KEY (RecipientAccountID) REFERENCES Accounts(AccountID)
);
-- Updated CreditCards Table
CREATE TABLE CreditCards (
    CreditCardID INT AUTO_INCREMENT PRIMARY KEY,
    UserID INT NOT NULL,
    CardNumber CHAR(12) NOT NULL UNIQUE, -- Updated to 12 digits
    ExpiryDate DATE NOT NULL,
    CVV CHAR(3) NOT NULL,
    CardLimit DECIMAL(10, 2) NULL, -- Initially NULL, set by admin upon approval
    CurrentOutstanding DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    Status ENUM('Pending', 'Approved', 'Rejected','BillPay') DEFAULT 'Pending',
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (UserID) REFERENCES Users(UserID)
);
// Function to generate a random 12-digit card number
function generateCardNumber() {
    return str_pad(rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
}
CREATE TABLE CreditCardTransactions (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY,
    CreditCardID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    TransactionType ENUM('Payment') NOT NULL,
    PaymentType ENUM('CardBill', 'Cashier') NOT NULL,
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (CreditCardID) REFERENCES CreditCards(CreditCardID)
);
-- Debit Cards Table
CREATE TABLE DebitCards (
    DebitCardID INT AUTO_INCREMENT PRIMARY KEY,
    AccountID INT NOT NULL,
    CardNumber CHAR(16) NOT NULL UNIQUE, -- 16-digit debit card number
    ExpiryDate DATE NOT NULL,
    CVV CHAR(3) NOT NULL,
    Status ENUM('Active', 'Blocked', 'Suspended') DEFAULT 'Active',
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
);
-- Trigger to auto-create Debit Card when an Account is created
DELIMITER //
CREATE TRIGGER CreateDebitCardAfterAccountInsert
AFTER INSERT ON Accounts
FOR EACH ROW
BEGIN
    INSERT INTO DebitCards (AccountID, CardNumber, ExpiryDate, CVV, Status)
    VALUES (NEW.AccountID, 
            LPAD(FLOOR(RAND() * 10000000000000000), 16, '0'),  -- Random 16-digit number
            DATE_ADD(CURRENT_DATE, INTERVAL 3 YEAR),            -- 3 years expiry from today
            LPAD(FLOOR(RAND() * 1000), 3, '0'),                -- Random 3-digit CVV
            'Active');                                          -- Default status is 'Active'
END //
DELIMITER ;
CREATE TABLE DebitCardTransactions (
    TransactionID INT AUTO_INCREMENT PRIMARY KEY,
    DebitCardID INT NOT NULL,
    Amount DECIMAL(10, 2) NOT NULL,
    TransactionType ENUM('Payment', 'Refund') NOT NULL,  -- For payment or refund type transactions
    Timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (DebitCardID) REFERENCES DebitCards(DebitCardID)
);
-- Fixed Deposits Table with Default Interest RateF
CREATE TABLE FixedDeposits (
    FDID INT AUTO_INCREMENT PRIMARY KEY, -- Unique identifier for Fixed Deposits
    UserID INT NOT NULL, -- Foreign key from Users table
    AccountID INT NOT NULL, -- Foreign key from Accounts table
    Amount DECIMAL(10, 2) NOT NULL, -- Deposit amount
    InterestRate DECIMAL(5, 2) NOT NULL DEFAULT 7.00, -- Default annual interest rate is 7%
    TenureMonths INT NOT NULL, -- Tenure in months (e.g., 12, 24, 36 months)
    MaturityAmount DECIMAL(10, 2) GENERATED ALWAYS AS (Amount * POW(1 + (InterestRate / 100 / 12), TenureMonths)) STORED, -- Auto-calculated maturity amount
    StartDate DATE DEFAULT CURRENT_DATE, -- Start date of the fixed deposit
    MaturityDate DATE GENERATED ALWAYS AS (DATE_ADD(StartDate, INTERVAL TenureMonths MONTH)) STORED, -- Auto-calculated maturity date
    Status ENUM('Active', 'Closed', 'Premature') DEFAULT 'Active', -- Status of the FD
    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
);
INSERT INTO Admins VALUES ('1','admin','a123');



