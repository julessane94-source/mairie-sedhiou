<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MarkPaymentAsPaidRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isCitoyen();
    }

    /**
     * Récupère les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'numero_transaction' => [
                'nullable',
                'string',
                'max:100',
                'unique:payments,numero_transaction',
                'regex:/^[A-Z0-9\-]+$/i',  // Alphanumériques et tirets
            ],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'numero_transaction.max' => 'Le numéro de transaction ne doit pas dépasser 100 caractères.',
            'numero_transaction.unique' => 'Ce numéro de transaction existe déjà.',
            'numero_transaction.regex' => 'Le numéro de transaction doit contenir uniquement des caractères alphanumériques et tirets.',
        ];
    }
}
