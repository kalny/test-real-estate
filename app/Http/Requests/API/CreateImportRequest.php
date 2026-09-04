<?php

namespace App\Http\Requests\API;

use App\Enums\Currency;
use App\Models\Supplier;
use App\Services\CreateImport\CreateImportCommand;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateImportRequest extends FormRequest
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
            'supplier' => [
                'required',
                'string',
                Rule::exists(Supplier::class, 'name'),
            ],
            'external_import_id' => [
                'required',
                'string',
                'max:255',
            ],
            'sent_at' => [
                'required',
                'date_format:Y-m-d\TH:i:s\Z',
            ],
            'offers' => [
                'required',
                'array',
            ],
            'offers.*.external_id' => [
                'required',
                'string',
                'max:255',
            ],
            'offers.*.property' => [
                'required',
                'array',
            ],
            'offers.*.property.code' => [
                'required',
                'string',
                'max:255',
            ],
            'offers.*.property.name' => [
                'required',
                'string',
                'max:255',
            ],
            'offers.*.property.city' => [
                'required',
                'string',
                'max:255',
            ],
            'offers.*.check_in' => [
                'required',
                'date_format:Y-m-d',
            ],
            'offers.*.check_out' => [
                'required',
                'date_format:Y-m-d',
                'after:offers.*.check_in',
            ],
            'offers.*.max_guests' => [
                'required',
                'integer',
                'min:1',
            ],
            'offers.*.price' => [
                'required',
                'integer',
                'min:0',
            ],
            'offers.*.currency' => [
                'required',
                Rule::enum(Currency::class),
            ],
            'offers.*.available_units' => [
                'required',
                'integer',
                'min:0',
            ],
            'offers.*.expires_at' => [
                'required',
                'date_format:Y-m-d\TH:i:s\Z',
            ],
        ];
    }

    public function getCommand(): CreateImportCommand
    {
        return new CreateImportCommand(
            supplier: $this->validated('supplier'),
            externalImportId: $this->validated('external_import_id'),
            payload: $this->validated(),
            sentAt: CarbonImmutable::parse($this->validated('sent_at'))
        );
    }
}
