<?php

declare(strict_types=1);

namespace OCR\Test\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OCR\Engine\EngineInterface;
use OCR\Engine\OcrSpaceEngine;
use OCR\Input\File;
use OCR\Input\InputInterface;
use OCR\Language\LanguageInterface;
use OCR\Language\OcrSpaceLanguageFactory;
use OCR\Utility\Http\Request\Multipart\GuzzleMultipartFormFactory;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EngineTest extends TestCase
{
    /**
     * @test
     */
    public function findsTextInThePictureWithText(): void
    {
        $engine = $this->createEngine('en-US');
        $input = $this->createInput('hello-world.png');

        $result = $engine->process($input);

        $this->assertSame("Hello world\r\n", $result);
    }

    /**
     * @test
     */
    public function doesNotThrowForPictureWithNoText(): void
    {
        $engine = $this->createEngine('en-US');
        $input = $this->createInput('no-text.png');

        $result = $engine->process($input);

        $this->assertSame('', $result);
    }

    private function createEngine(string $languageTag): EngineInterface
    {
        $httpClient = new Client();
        $requestFactory = new HttpFactory();
        $formFactory = new GuzzleMultipartFormFactory();
        $key = getenv('API_KEY');
        if (!$key) {
            throw new RuntimeException('Missing `API_KEY` environment variable.');
        }

        $engine = new OcrSpaceEngine(
            $httpClient,
            $requestFactory,
            $formFactory,
            $key,
        );

        $language = $this->createLanguage('en-US');
        $engine->setLanguage($language);

        return $engine;
    }

    private function createLanguage(string $languageTag): LanguageInterface
    {
        $languageFactory = new OcrSpaceLanguageFactory();
        $language = $languageFactory->createLanguage($languageTag);

        return $language;
    }

    private function createInput(string $file): InputInterface
    {
        $path = vsprintf('%s/data/%s', [
            __DIR__,
            $file,
        ]);

        $input = new File($path);

        return $input;
    }
}
