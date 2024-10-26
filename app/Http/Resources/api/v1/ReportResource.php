<?php

namespace App\Http\Resources\api\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'reporter_id' => $this->reporter_id,
            'reported_entity' => $this->reportable_type,
            'reported_entity_id' => $this->reportable_id,
            'report_type' => $this->report_type,
        ];
    }
}
