<?php

declare(strict_types=1);

namespace OCR\Engine;

use OCR\Exception\Exception;
use OCR\Input\InputInterface;
use OCR\Utility\Http\MultipartFormFactoryInterface;
use OCR\Utility\Http\MultipartFormInterface;
use OCR\Utility\Http\MultipartFormItem;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OcrSpaceEngine extends AbstractHttpEngine
{
    private const API_ENDPOINT = 'https://api.ocr.space/parse/image';

    private const API_METHOD = 'POST';

    private RequestFactoryInterface $requestFactory;

    private MultipartFormFactoryInterface $formFactory;

    private string $key;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        MultipartFormFactoryInterface $formFactory,
        string $key
    ) {
        parent::__construct($client);

        $this->requestFactory = $requestFactory;
        $this->formFactory = $formFactory;
        $this->key = $key;
    }

    protected function createRequest(InputInterface $input): RequestInterface
    {
        $form = $this->createRequestForm($input);
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

    protected function parseResponse(ResponseInterface $response): string
    {
        $data = $this->decodeResponse($response);
        $this->validateResponse($data);

        $getResult = function (array $data): string {
            $result = $data['ParsedText'] ?? null;
            if (!is_string($result)) {
                throw new Exception('Unexpected recognition service response.');
            }
            return $result;
        };

        $results = $data['ParsedResults'] ?? null;
        if (!is_array($results)) {
            throw new Exception('Unexpected recognition service response.');
        }

        $results = array_map($getResult, $results);
        $results = implode("\n", $results);

        return $results;
    }

    private function createRequestForm(InputInterface $input): MultipartFormInterface
    {
        $items = [];

        $language = $this->getLanguage();
        if ($language) {
            $language = $language->toString();
            $items[] = new MultipartFormItem('language', $language);
        }

        $detectOrientation = $this->getDetectOrientation() ? 'true' : 'false';
        $items[] = new MultipartFormItem('detectOrientation', $detectOrientation);

        $items[] = new MultipartFormItem('file', $input);

        $form = $this->formFactory->createForm($items);

        return $form;
    }

    /**
     * @return mixed[]
     */
    private function decodeResponse(ResponseInterface $response): array
    {
        $json = $response->getBody()->getContents();

        /** @var mixed[]|bool|null $data */
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new Exception('Unexpected recognition service response.');
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     */
    private function validateResponse(array $data): void
    {
        $exitCode = $data['OCRExitCode'];
        $success = (
            $exitCode === 1
                ||
            $exitCode === 2
        );

        if (!$success) {
            throw new Exception('Character recognition failed.');
        }
    }
}
