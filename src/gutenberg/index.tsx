import React, { useState } from 'react';
import { 
    Button, 
    TextControl, 
    Notice,
    Spinner,
    Card,
    CardHeader,
    CardBody
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import '@/styles/gutenberg.scss';
import { store as coreStore } from '@wordpress/core-data';

interface SlugGeneraterProps {
    attributes?: Record<string, unknown>;
    setAttributes?: (attributes: Record<string, unknown>) => void;
}

const SlugGenerater: React.FC<SlugGeneraterProps> = () => {
    const [isGenerating, setIsGenerating] = useState(false);
    const [candidates, setCandidates] = useState<string[]>([]);
    const [selectedCandidate, setSelectedCandidate] = useState('');
    const [similarity, setSimilarity] = useState<number | null>(null);
    const [error, setError] = useState<string | null>(null);
    const [notice, setNotice] = useState<string | null>(null);

    // Get post title from editor
    const postTitle = useSelect((select) => {
        // まず、WordPressのデータストアから取得を試行
        const title = select(editorStore).getEditedPostAttribute('title') || '';
        
        // タイトルが空の場合は、投稿IDから直接取得を試行
        if (!title.trim()) {
            const postId = select(editorStore).getCurrentPostId();
            if (postId) {
                // WordPressのget_the_title()に相当する処理
                const post = select(coreStore).getEntityRecord('postType', 'post', postId) as any;
                if (post && post.title && post.title.rendered) {
                    return post.title.rendered;
                }
            }
        }
        
        // それでも取得できない場合は、iframe内の要素から取得を試行
        if (!title.trim()) {
            // より具体的なiframeセレクターを使用
            const iframe = document.querySelector('#editor > div > div.editor-editor-interface.edit-post-layout.is-mode-visual.has-metaboxes.interface-interface-skeleton.has-footer > div.interface-interface-skeleton__editor > div.interface-interface-skeleton__body > div.interface-navigable-region.interface-interface-skeleton__content > div.editor-visual-editor.edit-post-visual-editor.is-iframed > div > div:nth-child(1) > div.block-editor-iframe__container > div.block-editor-iframe__scale-container > iframe') as HTMLIFrameElement;
            
            if (iframe && iframe.contentDocument) {
                const titleElement = iframe.contentDocument.querySelector('body > div.editor-visual-editor__post-title-wrapper.edit-post-visual-editor__post-title-wrapper.has-global-padding > h1');
                if (titleElement) {
                    return titleElement.textContent || '';
                }
            }
            
            // フォールバック: より一般的なiframeセレクター
            const fallbackIframe = document.querySelector('.editor-canvas__iframe, .block-editor-iframe__container iframe') as HTMLIFrameElement;
            if (fallbackIframe && fallbackIframe.contentDocument) {
                const titleElement = fallbackIframe.contentDocument.querySelector('body > div.editor-visual-editor__post-title-wrapper.edit-post-visual-editor__post-title-wrapper.has-global-padding > h1');
                if (titleElement) {
                    return titleElement.textContent || '';
                }
            }
            
            // iframe内で取得できない場合は、通常のDOM要素から取得を試行
            const titleElement = document.querySelector('.wp-block-post-title.editor-post-title h1');
            if (titleElement) {
                return titleElement.textContent || '';
            }
        }
        
        return select(editorStore).getEditedPostAttribute('title') || ''
    }, []);

    // Get post slug from editor
    const postSlug = useSelect((select) => {
        return select(editorStore).getEditedPostAttribute('slug') || '';
    }, []);

    // Get editor actions
    const { editPost } = useDispatch(editorStore);

    // Generate slug candidates
    const generateCandidates = async () => {
        // 複数の方法でタイトルを取得
        let currentTitle = postTitle;
        
        if (!currentTitle.trim()) {
            // より具体的なiframeセレクターを使用
            const iframe = document.querySelector('#editor > div > div.editor-editor-interface.edit-post-layout.is-mode-visual.has-metaboxes.interface-interface-skeleton.has-footer > div.interface-interface-skeleton__editor > div.interface-interface-skeleton__body > div.interface-navigable-region.interface-interface-skeleton__content > div.editor-visual-editor.edit-post-visual-editor.is-iframed > div > div:nth-child(1) > div.block-editor-iframe__container > div.block-editor-iframe__scale-container > iframe') as HTMLIFrameElement;
            
            if (iframe && iframe.contentDocument) {
                const titleElement = iframe.contentDocument.querySelector('body > div.editor-visual-editor__post-title-wrapper.edit-post-visual-editor__post-title-wrapper.has-global-padding > h1');
                if (titleElement) {
                    currentTitle = titleElement.textContent || '';
                }
            }
            
            // フォールバック: より一般的なiframeセレクター
            if (!currentTitle.trim()) {
                const fallbackIframe = document.querySelector('.editor-canvas__iframe, .block-editor-iframe__container iframe') as HTMLIFrameElement;
                if (fallbackIframe && fallbackIframe.contentDocument) {
                    const titleElement = fallbackIframe.contentDocument.querySelector('body > div.editor-visual-editor__post-title-wrapper.edit-post-visual-editor__post-title-wrapper.has-global-padding > h1');
                    if (titleElement) {
                        currentTitle = titleElement.textContent || '';
                    }
                }
            }
            
            // iframe内で取得できない場合は、通常のDOM要素から取得を試行
            if (!currentTitle.trim()) {
                const titleElement = document.querySelector('.wp-block-post-title.editor-post-title h1') || 
                                    document.querySelector('.editor-post-title__input') ||
                                    document.querySelector('h1[data-type="core/post-title"]');
                
                if (titleElement) {
                    currentTitle = titleElement.textContent || (titleElement as HTMLInputElement).value || '';
                }
            }
        }
        
        if (!currentTitle.trim()) {
            setError(__('Please enter a post title first.', 's2j-slug-generater'));
            return;
        }

        setIsGenerating(true);
        setError(null);
        setNotice(null);

        try {
            const nonce = (window as { s2jSlugGeneraterNonce?: string }).s2jSlugGeneraterNonce || '';
            const response = await fetch('/wp-json/s2j-slug-generater/v1/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify({
                    title: currentTitle,
                    nonce: nonce
                })
            });

            const data = await response.json();

            if (data.success) {
                setCandidates(data.data.candidates);
                setSimilarity(data.data.similarity);
                if (data.data.candidates.length > 0) {
                    setSelectedCandidate(data.data.candidates[0]);
                }
                setNotice(__('Slug candidates generated successfully!', 's2j-slug-generater'));
            } else {
                setError(data.message || __('Failed to generate slug candidates.', 's2j-slug-generater'));
            }
        } catch (err) {
            setError(__('An error occurred while generating slug candidates.', 's2j-slug-generater'));
            console.error('Slug generation error:', err);
        } finally {
            setIsGenerating(false);
        }
    };

    // Apply selected slug
    const applySlug = () => {
        if (selectedCandidate) {
            editPost({ slug: selectedCandidate });
            setNotice(__('Slug applied successfully!', 's2j-slug-generater'));
        }
    };

    // Handle candidate selection
    const handleCandidateChange = (value: string) => {
        setSelectedCandidate(value);
    };

    return (
        <Card className="s2j-slug-generater-block">
            <CardHeader>
                <h3>{__('S2J Slug Generater', 's2j-slug-generater')}</h3>
            </CardHeader>
            <CardBody>
                {error && (
                    <Notice status="error" isDismissible={false}>
                        {error}
                    </Notice>
                )}
                
                {notice && (
                    <Notice status="success" isDismissible={false}>
                        {notice}
                    </Notice>
                )}

                <div className="s2j-controls">
                    <Button
                        variant="primary"
                        onClick={generateCandidates}
                        disabled={isGenerating || !postTitle.trim()}
                        className="s2j-generate-button"
                    >
                        {isGenerating ? (
                            <>
                                <Spinner />
                                {__('Generating...', 's2j-slug-generater')}
                            </>
                        ) : (
                            __('Generate Candidates', 's2j-slug-generater')
                        )}
                    </Button>
                </div>

                {candidates.length > 0 && (
                    <div className="s2j-results">
                        <TextControl
                            label={__('Slug Candidates:', 's2j-slug-generater')}
                            value={selectedCandidate}
                            onChange={handleCandidateChange}
                            className="s2j-candidates-input"
                        />

                        {similarity !== null && (
                            <div className="s2j-similarity">
                                <label>{__('Similarity:', 's2j-slug-generater')}</label>
                                <span className="s2j-similarity-value">{similarity}%</span>
                            </div>
                        )}

                        <Button
                            variant="primary"
                            onClick={applySlug}
                            disabled={!selectedCandidate}
                            className="s2j-apply-button"
                        >
                            {__('Apply Slug', 's2j-slug-generater')}
                        </Button>
                    </div>
                )}

                {postSlug && (
                    <div className="s2j-current-slug">
                        <label>{__('Current Slug:', 's2j-slug-generater')}</label>
                        <code>{postSlug}</code>
                    </div>
                )}
            </CardBody>
        </Card>
    );
};

export default SlugGenerater;
