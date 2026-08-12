/**
 * Shared pure domain helpers (editor / admin).
 */

export type GenerateSuccess = {
  translated_title: string;
  similarity: number;
  candidates: string[];
  accepted: boolean;
};

export type EditorPanelState = {
  candidate: string;
  similarityLabel: string;
  isGenerating: boolean;
  errorMessage: string | null;
  accepted: boolean;
};

export function normalizeToSlug(text: string): string {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^-+|-+$/g, '');
}

export function formatPercentLabel(percent: number): string {
  return percent.toFixed(2);
}

export function onGenerateSuccess(
  state: EditorPanelState,
  success: GenerateSuccess
): EditorPanelState {
  const candidate =
    success.translated_title ||
    (success.candidates.length > 0 ? success.candidates[0] : '');

  return {
    ...state,
    candidate,
    similarityLabel: formatPercentLabel(success.similarity),
    isGenerating: false,
    errorMessage: null,
    accepted: Boolean(success.accepted),
  };
}

export function createInitialPanelState(): EditorPanelState {
  return {
    candidate: '',
    similarityLabel: '',
    isGenerating: false,
    errorMessage: null,
    accepted: false,
  };
}
