import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel as PluginDocumentSettingPanelFromEditPost } from '@wordpress/edit-post';
import {
  Button,
  TextControl,
  Notice,
  Spinner,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import * as editorPackage from '@wordpress/editor';
import apiFetch from '@wordpress/api-fetch';
import { useState } from '@wordpress/element';
import '@/styles/gutenberg.scss';
import {
  createInitialPanelState,
  normalizeToSlug,
  onGenerateSuccess,
  type EditorPanelState,
  type GenerateSuccess,
} from '@/domain/panel';

type DocumentSettingPanel = typeof PluginDocumentSettingPanelFromEditPost;

// WP バージョン差: editor へ移った環境と edit-post に残る環境の両方に対応
const PluginDocumentSettingPanel: DocumentSettingPanel | undefined =
  (editorPackage as { PluginDocumentSettingPanel?: DocumentSettingPanel })
    .PluginDocumentSettingPanel || PluginDocumentSettingPanelFromEditPost;

type GenerateResponse =
  | { success: true; data: GenerateSuccess }
  | { success: false; error?: { code?: string; message?: string }; message?: string };

const SlugPanel = () => {
  const [state, setState] = useState<EditorPanelState>(createInitialPanelState);
  const [notice, setNotice] = useState<string | null>(null);

  const postTitle = useSelect((select) => {
    return (select(editorStore).getEditedPostAttribute('title') as string) || '';
  }, []);

  const postSlug = useSelect((select) => {
    return (select(editorStore).getEditedPostAttribute('slug') as string) || '';
  }, []);

  const { editPost } = useDispatch(editorStore);

  const generateCandidates = async () => {
    const title = postTitle.trim();
    if (!title) {
      setState((prev) => ({
        ...prev,
        errorMessage: __('Please enter a post title first.', 's2j-slug-generater'),
      }));
      return;
    }

    setNotice(null);
    setState((prev) => ({
      ...prev,
      isGenerating: true,
      errorMessage: null,
    }));

    try {
      const data = (await apiFetch({
        path: '/s2j-slug-generater/v1/generate',
        method: 'POST',
        data: { title },
      })) as GenerateResponse;

      if (data && data.success) {
        setState((prev) => onGenerateSuccess(prev, data.data));
        setNotice(__('Slug candidate generated successfully.', 's2j-slug-generater'));
        if (!data.data.accepted) {
          setNotice(
            __('Similarity is below the threshold. Slugify is disabled.', 's2j-slug-generater')
          );
        }
      } else {
        const message =
          (!('success' in data) || !data.success
            ? (data as Extract<GenerateResponse, { success: false }>).error?.message ||
              (data as Extract<GenerateResponse, { success: false }>).message
            : null) ||
          __('Failed to generate slug candidate.', 's2j-slug-generater');
        setState((prev) => ({
          ...prev,
          isGenerating: false,
          errorMessage: message,
          accepted: false,
        }));
      }
    } catch (err: unknown) {
      const asRecord = err as {
        error?: { message?: string };
        message?: string;
      };
      const message =
        asRecord?.error?.message ||
        asRecord?.message ||
        __('An error occurred while generating the slug candidate.', 's2j-slug-generater');
      setState((prev) => ({
        ...prev,
        isGenerating: false,
        errorMessage: message,
        accepted: false,
      }));
    }
  };

  const applySlug = () => {
    if (!state.candidate || !state.accepted) {
      return;
    }
    const slug = normalizeToSlug(state.candidate);
    if (!slug) {
      setState((prev) => ({
        ...prev,
        errorMessage: __('Please generate a candidate first.', 's2j-slug-generater'),
      }));
      return;
    }
    editPost({ slug });
    setNotice(__('Slug applied successfully.', 's2j-slug-generater'));
  };

  return (
    <div className="s2j-slug-generater-panel">
      {state.errorMessage && (
        <Notice status="error" isDismissible={false}>
          {state.errorMessage}
        </Notice>
      )}

      {notice && !state.errorMessage && (
        <Notice
          status={state.accepted ? 'success' : 'warning'}
          isDismissible
          onRemove={() => setNotice(null)}
        >
          {notice}
        </Notice>
      )}

      <Button
        variant="primary"
        onClick={generateCandidates}
        disabled={state.isGenerating || !postTitle.trim()}
      >
        {state.isGenerating ? (
          <>
            <Spinner />
            {__('Generating...', 's2j-slug-generater')}
          </>
        ) : (
          __('Generate Candidates', 's2j-slug-generater')
        )}
      </Button>

      <TextControl
        label={__('Slug Candidate:', 's2j-slug-generater')}
        value={state.candidate}
        onChange={(value) =>
          setState((prev) => ({
            ...prev,
            candidate: value,
          }))
        }
      />

      <p className="s2j-similarity">
        <strong>{__('Similarity:', 's2j-slug-generater')}</strong>{' '}
        {state.similarityLabel ? `${state.similarityLabel}%` : '-'}
      </p>

      <Button
        variant="secondary"
        onClick={applySlug}
        disabled={!state.candidate || !state.accepted}
      >
        {__('Slugify', 's2j-slug-generater')}
      </Button>

      {postSlug ? (
        <p className="s2j-current-slug">
          <strong>{__('Current Slug:', 's2j-slug-generater')}</strong> <code>{postSlug}</code>
        </p>
      ) : null}
    </div>
  );
};

registerPlugin('s2j-slug-generater', {
  render: () => {
    if (!PluginDocumentSettingPanel) {
      return (
        <Notice status="error" isDismissible={false}>
          {__(
            'PluginDocumentSettingPanel is unavailable in this WordPress version.',
            's2j-slug-generater'
          )}
        </Notice>
      );
    }

    return (
      <PluginDocumentSettingPanel
        name="s2j-slug-generater-panel"
        title={__('S2J Slug Generater', 's2j-slug-generater')}
        className="s2j-slug-generater-document-panel"
      >
        <SlugPanel />
      </PluginDocumentSettingPanel>
    );
  },
});
