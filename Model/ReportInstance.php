<?php

namespace Mesd\Jasper\ReportViewerBundle\Model;

class ReportInstance
{
    ///////////////
    // VARIABLES //
    ///////////////

    /**
     * The uri of the report
     * @var string
     */
    protected $reportUri;

    /**
     * The parameters for the report
     * @var array
     */
    protected $parameters;

    //////////////////
    // BASE METHODS //
    //////////////////


    /**
     * Constructor
     *
     * @param string $reportUri  The uri of the report to run
     * @param array  $parameters The report parameters keyed by name
     */
    public function __construct($reportUri, $parameters = [])
    {
        //Set the arguments
        $this->reportUri = $reportUri;
        $this->parameters = $parameters;
    }


    /////////////////////////
    // GETTERS AND SETTERS //
    /////////////////////////


    /**
     * Gets the The uri of the report.
     *
     * @return string
     */
    public function getReportUri()
    {
        return $this->reportUri;
    }

    /**
     * Sets the The uri of the report.
     *
     * @param string $reportUri the report uri
     *
     * @return self
     */
    public function setReportUri($reportUri)
    {
        $this->reportUri = $reportUri;

        return $this;
    }

    /**
     * Gets the The parameters for the report.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets the The parameters for the report.
     *
     * @param array $parameters the parameters
     *
     * @return self
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }
}