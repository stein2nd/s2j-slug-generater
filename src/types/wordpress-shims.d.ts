declare module '@wordpress/edit-post' {
  import type { ComponentType, ReactNode } from 'react';

  export interface PluginDocumentSettingPanelProps {
    name: string;
    title: string;
    className?: string;
    children?: ReactNode;
  }

  export const PluginDocumentSettingPanel: ComponentType<PluginDocumentSettingPanelProps>;
}

declare module '@wordpress/plugins' {
  import type { ComponentType, ReactNode } from 'react';

  export interface WPPlugin {
    name?: string;
    icon?: string | ComponentType;
    render: ComponentType | (() => ReactNode);
  }

  export function registerPlugin(name: string, settings: WPPlugin): void;
}

interface S2JSlugGeneraterData {
  restUrl: string;
  nonce: string;
  version: string;
  i18n?: {
    emptyTitle: string;
    generating: string;
    generate: string;
    success: string;
    failed: string;
    error: string;
    emptyCandidate: string;
    applied: string;
    notAccepted: string;
  };
}

interface Window {
  s2jSlugGeneraterData?: S2JSlugGeneraterData;
}
