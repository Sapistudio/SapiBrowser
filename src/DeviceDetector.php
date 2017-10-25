<?php
namespace SapiStudio\Http;
use \Mobile_Detect;

class DeviceDetector extends Mobile_Detect
{
    public function __construct($userAgent = "")
    {
        if ($userAgent != "") {
            $this->setUserAgent($userAgent);
        }
    }

    /**
     * Get current device's browser.
     *
     * @since 0.1.0
     * @access public
     *
     * @return string Device's browser.
     */
    public function getBrowser()
    {
        foreach ($this::$browsers as $browser => $UA) {
            $is_browser = 'is'.$browser;
            if ($this->$is_browser()) {
                return $browser;
            }
        }
        return '';
    }
    /**
     * Get current device's operating system.
     *
     * @since 0.1.0
     * @access public
     *
     * @return string Device's OS.
     */
    public function getOperatingSystem()
    {
        foreach ($this::$operatingSystems as $os => $UA) {
            $is_os = 'is'.$os;
            if ($this->$is_os()) {
                return $os;
            }
        }
        return '';
    }
    /**
     * Get current device brand/manufacturer.
     *
     * @since 0.1.0
     * @access public
     *
     * @return string Device type.
     */
    public function getDevice()
    {
        $devices = $this->getDevices();
        foreach ($devices as $type => $UA) {
            $is_type = 'is'.$type;
            if ($this->$is_type()) {
                return $type;
            }
        }
        return '';
    }
    /**
     * All device brands/manufacturers.
     *
     * @since 0.1.0
     * @access public
     *
     * @return array Devices.
     */
    public function getDevices()
    {
        return \array_merge($this::$phoneDevices, $this::$tabletDevices);
    }
    /**
     * Is device a phone
     *
     * @since 0.1.0
     * @access public
     *
     * @return bool
     */
    public function isPhone()
    {
        return \array_key_exists($this->getDevice(), $this::$phoneDevices);
    }
    /**
     * Is device a smart device?
     *
     * @since 0.1.0
     * @access public
     *
     * @return bool
     */
    public function isSmart()
    {
        return (
            $this->isAndroidOS()
            || $this->isBlackBerryOS()
            || $this->isWindowsMobileOS()
            || $this->isWindowsPhoneOS()
            || $this->isiOS()
            || $this->iswebOS()
            || $this->isbadaOS()
        );
    }
}
