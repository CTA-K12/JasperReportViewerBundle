<?php

namespace Mesd\Jasper\ReportViewerBundle\Controller;

use Mesd\Jasper\ReportViewerBundle\Util\FormCompletenessChecker;
use Mesd\Jasper\ReportViewerBundle\Util\FormErrorConverter;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function homeAction(Request $request)
    {
        //Render the report home page
        $response = new Response($this->container->get('templating')->render('MesdJasperReportViewerBundle:ReportViewer:home.html.twig'));
        return $response;
    }

    /**
     * Display a requested page from an html report
     *
     * @param  string $reportUri The jasper server uri of the report to display the viewer for
     * @param  string $existing  Optional requestId of an existing report to preload when loading from history
     * @param  array  $direct    Optional array of report parameters to automatically run a report on load (Note this array should be encoded)
     *
     * @return Response  The rendered page
     */
    public function reportViewerAction(
        Request $request,
                $reportUri,
                $existing = null,
                $direct = null
    ) {
        //Determine whether to show or hide the report home button
        $hideHome = $request->query->get('hideHome') ?: 'false';

        //Set the defaults
        $preload = null;
        $autoRun = false;
        $data    = null;

        //If existing is set then create the route to preload from
        if (null !== $existing) {
            $preload = $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_page', [
                'requestId' => $existing, 'page' => 1]);
        }

        //If direct is set then prep a call to the execute action
        if (null !== $direct && $preload === null) {
            //Decode the array
            $data    = unserialize(urldecode($direct));
            $autoRun = true;
            $autoRun = isset($data['autorun']) ? $data['autorun'] : $autoRun;
        }
        //Build the form
        $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm(
            urldecode($reportUri), 'MesdJasperReportViewerBundle_report_form', [
                'routeParameters' => [
                    'reportUri' => $reportUri,
                ],
                'data'            => $data,
            ]
        );

        //If autorun is set, but the form is not complete, do not autorun
        if ($autoRun) {
            if (!FormCompletenessChecker::isComplete($form)) {
                $autoRun = false;
            }
        }

        //Display
        $response = new Response($this->container->get('templating')->render('MesdJasperReportViewerBundle:ReportViewer:reportViewer.html.twig'
            , [
                'hideHome'  => $hideHome,
                'reportUri' => $reportUri,
                'preload'   => $preload,
                'autoRun'   => $autoRun,
                'form'      => $form->createView(),
            ]
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
    public function historyAction(
        Request $request,
                $reportUri = null
    ) {
        //Determine whether to show or hide the report home button
        $hideHome = $request->query->get('hideHome') ?: 'false';

        //Render and return
        $response = new Response($this->container->get('templating')->render(
            'MesdJasperReportViewerBundle:ReportViewer:reportHistory.html.twig', [
                'reportUri' => $reportUri,
                'hideHome'  => $hideHome,
            ]
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
    public function getPageAction(
        Request $request,
                $requestId,
                $page
    ) {
        $response            = $this->loadPage($requestId, $page);
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
    protected function loadPage(
        $requestId,
        $page
    ) {
        //Create an array that will be converted into the json response
        $response = ['success' => true, 'output' => ''];

        //Load the report
        $rl = $this->container->get('mesd.jasper.report.loader')->getReportLoader();
        try {
            $report = $rl->getCachedReport($requestId, 'html', ['page' => $page]);
        } catch (\Exception $e) {
            $response['success']    = false;
            $response['output']     = 'An error occured trying to load the report';
            $response['totalPages'] = 0;
            $response['page']       = 0;
        }

        if ($response['success']) {
            $response['success']    = !$report->getError();
            $response['output']     = $report->getOutput();
            $response['totalPages'] = $report->getTotalPages();
            $response['page']       = $report->getPage();
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
    public function executeAction(
        Request $request,
                $reportUri
    ) {
        //Decode the report uri
        $decodedReportUri = urldecode($reportUri);

        //Get the form again
        $form = $this->container->get('mesd.jasper.report.client')->buildReportInputForm(
            $decodedReportUri, null, ['data' => $request->request->get('form')]);

        //Process the form
        $form->handleRequest($request);

        //If any errors
        if (!$form->isValid()) {
            //Get form errors
            $errors = FormErrorConverter::convertToArray($form);
            return new JsonResponse(['success' => false, 'errors' => $errors]);
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
    public function listJsonAction(Request $request)
    {
        //Get the folder
        $folderUri = $request->query->get('id');

        //Set folder uri to null to use the default if the root is requested ('#')
        if ('#' === $folderUri) {
            $folder = null;
        } else {
            $folder = $folderUri;
        }

        //Get the resource descriptors (not recursively)
        $resources = $this->container->get('mesd.jasper.report.client')->getResourceList($folder, false);

        //Convert the resources into an array to encode in json in the way jstree can read them
        $response = [];
        foreach ($resources as $resource) {
            $data           = [];
            $data['id']     = $resource->getUriString();
            $data['parent'] = $folderUri;
            $data['icon']   = false;

            if ('reportUnit' === $resource->getWsType()) {
                //Report object specific settings
                $data['text']     = '<i id="' . $data['id'] . '-icon" class="icon-file"></i> ' . $resource->getLabel();
                $data['children'] = false;
                //Set the href to the report input form
                $data['a_attr'] = ['href' => $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_report_viewer', ['reportUri' => rawurlencode($resource->getUriString())])];
                $data['dump']   = $resource->getUriString();
            } elseif ('folder' === $resource->getWsType()) {
                //Folder object specific settings
                $data['text']     = '<i id="' . $data['id'] . '-icon" class="icon-folder-close"></i> ' . $resource->getLabel();
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
    public function reportHistoryJsonAction(
        Request $request,
                $reportUri = null
    ) {
        //Get the sent parameters from datatables
        $limit  = $request->query->get('length');
        $offset = $request->query->get('start');

        //Create a new repsonse array that will be converted to json
        $response = [];

        //Get the history for the given report from the report history
        $records = $this->container->get('mesd.jasper.report.history')->getReportHistoryDisplay(
            urldecode($reportUri), true, ['limit' => $limit, 'offset' => $offset]);

        if ($reportUri) {
            //Get the count of records
            $count = $this->container->get('mesd.jasper.report.history')->loadHistoryForReport(
                urldecode($reportUri), true, ['count' => true]);
        } else {
            //Get the count of records
            $count = $this->container->get('mesd.jasper.report.history')->loadRecentHistory(
                true, ['count' => true]);
        }

        $response['recordsTotal']    = $count;
        $response['recordsFiltered'] = $count;

        //Convert the records to an array
        $response['data'] = [];
        foreach ($records as $record) {
            //Create the links
            $links = [];
            foreach (json_decode($record['formats']) as $format) {
                //If html handle it differently, else export like usual
                if ('html' === $format) {
                    $href = $this->container->get('router')->generate('MesdJasperReportViewerBundle_display_history_report_viewer', [
                        'reportUri' => urlencode($record['report']),
                        'existing'  => $record['requestId'],
                    ]
                    );
                } else {
                    $href = $this->container->get('router')->generate('MesdJasperReportBundle_export_cached_report', [
                        'requestId' => $record['requestId'],
                        'format'    => $format,
                    ]
                    );
                }
                $links[] = '<a href="' . $href . '">' . $format . '</a>';
            }

            //Convert to the format that datatables can read
            $response['data'][] = [
                $record['report'],
                $record['date']->format('Y-m-d H:i:s'),
                $record['parameters'],
                implode('  ', $links),
            ];
        }

        //Send back the Json
        return new JsonResponse($response);
    }
}
