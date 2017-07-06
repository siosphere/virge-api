<?php
namespace Virge\Api\Component\Method;

class Verifier
{
    protected $verifier;

    protected $additionalParams;

    public function __construct(string $verifier, array $additionalParams = [])
    {
        $this->verifier = $verifier;
        $this->additionalParams = $additionalParams;
    }

    public function getVerifier() : string
    {
        return $this->verifier;
    }

    public function getAdditionalParams() : array
    {
        return $this->additionalParams;
    }
}