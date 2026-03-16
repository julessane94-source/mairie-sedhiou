<?php

namespace App\Http\Controllers\Admin;

use App\Models\PlatformSettings;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    /**
     * Afficher les paramètres
     */
    public function index(): View
    {
        $settings = PlatformSettings::all();
        
        // Organiser par catégorie
        $categories = $this->organizeSettings($settings);

        return view('admin.settings.index', compact('categories', 'settings'));
    }

    /**
     * Mettre à jour un paramètre
     */
    public function update(Request $request, string $cle): RedirectResponse
    {
        $setting = PlatformSettings::where('cle', $cle)->first();

        if (!$setting || !$setting->isModifiable()) {
            return back()->with('error', 'Ce paramètre ne peut pas être modifié.');
        }

        $validated = $request->validate([
            'valeur' => 'required|string|max:1000',
        ]);

        $setting->update($validated);

        return back()->with('success', 'Paramètre mis à jour avec succès.');
    }

    /**
     * Afficher les paramètres applicatifs
     */
    public function application(): View
    {
        return view('admin.settings.application');
    }

    /**
     * Mettre à jour les paramètres applicatifs
     */
    public function updateApplication(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_description' => 'nullable|string|max:500',
            'app_logo' => 'nullable|image|max:5120',
            'app_email' => 'required|email',
            'app_phone' => 'nullable|string|max:20',
            'app_address' => 'nullable|string|max:255',
        ]);

        // Gérer l'upload du logo
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('logos', 'public');
            PlatformSettings::set('app_logo', $path);
        }

        // Mettre à jour les autres paramètres
        PlatformSettings::set('app_name', $validated['app_name']);
        PlatformSettings::set('app_description', $validated['app_description'] ?? '');
        PlatformSettings::set('app_email', $validated['app_email']);
        PlatformSettings::set('app_phone', $validated['app_phone'] ?? '');
        PlatformSettings::set('app_address', $validated['app_address'] ?? '');

        return back()->with('success', 'Paramètres applicatifs mis à jour.');
    }

    /**
     * Paramètres de fonctionnement
     */
    public function operations(): View
    {
        return view('admin.settings.operations');
    }

    /**
     * Mettre à jour les paramètres opérationnels
     */
    public function updateOperations(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'max_demandes_par_agent' => 'required|integer|min:1|max:100',
            'delai_reponse_jours' => 'required|integer|min:1|max:30',
            'activer_paiements_en_ligne' => 'boolean',
            'devise_par_defaut' => 'required|string|in:XOF,EUR,USD',
            'heures_travail_par_jour' => 'required|numeric|min:1|max:24',
            'jour_repos_hebdo' => 'required|in:lundi,mardi,mercredi,jeudi,vendredi,samedi,dimanche',
        ]);

        foreach ($validated as $cle => $valeur) {
            PlatformSettings::set($cle, $valeur);
        }

        return back()->with('success', 'Paramètres opérationnels mis à jour.');
    }

    /**
     * Paramètres de sécurité
     */
    public function security(): View
    {
        return view('admin.settings.security');
    }

    /**
     * Mettre à jour les paramètres de sécurité
     */
    public function updateSecurity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activer_2fa' => 'boolean',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'tentatives_login' => 'required|integer|min:3|max:10',
            'duree_lockout_minutes' => 'required|integer|min:5|max:120',
            'require_https' => 'boolean',
        ]);

        foreach ($validated as $cle => $valeur) {
            PlatformSettings::set($cle, $valeur, $valeur === 'on' ? 'boolean' : 'integer');
        }

        return back()->with('success', 'Paramètres de sécurité mis à jour.');
    }

    /**
     * Paramètres de notification
     */
    public function notifications(): View
    {
        return view('admin.settings.notifications');
    }

    /**
     * Mettre à jour les paramètres de notification
     */
    public function updateNotifications(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'activer_emails' => 'boolean',
            'activer_sms' => 'boolean',
            'activer_notifications_push' => 'boolean',
            'email_confirmation' => 'boolean',
            'email_rappel_paiement' => 'boolean',
            'email_notif_demande' => 'boolean',
        ]);

        foreach ($validated as $cle => $valeur) {
            PlatformSettings::set($cle, $valeur, 'boolean');
        }

        return back()->with('success', 'Paramètres de notification mis à jour.');
    }

    /**
     * Afficher les logs système
     */
    public function logs(): View
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = file_exists($logFile) ? file_get_contents($logFile) : 'Aucun log disponible';

        // Dernières 100 lignes
        $lines = array_slice(explode("\n", $logs), -100);

        return view('admin.settings.logs', compact('lines'));
    }

    /**
     * Nettoyer les logs
     */
    public function clearLogs(): RedirectResponse
    {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
        }

        return back()->with('success', 'Logs nettoyés.');
    }

    /**
     * Sauvegarder la base de données
     */
    public function backup(): RedirectResponse
    {
        try {
            $timestamp = now()->format('Y-m-d_H-i-s');
            $backupPath = storage_path("backups/mairi_backup_{$timestamp}.sql");

            // Utiliser mysqldump
            $command = sprintf(
                'mysqldump -u%s -p%s -h%s %s > %s',
                config('database.connections.mysql.username'),
                config('database.connections.mysql.password'),
                config('database.connections.mysql.host'),
                config('database.connections.mysql.database'),
                escapeshellarg($backupPath)
            );

            exec($command);

            return back()->with('success', "Sauvegarde créée: mairi_backup_{$timestamp}.sql");
        } catch (\Exception $e) {
            return back()->with('error', "Erreur lors de la sauvegarde: {$e->getMessage()}");
        }
    }

    /**
     * Organiser les paramètres par catégorie
     */
    private function organizeSettings($settings): array
    {
        return [
            'Application' => array_filter($settings, fn($k) => str_starts_with($k, 'app_'), ARRAY_FILTER_USE_KEY),
            'Opérationnels' => array_filter($settings, fn($k) => in_array($k, [
                'max_demandes_par_agent',
                'delai_reponse_jours',
                'devise_par_defaut',
                'heures_travail_par_jour'
            ]), ARRAY_FILTER_USE_KEY),
            'Sécurité' => array_filter($settings, fn($k) => str_starts_with($k, 'activer_') || str_starts_with($k, 'session_'), ARRAY_FILTER_USE_KEY),
        ];
    }
}
