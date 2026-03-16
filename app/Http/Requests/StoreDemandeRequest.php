<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDemandeRequest extends FormRequest
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
            'titre' => [
                'required',
                'string',
                'min:5',
                'max:255',
            ],
            'description' => [
                'required',
                'string',
                'min:20',
                'max:2000',
            ],
            'type' => [
                'required',
                'string',
                'max:100',
            ],
            'priorite' => [
                'required',
                'in:basse,normale,haute,urgente',
            ],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'titre.required' => 'Le titre est requis.',
            'titre.min' => 'Le titre doit contenir au moins 5 caractères.',
            'titre.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description est requise.',
            'description.min' => 'La description doit contenir au moins 20 caractères.',
            'description.max' => 'La description ne doit pas dépasser 2000 caractères.',
            'type.required' => 'Le type de demande est requis.',
            'priorite.required' => 'La priorité est requise.',
            'priorite.in' => 'La priorité sélectionnée est invalide.',
        ];
    }
}
