<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterCitizenRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true; // Tout le monde peut s'inscrire
    }

    /**
     * Récupère les règles de validation qui s'appliquent à la requête.
     */
    public function rules(): array
    {
        return [
            'prenom' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\p{L}\s\-\']+$/u',  // Lettres, traits d'union, apostrophes
            ],
            'nom' => [
                'required',
                'string',
                'min:2',
                'max:100',
                'regex:/^[\p{L}\s\-\']+$/u',
            ],
            'email' => [
                'required',
                'email',
                'unique:users',
                'max:255',
            ],
            'date_naissance' => [
                'required',
                'date',
                'before:today',
                'after:1900-01-01',  // Raisonnable pour un registre de population
            ],
            'lieu_naissance' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'regex:/^[\p{L}\s\-,]+$/u',  // Lettres, espaces, tirets, virgules
            ],
            'numero_registre' => [
                'required',
                'string',
                'min:5',
                'max:50',
                'unique:profils',
                'regex:/^[A-Za-z0-9\-\/]+$/',  // Alphanumériques, tirets, slashes
            ],
            'adresse' => [
                'required',
                'string',
                'min:10',
                'max:255',
            ],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]+$/',
                // Au moins 1 minuscule, 1 majuscule, 1 chiffre, 1 caractère spécial
            ],
        ];
    }

    /**
     * Messages de validation personnalisés
     */
    public function messages(): array
    {
        return [
            'prenom.required' => 'Le prénom est requis.',
            'prenom.min' => 'Le prénom doit contenir au moins 2 caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 100 caractères.',
            'prenom.regex' => 'Le prénom ne doit contenir que des lettres, espaces, tirets ou apostrophes.',
            
            'nom.required' => 'Le nom est requis.',
            'nom.min' => 'Le nom doit contenir au moins 2 caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 100 caractères.',
            'nom.regex' => 'Le nom ne doit contenir que des lettres, espaces, tirets ou apostrophes.',
            
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'email.max' => 'L\'email ne doit pas dépasser 255 caractères.',
            
            'date_naissance.required' => 'La date de naissance est requise.',
            'date_naissance.date' => 'La date doit être valide.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'date_naissance.after' => 'La date de naissance doit être après 1900.',
            
            'lieu_naissance.required' => 'Le lieu de naissance est requis.',
            'lieu_naissance.min' => 'Le lieu de naissance doit contenir au moins 3 caractères.',
            'lieu_naissance.max' => 'Le lieu de naissance ne doit pas dépasser 255 caractères.',
            'lieu_naissance.regex' => 'Le lieu de naissance contient des caractères invalides.',
            
            'numero_registre.required' => 'Le numéro de registre est requis.',
            'numero_registre.min' => 'Le numéro de registre doit contenir au moins 5 caractères.',
            'numero_registre.max' => 'Le numéro de registre ne doit pas dépasser 50 caractères.',
            'numero_registre.unique' => 'Ce numéro de registre est déjà utilisé.',
            'numero_registre.regex' => 'Le numéro de registre ne doit contenir que des alphanumérique, tirets ou slashes.',
            
            'adresse.required' => 'L\'adresse est requise.',
            'adresse.min' => 'L\'adresse doit contenir au moins 10 caractères.',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            
            'password.required' => 'Le mot de passe est requis.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins 1 minuscule, 1 majuscule, 1 chiffre et 1 caractère spécial (@$!%*?&).',
        ];
    }
}
