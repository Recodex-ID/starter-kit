<?php

use App\Models\Category;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component {
    use WithPagination;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('boolean')]
    public bool $is_active = true;

    public ?int $editingId = null;
    public string $search = '';
    public string $filterStatus = 'all';

    /**
     * Get categories with filtering and search
     */
    public function with(): array
    {
        $query = Category::query()
            ->withCount('posts')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        return [
            'categories' => $query->paginate(10),
        ];
    }

    /**
     * Open create modal
     */
    public function create(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->modal('category-form')->show();
    }

    /**
     * Create or update category
     */
    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $category = Category::findOrFail($this->editingId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            session()->flash('message', 'Category updated successfully.');
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
                'is_active' => $this->is_active,
            ]);

            session()->flash('message', 'Category created successfully.');
        }

        $this->reset(['name', 'description', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->modal('category-form')->close();
    }

    /**
     * Edit category
     */
    public function edit(int $id): void
    {
        $category = Category::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description ?? '';
        $this->is_active = $category->is_active;

        $this->modal('category-form')->show();
    }

    /**
     * Cancel editing
     */
    public function cancel(): void
    {
        $this->reset(['name', 'description', 'is_active', 'editingId']);
        $this->is_active = true;
        $this->modal('category-form')->close();
    }

    /**
     * Delete category
     */
    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->posts()->count() > 0) {
            session()->flash('error', 'Cannot delete category with existing posts.');
            return;
        }

        $category->delete();
        session()->flash('message', 'Category deleted successfully.');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);

        session()->flash('message', 'Category status updated.');
    }

    /**
     * Reset search when search is updated
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset search when filter is updated
     */
    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }
}; ?>

<section>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Categories</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your blog categories</p>
            </div>
            <flux:button wire:click="create" variant="primary" icon="plus">
                Add Category
            </flux:button>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <flux:callout variant="success" class="mb-4">
                {{ session('message') }}
            </flux:callout>
        @endif

        @if (session()->has('error'))
            <flux:callout variant="danger" class="mb-4">
                {{ session('error') }}
            </flux:callout>
        @endif

        {{-- Categories List --}}
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            {{-- Search and Filter --}}
            <div class="border-b border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search categories..."
                            type="search"
                        />
                    </div>
                    <div class="sm:w-48">
                        <flux:select wire:model.live="filterStatus">
                            <flux:select.option value="all">All Status</flux:select.option>
                            <flux:select.option value="active">Active</flux:select.option>
                            <flux:select.option value="inactive">Inactive</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </div>

            {{-- Categories List --}}
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($categories as $category)
                    <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $category->name }}
                                    </h3>
                                    <flux:badge
                                        color="{{ $category->is_active ? 'green' : 'gray' }}"
                                        size="sm"
                                    >
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </div>

                                @if ($category->description)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ $category->description }}
                                    </p>
                                @endif

                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                    <span>{{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }}</span>
                                    <span>Created {{ $category->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="toggleStatus({{ $category->id }})"
                                    title="Toggle Status"
                                    icon="{{ $category->is_active ? 'eye' : 'eye-off' }}"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="edit({{ $category->id }})"
                                    title="Edit"
                                    icon="pencil"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="delete({{ $category->id }})"
                                    wire:confirm="Are you sure you want to delete this category?"
                                    title="Delete"
                                    icon="trash"
                                />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-neutral-700">
                            <flux:icon.folder-open class="size-6 text-gray-400" />
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No categories found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $search ? 'Try adjusting your search or filter.' : 'Get started by creating a new category.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($categories->hasPages())
                <div class="border-t border-neutral-200 p-4 dark:border-neutral-700">
                    {{ $categories->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Flyout Modal for Create/Edit --}}
    <flux:modal name="category-form" variant="flyout" class="w-full max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit Category' : 'Add Category' }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $editingId ? 'Update the category details below.' : 'Fill in the details to create a new category.' }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                label="Name"
                placeholder="Enter category name"
                required
            />

            <flux:textarea
                wire:model="description"
                label="Description"
                placeholder="Enter category description (optional)"
                rows="4"
            />

            <flux:checkbox wire:model="is_active" label="Active" />

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Update Category' : 'Create Category' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
