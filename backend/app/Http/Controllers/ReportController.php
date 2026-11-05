<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\User;

class ReportController extends Controller
{
    // Return all reports for admin dashboard
    public function index()
    {
        // Fetch users once
        $users = User::all()->keyBy('id');

        // Emissions
        $emissions = DB::table('emissions')->get()->map(function ($e) use ($users) {
            return [
                'user_id' => $e->user_id,
                'user_name' => $users[$e->user_id]->name ?? 'Unknown',
                'type' => 'Emission',
                'co2' => $e->total_co2 ?? 0,
                'waste' => 0, // Correct for Emissions table if it only tracks CO2
                'water' => 0, // Correct for Emissions table if it only tracks CO2
                'created_at' => $e->created_at,
                'status' => $e->status ?? 'pending',
            ];
        });

        // Wastes
        $wastes = DB::table('wastes')->get()->map(function ($w) use ($users) {
            // *** FIX 1: Sum the individual waste columns to get the total waste ***
            $totalWaste = ($w->plastic_kg ?? 0) + ($w->paper_kg ?? 0) + ($w->organic_kg ?? 0);

            return [
                'user_id' => $w->user_id,
                'user_name' => $users[$w->user_id]->name ?? 'Unknown',
                'type' => 'Waste',
                'co2' => 0, // Correct for Wastes table if it only tracks Waste
                'waste' => $totalWaste, // <<< NOW CORRECTLY CALCULATED
                'water' => 0, // Correct for Wastes table if it only tracks Waste
                'created_at' => $w->created_at,
                'status' => $w->status ?? 'pending',
            ];
        });

        // Resource usages
        $resources = DB::table('resource_usages')->get()->map(function ($r) use ($users) {
            return [
                'user_id' => $r->user_id,
                'user_name' => $users[$r->user_id]->name ?? 'Unknown',
                'type' => 'Resource Usage',
                'co2' => 0, // Correct for Resource Usages table
                'waste' => 0, // Correct for Resource Usages table
                // *** FIX 2: Correct column name from water_used to water_liters ***
                'water' => $r->water_liters ?? 0, // <<< NOW CORRECT
                'created_at' => $r->created_at,
                'status' => $r->status ?? 'pending',
            ];
        });

        // Merge all reports and sort by created_at descending
        $reports = $emissions
            ->merge($wastes)
            ->merge($resources)
            ->sortByDesc('created_at')
            ->values();

        return response()->json([
            'reports' => $reports
        ]);
    }

    // Return totals for dashboard summary
    public function summary()
    {
        $totalCo2 = DB::table('emissions')->sum('total_co2');

        // *** FIX 1: Sum the individual waste columns across all records ***
        $totalPlastic = DB::table('wastes')->sum('plastic_kg');
        $totalPaper = DB::table('wastes')->sum('paper_kg');
        $totalOrganic = DB::table('wastes')->sum('organic_kg');
        $totalWaste = $totalPlastic + $totalPaper + $totalOrganic; // <<< NOW CORRECTLY CALCULATED

        // *** FIX 2: Correct column name from water_used to water_liters ***
        $totalWater = DB::table('resource_usages')->sum('water_liters'); // <<< NOW CORRECT

        return response()->json([
            'total_co2_tons' => round($totalCo2, 2),
            'total_waste_kg' => round($totalWaste, 2),
            'total_water_l' => round($totalWater, 2),
        ]);
    }
}