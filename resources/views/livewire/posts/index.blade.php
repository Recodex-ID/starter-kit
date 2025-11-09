<?php

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;

new class extends Component {
    use WithPagination, WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $title = '';

    #[Validate('nullable|string|max:500')]
    public string $excerpt = '';

    #[Validate('required|string')]
    public string $content = '';

    #[Validate('required|exists:categories,id')]
    public ?int $category_id = null;

    #[Validate('nullable|image|max:2048')]
    public $featured_image = null;

    #[Validate('required|in:draft,published,archived')]
    public string $status = 'draft';

    public array $selected_tags = [];

    public ?int $editingId = null;
    public string $search = '';
    public string $filterStatus = 'all';
    public ?int $filterCategory = null;

    /**
     * Get posts with filtering and search
     */
    public function with(): array
    {
        $query = Post::query()
            ->with(['user', 'category', 'tags'])
            ->withCount('tags')
            ->orderBy('created_at', 'desc');

        // Search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('excerpt', 'like', '%' . $this->search . '%')
                  ->orWhere('content', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->filterStatus === 'published') {
            $query->published();
        } elseif ($this->filterStatus === 'draft') {
            $query->draft();
        } elseif ($this->filterStatus === 'archived') {
            $query->where('status', 'archived');
        }

        // Category filter
        if ($this->filterCategory) {
            $query->where('category_id', $this->filterCategory);
        }

        return [
            'posts' => $query->paginate(10),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'allCategories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
        ];
    }

    /**
     * Open create modal
     */
    public function create(): void
    {
        $this->reset(['title', 'excerpt', 'content', 'category_id', 'featured_image', 'status', 'selected_tags', 'editingId']);
        $this->status = 'draft';
        $this->modal('post-form')->show();
    }

    /**
     * Create or update post
     */
    public function save(): void
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'category_id' => $this->category_id,
            'status' => $this->status,
        ];

        // Handle file upload
        if ($this->featured_image) {
            $data['featured_image'] = $this->featured_image->store('posts', 'public');
        }

        if ($this->editingId) {
            $post = Post::findOrFail($this->editingId);
            $post->update($data);

            // Sync tags
            $post->tags()->sync($this->selected_tags);

            session()->flash('message', 'Post updated successfully.');
        } else {
            $data['user_id'] = auth()->id();

            // Set published_at if status is published
            if ($this->status === 'published') {
                $data['published_at'] = now();
            }

            $post = Post::create($data);

            // Sync tags
            $post->tags()->sync($this->selected_tags);

            session()->flash('message', 'Post created successfully.');
        }

        $this->reset(['title', 'excerpt', 'content', 'category_id', 'featured_image', 'status', 'selected_tags', 'editingId']);
        $this->status = 'draft';
        $this->modal('post-form')->close();
    }

    /**
     * Edit post
     */
    public function edit(int $id): void
    {
        $post = Post::with('tags')->findOrFail($id);

        $this->editingId = $post->id;
        $this->title = $post->title;
        $this->excerpt = $post->excerpt ?? '';
        $this->content = $post->content;
        $this->category_id = $post->category_id;
        $this->status = $post->status;
        $this->selected_tags = $post->tags->pluck('id')->toArray();

        $this->modal('post-form')->show();
    }

    /**
     * Cancel editing
     */
    public function cancel(): void
    {
        $this->reset(['title', 'excerpt', 'content', 'category_id', 'featured_image', 'status', 'selected_tags', 'editingId']);
        $this->status = 'draft';
        $this->modal('post-form')->close();
    }

    /**
     * Delete post
     */
    public function delete(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->delete();

        session()->flash('message', 'Post deleted successfully.');
    }

    /**
     * Publish post
     */
    public function publish(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        session()->flash('message', 'Post published successfully.');
    }

    /**
     * Archive post
     */
    public function archive(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'archived']);

        session()->flash('message', 'Post archived successfully.');
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

    /**
     * Reset search when category filter is updated
     */
    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }
}; ?>

<section>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Posts</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your blog posts</p>
            </div>
            <flux:button wire:click="create" variant="primary" icon="plus">
                Add Post
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

        {{-- Posts List --}}
        <div class="rounded-xl border border-neutral-200 bg-white dark:border-neutral-700 dark:bg-neutral-800">
            {{-- Search and Filter --}}
            <div class="border-b border-neutral-200 p-4 dark:border-neutral-700">
                <div class="flex flex-col gap-4 sm:flex-row">
                    <div class="flex-1">
                        <flux:input
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search posts..."
                            type="search"
                        />
                    </div>
                    <div class="sm:w-48">
                        <flux:select wire:model.live="filterStatus">
                            <flux:select.option value="all">All Status</flux:select.option>
                            <flux:select.option value="published">Published</flux:select.option>
                            <flux:select.option value="draft">Draft</flux:select.option>
                            <flux:select.option value="archived">Archived</flux:select.option>
                        </flux:select>
                    </div>
                    <div class="sm:w-48">
                        <flux:select wire:model.live="filterCategory">
                            <flux:select.option value="">All Categories</flux:select.option>
                            @foreach ($allCategories as $cat)
                                <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </div>
            </div>

            {{-- Posts List --}}
            <div class="divide-y divide-neutral-200 dark:divide-neutral-700">
                @forelse ($posts as $post)
                    <div class="p-4 hover:bg-neutral-50 dark:hover:bg-neutral-700/50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-semibold text-gray-900 dark:text-white">
                                        {{ $post->title }}
                                    </h3>
                                    <flux:badge
                                        color="{{
                                            $post->status === 'published' ? 'green' :
                                            ($post->status === 'draft' ? 'yellow' : 'gray')
                                        }}"
                                        size="sm"
                                    >
                                        {{ ucfirst($post->status) }}
                                    </flux:badge>
                                </div>

                                @if ($post->excerpt)
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ Str::limit($post->excerpt, 100) }}
                                    </p>
                                @endif

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <flux:icon.user class="size-3" />
                                        {{ $post->user->name }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon.folder class="size-3" />
                                        {{ $post->category->name }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon.tag class="size-3" />
                                        {{ $post->tags_count }} {{ Str::plural('tag', $post->tags_count) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <flux:icon.eye class="size-3" />
                                        {{ $post->views_count }} views
                                    </span>
                                    <span>Created {{ $post->created_at->diffForHumans() }}</span>
                                </div>

                                @if ($post->tags->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach ($post->tags as $tag)
                                            <span
                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}"
                                            >
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="flex items-center gap-2">
                                @if ($post->status === 'draft')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="publish({{ $post->id }})"
                                        title="Publish"
                                        icon="arrow-up-circle"
                                    />
                                @endif

                                @if ($post->status !== 'archived')
                                    <flux:button
                                        size="sm"
                                        variant="ghost"
                                        wire:click="archive({{ $post->id }})"
                                        title="Archive"
                                        icon="archive-box"
                                    />
                                @endif

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="edit({{ $post->id }})"
                                    title="Edit"
                                    icon="pencil"
                                />

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    wire:click="delete({{ $post->id }})"
                                    wire:confirm="Are you sure you want to delete this post?"
                                    title="Delete"
                                    icon="trash"
                                />
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 dark:bg-neutral-700">
                            <flux:icon.document-text class="size-6 text-gray-400" />
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No posts found</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            {{ $search ? 'Try adjusting your search or filter.' : 'Get started by creating a new post.' }}
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($posts->hasPages())
                <div class="border-t border-neutral-200 p-4 dark:border-neutral-700">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Flyout Modal for Create/Edit --}}
    <flux:modal name="post-form" variant="flyout" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingId ? 'Edit Post' : 'Add Post' }}</flux:heading>
                <flux:text class="mt-2">
                    {{ $editingId ? 'Update the post details below.' : 'Fill in the details to create a new post.' }}
                </flux:text>
            </div>

            <flux:input
                wire:model="title"
                label="Title"
                placeholder="Enter post title"
                required
            />

            <flux:textarea
                wire:model="excerpt"
                label="Excerpt"
                placeholder="Enter post excerpt (optional)"
                rows="2"
            />

            <div x-data="quillEditor(@entangle('content'))" x-init="initQuill()">
                <flux:label>Content</flux:label>
                <div x-ref="editor" class="bg-white dark:bg-neutral-900" style="min-height: 200px;"></div>
                <input type="hidden" x-model="content" required>
            </div>

            <div>
                <flux:label>Category</flux:label>
                <flux:select wire:model="category_id" required>
                    <flux:select.option value="">Select a category</flux:select.option>
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <div>
                <flux:label>Tags</flux:label>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-full px-3 py-1.5 text-sm transition-colors hover:opacity-80"
                            style="background-color: {{ in_array($tag->id, $selected_tags) ? $tag->color : $tag->color . '20' }};
                                   color: {{ in_array($tag->id, $selected_tags) ? '#fff' : $tag->color }}"
                        >
                            <input
                                type="checkbox"
                                wire:model="selected_tags"
                                value="{{ $tag->id }}"
                                class="hidden"
                            />
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
                <flux:text class="mt-1 text-xs">
                    Select tags for this post
                </flux:text>
            </div>

            <div>
                <flux:label>Featured Image</flux:label>
                <flux:input
                    type="file"
                    wire:model="featured_image"
                    accept="image/*"
                />
                @if ($featured_image)
                    <div class="mt-2">
                        <img src="{{ $featured_image->temporaryUrl() }}" class="h-32 w-auto rounded-lg" alt="Preview">
                    </div>
                @endif
                <flux:text class="mt-1 text-xs">
                    Maximum file size: 2MB
                </flux:text>
            </div>

            <div>
                <flux:label>Status</flux:label>
                <flux:select wire:model="status" required>
                    <flux:select.option value="draft">Draft</flux:select.option>
                    <flux:select.option value="published">Published</flux:select.option>
                    <flux:select.option value="archived">Archived</flux:select.option>
                </flux:select>
            </div>

            <div class="flex gap-3">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">Cancel</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingId ? 'Update Post' : 'Create Post' }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
