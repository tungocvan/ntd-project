<?php

namespace Modules\Admin\Livewire\Menus;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Website\Models\Category;
use Spatie\Permission\Models\Permission;

class MenuTable extends Component
{
    use WithFileUploads;

    // --- CONSTANTS ---
    private const MENU_TYPE = 'menu';
    private const CACHE_KEY = 'admin.menus';
    private const CACHE_TTL = 3600; // 1 hour

    // --- SEARCH & FILTER ---
    public $search = '';
    public $filterStatus = 'active'; // all, active, inactive
    public $selectedMenus = [];
    public $selectAll = false;

    // --- IMPORT / EXPORT VARS ---
    public $showImportModal = false;
    public $importFile;

    // --- BULK PERMISSIONS VARS ---
    public $showBulkPermissionsModal = false;
    public $bulkPermission;

    protected $queryString = ['search', 'filterStatus'];

    protected $rules;

    public function __construct()
    {
        $this->rules = [
            'importFile' => 'nullable|file|mimes:' . implode(',', config('menu.import.allowed_mimes', ['json', 'txt'])) . '|max:' . config('menu.import.max_file_size', 2048)
        ];
    }

    public function updatedFilterStatus()
    {
        // Clear cache when filter changes
        Cache::forget(self::CACHE_KEY);
    }

    public function updatedSearch()
    {
        // Clear cache when search changes
        Cache::forget(self::CACHE_KEY);
    }

    public function getImportFileNameProperty()
    {
        return $this->importFile ? $this->importFile->getClientOriginalName() : null;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
        $this->resetValidation('importFile');
    }

    // Xóa Menu
    public function delete($id)
    {
        $menu = Category::find($id);
        if (!$menu) return;

        // Log action
        Log::info('Menu deleted', [
            'menu_id' => $id,
            'menu_name' => $menu->name,
            'user_id' => auth()->id()
        ]);

        $menu->delete();
        Cache::forget(self::CACHE_KEY);

        $this->dispatch('notify', content: 'Đã xóa menu.', type: 'success');
    }

    // --- BULK ACTIONS ---
    public function bulkDelete()
    {
        if (empty($this->selectedMenus)) {
            $this->dispatch('notify', content: 'Vui lòng chọn menu cần xóa.', type: 'warning');
            return;
        }

        $count = count($this->selectedMenus);
        Category::whereIn('id', $this->selectedMenus)->delete();

        $this->selectedMenus = [];
        $this->selectAll = false;
        Cache::forget(self::CACHE_KEY);

        // Log action
        Log::info('Bulk menu delete', [
            'menu_ids' => $this->selectedMenus,
            'count' => $count,
            'user_id' => auth()->id()
        ]);

        $this->dispatch('notify', content: "Đã xóa {$count} menu thành công.", type: 'success');
    }

    public function bulkToggleStatus($status)
    {
        if (empty($this->selectedMenus)) {
            $this->dispatch('notify', content: 'Vui lòng chọn menu cần cập nhật.', type: 'warning');
            return;
        }

        Category::whereIn('id', $this->selectedMenus)
            ->update(['is_active' => $status]);

        $this->selectedMenus = [];
        $this->selectAll = false;
        Cache::forget(self::CACHE_KEY);

        $action = $status ? 'hiển thị' : 'ẩn';
        $count = count($this->selectedMenus);

        // Log action
        Log::info('Bulk menu status toggle', [
            'menu_ids' => $this->selectedMenus,
            'new_status' => $status,
            'count' => $count,
            'user_id' => auth()->id()
        ]);

        $this->dispatch('notify', content: "Đã {$action} {$count} menu.", type: 'success');
    }

    public function bulkAssignPermissions()
    {
        if (empty($this->selectedMenus)) {
            $this->dispatch('notify', content: 'Vui lòng chọn menu cần cập nhật.', type: 'warning');
            return;
        }

        $this->validate([
            'bulkPermission' => 'nullable|exists:permissions,name'
        ]);

        try {
            Category::whereIn('id', $this->selectedMenus)
                ->update(['can' => $this->bulkPermission]);

            $this->selectedMenus = [];
            $this->selectAll = false;
            $this->showBulkPermissionsModal = false;
            $this->bulkPermission = null;
            Cache::forget(self::CACHE_KEY);

            $count = count($this->selectedMenus);
            $permissionName = $this->bulkPermission ?: 'không có';

            // Log action
            Log::info('Bulk menu permissions assign', [
                'menu_ids' => $this->selectedMenus,
                'permission' => $this->bulkPermission,
                'count' => $count,
                'user_id' => auth()->id()
            ]);

            $this->dispatch('notify', content: "Đã cập nhật quyền cho {$count} menu thành '{$permissionName}'.", type: 'success');

        } catch (\Exception $e) {
            Log::error('Bulk permissions assign failed', [
                'error' => $e->getMessage(),
                'menu_ids' => $this->selectedMenus,
                'permission' => $this->bulkPermission,
                'user_id' => auth()->id()
            ]);

            $this->dispatch('notify', content: 'Lỗi khi cập nhật quyền menu.', type: 'error');
        }
    }

    public function openBulkPermissionsModal()
    {
        if (empty($this->selectedMenus)) {
            $this->dispatch('notify', content: 'Vui lòng chọn menu cần cập nhật.', type: 'warning');
            return;
        }

        $this->showBulkPermissionsModal = true;
    }

    // Toggle Ẩn/Hiện
    public function toggleStatus($id)
    {
        $menu = Category::find($id);
        if ($menu) {
            $menu->update(['is_active' => !$menu->is_active]);
            $this->dispatch('notify', content: 'Đã cập nhật trạng thái.', type: 'success');
        }
    }

    // --- LOGIC KÉO THẢ QUAN TRỌNG ---
    public function updateMenuOrder($list)
    {
        // $list là mảng phân cấp được gửi từ JS
        // Structure: [{id: 1, children: [{id: 2}, {id: 3}]}, {id: 4}]

        $this->updateRecursive($list, null);

        $this->dispatch('notify', content: 'Đã lưu cấu trúc menu mới.', type: 'success');
    }

    private function updateRecursive($items, $parentId)
    {
        foreach ($items as $index => $item) {
            // Cập nhật cha và thứ tự
            Category::where('id', $item['id'])->update([
                'parent_id' => $parentId,
                'sort_order' => $index
            ]);

            // Nếu có con, đệ quy tiếp
            if (isset($item['children']) && !empty($item['children'])) {
                $this->updateRecursive($item['children'], $item['id']);
            }
        }
    }

    // --- 1. CHỨC NĂNG DUPLICATE (NHÂN BẢN) ---
    public function duplicate($id)
    {
        $original = Category::find($id);
        if (!$original) {
            $this->dispatch('notify', content: 'Menu không tồn tại.', type: 'warning');
            return;
        }

        DB::transaction(function () use ($original) {
            $this->recursiveDuplicate($original, $original->parent_id);
        });

        Cache::forget(self::CACHE_KEY);

        Log::info('Menu duplicated', [
            'menu_id' => $id,
            'menu_name' => $original->name,
            'user_id' => auth()->id()
        ]);

        $this->dispatch('notify', content: 'Đã nhân bản menu thành công.', type: 'success');
    }

    private function recursiveDuplicate($original, $parentId)
    {
        // 1. Sao chép bản ghi
        $new = $original->replicate();
        $new->name = $original->name . ' (Copy)';
        $new->parent_id = $parentId;
        $new->slug = $this->generateUniqueMenuSlug($original);
        $new->sort_order = $original->sort_order + 1;
        $new->save();

        // 2. Tìm các con của bản ghi gốc và nhân bản tiếp
        $children = Category::where('parent_id', $original->id)->get();
        foreach ($children as $child) {
            $this->recursiveDuplicate($child, $new->id);
        }
    }

    private function generateUniqueMenuSlug($original): string
    {
        $baseSlug = $original->slug
            ? $original->slug . '-copy'
            : Str::slug($original->name . ' copy');

        $slug = $baseSlug;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    // --- EXPORT FUNCTIONALITY ---
    public function export()
    {
        try {
            $menus = $this->getFilteredMenus()->whereNull('parent_id')->get();

            if ($menus->isEmpty()) {
                $this->dispatch('notify', content: 'Không có menu nào để export.', type: 'warning');
                return;
            }

            $data = $this->buildTreeData($menus);
            $json = json_encode($data, config('menu.export.pretty_print') ? JSON_PRETTY_PRINT : 0 | (config('menu.export.unicode_support') ? JSON_UNESCAPED_UNICODE : 0));

            // Save to seeder file
            $seederPath = config('menu.seeder_path');
            File::ensureDirectoryExists(dirname($seederPath));
            File::put($seederPath, $json);

            // Log action
            Log::info('Menu exported', [
                'menu_count' => $menus->count(),
                'user_id' => auth()->id(),
                'exported_to' => $seederPath
            ]);

            $fileName = config('menu.export.filename_prefix') . '_' . date('Y-m-d_His') . '.json';

            return response()->streamDownload(function () use ($json) {
                echo $json;
            }, $fileName);

        } catch (\Exception $e) {
            Log::error('Menu export failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            $this->dispatch('notify', content: 'Lỗi khi export menu.', type: 'error');
        }
    }


    private function buildTreeData($categories)
    {
        $result = [];
        foreach ($categories as $cat) {
            $item = [
                'name' => $cat->name,
                'url' => $cat->url,
                'icon' => $cat->icon,
                'can' => $cat->can,
                'is_active' => $cat->is_active,
                'children' => []
            ];

            if ($cat->children->isNotEmpty()) {
                $item['children'] = $this->buildTreeData($cat->children);
            }

            $result[] = $item;
        }
        return $result;
    }

    // --- 3. CHỨC NĂNG IMPORT ---


    // --- IMPORT FUNCTIONALITY ---
    public function import()
    {
        $this->validate();

        try {
            $content = $this->getImportContent();

            if (!$content) {
                throw new \Exception('Không có dữ liệu để import.');
            }

            $json = json_decode($content, true);

            if (!is_array($json)) {
                throw new \Exception('File JSON không hợp lệ.');
            }

            $result = $this->processImport($json);

            // Reset UI
            $this->showImportModal = false;
            $this->importFile = null;

            Cache::forget(self::CACHE_KEY);

            // Log action
            Log::info('Menu imported', [
                'success_count' => $result['success'],
                'skip_count' => $result['skip'],
                'user_id' => auth()->id()
            ]);

            $msg = "Import hoàn tất: Thêm mới {$result['success']}, Bỏ qua {$result['skip']} (do trùng).";
            $type = $result['success'] > 0 ? 'success' : 'warning';

            $this->dispatch('notify', content: $msg, type: $type);

        } catch (\Exception $e) {
            Log::error('Menu import failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            $this->addError('importFile', 'Lỗi: ' . $e->getMessage());
        }
    }

    public function restoreDefaultMenu()
    {
        try {
            $seederPath = config('menu.seeder_path');

            if (!File::exists($seederPath)) {
                throw new \Exception('File khôi phục mặc định không tồn tại: ' . $seederPath);
            }

            $content = File::get($seederPath);
            $json = json_decode($content, true);

            if (!is_array($json)) {
                throw new \Exception('File khôi phục mặc định không hợp lệ.');
            }

            // Xóa toàn bộ menu hiện tại trước khi khôi phục
            Category::menu()->delete();

            $result = $this->processImport($json);
            Cache::forget(self::CACHE_KEY);

            Log::info('Menu restored from default seeder', [
                'seeder_path' => $seederPath,
                'success_count' => $result['success'],
                'skip_count' => $result['skip'],
                'user_id' => auth()->id()
            ]);

            $this->dispatch('notify', content: "Khôi phục menu mặc định hoàn tất: Thêm mới {$result['success']}.", type: 'success');

        } catch (\Exception $e) {
            Log::error('Menu restore failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            $this->dispatch('notify', content: 'Lỗi khi khôi phục menu mặc định: ' . $e->getMessage(), type: 'error');
        }
    }

    private function getImportContent()
    {
        if ($this->importFile) {
            return file_get_contents($this->importFile->getRealPath());
        }

        $seedPath = config('menu.seeder_path');
        return File::exists($seedPath) ? File::get($seedPath) : null;
    }

    private function processImport($json)
    {
        $countSuccess = 0;
        $countSkip = 0;

        DB::transaction(function () use ($json, &$countSuccess, &$countSkip) {
            $maxSort = $this->getFilteredMenus()->max('sort_order') ?? 0;

            foreach ($json as $item) {
                $maxSort++;
                $this->recursiveImport($item, null, $maxSort, $countSuccess, $countSkip);
            }
        });

        return ['success' => $countSuccess, 'skip' => $countSkip];
    }


    private function recursiveImport($item, $parentId, $sortOrder, &$countSuccess, &$countSkip)
    {
        // 1. Xác định tiêu chí trùng lặp
        // Một menu được coi là trùng nếu: Cùng Loại, Cùng Cha, Cùng Tên và Cùng URL
        $criteria = [
            'type' => 'menu',
            'parent_id' => $parentId,
            'name' => $item['name'],
            'url' => $item['url'] ?? null,
        ];

        // 2. Kiểm tra tồn tại
        $existingMenu = Category::where($criteria)->first();

        if ($existingMenu) {
            // [TRÙNG] -> Bỏ qua tạo mới
            $countSkip++;
            $currentId = $existingMenu->id; // Lấy ID cũ để dùng cho con
        } else {
            // [KHÔNG TRÙNG] -> Tạo mới
            $newMenu = Category::create(array_merge($criteria, [
                'icon' => $item['icon'] ?? null,
                'can' => $item['can'] ?? null,
                'is_active' => $item['is_active'] ?? true,
                'sort_order' => $sortOrder,
                // Các trường meta khác nếu có
            ]));

            $countSuccess++;
            $currentId = $newMenu->id;
        }

        // 3. Xử lý Đệ quy cho Menu con (Children)
        // Lưu ý: Vẫn chạy đệ quy ngay cả khi menu cha bị trùng (Skip),
        // vì có thể trong menu cha cũ chưa có các menu con mới này.
        if (!empty($item['children'])) {
            $childSort = 0;
            foreach ($item['children'] as $child) {
                // Truyền $currentId (ID của cha vừa tìm thấy hoặc vừa tạo) xuống
                $this->recursiveImport($child, $currentId, $childSort++, $countSuccess, $countSkip);
            }
        }
    }

    private function getFilteredMenus()
    {
        $query = Category::menu()->with('children');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('url', 'like', '%' . $this->search . '%');
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('sort_order');
    }

    public function render()
    {
        // Use caching for better performance, but only when no filters are applied
        $cacheEnabled = config('menu.cache.enabled', true) && empty($this->search) && $this->filterStatus === 'all';
        $cacheKey = config('menu.cache.key', self::CACHE_KEY);
        $cacheTtl = config('menu.cache.ttl', self::CACHE_TTL);

        if ($cacheEnabled) {
            $menus = Cache::remember($cacheKey, $cacheTtl, function () {
                return $this->getFilteredMenus()
                    ->whereNull('parent_id')
                    ->get();
            });
        } else {
            $menus = $this->getFilteredMenus()
                ->whereNull('parent_id')
                ->get();
        }

        $totalMenus = $this->getFilteredMenus()->count();
        $activeMenus = $this->getFilteredMenus()->where('is_active', true)->count();

        return view('Admin::livewire.menus.menu-table', compact('menus', 'totalMenus', 'activeMenus'));
    }
}
