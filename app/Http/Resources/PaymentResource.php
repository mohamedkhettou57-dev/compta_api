<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'invoice_id' => $this->invoice_id,
            'student_id' => $this->student_id,

            'payment_date' => $this->payment_date,
            'amount'       => $this->amount,
            'method'       => $this->method,
            'reference'    => $this->reference,
            'note'         => $this->note,

            'invoice' => new InvoiceResource($this->whenLoaded('invoice')),
            'student' => new StudentResource($this->whenLoaded('student')),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}