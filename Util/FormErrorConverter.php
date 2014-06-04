<?php

namespace Mesd\Jasper\ReportViewerBundle\Util;

class FormErrorConverter
{
    /**
     * The Form Error Converter provides a set of static functions that take a form
     * and converts its errors into an easy to work with array
     */
    
    //////////////////////
    // STATIC FUNCTIONS //
    //////////////////////


    /**
     * Converts a form into an array of its error messages
     *
     * @param  SymfonyComponentFormForm $form The invalid form
     *
     * @return array                          The array of error messages in the following format
     *                                          (array('errors' => array(1 => 'oops'), 'children' => array('beginDate' => array())))
     */
    public static function convertToArray(\Symfony\Component\Form\Form $form) {
        //The array of errors messages
        $errors = array();
        $errors['errors'] = array();
        $errors['children'] = array();

        //Get the errors for this level
        foreach($form->getErrors() as $error) {
            $errors['errors'][] = $error->getMessage();
        }

        //Check the errors foreach of this level's children
        foreach($form->all() as $name => $child) {
            //If the child is an instance of \Symfony\Component\Form\Form
            if ($child instanceof \Symfony\Component\Form\Form) {
                //If the child has errors call the convertToArray
                $errors['children'][$name] = self::convertToArray($child);
            }
        }

        //Return the errors array
        return $errors;
    }
}