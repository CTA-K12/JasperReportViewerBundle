<?php

namespace MESD\Jasper\ReportViewerBundle\Twig\Extension;

class ReportViewerExtension extends \Twig_Extension
{
    //////////////////////////////
    // TWIG EXTENSION INTERFACE //
    //////////////////////////////


    //InitRuntime function, called at runtime, overriding to get an instance of the twig environment
    public function initRuntime(\Twig_Environment $environment) {
        $this->environment = $environment;
    }

    //Get functions lists the functions in this class
    public function getFunctions() {
        //Function definition
        return array(
            'mesd_jasper_reportviewer_report_link' => new \Twig_Function_Method($this, 'renderReportLink',  array('is_safe' => array('html')))
        );
    }

    //Returns the name of this extension (this is required)
    public function getName() {
        return 'mesd_jasper_reportviewer_extension';
    }


    ///////////////
    // FUNCTIONS //
    ///////////////


    /**
     * Creates a link to open a cached report in the report viewer
     *
     * @param  string  $requestId    The request id of the report to open in the viewer
     * @param  boolean $openInNewTab Whether to open the viewer in a new tab or not
     *
     * @return string                The link
     */
    public function renderReportLink($requestId, $linkText, $classes = '', $openInNewTab = true, $hideHome = true) {
        return $this->environment->render(
            'MESDJasperReportViewerBundle:Partials:reportLink.html.twig', 
            array(
                'requestId' => $requestId,
                'linkText' => $linkText,
                'classes' => $classes,
                'openInNewTab' => $openInNewTab,
                'hideHome' => $hideHome));
    }
}