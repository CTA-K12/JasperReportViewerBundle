<?php

namespace Mesd\Jasper\ReportViewerBundle\Twig\Extension;

use Mesd\Jasper\ReportViewerBundle\Model\ReportInstance;

class ReportViewerExtension extends \Twig_Extension
{
    //////////////////////////////
    // TWIG EXTENSION INTERFACE //
    //////////////////////////////


    //InitRuntime function, called at runtime, overriding to get an instance of the twig environment
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    //Get functions lists the functions in this class
    public function getFunctions()
    {
        //Function definition
        return array(
            'mesd_jasper_reportviewer_stored_report_link' => new \Twig_Function_Method($this, 'renderStoredReportLink',  array('is_safe' => array('html'))),
            'mesd_jasper_reportviewer_report_link' => new \Twig_Function_Method($this, 'renderReportLink',  array('is_safe' => array('html'))),
            'mesd_jasper_reportviewer_home' => new \Twig_Function_Method($this, 'renderReportHome', array('is_safe' => array('html'))),
            'mesd_jasper_reportviewer_uri' => new \Twig_Function_Method($this, 'renderReportURI', array('is_safe' => array('html'))),
            'mesd_jasper_reportviewer_direct_link' => new \Twig_Function_Method($this, 'renderDirectReportLink', array('is_safe' => array('html')))
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
     * Render a link to the report viewer home
     *
     * @param [type]  $linkText       [description]
     * @param string  $class          [description]
     * @param boolean $optionInNewTab [description]
     *
     * @return [type]                  [description]
     */
    public function renderReportURI()
    {
        return $this->environment->render(
            'MesdJasperReportViewerBundle:Partials:reportURI.html.twig',
            array(
            )
        );
    }


    /**
     * Render a link to the report viewer home
     *
     * @param [type]  $linkText       [description]
     * @param string  $class          [description]
     * @param boolean $optionInNewTab [description]
     *
     * @return [type]                  [description]
     */
    public function renderReportHome($linkText, $classes = ' ', $openInNewTab = true)
    {
        return $this->environment->render(
            'MesdJasperReportViewerBundle:Partials:reportHome.html.twig',
            array(
                'linkText' => $linkText,
                'classes' => $classes,
                'openInNewTab' => $openInNewTab));
    }

    /**
     * Renders a link to a report
     *
     * @param string  $reportUri    The uri of the report
     * @param string  $linkText     The text to display for the link
     * @param string  $classes      The classes to have for the anchor tag
     * @param boolean $openInNewTab Whether to open in a new tab or not
     * @param boolean $hideHome     Whether to hide the home and history nav links
     *
     * @return string                The final link tag
     */
    public function renderReportLink($reportUri, $linkText, $classes = ' ', $openInNewTab = true, $hideHome = true)
    {
        return $this->environment->render(
            'MesdJasperReportViewerBundle:Partials:reportLink.html.twig',
            array(
                'reportUri' => $reportUri,
                'linkText' => $linkText,
                'classes' => $classes,
                'openInNewTab' => $openInNewTab,
                'hideHome' => $hideHome));
    }


    /**
     * Renders a link to a stored report
     *
     * @param string  $reportUri    The uri of the report the request id is for
     * @param string  $requestId    The request id of the stored report
     * @param string  $linkText     The text to display for the link
     * @param string  $classes      The classes to have for the anchor tag
     * @param boolean $openInNewTab Whether to open in a new tab or not
     * @param boolean $hideHome     Whether to hide the home and history nav links
     *
     * @return string                The final link tag
     */
    public function renderStoredReportLink($reportUri, $requestId, $linkText, $classes = ' ', $openInNewTab = true, $hideHome = true)
    {
        return $this->environment->render(
            'MesdJasperReportViewerBundle:Partials:storedReportLink.html.twig',
            array(
                'reportUri' => $reportUri,
                'requestId' => $requestId,
                'linkText' => $linkText,
                'classes' => $classes,
                'openInNewTab' => $openInNewTab,
                'hideHome' => $hideHome));
    }


    /**
     * Renders a report immediately in the viewer with the parameters from the report instance object
     *
     * @param  ReportInstance $reportInstance The instance of a report to run
     * @param  string         $linkText       THe text to place in the link
     * @param  string         $classes        The classes to add to the link
     * @param  boolean        $openInNewTab   Whether the report viewer would open in a new tab
     * @param  boolean        $hideHome       Whether the report home should be shown
     *
     * @return string                         The rendered link html
     */
    public function renderDirectReportLink(ReportInstance $reportInstance, $linkText, $classes = ' ', $openInNewTab = true, $hideHome = true)
    {
        return $this->environment->render(
            'MesdJasperReportViewerBundle:Partials:directReportLink.html.twig',
            array(
                'reportUri' => $reportInstance->getReportUri(),
                'parameters' => urlencode(serialize($reportInstance->getParameters())),
                'linkText' => $linkText,
                'classes' => $classes,
                'openInNewTab' => $openInNewTab,
                'hideHome' => $hideHome));
    }
}
