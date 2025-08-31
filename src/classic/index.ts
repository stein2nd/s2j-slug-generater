import jQuery from 'jquery';
import '@/styles/classic.scss';

declare global {
    interface Window {
        s2jSlugGeneraterNonce?: string;
    }
}

jQuery(function($: any) {
    'use strict';

    /**
     * Classic Editor support for S2J Slug Generater
     */
    class ClassicSlugGenerater {
        private $container: any;
        private $generateButton: any;
        private $candidatesInput: any;
        private $similarityValue: any;
        private $slugifyButton: any;
        private $titleInput: any;
        private $slugInput: any;

        constructor() {
            this.init();
        }

        private init(): void {
            this.findElements();
            this.bindEvents();
            this.setupNonce();
        }

        private findElements(): void {
            this.$container = $('#s2j-slug-generater-classic-metabox');
            this.$generateButton = this.$container.find('#s2j-generate-candidates');
            this.$candidatesInput = this.$container.find('#s2j-slug-candidates');
            this.$similarityValue = this.$container.find('#s2j-similarity-value');
            this.$slugifyButton = this.$container.find('#s2j-slugify');
            
            // Find title and slug inputs in the post form
            this.$titleInput = $('#title');
            this.$slugInput = $('#post_name');
        }

        private bindEvents(): void {
            this.$generateButton?.on('click', () => this.generateCandidates());
            this.$slugifyButton?.on('click', () => this.slugify());
        }

        private setupNonce(): void {
            // Add nonce to window object for AJAX requests
            (window as any).s2jSlugGeneraterNonce = this.$container?.find('input[name="s2j_slug_generater_nonce"]').val();
        }

        private async generateCandidates(): Promise<void> {
            const title = this.$titleInput?.val() as string;
            
            if (!title || !title.trim()) {
                this.showError('Please enter a post title first.');
                return;
            }

            this.setGeneratingState(true);
            this.clearMessages();

            try {
                const response = await this.makeApiRequest(title);
                
                if (response.success) {
                    this.handleSuccess(response.data);
                } else {
                    this.showError(response.message || 'Failed to generate slug candidates.');
                }
            } catch (error) {
                this.showError('An error occurred while generating slug candidates.');
                console.error('Slug generation error:', error);
            } finally {
                this.setGeneratingState(false);
            }
        }

        private async makeApiRequest(title: string): Promise<any> {
            const nonce = (window as any).s2jSlugGeneraterNonce;
            
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: '/wp-json/s2j-slug-generater/v1/generate',
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                    data: JSON.stringify({
                        title: title,
                        nonce: nonce
                    }),
                    success: resolve,
                    error: (xhr: any, _status: any, error: any) => {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            reject(new Error(response.message || error));
                        } catch {
                            reject(new Error(error));
                        }
                    }
                });
            });
        }

        private handleSuccess(data: any): void {
            if (data.candidates && data.candidates.length > 0) {
                this.$candidatesInput?.val(data.candidates[0]);
                this.$similarityValue?.text(data.similarity || 'N/A');
                this.showSuccess('Slug candidates generated successfully!');
            } else {
                this.showWarning('No slug candidates generated. Please check your API settings.');
            }
        }

        private slugify(): void {
            const candidate = this.$candidatesInput?.val() as string;
            
            if (!candidate) {
                this.showError('Please generate candidates first.');
                return;
            }

            // Convert to slug format
            const slug = this.convertToSlug(candidate);
            
            // Set the slug in the post form
            this.$slugInput?.val(slug);
            this.$candidatesInput?.val(slug);
            
            this.showSuccess('Slug applied successfully!');
        }

        private convertToSlug(text: string): string {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '') // Remove special characters
                .replace(/\s+/g, '-') // Replace spaces with hyphens
                .replace(/-+/g, '-') // Replace multiple hyphens with single
                .trim();
        }

        private setGeneratingState(isGenerating: boolean): void {
            this.$generateButton?.prop('disabled', isGenerating);
            
            if (isGenerating) {
                this.$generateButton?.text('Generating...');
            } else {
                this.$generateButton?.text('Generate Candidates');
            }
        }

        private showError(message: string): void {
            this.showMessage(message, 'error');
        }

        private showSuccess(message: string): void {
            this.showMessage(message, 'success');
        }

        private showWarning(message: string): void {
            this.showMessage(message, 'warning');
        }

        private showMessage(message: string, type: string): void {
            // Remove existing messages
            this.$container?.find('.s2j-message').remove();
            
            // Create message element
            const $message = $(`<div class="s2j-message s2j-message-${type}">${message}</div>`);
            
            // Insert after the first paragraph
            this.$container?.find('p:first').after($message);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                $message.fadeOut(() => $message.remove());
            }, 5000);
        }

        private clearMessages(): void {
            this.$container?.find('.s2j-message').remove();
        }
    }

    // Initialize when DOM is ready
    if ($('#s2j-slug-generater-classic-metabox').length) {
        new ClassicSlugGenerater();
    }
});
