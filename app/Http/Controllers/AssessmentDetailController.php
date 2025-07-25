<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentDetailController extends Controller
{
    public function index()
    {
        // Get all assessment data grouped by kemandoran, panel_sadap, and individual kelas values
        $summaryByKemandoranPanel = [];

        // Get data for kelas perawan (1, 2, 3, 4, NC)
        $kelasPerawanData = DB::table('assessments')
            ->select('kemandoran', 'panel_sadap', 'dept', 'kelas_perawan', DB::raw('COUNT(*) AS penyadap'))
            ->whereNotNull('kelas_perawan')
            ->where('kelas_perawan', '!=', '')
            ->groupBy('kemandoran', 'panel_sadap', 'dept', 'kelas_perawan')
            ->get();

        // Get data for kelas pulihan (1, 2, 3, 4, NC)
        $kelasPulihanData = DB::table('assessments')
            ->select('kemandoran', 'panel_sadap', 'dept', 'kelas_pulihan', DB::raw('COUNT(*) AS penyadap'))
            ->whereNotNull('kelas_pulihan')
            ->where('kelas_pulihan', '!=', '')
            ->groupBy('kemandoran', 'panel_sadap', 'dept', 'kelas_pulihan')
            ->get();

        // Get data for kelas nta (1, 2, 3, 4, NC)
        $kelasNtaData = DB::table('assessments')
            ->select('kemandoran', 'panel_sadap', 'dept', 'kelas_nta', DB::raw('COUNT(*) AS penyadap'))
            ->whereNotNull('kelas_nta')
            ->where('kelas_nta', '!=', '')
            ->groupBy('kemandoran', 'panel_sadap', 'dept', 'kelas_nta')
            ->get();

        // Process kelas perawan data - group by individual kelas values
        foreach ($kelasPerawanData as $item) {
            $key = $item->kemandoran . ' - ' . $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['kemandoran'] = $item->kemandoran;
            $summaryByKemandoranPanel[$key]['panel_sadap'] = $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['dept'] = $item->dept;

            // Store as "perawan_1", "perawan_2", etc.
            $kelasKey = 'perawan_' . $item->kelas_perawan;
            $summaryByKemandoranPanel[$key][$kelasKey] = $item->penyadap;
        }

        // Process kelas pulihan data - group by individual kelas values
        foreach ($kelasPulihanData as $item) {
            $key = $item->kemandoran . ' - ' . $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['kemandoran'] = $item->kemandoran;
            $summaryByKemandoranPanel[$key]['panel_sadap'] = $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['dept'] = $item->dept;

            // Store as "pulihan_1", "pulihan_2", etc.
            $kelasKey = 'pulihan_' . $item->kelas_pulihan;
            $summaryByKemandoranPanel[$key][$kelasKey] = $item->penyadap;
        }

        // Process kelas nta data - group by individual kelas values
        foreach ($kelasNtaData as $item) {
            $key = $item->kemandoran . ' - ' . $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['kemandoran'] = $item->kemandoran;
            $summaryByKemandoranPanel[$key]['panel_sadap'] = $item->panel_sadap;
            $summaryByKemandoranPanel[$key]['dept'] = $item->dept;

            // Store as "nta_1", "nta_2", etc.
            $kelasKey = 'nta_' . $item->kelas_nta;
            $summaryByKemandoranPanel[$key][$kelasKey] = $item->penyadap;
        }

        // Calculate totals for each kemandoran-panel combination
        foreach ($summaryByKemandoranPanel as $key => &$data) {
            $data['grand_total'] = 0;

            // Sum all individual kelas counts
            foreach (
                [
                    'perawan_1',
                    'perawan_2',
                    'perawan_3',
                    'perawan_4',
                    'perawan_NC',
                    'pulihan_1',
                    'pulihan_2',
                    'pulihan_3',
                    'pulihan_4',
                    'pulihan_NC',
                    'nta_1',
                    'nta_2',
                    'nta_3',
                    'nta_4',
                    'nta_NC'
                ] as $kelasKey
            ) {
                if (isset($data[$kelasKey])) {
                    $data['grand_total'] += $data[$kelasKey];
                }
            }
        }

        // Sort by kemandoran then by panel_sadap
        ksort($summaryByKemandoranPanel);

        // Uncomment the line below to see the data structure
        // dd($summaryByKemandoranPanel);

        $departments = DB::table('assessments')
            ->select('dept')
            ->distinct()
            ->orderBy('dept')
            ->get();

        $bloks = DB::table('assessments')
            ->select('blok')
            ->distinct()
            ->orderBy('blok')
            ->get();

        $kemandoran = DB::table('assessments')
            ->select('kemandoran')
            ->distinct()
            ->orderBy('kemandoran')
            ->get();

        $panelSadap = DB::table('assessments')
            ->select('panel_sadap')
            ->distinct()
            ->orderBy('panel_sadap')
            ->get();

        return view('assessment-details.index', [
            'title' => 'Assessment Details',
            'summaryByKemandoranPanel' => $summaryByKemandoranPanel,
            'kelasPerawanData' => $kelasPerawanData,
            'kelasPulihanData' => $kelasPulihanData,
            'kelasNtaData' => $kelasNtaData,
            'departments' => $departments,
            'bloks' => $bloks,
            'kemandoran' => $kemandoran,
            'panelSadap' => $panelSadap,
            'filters' => request()->only(['search', 'department', 'blok', 'kemandoran'])
        ]);
    }
}
