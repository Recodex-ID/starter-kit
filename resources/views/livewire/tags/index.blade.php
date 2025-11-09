<?php

use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component {
    use WithPagination;

    #[Validate('required|string|max:255')]
    public string $name = '';

    #[Validate('nullable|string|max:7')]
    public string $color = '#3b82f6';

    public ?int $editingId = null;
    public string $search = '';

    /**
     * Get tags with filtering and search
     */
    public function with(): array
    {
        $query = Tag::query()
            ->withCount('posts')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('slug', 'like', '%' . $this->search . '%');
            });
        }

        return [
            'tags' => $query->paginate(10),
        ];
    }

    /**
     * Open create modal
     */
    public function create(): void
    {
        $this->reset(['name', 'color', 'editingId']);
        $this->color = '#3b82f6';
        $this->modal('tag-form')->show();
    }

    /**
     * Create or update tag
     */
    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $tag = Tag::findOrFail($this->editingId);
            $tag->update([
                'name' => $this->name,
                'color' => $this->color,
            ]);

            session()->flash('message', 'Tag updated successfully.');
        } else {
            Tag::create([
                'name' => $this->name,
                'color' => $this->color,
            ]);

            session()->flash('message', 'Tag created successfully.');
        }

        $this->reset(['name', 'color', 'editingId']);
        $this->color = '#3b82f6';
        $this->modal('tag-form')->close();
    }

    /**
     * Edit tag
     */
    public function edit(int $id): void
    {
        $tag = Tag::findOrFail($id);

        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->color = $tag->color;

        $this->modal('tag-form')->show();
    }

    /**
     * Cancel editing
     */
    public function cancel(): void
    {
        $this->reset(['name', 'color', 'editingId']);
        $this->color = '#3b82f6';
        $this->modal('tag-form')->close();
    }

    /**
     * Delete tag
     */
    public function delete(int $id): void
    {
        $tag = Tag::findOrFail($id);

        if ($tag->posts()->count() > 0) {
            session()->flash('error', 'Cannot delete tag with existing posts.');
            return;
        }

        $tag->delete();
        session()->flash('message', 'Tag deleted successfully.');
    }

    /**
     * Reset search when search is updated
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
}; ?>

<section>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tags</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your blog tags</p>
            </div>
            <flux:button wire:click="create" variant="primary" icon="plus">
                Add Tag
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

        {{-- Tags List --}}
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            {{-- Search --}}
            <div class="border-b border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search tags..."
                            type="search"
                        />
                    </div>
                </div>
            </div>

            {{-- Tags List --}}
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($tags as $tag)
                    <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="size-4 rounded-full border border-gray-300 dark:border-gray-600"
                                        style="background-color: {{ $tag->color }}"
                                    ></div>
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $tag->name }}
                                    </h3>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        ({{ $tag->slug }})
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                    <span>{{ $tag->posts_count }} {{ Str::plural('post', $tag->posts_count) }}</span>
                                    <span>Created {{ $tag->created_at->diffForHumans() }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="edit({{ $tag->id }})"
                                    title="Edit"
                                    icon="pencil"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="delete({{ $tag->id }})"
                                    wire:confirm="Are you sure you want to delete this tag?"
                                    title="Delete"
                                    icon="trash"
                                />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-neutral-700">
                            <flux:icon.tag class="size-6 text-gray-400" />
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No tags found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $search ? 'Try adjusting your search.' : 'Get started by creating a new tag.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($tags->hasPages())
                <div class="border-t border-neutral-200 p-4 dark:border-neutral-700">
                    {{ $tags->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Flyout Modal for Create/Edit --}}
    <flux:modal name="tag-form" variant="flyout" class="w-full max-w-md">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit Tag' : 'Add Tag' }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $editingId ? 'Update the tag details below.' : 'Fill in the details to create a new tag.' }}
                </flux:text>
            </div>

            <flux:input
                wire:model="name"
                label="Name"
                placeholder="Enter tag name"
                required
            />

            <div>
                <flux:label>Color</flux:label>
                <div class="mt-2 flex items-center gap-3">
                    <input
                        type="color"
                        wire:model.live="color"
                        class="h-10 w-20 cursor-pointer rounded border border-neutral-300 dark:border-neutral-600"
                    />
                    <flux:input
                        wire:model="color"
                        placeholder="#3b82f6"
                        class="flex-1"
                    />
                </div>
                <flux:text class="mt-1 text-xs">
                    Choose a color for this tag
                </flux:text>
            </div>

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Update Tag' : 'Create Tag' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
