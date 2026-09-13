<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
// CREATE VIEW view_placement_applications AS
// SELECT
//     pa.id,
//     CONCAT(e.first_name, ' ', e.last_name) AS name,
//      pa.status,
//     e.phone,
//     e.profile_photo,
//     d.id AS designation_id,
//     d.name AS designation_name,
//     h.name AS hospital,
//     v.name AS vendor
// FROM placement_applications AS pa
// LEFT JOIN enrolments AS e ON pa.enrolment_id = e.id
// LEFT JOIN designations AS d ON e.designation = d.id
// LEFT JOIN users AS v ON v.id = e.vendor_id
// LEFT JOIN users AS h ON h.id = pa.hospital_id;
