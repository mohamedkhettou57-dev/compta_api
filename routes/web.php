<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Laravel is running';
});

Route::get('/test-post-student', function () {
    $token = csrf_token();

    return "
    <form method='POST' action='http://127.0.0.1:8000/api/students'>
        <input type='hidden' name='_token' value='{$token}'>
        <label>user_id</label><input name='user_id' value='1'><br><br>
        <label>cne</label><input name='cne' value='CNE88888'><br><br>
        <label>filiere</label><input name='filiere' value='Finance'><br><br>
        <label>niveau</label><input name='niveau' value='S4'><br><br>
        <label>annee_universitaire</label><input name='annee_universitaire' value='2025-2026'><br><br>
        <button type='submit'>Send POST</button>
    </form>
    ";
});