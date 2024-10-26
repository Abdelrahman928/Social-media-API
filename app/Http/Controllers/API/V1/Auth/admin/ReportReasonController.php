<?php

namespace App\Http\Controllers\api\v1\auth\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\api\v1\ReportReasonRequest;
use App\Models\ReportType;

class ReportReasonController extends Controller
{
    public function create(ReportReasonRequest $request){
        $data = $request->validated();

        ReportType::create($data);

        return response()->json([
            'status' => 201,
            'message' => 'created'
        ], 201);
    }
}
