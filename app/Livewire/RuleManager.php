<?php

namespace App\Livewire;

use App\Models\Rule;
use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;

class RuleManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    public $viewType = 'grid'; // 'grid' or 'table'
    public $isModalOpen = false;
    public $isPreviewModalOpen = false;
    public $ruleId;
    public $previewRule;

    // Search and Filters
    public $search = '';
    public $filterCategory = '';
    public $filterLocation = '';
    public $filterStatus = '';

    // Form fields
    public $title, $description, $category, $location_id, $is_active = true;

    protected $listeners = ['echo:stats,DatabaseUpdated' => '$refresh'];

    protected $rules = [
        'title' => 'required|min:3',
        'description' => 'nullable',
        'category' => 'required',
        'location_id' => 'nullable|exists:locations,id',
        'is_active' => 'boolean',
    ];

    public function setView($type)
    {
        $this->viewType = $type;
    }

    public function openModal($id = null)
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $this->ruleId = $id;
            $rule = Rule::find($id);
            $this->title = $rule->title;
            $this->description = $rule->description;
            $this->category = $rule->category;
            $this->location_id = $rule->location_id;
            $this->is_active = $rule->is_active;
        }

        $this->isModalOpen = true;
        $this->dispatch('isModalOpenChanged');
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    public function openPreviewModal($id)
    {
        $this->previewRule = Rule::with('location')->find($id);
        if ($this->previewRule) {
            $this->isPreviewModalOpen = true;
        }
    }

    public function closePreviewModal()
    {
        $this->isPreviewModalOpen = false;
        $this->previewRule = null;
    }

    private function resetForm()
    {
        $this->ruleId = null;
        $this->title = '';
        $this->description = '';
        $this->category = '';
        $this->location_id = null;
        $this->is_active = true;
    }

    public function saveRule()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'location_id' => $this->location_id ?: null,
            'is_active' => $this->is_active,
        ];

        if ($this->ruleId) {
            $rule = Rule::findOrFail($this->ruleId);
            $rule->update($data);
        } else {
            Rule::create($data);
        }

        $this->closeModal();
    }

    public function deleteRule($id)
    {
        $rule = Rule::find($id);
        if ($rule) {
            $rule->delete();
        }
    }

    public function toggleStatus($id)
    {
        $rule = Rule::find($id);
        if ($rule) {
            $rule->update(['is_active' => !$rule->is_active]);
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterCategory()
    {
        $this->resetPage();
    }

    public function updatingFilterLocation()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Rule::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterLocation) {
            if ($this->filterLocation === 'global') {
                $query->whereNull('location_id');
            } else {
                $query->where('location_id', $this->filterLocation);
            }
        }

        if ($this->filterStatus !== '') {
            $query->where('is_active', $this->filterStatus);
        }

        $categories = Rule::distinct()->pluck('category')->filter()->sort();
        $locations = Location::orderBy('name')->get();

        return view('livewire.rule-manager', [
            'rules' => $query->with('location')->orderBy('category')->orderBy('title')->paginate(12),
            'categories' => $categories,
            'locations' => $locations
        ]);
    }
}
