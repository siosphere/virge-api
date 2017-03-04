<?php
namespace Virge\Api\Component;

/**
 * Used to wrap internal API calls in a common message component
 */
class InternalResponse extends \Virge\Router\Component\Response
{
    public function __construct($responseData, $statusCode = 200)
    {
        parent::__construct($this->formatBody($responseData), $statusCode);
    }

    public function formatBody($responseData)
    {
        return json_encode(
            [
                'response_time'     => new \DateTime,
                'response_server'   => gethostname(),
                'response_ip'       => gethostbyname(gethostname()),
                'data'              => $responseData
            ]
        );
    }
}