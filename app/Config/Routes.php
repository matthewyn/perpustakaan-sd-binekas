<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get("/", "BookController::index");
$routes->get("books/filter", "BookController::filter");
$routes->get("books/search-autocomplete", "BookController::searchBooks");
$routes->get("books/detail", "BookController::detail");
$routes->post("books/add", "BookController::add");
$routes->get("books/next-kode", "BookController::getNextKodeSekolah");

$routes->get("login", "AuthController::login");
$routes->post("login", "AuthController::attemptLogin");
$routes->get("logout", "AuthController::logout");

$routes->get("peminjaman-perpustakaan", "TransactionController::peminjaman");
$routes->post(
    "peminjaman-perpustakaan/add",
    "TransactionController::addBorrowing"
);
$routes->post(
    "peminjaman-perpustakaan/return-multiple",
    "TransactionController::addReturn"
);

$routes->get("transaction/borrowings", "TransactionController::getBorrowings");
$routes->get("transaction/borrowings-all", "TransactionController::getAllBorrowings");
$routes->get("transaction/returns", "TransactionController::getReturns");
$routes->get("transaction/returns-all", "TransactionController::getAllReturns");

$routes->match(
    ["get", "post"],
    "api/analyze-image",
    "ApiController::analyzeImage"
);
$routes->get("api/test", "ApiController::test");

$routes->get("management-buku", "BookManagementController::index");
$routes->post(
    "management-buku/edit/(:any)",
    'BookManagementController::edit/$1'
);
$routes->match(
    ["get", "post"],
    "management-buku/delete",
    "BookManagementController::delete"
);
$routes->post(
    "management-buku/importJson",
    "BookManagementController::importJson"
);
$routes->get(
    "management-buku/export-csv",
    "BookManagementController::exportCsv"
);
$routes->get(
    "management-buku/get-book-borrowers",
    "BookManagementController::getBookBorrowers"
);

$routes->get("user", "UserController::index");
$routes->get("user/list/(:segment)", 'UserController::list/$1');
$routes->post("user/add", "UserController::addUser");
$routes->post("user/reset-trust-score", "UserController::resetTrustScore");
$routes->post("user/update/(:any)", 'UserController::updateUser/$1');
$routes->post("user/add-guru", "UserController::addGuru");
$routes->post("user/update-guru/(:any)", 'UserController::updateGuru/$1');

$routes->get("classes", "ClassController::index");
$routes->get("classes/list", "ClassController::list");
$routes->post("classes/add", "ClassController::add");
$routes->post("classes/update/(:any)", 'ClassController::update/$1');
$routes->post("classes/delete/(:any)", 'ClassController::delete/$1');
$routes->get(
    "classes/getUnassignedStudents",
    "ClassController::getUnassignedStudents"
);
$routes->get(
    "classes/getUnassignedBooks",
    "ClassController::getUnassignedBooks"
);
$routes->get(
    "classes/getClassMembers/(:any)",
    'ClassController::getClassMembers/$1'
);

$routes->get("automate", "AutomateTransactionController::automateView");
$routes->post(
    "automate/process",
    "AutomateTransactionController::automateTransaction"
);

$routes->get("forgot-password", "AuthController::resetPasswordPage");
$routes->post("verify-user-binekas", "AuthController::verifyUser");
$routes->post("reset-password-binekas", "AuthController::resetPassword");

$routes->get("peminjaman-kelas", "ClassTransactionController::index");
$routes->get(
    "peminjaman-kelas/class-data",
    "ClassTransactionController::getClassData"
);
$routes->post(
    "peminjaman-kelas/transactions",
    "ClassTransactionController::getClassTransactions"
);
$routes->post(
    "peminjaman-kelas/add",
    "ClassTransactionController::addBorrowing"
);
$routes->post(
    "peminjaman-kelas/return-multiple",
    "ClassTransactionController::returnMultiple"
);
$routes->post(
    "peminjaman-kelas/apply-late-penalties",
    "ClassTransactionController::applyLatePenalties"
);

$routes->get("management-website", "WebsiteManagementController::index");
$routes->post(
    "management-website/update",
    "WebsiteManagementController::update"
);
