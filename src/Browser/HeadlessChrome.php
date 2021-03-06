<?php
/** fork from https://github.com/spatie/browsershot*/
namespace SapiStudio\Http\Browser;

use Symfony\Component\Process\Process;
use SapiStudio\Http\Exceptions\CouldNotTakeBrowsershot;
use Symfony\Component\Process\Exception\ProcessFailedException;


class HeadlessChrome
{
    protected $nodeBinary           = 'node';
    protected $npmBinary            = 'npm';
    protected $includePath          = '$PATH:/usr/local/bin';
    protected $networkIdleTimeout   = 0;
    protected $clip                 = null;
    protected $deviceScaleFactor    = 1;
    protected $format               = null;
    protected $fullPage             = false;
    protected $landscape            = false;
    protected $margins              = null;
    protected $noSandbox            = true;
    protected $pages                = '';
    protected $paperHeight          = 0;
    protected $paperWidth           = 0;
    protected $showBackground       = false;
    protected $showBrowserHeaderAndFooter = false;
    protected $timeout              = 60;
    protected $url                  = '';
    protected $userAgent            = '';
    protected $windowHeight         = 1200;
    protected $windowWidth          = 1920;

    /** HeadlessChrome::loadUrl() */
    public static function loadUrl($url)
    {
        return (new static)->setUrl($url);
    }
    
    /** HeadlessChrome::loadFile()*/
    public static function loadFile($filePath)
    {
        return (new static)->setFile($filePath);
    }

    /** HeadlessChrome::__construct()*/
    public function __construct($url = NULL)
    {
        $this->url = $url;
    }

    /** HeadlessChrome::setNodeBinary()  */
    public function setNodeBinary($nodeBinary = NULL)
    {
        $this->nodeBinary = $nodeBinary;
        return $this;
    }

    /** HeadlessChrome::setNpmBinary() */
    public function setNpmBinary($npmBinary = NULL)
    {
        $this->npmBinary = $npmBinary;
        return $this;
    }

    /** HeadlessChrome::setIncludePath() */
    public function setIncludePath($includePath = NULL)
    {
        $this->includePath = $includePath;
        return $this;
    }

    /** HeadlessChrome::setNetworkIdleTimeout()*/
    public function setNetworkIdleTimeout(int $networkIdleTimeout = NULL)
    {
        $this->networkIdleTimeout = $networkIdleTimeout;
        return $this;
    }

    /** HeadlessChrome::setUrl() */
    public function setUrl($url = NULL)
    {
        $this->url = $url;
        return $this;
    }
  
    /** HeadlessChrome::setFile() */
    public function setFile($file = NULL)
    {
        $this->url = "file://{$file}";;
        return $this;
    }

    /** HeadlessChrome::clip() */
    public function clip(int $x, int $y, int $width, int $height)
    {
        $this->clip = compact('x', 'y', 'width', 'height');
        return $this;
    }

    /** HeadlessChrome::showBrowserHeaderAndFooter() */
    public function showBrowserHeaderAndFooter()
    {
        $this->showBrowserHeaderAndFooter = true;
        return $this;
    }

    /** HeadlessChrome::hideBrowserHeaderAndFooter() */
    public function hideBrowserHeaderAndFooter()
    {
        $this->showBrowserHeaderAndFooter = false;
        return $this;
    }

    /** HeadlessChrome::deviceScaleFactor() */
    public function deviceScaleFactor($deviceScaleFactor)
    {
        // Google Chrome currently supports values of 1, 2, and 3.
        $this->deviceScaleFactor = max(1, min(3, $deviceScaleFactor));
        return $this;
    }

    /** HeadlessChrome::fullPage() */
    public function fullPage()
    {
        $this->fullPage = true;
        return $this;
    }

    /** HeadlessChrome::showBackground() */
    public function showBackground()
    {
        $this->showBackground = true;
        return $this;
    }

    /** HeadlessChrome::hideBackground() */
    public function hideBackground()
    {
        $this->showBackground = false;
        return $this;
    }

    /** HeadlessChrome::landscape()*/
    public function landscape($landscape = true)
    {
        $this->landscape = $landscape;
        return $this;
    }

    /** HeadlessChrome::margins()*/
    public function margins(int $top, int $right, int $bottom, int $left)
    {
        $this->margins = compact('top', 'right', 'bottom', 'left');
        return $this;
    }

    /** HeadlessChrome::noSandbox() */
    public function noSandbox()
    {
        $this->noSandbox = true;
        return $this;
    }

    /** HeadlessChrome::pages() */
    public function pages($pages)
    {
        $this->pages = $pages;
        return $this;
    }

    /** HeadlessChrome::paperSize() */
    public function paperSize($width,$height)
    {
        $this->paperWidth = $width;
        $this->paperHeight = $height;
        return $this;
    }

    /** HeadlessChrome::format()*/
    public function format($format)
    {
        $this->format = $format;
        return $this;
    }

    /** HeadlessChrome::timeout() */
    public function timeout($timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    /** HeadlessChrome::userAgent() */
    public function userAgent($userAgent)
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    /** HeadlessChrome::windowSize() */
    public function windowSize($width,$height)
    {
        $this->windowWidth  = $width;
        $this->windowHeight = $height;
        return $this;
    }
    
    /** HeadlessChrome::savePdf() */
    public function savePdf($targetPath)
    {
        $this->callBrowser($this->createPdfCommand($targetPath));
        if (! file_exists($targetPath)) {
            throw CouldNotTakeBrowsershot::chromeOutputEmpty($targetPath);
        }
    }
    
    /** HeadlessChrome::savePrintScreen()*/
    public function savePrintScreen($targetPath)
    {
        if (strtolower(pathinfo($targetPath, PATHINFO_EXTENSION)) === 'pdf') {
            return $this->savePdf($targetPath);
        }
        $this->callBrowser($this->createScreenshotCommand($targetPath));
        if (! file_exists($targetPath)) {
            throw CouldNotTakeBrowsershot::chromeOutputEmpty($targetPath);
        }
    }

    /** HeadlessChrome::getBodyHtml()*/
    public function getBodyHtml()
    {
        return $this->callBrowser($this->createCommand($this->url, 'content'));
    }

    /** HeadlessChrome::createScreenshotCommand() */
    public function createScreenshotCommand($targetPath)
    {
        $command    = $this->createCommand($this->url, 'screenshot', ['path' => $targetPath]);
        if ($this->fullPage) {
            $command['options']['fullPage'] = true;
        }
        if ($this->clip) {
            $command['options']['clip'] = $this->clip;
        }
        return $command;
    }

    /** HeadlessChrome::createPdfCommand() */
    public function createPdfCommand($targetPath)
    {
        $command    = $this->createCommand($this->url, 'pdf', ['path' => $targetPath]);
        if ($this->showBrowserHeaderAndFooter) {
            $command['options']['displayHeaderFooter'] = true;
        }
        if ($this->showBackground) {
            $command['options']['printBackground'] = true;
        }
        if ($this->landscape) {
            $command['options']['landscape'] = true;
        }
        if ($this->margins) {
            $command['options']['margin'] = [
                'top' => $this->margins['top'].'mm',
                'right' => $this->margins['right'].'mm',
                'bottom' => $this->margins['bottom'].'mm',
                'left' => $this->margins['left'].'mm',
            ];
        }
        if ($this->pages) {
            $command['options']['pageRanges'] = $this->pages;
        }
        if ($this->paperWidth > 0 && $this->paperHeight > 0) {
            $command['options']['width'] = $this->paperWidth.'mm';
            $command['options']['height'] = $this->paperHeight.'mm';
        }
        if ($this->format) {
            $command['options']['format'] = $this->format;
        }
        return $command;
    }

    /** HeadlessChrome::createCommand() */
    protected function createCommand($url,$action,$options = [])
    {
        $command = compact('url', 'action', 'options');
        $command['options']['viewport'] = [
            'width' => $this->windowWidth,
            'height' => $this->windowHeight,
        ];
        if ($this->userAgent) {
            $command['options']['userAgent'] = $this->userAgent;
        }
        if ($this->deviceScaleFactor > 1) {
            $command['options']['viewport']['deviceScaleFactor'] = $this->deviceScaleFactor;
        }
        if ($this->networkIdleTimeout > 0) {
            $command['options']['networkIdleTimeout'] = $this->networkIdleTimeout;
        }
        if ($this->noSandbox) {
            //$command['options']['args'] = ['--no-sandbox','--window-size=1920,1080','--force-device-scale-factor=2'];
            $command['options']['args'] = ['--no-sandbox'];
        }
        return $command;
    }
  
    /** HeadlessChrome::callBrowser()*/
    protected function callBrowser($command)
    {
        $setNodePathCommand     = "NODE_PATH=`{$this->npmBinary} root -g`";
        $binPath                = dirname(dirname(__DIR__)).'/bin/browser.js';
        $fullCommand            = "sudo ".$setIncludePathCommand.' '.$setNodePathCommand.' '.$this->nodeBinary.' '.escapeshellarg($binPath).' '.escapeshellarg(json_encode($command));
        $process                = (new Process($fullCommand))->setTimeout($this->timeout);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $response = json_decode($process->getOutput());
        if(!$response && !isset($response->response))
            return false;
        if(isset($response->response->error))
            throw new \Exception($response->response->error);
        return $response;
    }
}
