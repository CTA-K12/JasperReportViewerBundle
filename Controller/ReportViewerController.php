<?php

namespace MESD\Jasper\ReportViewerBundle;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends ContainerAware
{
    ////////////////////////
    // RENDERED RESPONSES //
    ////////////////////////


    /**
     * Display a requested page from an html report
     *
     * @param  string $requestId The request id of the cached report
     * @param  string $page      The page number to display
     *
     * @return RenderedResponse  The rendered page
     */
    public function displayHtmlReportAction($requestId, $page) {
        $rl = $this->get('mesd.jasperreport.loader')->getReportLoader();
        $report = $rl->getCachedReport($requestId, 'html', array('page' => $page));

        return $this->render( 'MESDJasperReportViewerBundle:ReportViewer:reportViewer.html.twig'
            , array(
                'report' => $report
            )
        );
    }


    ////////////////////
    // JSON RESPONSES //
    ////////////////////
}