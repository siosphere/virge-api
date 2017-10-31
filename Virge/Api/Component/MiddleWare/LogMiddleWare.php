<?php
namespace Virge\Api\Component\MiddleWare;

use Virge\Core\Config;
use Virge\Router\Component\Request;

class LogMiddleWare extends \Virge\Api\Component\BaseMiddleWare
{
    protected $logFile;

    public function __construct(string $logFile = null)
    {
        $this->logFile = $logFile ?? Config::get('base_path') . 'storage/log/api.log';

        $folder = dirname($this->logFile);
        if(!is_dir($folder) && !mkdir($folder, 0775, true) && !is_writeable($this->logFile)) {
            throw new \RuntimeException('API Log file ['.$this->logFIle.'] is not writeable');
        }
    }

    public function apply(string $version, string $method, Request $request) : Request
    {
        $logOutput = [
            'date' => (new \DateTime)->format('Y-m-d H:i:s'),
            'version' => $version,
            'method' => $method,
            'GET' => [],
            'POST' => [],
            'JSON' => [],
            'SERVER' => [],
        ];

        //get variables
        foreach($request->getGet() as $key => $value) {
            $logOutput['GET'][$key] = $value;
        }

        foreach($request->getPost() as $key => $value) {
            $logOutput['POST'][$key] = $value;
        }

        foreach($request->getJson() as $key => $value) {
            $logOutput['JSON'][$key] = $value;
        }

        foreach($request->getServer() as $key => $value) {
            $logOutput['SERVER'][$key] = $value;
        }

        file_put_contents($this->logFile, json_encode($logOutput) . "\n\n", FILE_APPEND);

        return parent::apply($version, $method, $request);
    }
}