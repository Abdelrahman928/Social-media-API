<?php

namespace App\Http\Controllers\api\v1\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\api\v1\WarningResource;
use App\Models\Warning;
use Illuminate\Support\Facades\Gate;

class WarningController extends Controller
{
    public function show(Warning $warning){
        Gate::authorize('view', $warning);

        return response()->json([
            'status' => 200,
            'Warning' => new WarningResource($warning)
        ], 200);
    }
}
