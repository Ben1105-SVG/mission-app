<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

/**
 * Base controller for all admin panel controllers.
 * 
 * All admin controllers should extend this class to maintain consistency.
 * Authentication and authorization middleware is applied at the route level.
 */
abstract class AdminController extends Controller
{
    //
}

