<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $poll = $this->route('poll');

        return [
            'items' => ['required', 'array', 'size:'.$poll->podium_size],
            'items.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('poll_items', 'id')->where('poll_id', $poll->id),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $poll = $this->route('poll');
            $items = $this->input('items');

            if (! is_array($items)) {
                return;
            }

            $positions = array_map('intval', array_keys($items));
            sort($positions);

            if ($positions !== range(1, $poll->podium_size)) {
                $validator->errors()->add(
                    'items',
                    'Escolha exatamente um item para cada posição do pódio.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.size' => 'Preencha todas as :size posições do pódio.',
            'items.*.distinct' => 'Um mesmo item não pode ocupar mais de uma posição.',
            'items.*.exists' => 'Item inválido para esta votação.',
        ];
    }
}
