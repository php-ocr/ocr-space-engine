<?php

declare(strict_types=1);

namespace OCR\Language;

class OcrSpaceLanguageFactory extends AbstractLanguageFactory
{
    protected function getLanguages(): array
    {
        $languages = [
            'en' => 'eng',
            'ru' => 'rus',
        ];

        return $languages;
    }
}
