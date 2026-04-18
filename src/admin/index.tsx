import React, { useState } from 'react';
import { 
    Button, 
    TextControl, 
    SelectControl,
    RangeControl,
    Notice,
    Card,
    CardHeader,
    CardBody,
    Spinner
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import '@/styles/admin.scss';

interface AdminSettingsProps {
    settings?: FormData;
}

interface FormData {
    translation_service: 'deepl' | 'google';
    api_key: string;
    source_language: 'ja' | 'en' | 'zh' | 'ko' | 'de' | 'fr' | 'es';
    similarity_threshold: number;
}

const DEFAULT_FORM_DATA: FormData = {
    translation_service: 'deepl',
    api_key: '',
    source_language: 'ja',
    similarity_threshold: 80
};

const AdminSettings: React.FC<AdminSettingsProps> = ({ settings }) => {
    const [isSaving, setIsSaving] = useState(false);
    const [notice, setNotice] = useState<string | null>(null);
    const [error, setError] = useState<string | null>(null);

    const [formData, setFormData] = useState<FormData>(() => ({
        ...DEFAULT_FORM_DATA,
        ...settings
    }));

    const handleInputChange = (key: string, value: string | number) => {
        setFormData(prev => ({
            ...prev,
            [key]: value
        }));
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setIsSaving(true);
        setNotice(null);
        setError(null);

        try {
            // Save settings using WordPress settings API
            const form = document.querySelector('form[action="options.php"]') as unknown as HTMLFormElement;
            if (form) {
                // Update form values
                Object.keys(formData).forEach(key => {
                    const input = form.querySelector(`[name="s2j_slug_generater_${key}"]`) as unknown as HTMLInputElement;
                    if (input) {
                        input.value = formData[key as keyof typeof formData] as string;
                    }
                });

                // Submit form
                form.submit();
            }
        } catch {
            setError(__('Failed to save settings.', 's2j-slug-generater'));
        } finally {
            setIsSaving(false);
        }
    };

    const getServiceUrls = (service: string) => {
        const urls = {
            deepl: {
                plan: 'https://www.deepl.com/pro-api#api-pricing',
                api: 'https://www.deepl.com/ja/pro#developer'
            },
            google: {
                plan: 'https://cloud.google.com/translate/pricing?hl=ja',
                api: 'https://cloud.google.com/translate/docs/setup?hl=ja'
            }
        };

        return urls[service as keyof typeof urls] || urls.deepl;
    };

    const currentUrls = getServiceUrls(formData.translation_service);

    return (
        <div className="wrap">
            <h1>{__('S2J Slug Generater Settings', 's2j-slug-generater')}</h1>
            
            {notice && (
                <Notice status="success" isDismissible={false}>
                    {notice}
                </Notice>
            )}
            
            {error && (
                <Notice status="error" isDismissible={false}>
                    {error}
                </Notice>
            )}

            <Card className="s2j-settings-card">
                <CardHeader>
                    <h2>{__('Translation Service Configuration', 's2j-slug-generater')}</h2>
                </CardHeader>
                <CardBody>
                    <form onSubmit={handleSubmit}>
                        <SelectControl
                            label={__('Translation Service', 's2j-slug-generater')}
                            value={formData.translation_service}
                            options={[
                                { label: __('DeepL API', 's2j-slug-generater'), value: 'deepl' },
                                { label: __('Google Translate API', 's2j-slug-generater'), value: 'google' }
                            ]}
                            onChange={(value) => handleInputChange('translation_service', value)}
                        />
                        
                        <p className="description">
                            {__('Go to', 's2j-slug-generater')} 
                            <a href={currentUrls.plan} target="_blank" rel="noopener noreferrer">
                                {__('the API plan selection page', 's2j-slug-generater')}
                            </a>
                            {__(' and ', 's2j-slug-generater')}
                            <a href={currentUrls.api} target="_blank" rel="noopener noreferrer">
                                {__('obtain a free API key', 's2j-slug-generater')}
                            </a>
                            .
                        </p>

                        <TextControl
                            label={__('API Key', 's2j-slug-generater')}
                            type="password"
                            value={formData.api_key}
                            onChange={(value) => handleInputChange('api_key', value)}
                            help={__('Enter your API key for the selected translation service.', 's2j-slug-generater')}
                        />

                        <SelectControl
                            label={__('Source Language', 's2j-slug-generater')}
                            value={formData.source_language}
                            options={[
                                { label: __('Japanese', 's2j-slug-generater'), value: 'ja' },
                                { label: __('English', 's2j-slug-generater'), value: 'en' },
                                { label: __('Chinese', 's2j-slug-generater'), value: 'zh' },
                                { label: __('Korean', 's2j-slug-generater'), value: 'ko' },
                                { label: __('German', 's2j-slug-generater'), value: 'de' },
                                { label: __('French', 's2j-slug-generater'), value: 'fr' },
                                { label: __('Spanish', 's2j-slug-generater'), value: 'es' }
                            ]}
                            onChange={(value) => handleInputChange('source_language', value)}
                            help={__('Select the language of your post titles.', 's2j-slug-generater')}
                        />

                        <RangeControl
                            label={__('Similarity Threshold', 's2j-slug-generater')}
                            value={formData.similarity_threshold}
                            onChange={(value) => value !== undefined && handleInputChange('similarity_threshold', value)}
                            min={0}
                            max={100}
                            step={10}
                            help={__('Set the minimum similarity threshold for slug candidates (0-100%).', 's2j-slug-generater')}
                        />

                        <div className="s2j-threshold-display">
                            <strong>{__('Current threshold:', 's2j-slug-generater')}</strong> {formData.similarity_threshold}%
                        </div>

                        <div className="s2j-submit-section">
                            <Button
                                variant="primary"
                                type="submit"
                                disabled={isSaving}
                                className="s2j-save-button"
                            >
                                {isSaving ? (
                                    <>
                                        <Spinner />
                                        {__('Saving...', 's2j-slug-generater')}
                                    </>
                                ) : (
                                    __('Save Settings', 's2j-slug-generater')
                                )}
                            </Button>
                        </div>
                    </form>
                </CardBody>
            </Card>
        </div>
    );
};

export default AdminSettings;
