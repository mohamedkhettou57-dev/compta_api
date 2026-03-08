<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'student_id' => $this->student_id,

            'invoice_date' => $this->invoice_date,
            'due_date'     => $this->due_date,

            'total_amount' => $this->total_amount,
            'paid_amount'  => $this->paid_amount,
            'due_amount'   => $this->due_amount,
            'status'       => $this->status,

            'note' => $this->note,

            // relations (only if loaded)
            'student'  => new StudentResource($this->whenLoaded('student')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}