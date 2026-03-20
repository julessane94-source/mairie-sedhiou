<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Afficher le calendrier de pointage
     */
    public function index(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $agents = User::where('role', 'agent')
            ->where('statut', '!=', 'inactif')
            ->get();

        // Récupérer tous les pointages du mois
        $attendances = Attendance::whereYear('date_presence', $year)
            ->whereMonth('date_presence', $month)
            ->get()
            ->groupBy(function ($item) {
                return $item->agent_id;
            });

        $datesOfMonth = Carbon::createFromDate($year, $month, 1)
            ->daysInMonth;

        return view('admin.attendance.index', compact(
            'agents',
            'attendances',
            'month',
            'year',
            'datesOfMonth'
        ));
    }

    /**
     * Afficher les détails du pointage d'un agent pour un mois
     */
    public function show(User $agent): View
    {
        $month = request('month', now()->month);
        $year = request('year', now()->year);

        $attendances = $agent->attendances()
            ->forMonth($year, $month)
            ->orderBy('date_presence')
            ->get();

        $stats = [
            'total_jours' => $attendances->count(),
            'presents' => $attendances->where('statut', 'present')->count(),
            'absents' => $attendances->where('statut', 'absent')->count(),
            'justifies' => $attendances->filter(fn($a) => $a->isJustified())->count(),
            'heures_totales' => $attendances->sum('heures_travaillees'),
        ];

        return view('admin.attendance.show', compact('agent', 'attendances', 'stats', 'month', 'year'));
    }

    /**
     * Marquer la présence d'un agent
     */
    public function marquerPresence(Request $request, User $agent): RedirectResponse
    {
        $validated = $request->validate([
            'date_presence' => 'required|date',
            'statut' => 'required|in:present,absent,congé,retard,repos',
            'heure_debut' => 'nullable|date_format:H:i',
            'heure_fin' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Vérifier s'il existe déjà une entrée pour ce jour
        $attendance = Attendance::where('agent_id', $agent->id)
            ->where('date_presence', $validated['date_presence'])
            ->first();

        if ($attendance) {
            $attendance->update($validated);
        } else {
            Attendance::create([
                'agent_id' => $agent->id,
                ...$validated,
            ]);
        }

        return back()->with('success', 'Présence enregistrée avec succès.');
    }

    /**
     * Pointer un agent aujourd'hui (checkin)
     */
    public function checkIn(User $agent): RedirectResponse
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('agent_id', $agent->id)
            ->where('date_presence', $today)
            ->first();

        if ($attendance && $attendance->heure_debut) {
            return back()->with('error', "L'agent est déjà pointé à l'arrivée.");
        }

        if (!$attendance) {
            $attendance = Attendance::create([
                'agent_id' => $agent->id,
                'date_presence' => $today,
                'heure_debut' => now()->format('H:i:s'),
                'statut' => 'present',
            ]);
        } else {
            $attendance->update([
                'heure_debut' => now()->format('H:i:s'),
                'statut' => 'present',
            ]);
        }

        return back()->with('success', "Check-in enregistré pour {$agent->nom}.");
    }

    /**
     * Pointer un agent à la sortie (checkout)
     */
    public function checkOut(User $agent): RedirectResponse
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('agent_id', $agent->id)
            ->where('date_presence', $today)
            ->first();

        if (!$attendance) {
            return back()->with('error', "Aucun pointage d'arrivée trouvé.");
        }

        $attendance->update([
            'heure_fin' => now()->format('H:i:s'),
            'heures_travaillees' => $attendance->calculateWorkingHours(),
        ]);

        return back()->with('success', "Check-out enregistré pour {$agent->nom}.");
    }

    /**
     * Justifier une absence
     */
    public function justifierAbsence(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'justificatif' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'notes' => 'required|string|max:500',
        ]);

        if ($request->hasFile('justificatif')) {
            $path = $request->file('justificatif')->store('absences', 'public');
            $attendance->justificatif = $path;
        }

        $attendance->notes = $validated['notes'];
        $attendance->save();

        return back()->with('success', 'Absence justifiée.');
    }

    /**
     * Rapport mensuel du pointage
     */
    public function rapport(Request $request): View
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $agent_id = $request->input('agent_id');

        $query = Attendance::whereYear('date_presence', $year)
            ->whereMonth('date_presence', $month);

        if ($agent_id) {
            $query->where('agent_id', $agent_id);
            $agent = User::find($agent_id);
        } else {
            $agent = null;
        }

        $attendances = $query->orderBy('date_presence')->get();

        $agents = User::where('role', 'agent')->get();

        return view('admin.attendance.rapport', compact(
            'attendances',
            'agents',
            'month',
            'year',
            'agent'
        ));
    }
}
