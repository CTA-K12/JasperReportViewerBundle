Overview
========
The report viewer serves as an additional layer on top of the Jasper Report Bundle and provides a visual interface for displaying and running reports, and viewing report history.  It requires the Jasper Report Bundle in order to work.

###Features
- Provides a list of all reports a user can view
- Provides an interface to handle report input
- Displays reports and ajax loads their pages
- UI for report history

###Todo
- [ ] Currently relies on a cdn for third party css and javascript, needs to be better integrated into the bundle
- [ ] Tests are seriously needed

Installation
============
###Install Report Bundle
The report viewer bundle requires the report bundle to have been set up in order to work.  If the report bundle is not yet installed, go [here](https://github.com/MESD/JasperReportBundle)

###Add to Composer
The first step is to add the bundle to the composer.json of the application.
```javascript
"repositories": [
    {
        "type" : "vcs",
        "url" : "https://github.com/MESD/JasperReportViewerBundle.git"
    }
],
"require": {
    "mesd/jasper-report-viewer-bundle": "1.0.0-alpha+001"
}
```

###Add to the AppKernel
In order for the application to load the bundle, it needs to be registered in the AppKernel.
```php
public function registerBundles() {
    $bundles = array(
        ...
        new Mesd\Jasper\ReportViewerBundle\MesdJasperReportViewerBundle(),
    )
}
```

###Add To Assetic
To have assets work, the bundle has to be registered with assetic.  This is done in the config file where the rest of assetic is setup.  Example:
```yaml
assetic:
    ...
    bundles:
        - MesdJasperReportViewerBundle
```

###Import the Routing File
The final step is to include the report viewer's routing file in the main application's routing file.  Look at the example below:
```yaml
MESDJasperReportViewerBundle:
    resource: "@MesdJasperReportViewerBundle/Resources/config/routing.yml"
    prefix: /reportviewer
```

Usage
=====
To add links to the report home and report viewer use the following twig functions
```twig
{# report home link #}
{{ mesd_jasper_reportviewer_home('Link Text') }}
 
{# report viewer link #}
{{ mesd_jasper_reportviewer_report_link('/reports/uri_of_report', 'Link Text') }}
 
{# stored report link #}
{{ mesd_jasper_reportviewer_stored_report_link('/reports/uri_of_report', 'requestId', 'Link Text') }}
``` 

These routes may also be used in controllers:
```PHP
    public function indexAction( Request $request ) {
        $sc = $this->get('security.context');
        if ( $sc->isGranted('ROLE_REPORT') ) {
            return $this->redirect($this->generateUrl('MesdJasperReportViewerBundle_home'));
        } 
    }
```
or:
```PHP
    public function indexAction( Request $request ) {
        $sc = $this->get('security.context');
        if ( $sc->isGranted('ROLE_REPORT') ) {
            return $this->redirect($this->generateUrl(
                'MesdJasperReportViewerBundle_home',
                array('openInNewTab' => true ) ) 
            );
        } 
    }
```



A further look at each function:
- mesd_jasper_reportviewer_home(linkText, classes = ' ', openInNewTab = true)
  - linkText => The text to display on the link
  - classes => classes to apply to the anchor tag
  - openInNewTab => whether to open the link in a new tab/window or not
- mesd_jasper_reportviewer_report_link(reportUri, linkText, classes = ' ', openInNewTab = true, hideHome = true)
  - reportUri => The uri of the report to open the report viewer to on the Jasper Server
  - linkText => The text display on the link
  - classes => classes to apply to the anchor tag
  - openInNewTab => whether to open the link in a new tab/window or not
  - hideHome => Whether to display navbar links to home and history
- mesd_jasper_reportviewer_stored_report_link(reportUri, requestId, linkText, classes = ' ', openInNewTab = true, hideHome = true)
  - reportUri => The uri of the report to open the report viewer to on the Jasper Server
  - requestId => The request Id of the stored report
  - linkText => The text display on the link
  - classes => classes to apply to the anchor tag
  - openInNewTab => whether to open the link in a new tab/window or not
  - hideHome => Whether to display navbar links to home and history

API Documentation
=================
Generated documentation exists in the bundle under the docs directory.

License
========
This project is licensed under the MIT license.  See the [LICENSE.md](LICENSE.md) file for more information.
