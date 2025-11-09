import Quill from 'quill';
import 'quill/dist/quill.snow.css';

/**
 * Quill Editor Alpine.js Component
 *
 * This component integrates Quill.js rich text editor with Livewire
 * using Alpine.js for seamless two-way data binding.
 */

// Make Quill available globally for debugging
window.Quill = Quill;

// Register Alpine component before Alpine starts
document.addEventListener('alpine:init', () => {
    Alpine.data('quillEditor', (content) => ({
        content: content,
        quillInstance: null,

        initQuill() {
            // Define comprehensive toolbar options
            const toolbarOptions = [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'script': 'sub'}, { 'script': 'super' }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'align': [] }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ];

            // Initialize Quill instance
            this.quillInstance = new Quill(this.$refs.editor, {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Write your post content here...'
            });

            // Set initial content if available
            if (this.content) {
                this.quillInstance.root.innerHTML = this.content;
            }

            // Update Livewire property when Quill content changes
            this.quillInstance.on('text-change', () => {
                this.content = this.quillInstance.root.innerHTML;
            });

            // Listen for Livewire updates (e.g., when editing a post)
            // This ensures the editor updates when content is changed externally
            this.$watch('content', (value) => {
                if (this.quillInstance.root.innerHTML !== value) {
                    this.quillInstance.root.innerHTML = value || '';
                }
            });
        }
    }));
});
