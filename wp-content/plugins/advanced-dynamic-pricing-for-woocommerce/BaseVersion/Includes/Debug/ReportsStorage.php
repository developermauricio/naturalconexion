<?php

namespace ADP\BaseVersion\Includes\Debug;

defined('ABSPATH') or exit;

class ReportsStorage
{
    /**
     * @var string
     */
    protected $importKey;

    protected $expirationTimeInSeconds = 1200;

    /**
     * @param string $importKey
     */
    public function __construct($importKey)
    {
        $this->importKey = $importKey;
    }

    /**
     * @param string $reportKey
     *
     * @return array|bool
     */
    public function getReport($reportKey)
    {
        $result = get_transient($this->getReportTransientKey($reportKey));

        return is_array($result) ? $result : array();
    }

    public function storeReport($reportKey, $data)
    {
        set_transient($this->getReportTransientKey($reportKey), $data, $this->expirationTimeInSeconds);
    }

    private function getReportTransientKey($reportKey)
    {
        return sprintf("wdp_profiler_%s_%s", $reportKey, $this->importKey);
    }

}
