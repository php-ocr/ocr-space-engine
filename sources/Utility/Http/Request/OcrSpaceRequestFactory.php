<?php

declare(strict_types=1);

namespace OCR\Utility\Http\Request;

use OCR\Engine\EngineInterface;
use OCR\Input\InputInterface;
use OCR\Utility\Http\Request\Multipart\MultipartFormFactoryInterface;
use OCR\Utility\Http\Request\Multipart\MultipartFormInterface;
use OCR\Utility\Http\Request\Multipart\MultipartFormItem;
use OCR\Utility\Http\Request\Multipart\MultipartFormItemInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

class OcrSpaceRequestFactory extends AbstractRequestFactory
{
    private const API_ENDPOINT = 'https://api.ocr.space/parse/image';

    private const API_METHOD = 'POST';

    private string $key;

    public function __construct(
        RequestFactoryInterface $requestFactory,
        MultipartFormFactoryInterface $formFactory,
        string $key
    ) {
        parent::__construct($requestFactory, $formFactory);

        $this->key = $key;
    }

    public function createRequest(EngineInterface $engine, InputInterface $input): RequestInterface
    {
        $form = $this->createRequestForm($engine, $input);
        $contentType = $form->getContentType();
        $body = $form->getStream();

        $request = $this->requestFactory
            ->createRequest(self::API_METHOD, self::API_ENDPOINT)
            ->withHeader('apikey', $this->key)
            ->withHeader('content-type', $contentType)
            ->withBody($body)
        ;

        return $request;
    }

    private function createRequestForm(EngineInterface $engine, InputInterface $input): MultipartFormInterface
    {
        $items = $this->getRequestFormItems($engine, $input);
        $form = $this->formFactory->createForm($items);

        return $form;
    }

    /**
     * @return MultipartFormItemInterface[]
     */
    private function getRequestFormItems(EngineInterface $engine, InputInterface $input): array
    {
        $items = [];

        $language = $engine->getLanguage();
        if ($language) {
            $language = $language->toString();
            $items[] = new MultipartFormItem('language', $language);
        }

        $detectOrientation = $engine->getDetectOrientation() ? 'true' : 'false';
        $items[] = new MultipartFormItem('detectOrientation', $detectOrientation);

        $items[] = new MultipartFormItem('file', $input);

        return $items;
    }
}
