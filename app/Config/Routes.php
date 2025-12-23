<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'BookController::index');
$routes->get('books/filter', 'BookController::filter');
$routes->get('books/all', 'BookController::all');
$routes->get('books/all-key', 'BookController::all_key');
$routes->get('books/detail', 'BookController::detail');
$routes->post('books/add', 'BookController::add');
$routes->post('books/edit', 'BookController::edit');
$routes->post('books/upload-image', 'BookController::uploadImage');
$routes->get('books/next-kode', 'BookController::getNextKodeSekolah');

$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

$routes->get('peminjaman', 'TransactionController::peminjaman');
$routes->post('peminjaman/add', 'TransactionController::addBorrowing');
$routes->post('peminjaman/return', 'TransactionController::addReturn');
$routes->post('peminjaman/return-multiple', 'TransactionController::addReturn');

$routes->get('api/borrowings', 'TransactionController::apiBorrowings');
$routes->get('api/borrowings-all', 'TransactionController::apiAllBorrowings');
$routes->get('api/returns', 'TransactionController::apiReturns');
$routes->get('api/returns-all', 'TransactionController::apiAllReturns');

// API Routes - Book Analysis (Updated to use ApiController directly)
$routes->match(['get', 'post'], 'api/analyze-image', 'ApiController::analyzeImage');
$routes->get('api/test', 'ApiController::test');

// BUKU CRUD ROUTES
$routes->get('management-buku', 'BookManagementController::index');
$routes->post('management-buku/add', 'BookManagementController::add');
$routes->post('management-buku/edit/(:any)', 'BookManagementController::edit/$1');
$routes->match(['get','post'], 'management-buku/delete', 'BookManagementController::delete');
$routes->post('management-buku/importJson', 'BookManagementController::importJson');
$routes->get('management-buku/export-csv', 'BookManagementController::exportCsv');

// USER CRUD ROUTES
$routes->get('user', 'UserController::index');
$routes->get('user/list/(:segment)', 'UserController::list/$1');
$routes->post('user/add', 'UserController::addUser');
$routes->post('user/update/(:any)', 'UserController::updateUser/$1');
$routes->post('user/add-guru', 'UserController::addGuru');
$routes->post('user/update-guru/(:any)', 'UserController::updateGuru/$1');

// CLASS CRUD ROUTES
$routes->get('management-class', 'ClassController::index');
$routes->get('management-class/list', 'ClassController::list');
$routes->get('management-class/detail/(:any)', 'ClassController::detail/$1');
$routes->post('management-class/add', 'ClassController::add');
$routes->post('management-class/update/(:any)', 'ClassController::update/$1');
$routes->post('management-class/delete/(:any)', 'ClassController::delete/$1');
$routes->get('management-class/getUnassignedStudents', 'ClassController::getUnassignedStudents');
$routes->get('management-class/getUnassignedBooks', 'ClassController::getUnassignedBooks');
$routes->get('management-class/getClassMembers/(:any)', 'ClassController::getClassMembers/$1');

// AUTO SCAN ROUTES
$routes->get('automate', 'AutomateTransactionController::automateView');
$routes->post('automate/process', 'AutomateTransactionController::automateTransaction');

// FORGOT PASSWORD ROUTES
$routes->get('forgot-password', 'AuthController::resetPasswordPage');
$routes->post('verify-user-binekas', 'AuthController::verifyUser');           
$routes->post('reset-password-binekas', 'AuthController::resetPassword');

// PEMINJAMAN KELAS ROUTES
$routes->get('peminjaman-kelas', 'ClassTransactionController::index');
$routes->get('peminjaman-kelas/class-data', 'ClassTransactionController::getClassData');
$routes->get('peminjaman-kelas/all-books', 'ClassTransactionController::getAllBooks');
$routes->get('peminjaman-kelas/transactions', 'ClassTransactionController::getClassTransactions');
$routes->post('peminjaman-kelas/add', 'ClassTransactionController::addBorrowing');
$routes->post('peminjaman-kelas/return', 'ClassTransactionController::addReturn');
$routes->post('peminjaman-kelas/apply-late-penalties', 'ClassTransactionController::applyLatePenalties');