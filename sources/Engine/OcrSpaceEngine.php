<?php

declare(strict_types=1);

namespace OCR\Engine;

use OCR\Utility\Http\Request\Multipart\MultipartFormFactoryInterface;
use OCR\Utility\Http\Request\OcrSpaceRequestFactory;
use OCR\Utility\Http\Request\RequestFactoryInterface;
use OCR\Utility\Http\Response\OcrSpaceResponseParser;
use OCR\Utility\Http\Response\ResponseParserInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface as BaseRequestFactoryInterface;

class OcrSpaceEngine extends AbstractHttpEngine
{
    private BaseRequestFactoryInterface $requestFactory;

    private MultipartFormFactoryInterface $formFactory;

    private string $key;

    public function __construct(
        ClientInterface $client,
        BaseRequestFactoryInterface $requestFactory,
        MultipartFormFactoryInterface $formFactory,
        string $key
    ) {
        parent::__construct($client);

        $this->requestFactory = $requestFactory;
        $this->formFactory = $formFactory;
        $this->key = $key;
    }

    protected function createRequestFactory(): RequestFactoryInterface
    {
        $factory = new OcrSpaceRequestFactory(
            $this->requestFactory,
            $this->formFactory,
            $this->key,
        );

        return $factory;
    }

    protected function createResponseParser(): ResponseParserInterface
    {
        $parser = new OcrSpaceResponseParser();

        return $parser;
    }
}
