<?php

namespace App\Http\Requests\API;

use App\Services\ReserveOffer\ReserveOfferCommand;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class OfferReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_reference' => ['required', 'string', 'max:255'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'max:255'],
        ];
    }

    public function getCommand(int $offerId): ReserveOfferCommand
    {
        return new ReserveOfferCommand(
            offerId: $offerId,
            clientReference: $this->validated('client_reference'),
            customerName: $this->validated('customer_name'),
            customerEmail: $this->validated('customer_email'),
        );
    }
}
