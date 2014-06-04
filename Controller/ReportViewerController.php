<?php

namespace Mesd\Jasper\ReportViewerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\FormError;

use Mesd\Jasper\ReportViewerBundle\Util\FormErrorConverter;

class ReportViewerController extends ContainerAware
{
    ////////////////////////
    // RENDERED RESPONSES //
    ////////////////////////


    /**
     * Displays the report home and the list of reports from the default 
     *
     * @return Response The rendered home page
     */
    public function homeAction() {
        //Render the report home page
        $response = new Response($this->container->get('templating')->render('MesdJasperReportViewerBundle:ReportViewer:home.html.twig'));
        return $response;
    }


    /**
     * Display a requested page from an html report
     *
     * @param  string $reportUri The jasper server uri of the report to display the viewer for
     * @param  string $existing  Optional requestId of an existing report to preload when loading from history
     *
     * @return Response  The rendered page
     */
    public function reportViewerAction($reportUri, $existing = null) {
        //Determine whether to show or hide the report home button
        $hideHome = $this->container->get('request')->query->get('hideHome') ?: 'false';

        //Build the form
        $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm(
            urldecode($reportUri), 'MesdJasperReportViewerBundle_report_form', array(
                'routeParameters' => array(
                    'reportUri' => $reportUri
                )
            )
        );

        //If existing is set then create the route to preload from
        if (null !== $existing) {
            $preload = $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_page', array(
                'requestId' => $existing, 'page' => 1));
        } else {
            $preload = null;
        }

        //Display
        $response = new Response($this->container->get('templating')->render( 'MesdJasperReportViewerBundle:ReportViewer:reportViewer.html.twig'
            , array(
                'hideHome' => $hideHome,
                'reportUri' => $reportUri,
                'preload' => $preload,
                'form' => $form->createView()
            )
        ));

        return $response;
    }


    /**
     * Display information and history for a given report
     *
     * @param  string $reportUri The url encoded uri of the report to show info for
     *
     * @return Response          Rendered page
     */
    public function historyAction($reportUri = null) {
        //Determine whether to show or hide the report home button
        $hideHome = $this->container->get('request')->query->get('hideHome') ?: 'false';

        //Render and return
        $response = new Response($this->container->get('templating')->render(
            'MesdJasperReportViewerBundle:ReportViewer:reportHistory.html.twig', array(
                'reportUri' => $reportUri,
                'hideHome' => $hideHome
                )
            )
        );
        return $response;
    }


    ////////////////////
    // JSON RESPONSES //
    ////////////////////


    /**
     * Display a requested page from an html report
     *
     * @param  string $requestId The request id of the cached report
     * @param  string $page      The page number to display
     *
     * @return JsonResponse      JsonResponse with the 
     */
    public function getPageAction($requestId, $page) {
        $response = $this->loadPage($requestId, $page);
        $response['toolbar'] = $this->container->get('mesd.jasper.reportviewer.linkhelper')->generateToolbarLinks(
            $requestId, $response['page'], $response['totalPages']);

        return new JsonResponse($response);
    }


    /**
     * Loads a page from the report store
     *
     * @param  string $requestId The request id of the cached report
     * @param  string $page      The page number to display
     *
     * @return array             The response array 
     */
    protected function loadPage($requestId, $page) {
        //Create an array that will be converted into the json response
        $response = array('success' => true, 'output' => '');

        //Load the report
        $rl = $this->container->get('mesd.jasper.report.loader')->getReportLoader();
        try {
            $report = $rl->getCachedReport($requestId, 'html', array('page' => $page));
        } catch(\Exception $e) {
            $response['success'] = false;
            $response['output'] = 'An error occured trying to load the report';
            $response['totalPages'] = 0;
            $response['page'] = 0;
        }

        if ($response['success']) {
            $response['success'] = !$report->getError();
            $response['output'] = $report->getOutput();
            $response['totalPages'] = $report->getTotalPages();
            $response['page'] = $report->getPage();
        }

        //return the response array
        return $response;
    }


    /**
     * Runs a report
     *
     * @param  string  $reportUri The jasper server uri for the report 
     * @param  Request $request   The request from the submitted request input form
     *
     * @return JsonResponse       Json Response giving links to the output or what errors occured
     */
    public function executeAction($reportUri, Request $request) {
        //Decode the report uri
        $decodedReportUri = urldecode($reportUri);

        //Get the form again
        $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm($decodedReportUri);

        //Process the form
        $form->handleRequest($request);

        //If any errors
        if (!$form->isValid()) {
            //Get form errors
            $errors = FormErrorConverter::convertToArray($form);
            return new JsonResponse(array('success' => false, 'errors' => $errors));
        }

        //Build the report
        $rb = $this->container->get('mesd.jasper.report.client')->createReportBuilder($decodedReportUri);
        $rb->setInputParametersArray($form->getData());
        $rb->setFormat('html');
        $rb->setPage(1);

        //Run the report and get the request id back
        $requestId = $rb->runReport();

        //Load the first page of the report for the output
        $response = $this->loadPage($requestId, 1);

        //Setup the links for the toolbar
        $response['toolbar'] = $this->container->get('mesd.jasper.reportviewer.linkhelper')->generateToolbarLinks(
            $requestId, $response['page'], $response['totalPages']);

        //Return the json response
        return new JsonResponse($response);
    }


    /**
     * Gets the resources contained in the requested folder
     *
     * Folder name comes in via a query string parameter ('#' to use the default)
     *
     * @return JsonResponse   The Json Object of the returned resources
     */
    public function listJsonAction() {
        //Get the folder
        $folderUri = $this->container->get('request')->query->get('id');

        //Set folder uri to null to use the default if the root is requested ('#')
        if ('#' === $folderUri) {
            $folder = null;
        } else {
            $folder = $folderUri;
        }

        //Get the resource descriptors (not recursively)
        $resources = $this->container->get('mesd.jasper.report.client')->getResourceList($folder, false);

        //Convert the resources into an array to encode in json in the way jstree can read them
        $response = array();
        foreach($resources as $resource) {
            $data = array();
            $data['id'] = $resource->getUriString();
            $data['parent'] = $folderUri;
            $data['icon'] = false;

            if ('reportUnit' === $resource->getWsType()) {
                //Report object specific settings
                $data['text'] = '<i id="' . $data['id'] . '-icon" class="icon-file"></i> ' . $resource->getLabel();
                $data['children'] = false;
                //Set the href to the report input form
                $data['a_attr'] = array('href' => $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_report_viewer', array('reportUri' => urlencode($resource->getUriString()))));
            } elseif ('folder' === $resource->getWsType()) {
                //Folder object specific settings
                $data['text'] = '<i id="' . $data['id'] . '-icon" class="icon-folder-close"></i> ' . $resource->getLabel();
                $data['children'] = true;
            }

            $response[] = $data;
        }

        //Get the resources
        return new JsonResponse($response);
    }


    /**
     * Returns the json of a
     *
     * @param  string $reportUri Url encoded report uri
     *
     * @return Response          The json representation of this reports history table
     */
    public function reportHistoryJsonAction($reportUri = null) {
        //Get the sent parameters from datatables
        $limit   = $this->container->get('request')->query->get('length');
        $offset  = $this->container->get('request')->query->get('start');

        //Create a new repsonse array that will be converted to json
        $response = array();
        
        //Get the history for the given report from the report history
        $records = $this->container->get('mesd.jasper.report.history')->getReportHistoryDisplay(
            urldecode($reportUri), true, array('limit' => $limit, 'offset' => $offset));

        if ($reportUri) {
            //Get the count of records
            $count = $this->container->get('mesd.jasper.report.history')->loadHistoryForReport(
                urldecode($reportUri), true, array('count' => true));
        } else {
            //Get the count of records
            $count = $this->container->get('mesd.jasper.report.history')->loadRecentHistory(
                true, array('count' => true));
        }

        $response['recordsTotal'] = $count;
        $response['recordsFiltered'] = $count;

        //Convert the records to an array
        $response['data'] = array();
        foreach($records as $record) {
            //Create the links
            $links = array();
            foreach(json_decode($record['formats']) as $format) {
                //If html handle it differently, else export like usual
                if ('html' === $format) {
                    $href = $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_history_report_viewer', array(
                            'reportUri' => urlencode($record['report']),
                            'existing' => $record['requestId']
                        )
                    );
                } else {
                    $href = $this->container->get('router')->generate('MesdJasperReportBundle_export_cached_report', array(
                            'requestId' => $record['requestId'],
                            'format' => $format
                        )
                    );
                }
                $links[] = '<a href="' . $href . '">' . $format . '</a>';
            }

            //Convert to the format that datatables can read
            $response['data'][] = array(
                $record['report'],
                $record['date']->format('Y-m-d H:i:s'),
                $record['parameters'],
                implode('  ', $links)
            );
        }

        //Send back the Json
        return new JsonResponse($response);
    }
}