<?php

namespace Modules\System\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function __construct()
    {
       // $this->middleware('permission:system-list|system-create|system-edit|system-delete', ['only' => ['index','show']]);
       // $this->middleware('permission:system-create', ['only' => ['create','store']]);
       // $this->middleware('permission:system-edit', ['only' => ['edit','update']]);
       // $this->middleware('permission:system-delete', ['only' => ['destroy']]);
    }

    public function index()
    {
        return view('System::system');
    }
}