<?php

namespace MESD\Jasper\ReportViewerBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
        $response = new Response($this->container->get('templating')->render('MESDJasperReportViewerBundle:ReportViewer:home.html.twig'));
        return $response;
    }


    /**
     * Display a requested page from an html report
     *
     * @param  string $requestId The request id of the cached report
     * @param  string $page      The page number to display
     *
     * @return Response  The rendered page
     */
    public function displayHtmlReportAction($requestId, $page) {
        //Determine whether to show or hide the report home button
        $hideHome = $this->container->get('request')->query->get('hideHome') ?: 'false';

        //Load the report
        $rl = $this->container->get('mesd.jasperreport.loader')->getReportLoader();
        $report = $rl->getCachedReport($requestId, 'html', array('page' => $page));

        //Display
        $response = new Response($this->container->get('templating')->render( 'MESDJasperReportViewerBundle:ReportViewer:reportViewer.html.twig'
            , array(
                'report' => $report,
                'hideHome' => $hideHome
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
    public function reportInfoAction($reportUri) {
        //Decode the report uri
        $decodedReportUri = urldecode($reportUri);

        //Get the input controls for this report
        $form = $this->container->get('mesd.jasperreport.client')->buildReportInputForm(
            $decodedReportUri, 'MESDJasperReportViewerBundle_report_form', array(
                'routeParameters' => array(
                    'reportUri' => $reportUri
                )
            )
        );

        //Render and return
        $response = new Response($this->container->get('templating')->render(
            'MESDJasperReportViewerBundle:ReportViewer:reportInfo.html.twig', array(
                'reportUri'        => $reportUri,
                'decodedReportUri' => $decodedReportUri,
                'form' => $form->createView()
                )
            )
        );
        return $response;
    }


    ////////////////////
    // JSON RESPONSES //
    ////////////////////


    /**
     * Runs a report
     *
     * @param  string  $reportUri The jasper server uri for the report 
     * @param  Request $request   The request from the submitted request input form
     *
     * @return JsonResponse       Json Response giving links to the output or what errors occured
     */
    public function executeAction($reportUri, Request $request) {
        //Create the array that will be used to create the json response
        $response = array('success' => true, 'output' => '');

        //Decode the report uri
        $decodedReportUri = urldecode($reportUri);

        //Get the form again
        $form = $this->container->get('mesd.jasperreport.client')->buildReportInputForm($decodedReportUri);

        //Process the form
        $form->handleRequest($request);

        //If any errors
        if (!$form->isValid()) {
            //Get form errors
            $errors = $form->getErrors();
            var_dump($errors); die;
        }

        //Build the report
        $rb = $this->container->get('mesd.jasperreport.client')->createReportBuilder($decodedReportUri);
        $rb->setInputParametersArray($form->getData());
        $rb->setFormat('html');
        $rb->setPage(1);

        //Run the report and get the request id back
        $requestId = $rb->runReport();

        //Return a route to the html output of the new report
        $response['output'] = $this->container->get('router')->generate('MESDJasperReportViewerBundle_display_html_report', array(
            'requestId' => $requestId,
            'page' => '1'
        ));

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
        $resources = $this->container->get('mesd.jasperreport.client')->getResourceList($folder, false);

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
                $data['a_attr'] = array('href' => $this->container->get('router')->generate('MESDJasperReportViewerBundle_report_info', array('reportUri' => urlencode($resource->getUriString()))));
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
    public function reportHistoryJsonAction($reportUri) {
        //Get the sent parameters from datatables
        $limit   = $this->container->get('request')->query->get('length');
        $offset  = $this->container->get('request')->query->get('start');

        //Create a new repsonse array that will be converted to json
        $response = array();
        
        //Get the history for the given report from the report history
        $records = $this->container->get('mesd.jasperreport.history')->loadHistoryForReport(
            urldecode($reportUri), true, array('limit' => $limit, 'offset' => $offset));

        //Get the count of records
        $count = $this->container->get('mesd.jasperreport.history')->loadHistoryForReport(
            urldecode($reportUri), true, array('count' => true));
        $response['recordsTotal'] = $count;
        $response['recordsFiltered'] = $count;

        //Convert the records to an array
        $response['data'] = array();
        foreach($records as $record) {
            //Create the links
            $links = array();
            foreach(json_decode($record->getFormats()) as $format) {
                //If html handle it differently, else export like usual
                if ('html' === $format) {
                    $href = $this->container->get('router')->generate('MESDJasperReportViewerBundle_display_html_report', array(
                            'requestId' => $record->getRequestId(),
                            'page' => 1
                        )
                    );
                } else {
                    $href = $this->container->get('router')->generate('mesd_jasperreport_export_cached_report', array(
                            'requestId' => $record->getRequestId(),
                            'format' => $format
                        )
                    );
                }
                $links[] = '<a href="' . $href . '">' . $format . '</a>';
            }

            //Convert to the format that datatables can read
            $response['data'][] = array(
                $record->getDate()->format('Y-m-d H:i:s'),
                $record->getUsername(),
                $record->getParameters(),
                implode('  ', $links)
            );
        }

        //Send back the Json
        return new JsonResponse($response);
    }
}