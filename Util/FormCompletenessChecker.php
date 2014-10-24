<?php

namespace Mesd\Jasper\ReportViewerBundle\Util;

use Symfony\Component\Form\Form;

class FormCompletenessChecker
{
    ////////////////////
    // STATIC METHODS //
    ////////////////////


    /**
     * Checks if the required fields of a form have values
     *
     * @param  Form    $form The form to check
     *
     * @return boolean       Whether the required fields are set
     */
    public static function isComplete(Form $form)
    {
        //Set the return value to true by default
        $ret = true;

        //Foreach child of the form
        foreach($form->all() as $child) {
            //If the child is required, check that its set
            if ($child->isRequired()) {
                if ($child->isEmpty()) {
                    $ret = false;
                    break;
                }
            }
        }

        //Return
        return $ret;
    }
}