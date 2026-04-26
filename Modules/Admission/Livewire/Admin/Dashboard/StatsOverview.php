<?php

namespace Modules\Admission\Livewire\Admin\Dashboard;

use Livewire\Component;
use Modules\Admission\Models\AdmissionApplication;

class StatsOverview extends Component
{
    public $total = 0;
    public $pending = 0;
    public $approved = 0;
    public $classTypes = [];

    public function mount()
    {
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->total = AdmissionApplication::count();

        $statusCounts = AdmissionApplication::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $this->pending = $statusCounts['pending'] ?? 0;
        $this->approved = $statusCounts['approved'] ?? 0;

        $this->classTypes = AdmissionApplication::selectRaw('loai_lop_dang_ky, COUNT(*) as total')
            ->groupBy('loai_lop_dang_ky')
            ->orderByDesc('total')
            ->pluck('total', 'loai_lop_dang_ky')
            ->toArray();
    }

    public function render()
    {
        return view('Admission::livewire.admin.dashboard.stats-overview');
    }
}