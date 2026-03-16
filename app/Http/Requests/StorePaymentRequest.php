<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'montant' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'regex:/^\d+(\.\d{1,2})?$/',  // Valide à 2 décimales max
            ],
            'methode_paiement' => [
                'required',
                'string',
                'in:virement,cheque,especes,carte,mobile_money',
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
                'regex:/^[\w\s\.,\-àâäéèêëïîôûœùç]+$/i',  // Caractères basiques + accents
            ],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'montant.required' => 'Le montant est requis.',
            'montant.numeric' => 'Le montant doit être un nombre.',
            'montant.min' => 'Le montant doit être au minimum 0,01.',
            'montant.max' => 'Le montant ne doit pas dépasser 999 999,99.',
            'montant.regex' => 'Le montant doit avoir au maximum 2 décimales.',
            'methode_paiement.required' => 'La méthode de paiement est requise.',
            'methode_paiement.in' => 'La méthode de paiement sélectionnée est invalide.',
            'description.max' => 'La description ne doit pas dépasser 500 caractères.',
            'description.regex' => 'La description contient des caractères non autorisés.',
        ];
    }
}
