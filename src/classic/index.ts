import jQuery from 'jquery';
import '@/styles/classic.scss';
import {
  createInitialPanelState,
  normalizeToSlug,
  onGenerateSuccess,
  type EditorPanelState,
  type GenerateSuccess,
} from '@/domain/panel';

jQuery(function ($) {
  const data = window.s2jSlugGeneraterData;
  if (!data || !data.i18n || !$('#s2j-slug-generater-classic').length) {
    return;
  }

  const i18n = data.i18n;

  const $root = $('#s2j-slug-generater-classic');
  const $generate = $root.find('#s2j-generate-candidates');
  const $candidate = $root.find('#s2j-slug-candidate');
  const $similarity = $root.find('#s2j-similarity-value');
  const $slugify = $root.find('#s2j-slugify');
  const $messages = $root.find('#s2j-classic-messages');
  const $title = $('#title');
  const $postName = $('#post_name');
  const $editablePostName = $('#editable-post-name');

  let state: EditorPanelState = createInitialPanelState();

  const renderState = () => {
    $candidate.val(state.candidate);
    $similarity.text(state.similarityLabel || '-');
    $generate.prop('disabled', state.isGenerating);
    $generate.text(state.isGenerating ? i18n.generating : i18n.generate);
    $slugify.prop('disabled', !state.candidate || !state.accepted);
  };

  const showMessage = (message: string, type: 'error' | 'success' | 'warning') => {
    $messages.empty();
    $messages.append(
      $(`<div class="s2j-message s2j-message-${type}"></div>`).text(message)
    );
  };

  const clearMessages = () => {
    $messages.empty();
  };

  $candidate.on('input', function () {
    state = {
      ...state,
      candidate: String($(this).val() || ''),
    };
    renderState();
  });

  $generate.on('click', async () => {
    const title = String($title.val() || '').trim();
    if (!title) {
      showMessage(i18n.emptyTitle, 'error');
      return;
    }

    clearMessages();
    state = { ...state, isGenerating: true, errorMessage: null };
    renderState();

    try {
      const response = await fetch(data.restUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': data.nonce,
        },
        credentials: 'same-origin',
        body: JSON.stringify({ title }),
      });

      const payload = await response.json();

      if (payload.success && payload.data) {
        const success = payload.data as GenerateSuccess;
        state = onGenerateSuccess(state, success);
        renderState();
        showMessage(
          success.accepted ? i18n.success : i18n.notAccepted,
          success.accepted ? 'success' : 'warning'
        );
      } else {
        const message =
          payload?.error?.message || payload?.message || i18n.failed;
        state = {
          ...state,
          isGenerating: false,
          accepted: false,
          errorMessage: message,
        };
        renderState();
        showMessage(message, 'error');
      }
    } catch {
      state = {
        ...state,
        isGenerating: false,
        accepted: false,
      };
      renderState();
      showMessage(i18n.error, 'error');
    }
  });

  $slugify.on('click', () => {
    if (!state.candidate || !state.accepted) {
      return;
    }

    const slug = normalizeToSlug(state.candidate);
    if (!slug) {
      showMessage(i18n.emptyCandidate, 'error');
      return;
    }

    $postName.val(slug);
    if ($editablePostName.length) {
      $editablePostName.text(slug);
    }

    // Keep permalink UI in sync when present.
    const $editablePostNameFull = $('#editable-post-name-full');
    if ($editablePostNameFull.length) {
      $editablePostNameFull.text(slug);
    }

    showMessage(i18n.applied, 'success');
  });

  renderState();
});
